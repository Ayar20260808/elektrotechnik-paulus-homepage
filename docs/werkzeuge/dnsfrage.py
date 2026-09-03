#!/usr/bin/env python3
"""DNS-Eintraege von elektrotechnik-paulus.de pruefen.

Warum selbstgebaut: Im Sitzungs-Container gibt es kein `dig`, und die
DNS-ueber-HTTPS-Dienste (dns.google, cloudflare-dns.com) sperrt die
Egress-Richtlinie mit 403. Eine rohe UDP-Abfrage auf Port 53 geht dagegen --
auch direkt an einen autoritativen Server, was Zwischenspeicher ausschliesst.

    python3 dnsfrage.py                    # ueber den lokalen Resolver
    python3 dnsfrage.py --autoritativ      # direkt bei den Nameservern
"""
import socket, struct, random, sys, collections, time

ZIEL = 'elektrotechnik-paulus.de'
TYPEN = {'A': 1, 'NS': 2, 'CNAME': 5, 'SOA': 6, 'MX': 15, 'TXT': 16}


def resolver():
    try:
        for zeile in open('/etc/resolv.conf'):
            if zeile.startswith('nameserver'):
                return zeile.split()[1]
    except OSError:
        pass
    return '8.8.8.8'


def frage(name, typ, server=None):
    kopf = struct.pack('>HHHHHH', random.randint(0, 65535), 0x0100, 1, 0, 0, 0)
    q = b''.join(bytes([len(t)]) + t.encode() for t in name.split('.')) + b'\x00'
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.settimeout(8)
    s.sendto(kopf + q + struct.pack('>HH', TYPEN[typ], 1), (server or resolver(), 53))
    daten, _ = s.recvfrom(4096)
    s.close()
    return daten


def name_lesen(d, i):
    teile = []
    while True:
        laenge = d[i]
        if laenge == 0:
            i += 1
            break
        if laenge & 0xC0 == 0xC0:
            teile.append(name_lesen(d, struct.unpack('>H', d[i:i+2])[0] & 0x3FFF)[0])
            i += 2
            break
        teile.append(d[i+1:i+1+laenge].decode('latin1'))
        i += 1 + laenge
    return '.'.join(teile), i


def antworten(d):
    """Liefert (Abschnitt, Typnummer, TTL, Text) je Eintrag."""
    anzahl = struct.unpack('>HHH', d[6:12])
    i = 12
    _, i = name_lesen(d, i)
    i += 4
    aus = []
    for abschnitt, n in zip(('ANTWORT', 'AUTORITAET', 'ZUSATZ'), anzahl):
        for _ in range(n):
            _, i = name_lesen(d, i)
            typ, _klasse, ttl, laenge = struct.unpack('>HHIH', d[i:i+10])
            i += 10
            wert = d[i:i+laenge]
            if typ == 1:
                text = '.'.join(str(b) for b in wert)
            elif typ == 15:
                text = f'Prio {struct.unpack(">H", wert[:2])[0]} ' + name_lesen(d, i+2)[0]
            elif typ == 16:
                text = wert[1:1+wert[0]].decode('latin1')
            elif typ in (2, 5, 6):
                text = name_lesen(d, i)[0]
            else:
                text = wert.hex()[:40]
            aus.append((abschnitt, typ, ttl, text))
            i += laenge
    return aus


def ueber_resolver():
    """Mehrfach messen -- waehrend einer Umstellung schwanken die Antworten."""
    for typ in ('NS', 'MX', 'TXT', 'A'):
        zaehler = collections.Counter()
        for _ in range(6):
            try:
                zaehler[tuple(sorted(w for ab, t, ttl, w in antworten(frage(ZIEL, typ))
                                     if ab == 'ANTWORT'))] += 1
            except Exception as fehler:
                zaehler[('FEHLER', str(fehler))] += 1
            time.sleep(0.3)
        print(f'===== {typ} =====')
        for werte, n in zaehler.most_common():
            print(f'  {n}x  {" | ".join(werte) if werte else "(leer)"}')


def autoritativ():
    """Direkt bei den Nameservern -- so sieht man die Zone ohne Zwischenspeicher.

    Die Kontrollabfrage beweist, dass die Antwort wirklich von dort kommt:
    ein erfundener Name muss NXDOMAIN (Antwortcode 3) ergeben.
    """
    namen = sorted({w for ab, t, ttl, w in antworten(frage(ZIEL, 'NS')) if t == 2})
    if not namen:
        print('Keine Nameserver gefunden.')
        return
    for name in namen:
        try:
            adresse = socket.gethostbyname(name)
        except OSError as fehler:
            print(f'--- {name}: nicht aufloesbar ({fehler}) ---')
            continue
        print(f'--- {name} ({adresse}) ---')
        for typ in ('MX', 'TXT', 'A'):
            werte = [w for ab, t, ttl, w in antworten(frage(ZIEL, typ, adresse))
                     if ab == 'ANTWORT']
            for w in sorted(werte):
                print(f'   {typ:4} {w}')
            if not werte:
                print(f'   {typ:4} (keine Antwort)')
        rcode = frage('gibtesnicht-kontrolle.' + ZIEL, 'A', adresse)[3] & 0x0F
        print(f'   Kontrolle: Antwortcode {rcode} '
              f'({"autoritativ erreicht" if rcode == 3 else "VERDAECHTIG, evtl. Zwischenspeicher"})')
        print()


if __name__ == '__main__':
    if '--autoritativ' in sys.argv:
        autoritativ()
    else:
        ueber_resolver()
