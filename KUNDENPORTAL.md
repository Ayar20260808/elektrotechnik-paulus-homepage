# Kundenportal

Stand: 19.09.2026

Ein Kunde bekommt von euch einen Link. Er klickt ihn an und sieht **genau
einen Auftrag** — und davon nur das, was ihr vorher angehakt habt. Kein Konto,
kein Passwort, keine Registrierung.

---

## 1. Wie ihr es benutzt

1. `portal-verwaltung.html` aufrufen und mit eurem Supabase-Konto anmelden.
2. Auftrag suchen (Nummer, Leistung, Ort oder Kundenname).
3. Auf **Freigeben** klicken und ankreuzen, was der Kunde sehen darf.
4. **Link kopieren** und dem Kunden schicken — per WhatsApp, E-Mail, egal.

Fertig. Ihr seht später in der Liste, ob und wie oft er den Link geöffnet hat.
Mit **Zugang abschalten** ist der Link sofort tot; **Wieder einschalten** holt
ihn zurück. Dieselbe Adresse funktioniert danach wieder.

### Die sechs Schalter

| Schalter | Was der Kunde sieht | Voreinstellung |
|---|---|---|
| Was gemacht wird | Die Leistungsbeschreibung | **an** |
| Stand der Arbeiten | Fortschritt in fünf Stufen | **an** |
| Einsatzort | Die Adresse der Baustelle | **an** |
| Termine | Nur Titel und Zeit, keine Mitarbeiter | aus |
| Unterlagen | Angebote, Rechnungen, Lieferscheine | aus |
| Beträge | Geldsummen zu den Unterlagen | aus |

Alles, was heikel ist, ist **von Haus aus aus**. Ihr müsst es bewusst
einschalten — nicht bewusst abschalten. Das ist Absicht: Wer einen Schalter
übersieht, gibt dadurch zu wenig frei statt zu viel.

---

## 2. Warum es ohne Kundenkonten läuft

Das ist die wichtigste Entscheidung im ganzen Aufbau, deshalb ausführlich.

In eurer Datenbank steht für die Tabellen `Auftraege`, `Kontakte`,
`Kundendokumente` und `Termine` diese Zugriffsregel:

```
SELECT erlaubt für Rolle "authenticated"  →  Bedingung: true
```

`true` heißt: **keine Einschränkung**. Jeder, der in dieser Datenbank
eingeloggt ist, darf alle Zeilen lesen.

Für euch ist das in Ordnung — es gibt genau ein Konto, eures. Aber wenn ein
Kunde ein Konto bekäme, wäre er ebenfalls „authenticated" und dürfte damit
**alle 1.201 Kontakte, 218 Aufträge und 1.334 Dokumente** lesen. Auch die
eurer anderen Kunden.

Deshalb bekommt kein Kunde ein Konto. Der Weg sieht so aus:

```
Kunde ──► kundenportal.html ──► Edge Function "kundenportal" ──► Datenbank
          (kein Schlüssel)      (Service-Schlüssel,
                                 entscheidet, was rausgeht)
```

Der Kunde spricht nie mit der Datenbank. Er schickt nur seinen Token an die
Funktion. Die Funktion schaut nach, was freigegeben ist, und stellt die
Antwort zusammen. **Die gesamte Sicherheit steckt in einer einzigen Datei:**
`supabase/functions/kundenportal/index.ts`.

Nebenwirkung, die euch nützt: Die Kundenseite braucht keinen einzigen
Zugangsschlüssel im Quelltext.

---

## 3. Was die Funktion niemals herausgibt

### Zwei Positivlisten für Dokumente

Ein Dokument geht nur raus, wenn **beides** stimmt: Art *und* Bearbeitungsstand.

**Erlaubte Arten:** Angebot, Auftragsbestätigung, Rechnung, Teilrechnung,
Stornorechnung, Lieferschein, Aufmaßdokument.

**Gesperrte Arten** — und warum:

| Art | Anzahl | Warum gesperrt |
|---|---|---|
| Kalkulation | 90 | Eure Einkaufspreise und Marge |
| Lohnzeitenliste | 105 | Lohndaten eurer Mitarbeiter |
| Bestellschein | 17 | Eure Lieferanten und Konditionen |
| Allgemein, Brief | 79 | Inhalt unbekannt |
| Mahnung, Zahlungserinnerung | 10 | Gehört in eure Hand, nicht still ins Portal |

**Erlaubte Stände:** Erstellt, Erstellt (Hochgeladen), Versendet, Storniert.

**Gesperrte Stände:** *Gelöscht* (495 Stück!) und *Entwurf* (33). Eine
gelöschte Rechnung ist immer noch eine Rechnung — die Art allein reicht als
Prüfung also nicht.

> Diese zweite Liste ist beim Testen mit echten Daten aufgefallen. Beim ersten
> Durchlauf wurden 40 Dokumente ausgeliefert, davon viele gelöschte. Jetzt
> sind es 15. Deshalb testet man nicht mit ausgedachten Daten.

Beide Listen sind **Positivlisten**: Was nicht ausdrücklich erlaubt ist,
bleibt draußen. Kommt in Hero eine neue Dokumentart dazu, ist sie automatisch
gesperrt, bis jemand sie bewusst freigibt. Eine Sperrliste hätte den
umgekehrten, gefährlichen Fehler.

### Felder, die gar nicht erst geholt werden

Die Funktion fragt bestimmte Spalten schon in der Datenbankabfrage nicht ab.
Was nicht geholt wird, kann auch nicht versehentlich durchrutschen:

- `Auftraege.notizen` — eure internen Notizen
- `Auftraege.projekttitel` — Platzhalter wie `-348 | --, --, --`
- `Auftraege.mitarbeiter` — wer bei euch daran arbeitet
- `Termine.beschreibung`, `.mitarbeiter`, `.ressourcen` — Einsatzplanung
- Vom Kontakt nur der **Name**, nie Telefon, E-Mail, Adresse oder Notizen

### Interne Wörter werden übersetzt

Eure Pipelinestufen sagen dem Kunden nichts und verraten unnötig etwas über
den Innenbetrieb:

| Bei euch | Beim Kunden | Stufe |
|---|---|---|
| Detailgespräch | In Planung | 1 von 5 |
| Umsetzungsbeginn | Start steht bevor | 2 von 5 |
| In Umsetzung | In Arbeit | 3 von 5 |
| Kundenrechnung | Arbeiten fertig, Rechnung unterwegs | 4 von 5 |
| Abgeschlossen | Abgeschlossen | 5 von 5 |

---

## 4. Der Link

```
https://www.elektrotechnik-paulus.de/kundenportal.html?code=1e5a95a80a634645b15acc3f32758102
```

Der Teil hinter `code=` sind 32 Zeichen aus dem kryptographischen
Zufallsgenerator der Datenbank — 122 Bit. Das lässt sich nicht erraten: Wer
pro Sekunde eine Milliarde Tokens durchprobiert, braucht dafür länger, als das
Universum alt ist.

**Wer den Link hat, kommt rein.** Das ist derselbe Handel wie bei einem
Google-Docs-Freigabelink: bequem, dafür weitergebbar. Das ist hier vertretbar,
weil der Link immer nur einen einzigen Auftrag öffnet und ihr ihn jederzeit
abschalten könnt.

Zwei Vorkehrungen dagegen, dass der Link ungewollt hinausgerät:

- `kundenportal.html` trägt `noindex, nofollow, noarchive` — Suchmaschinen
  dürfen die Seite nicht aufnehmen.
- `referrer: no-referrer` — klickt der Kunde auf der Seite einen Link nach
  draußen, erfährt die Zielseite die Adresse mit dem Token nicht.

Wenn ein Link einmal falsch verschickt wurde: in der Verwaltung **Zugang
abschalten**, neu freigeben. Der neue Link hat einen neuen Token.

---

## 5. Die Dateien

| Datei | Wofür |
|---|---|
| `kundenportal.html` | Was der Kunde sieht. Kein Schlüssel im Quelltext. |
| `portal-verwaltung.html` | Eure Verwaltung. Anmeldung nötig, nur Rolle `inhaber`. |
| `supabase/functions/kundenportal/index.ts` | Die Funktion dazwischen. **Hier steckt die Sicherheit.** |

Tabelle `portal_freigaben` in Supabase: eine Zeile je Freigabe. Lesen und
Schreiben darf nur die Rolle `inhaber`; für nicht angemeldete Aufrufer gibt es
bewusst gar keine Regel. Die Funktion kommt mit dem Service-Schlüssel daran
vorbei — genau dafür ist sie da.

Beide Seiten kommen ohne Framework und ohne npm aus, wie der Rest des
Projekts.

---

## 6. Was noch offen ist

### Dringend: Die Selbstregistrierung ist offen

Beim Bauen getestet und bestätigt: Über `/auth/v1/signup` kann sich **jeder
ein Konto in eurer Datenbank anlegen**. Eine E-Mail-Bestätigung ist nötig,
aber die kann jeder mit seiner eigenen Adresse erledigen. Danach ist er
„authenticated" — und die `true`-Regel aus Abschnitt 2 gibt ihm alle Kontakte,
Aufträge und Dokumente.

Das betrifft nicht das Portal, sondern eure ganze Datenbank, und es gilt
unabhängig davon, ob das Portal existiert.

**Der einfache Schnitt** (eine Minute, kein Risiko): In Supabase unter
*Authentication → Sign In / Providers → Email* den Schalter **Allow new users
to sign up** ausschalten. Ihr braucht ihn nicht — es meldet sich ohnehin nur
ein Konto an.

**Die gründliche Reparatur** (empfohlen, aber vorher besprechen): Die
Leseregeln von `true` auf „nur Inhaber" umstellen.

```sql
-- NICHT ungeprüft ausführen. Danach kommt nur noch an diese Tabellen,
-- wer in user_roles die Rolle "inhaber" hat. Aktuell trifft das auf das
-- einzige vorhandene Konto zu -- aber prüft erst, ob eure interne App
-- noch auf anderem Weg liest.
alter policy "Authenticated users can read Auftraege"       on public."Auftraege"
  using (public.has_role(auth.uid(), 'inhaber'::public.app_role));
alter policy "Authenticated users can read Kontakte"        on public."Kontakte"
  using (public.has_role(auth.uid(), 'inhaber'::public.app_role));
alter policy "Authenticated users can read Kundendokumente" on public."Kundendokumente"
  using (public.has_role(auth.uid(), 'inhaber'::public.app_role));
alter policy "Authenticated users can read Termine"         on public."Termine"
  using (public.has_role(auth.uid(), 'inhaber'::public.app_role));
```

### Außerdem offen

| Punkt | Stand |
|---|---|
| Tabelle `steuern_abgaben` | Zugriffsschutz ganz abgeschaltet, 13 Zeilen offen lesbar. Braucht Regeln, nicht nur ein Einschalten. |
| Dokumente herunterladen | Das Portal listet Unterlagen auf, aber es gibt keine PDF zum Öffnen. Die Dateien liegen in Hero, nicht bei euch. |
| Ablaufdatum | Die Spalte `gueltig_bis` ist da und die Funktion wertet sie aus, die Verwaltung hat aber noch kein Feld dafür. |
| Termine im Portal | Nur 15 der 218 Aufträge haben überhaupt verknüpfte Termine. Der Schalter nützt bis dahin wenig. |
| E-Mail-Versand | Den Link verschickt ihr bisher von Hand. |

---

## 7. So wurde geprüft

**Datenbank und Funktion** — die Datenbank ruft ihre eigene Funktion über
`pg_net` auf, mit echten Daten:

- Gültiger Token, alles freigegeben → 200, richtige Daten
- Gültiger Token, sparsame Freigabe → 200, `termine: []`, `dokumente: []`
- Unbekannter Token → 404
- Token aus Buchstaben, Liste statt Text, gar kein Token → jeweils 404,
  immer mit **derselben** Meldung, damit niemand herauslesen kann, ob er
  einen echten Token erwischt hat
- Auftrag ELEK-285 (53 Dokumente, davon 13 gesperrter Art): 15 ausgeliefert,
  **0 gesperrte Arten, 0 gelöschte, 0 Entwürfe**

**Die Seiten im Browser** (Playwright, Chromium) bei 375, 600, 768, 900, 1024,
1200, 1440 und 1920 px. Der Aufruf zu Supabase wird abgefangen und durch die
echte Antwort der veröffentlichten Funktion ersetzt.

- Kundenseite: 8 Breiten, überall 0 px waagerechter Überlauf, keine JS-Fehler.
  Fehlerseite zeigt keinerlei Auftragsdaten.
- Verwaltung: 39 Prüfungen, alle bestanden — Anmeldung, Abweisung bei falschem
  Passwort, Sperre für Konten ohne Inhaberrolle, Suche, Filter, Anlegen,
  Link kopieren, Abschalten, 5 Breiten ohne Überlauf.

Die Testskripte liegen nicht im Repository; sie stehen oben in der Sitzung.
