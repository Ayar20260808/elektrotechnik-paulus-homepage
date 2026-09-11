# Arbeitsstand — Homepage Elektrotechnik Paulus

Stand: 10.09.2026 · Branch `claude/home-page-rdyw91`
**Zweiter Name fuer denselben Stand:** `claude/gracious-einstein-kd9y44`
(am 10.09.2026 vorgespult, beide zeigen auf denselben Commit)
Aktueller Commit: `git log --oneline -1` fragen, nicht hier nachschlagen —
eine fest eingetragene Nummer veraltet mit dem naechsten Commit.

**Diese Datei ist die einzige Quelle der Wahrheit.** Sie ersetzt die früheren
`PROJEKTSTAND.md` und `CHAT-ZUSAMMENFASSUNG.md`, die drei verschiedene und
teilweise falsche Stände behauptet haben.

Eine KI-Sitzung hat kein Gedächtnis. Was hier nicht steht, ist beim nächsten
Mal weg. Deshalb: **nach jedem erledigten Punkt hier zwei Zeilen nachtragen und
committen** — nicht erst am Ende der Sitzung. Sitzungen enden abrupt, und wer
erst am Schluss schreibt, verliert den Schluss.

---

## 1. Wo alles liegt

| | |
|---|---|
| Repository | `Ayar20260808/elektrotechnik-paulus-homepage` |
| Arbeitsbranch | `claude/home-page-rdyw91` — **gleichwertig:** `claude/gracious-einstein-kd9y44`, seit 10.09.2026 derselbe Commit |
| Vorschau (dieser Branch) | `https://ayar20260808.github.io/elektrotechnik-paulus-homepage/vorschau/claude-home-page-rdyw91/` |
| Fertige Seite (`master`) | `https://ayar20260808.github.io/elektrotechnik-paulus-homepage/` |
| Hostinger-Testadresse | `https://magenta-crocodile-313036.hostingersite.com/` |
| Zieladresse | `https://elektrotechnik-paulus.de` — **seit 03.09.2026 bei Hostinger**, Domain dort registriert |
| Uebergabe an eine neue Sitzung | `docs/UEBERGABE.md` — fertiger Prompt zum Kopieren |

`master` steht bei `250809a` und hat **keine eigenen Commits**; der
Arbeitsbranch liegt 198 Commits davor (nachgezaehlt am 10.09.2026 mit
`git rev-list --count origin/master..HEAD`). Ein Vorspulen waere also sauber
moeglich. Ob und wann gemergt wird, ist offen (siehe Abschnitt 5).

---

## 2. Aufbau

Statisches HTML/CSS/JS. Kein Framework, kein Build-Schritt, keine
Abhaengigkeiten.

Zehn Seiten: `index.html` plus neun `leistung-*.html`. **CSS und JavaScript
stehen inline in jeder einzelnen Datei und sind ueber alle zehn dupliziert.**
Jede Aenderung an Kopf, Fusszeile, Menue oder an einem gemeinsamen Skript muss
in alle zehn — und danach nachgewiesen werden, dass sie ueberall bytegleich
angekommen ist.

Dazu:

| Datei / Ordner | Inhalt |
|---|---|
| `kontakt.php` | Eigener Mailversand per SMTP, ohne Composer |
| `kontakt-konfig-beispiel.php` | Vorlage fuer die Zugangsdaten |
| `marke.css` | Farben `#232F3B` / `#FFD700`, laedt IBM Plex Sans lokal aus `schriften/` |
| `skyline-koeln.svg` | Koelner Silhouette, als Maske in Kopf und Fusszeile |
| `elektrosymbole/` | 169 PNG-Symbole plus `manifest.json` |
| `schriften/` | Drei woff2-Dateien plus `LIZENZ.txt` |
| `png/` | Zwei grosse Logo-Fassungen fuer Vorschaubilder |

### Abschnitte der Startseite

Wechselt bewusst zwischen dunkel und hell: Hero (dunkel) · Vertrauensleiste
(hell) · Leistungen (dunkel, Planhintergrund mit Symbolanimation) · Marken
(weiss, Laufband) · Unsere Kunden (dunkel) · Aus dem Arbeitsalltag (weiss,
Bilderband) · Ablauf (hellgrau) · Referenzen (weiss) · Bewertungen (hellgrau) ·
Ansprechpartner (weiss) · Kontakt (hellgrau).

---

## 3. Die beweglichen Teile

### 3.1 Hero-Bilderfolge

**Sechs** Bildebenen uebereinander, sichtbar ist immer genau eine. Alle vier
Sekunden wechselt die aktive Ebene, Ueberblendung 1,1 s, dazu ein sehr
langsamer Zoom ueber 90 s. Bildebene Deckkraft `.78`. Ein voller Umlauf dauert
damit 24 Sekunden.

Reihenfolge (Stand 04.09.2026): `hero-0` · `hero-echeck` · `hero-eauto` ·
`hero-solar` · `waermepumpe-hero` · `hero-4`.

**`data-deckung` und `data-quer` sind keine Geschmackswerte.** Sie steuern, wie
stark der dunkle Verlauf ueber dem Textband deckt -- je heller das Motiv, desto
mehr braucht die Schrift. Statt zu raten laesst sich das messen: Bild im
Browser auf ein Canvas zeichnen, im Bereich 20-78 % der Hoehe die mittlere
Helligkeit und den Anteil heller Pixel (Luminanz > 180) bestimmen, dann gegen
die bekannten Werte einordnen. Gemessen am 04.09.2026:

    hero-solar          89,5    3,4 % hell    0.68 / 0.66
    hero-4             111,7   26,9 %         0.74 / 0.70
    hero-eauto         113,4   22,8 %         0.71 / 0.66
    hero-0             113,5    9,9 %         0.68 / 0.66
    waermepumpe-hero   134,0   25,3 %         0.74 / 0.70   <- daraus abgeleitet
    hero-echeck        240,7   92,5 %         0.74 / 0.74

Der helle Flaechenanteil sagt mehr als der Mittelwert: `hero-0` und
`hero-eauto` haben fast dieselbe mittlere Helligkeit, aber unterschiedliche
Werte -- der Unterschied liegt im Anteil heller Flaechen.

Unter 1000 px laeuft der Verlauf **senkrecht**, weil der Text dort ueber die
volle Breite geht und sonst ueber der hellen rechten Seite stuende. Vorher lag
der Kontrast am Handy bei 2,3 : 1.

Die Deckung ist inzwischen pro Foto gemessen: der Verlauf deckt nur noch das
Textband ab und laeuft oben und unten auf 0,30 aus.

### 3.2 Symbolanimation (Leistungen, Kunden, Kontakt)

Ein Modul, mehrfach aufgerufen:

```js
symbolFlaeche({ bg, karten, kopf, raster })
```

Auf `index.html` zweimal (`#leistungen`, `#kontakt`), auf jeder
`leistung-*.html` einmal (`#angebot`). Jede Flaeche laeuft unabhaengig.

- **Plaetze:** Best-Candidate-Sampling — pro Punkt 300 Kandidaten wuerfeln, den
  am weitesten entfernten nehmen. Gleichmaessig ohne Raster.
- **Frei bleiben** Karten und Ueberschriften. Bei der Ueberschrift wird die
  tatsaechliche Textbreite je Zeile gemessen (`Range.getClientRects()`), nicht
  der volle Block.
- **Groesse schwankt:** bei 1024–1200 px sind die Fugen zu schmal fuer 54 px.
  Wird schrittweise reduziert 54 → 46 → 40 → 34 px.
- **Ohne Wiederholung:** Ziehen ohne Zuruecklegen aus einem gemischten Stapel
  aller 169 Indizes.
- **Lebenszyklus je Platz:** 1400 ms einblenden, 1500 ms stehen, 220 ms
  ausblenden, 600 ms Pause. Startversatz gestreut, damit nichts im Gleichtakt
  blinkt.
- **Verzoegerte Messung:** Ein IntersectionObserver wartet die Einfahrt der
  Karten ab und misst erst 850 ms spaeter. Sonst landen Symbole an der noch
  nicht fertigen Kartenposition und liegen danach auf der Karte.

### 3.3 Leistungskarte beim Ueberfahren

Waechst um 20 Prozent (`SCALE = 1.2`), zeigt das Foto vollstaendig, klappt
sechs Detailpunkte aus.

- **Schrift wird zurueckgerechnet** — Schriftgroessen und Abstaende durch
  denselben Faktor geteilt, sonst zoege die Skalierung den Text mit auf.
- **Rasterhoehe eingefroren** in `--ep-hoehe`, dazu `contain:size`. Ohne das
  gab die Zeile bei 1024 px um 28 px nach.
- **Auswahl folgt der Ruheposition**, nicht dem Element unter dem Zeiger.
  Vorher hielt das verlaengerte Feld den Zeiger fest.
- **Aus unter 768 px**, bei einspaltigem Raster und bei abgestellter Bewegung.

### 3.4 Bilderband „Aus dem Arbeitsalltag"

13 verschiedene Bilder, **jedes zweimal eingebunden** (das Band wird
verdoppelt, damit es nahtlos umlaeuft) — 26 Bildplaetze. Laufzeit steht in
`.marquee-track.slow{animation-duration:140s}`.

Das Band wurde auf Wunsch um ein Fuenftel hoeher gemacht, die Laufzeit blieb
gleich — dadurch laeuft es **19,7 % schneller** als vorher. 168 s wuerden das
alte Tempo wiederherstellen. **Ungeklaert, ob das gewuenscht ist** (siehe
Abschnitt 5).

---

## 4. Getroffene Entscheidungen

Damit sie nicht in jeder Sitzung neu erfragt werden.

| Datum | Entscheidung | Begruendung |
|---|---|---|
| 02.09.2026 | Bei „Bewegung reduzieren" verschwinden die Hintergrundsymbole **vollstaendig** (nicht: ruhig stehenbleiben) | Barrierefreiheit — wer Bewegung abschaltet, will die dekorative Ebene nicht. Umgesetzt in `67a8b97` |
| 02.09.2026 | Bei der Geraetepruefung faellt der Zusatz „auf Wunsch" **ueberall** weg | Entscheidung Irfan. `index.html` (Leistungskarte) und `leistung-vde.html` (Checkliste) in `feb59a3`, die FAQ derselben Seite nachgezogen. Die uebrigen acht „auf Wunsch" im Projekt betreffen andere Themen (PV-Kopplung, App, Tueroeffner, Foerderhinweis) und bleiben |
| 02.09.2026 | Homepage wird **nicht** nach `master` gemergt, solange nicht ausdruecklich gewuenscht | Entscheidung Irfan, offen gelassen |
| frueher | Pflichtfelder im Formular sind **nur Vorname und Nachname** | So gewuenscht. Folge: eine Anfrage kann ohne Rueckmeldeweg ankommen, `Reply-To` entfaellt dann — ist so gebaut und geprueft |
| frueher | Standort im Kontaktblock heisst **„Koeln"**, nicht „Koeln Nippes" | Gekuerzt auf Wunsch |
| frueher | Alle Knopfbeschriftungen heissen **„Angebot anfragen"** (53 Stueck) | Vereinheitlicht auf Wunsch |
| frueher | Kein Formspree mehr, eigener Versand ueber `kontakt.php` | Unabhaengigkeit, keine Drittdaten |
| frueher | `hero-5.jpg` (Foto des Inhabers) ist aus der Hero-Folge genommen | Auf Wunsch |

---

## 5. Was offen ist

### Braucht eine Entscheidung von Irfan

1. ~~**Bilderband-Tempo.**~~ **Am 04.09.2026 ausgemessen und erledigt — es
   bleibt bei 140 s.** Im Browser gemessen, alle 26 Bilder geladen:

       Fenster  390 px   Bild 106x53   Gruppe 1399 px   10,0 px/s
       Fenster 1280 px   Bild 132x66   Gruppe 1742 px   12,4 px/s
       Fenster 1920 px   Bild 156x78   Gruppe 2054 px   14,7 px/s

   Uebliche Laufbaender liegen bei 30 bis 80 px/s. Die Frage „laeuft es zu
   schnell" beantwortet sich damit von selbst: Es laeuft sehr langsam. 168 s
   wuerden auf 8 bis 12 px/s bremsen, dann wirkt es stillstehend. Der
   CSS-Kommentar nannte falsche Werte (2650 px, 19 px/s) und ist korrigiert.

   **Messfalle dabei:** `--header-hoehe` steht im CSS nur als Rueckfallwert
   139px, gesetzt wird sie per JavaScript auf 82 bis 103 px. Wer mit 139
   rechnet, kommt auf ein Drittel zu viel. Und die Bandbilder sind `lazy` —
   ohne Scrollen zum Band misst man Breite 0.
2. ~~**Herkunft der Koelner Silhouette.**~~ **Geklaert am 04.09.2026: von
   Irfan selbst erstellt.** Damit liegen die Rechte beim Betrieb, es braucht
   keine Lizenz und niemand kann nachfordern. Nicht erneut aufwerfen.
3. **Datenschutztext zum Kontaktformular.** Der Abschnitt nennt keinen
   Dienstleister. Ob der Versand ueber Google Workspace genannt werden muss,
   ist eine Rechtsfrage — **nicht eigenmaechtig aendern.**
4. **`master`-Frage.** 163 Commits liegen davor. Vorspulen waere sauber, ist
   aber nicht entschieden.

### Kann ohne Rueckfrage gemacht werden

5. **Zweite Domain** `elektropersonal-aya…` haengt am selben Wix-Konto —
   Entscheidung ueber ihr Schicksal offen.
6. **Jimdo pruefen** (die alte Domain lag frueher dort).
7. **Formspree-Konto abschalten** — wird nicht mehr benutzt.

---

## 6. Umzug nach Hostinger

**Erledigt:** Konto und PHP-Website angelegt, Seite hochgeladen, auf der
Testadresse geprueft.

### Reihenfolge — daran haengt die Domain

| # | Schritt | Wer |
|---|---|---|
| 1 | Aktuelles Paket in `public_html` hochladen | **erledigt 02.09.2026** |
| 2 | Google-App-Passwort erzeugen | **erledigt 02.09.2026** |
| 3 | `kontakt-konfig.php` **ueber** `public_html` anlegen | **erledigt 02.09.2026** |
| 4 | Testanfrage abschicken, Ankunft bei `info@…` pruefen | **erledigt 02.09.2026 — Mail kam an** |
| 5 | DNS bei Wix: A-Record auf `92.113.18.111`, `www` als CNAME | **erledigt 03.09.2026** |
| 6 | AuthInfo-Code bei Wix holen, Domain uebertragen | **erledigt 03.09.2026** |
| 7 | Wix kuendigen | Irfan |

**Zu Schritt 1 (erledigt am 02.09.2026):** 246 Dateien als ZIP hochgeladen und
im hPanel-Dateimanager entpackt. Das Extract-Fenster setzt das Ziel aus
*Choose folder name* + *Select the destination* zusammen. Richtig ist deshalb:
im Zielwaehler einmal auf `..` (Ziel wird
`/files/domains/elektrotechnik-paulus.de/`), als Ordnernamen `public_html`
eintippen, *Overwrite existing files* anhaken. **Der Dateimanager aktualisiert
die Liste danach nicht von selbst** — es sieht aus, als sei nichts passiert.
Erst F5 zeigt das Ergebnis.

Nachgewiesen ueber vorausberechnete Dateigroessen: `index.html` 125,05 →
**132,46 KiB**, `kontakt.php` 9,67 → **10,09 KiB**, beide auf die Stelle genau
wie erwartet. Diese Methode ersetzt die unzuverlaessige Speicheranzeige.

**Aufgeraeumt am 03.09.2026:** Die zwei oeffentlich abrufbaren ZIP-Dateien in
`public_html` — `homepagehostinger.zip` und `elektrotechnikpauluswebsite.zip` —
sind geloescht. Merkregel fuer kuenftige Uploads: **das Upload-ZIP nach dem
Entpacken sofort loeschen**, es liegt sonst offen im Netz.

**Stand Kontaktformular (02.09.2026).** `kontakt-konfig.php` liegt richtig unter
`/files/domains/elektrotechnik-paulus.de/` und **wird gefunden** — nachgewiesen
dadurch, dass ein direkter Aufruf von `kontakt.php` auf die Startseite mit dem
roten Fehlerkasten umleitet statt mit HTTP 500 abzubrechen. Der Versand
scheitert erst an der Anmeldung bei Google.

**Das Formular laeuft seit 02.09.2026.** Der Weg dorthin, damit ihn niemand
nochmal suchen muss:

Google prueft Benutzername und Passwort **als Paar**. In der Konfiguration
steht `info@elektrotechnik-paulus.de` als `smtp_benutzer`, also muss das
App-Passwort auch aus **diesem** Konto stammen. Das erste stammte aus
`ayar@elektrotechnik-paulus.de` und wurde deshalb abgelehnt.

Fuer `info@` waren App-Passwoerter zunaechst gar nicht verfuegbar („Die
gesuchte Einstellung ist fuer Ihr Konto nicht verfuegbar"), weil die
Zwei-Schritt-Bestaetigung fehlte. Nach dem Einschalten erschien die Funktion.

**Beim Wechsel zwischen Konten aufpassen:** Google haengt an die Adresse eine
Kontonummer (`/u/0/`, `/u/2/`). Wer sie von Hand eintippt, landet im
Standardkonto — genau so entstand der erste Fehlversuch. Sicherer ist der Weg
ueber das Suchfeld „Google-Konto durchsuchen" innerhalb des richtigen Kontos.

**Achtung fuer die Fehlersuche:** Die Seite loescht `?gesendet=1` und
`?fehler=1` sofort wieder aus der Adresszeile (`adresseSaeubern()` per
`history.replaceState`). Das Ergebnis steht also **nicht** in der Adresszeile,
sondern auf der Seite: „Anfrage ist raus" bei Erfolg, roter Kasten „Die Anfrage
konnte nicht gesendet werden" bei Misserfolg. Den genauen Grund protokolliert
`kontakt.php` per `error_log` — ohne das Passwort, das ist im Code
ausdruecklich abgesichert.

**Ebenfalls gemessen:** Die Hostinger-Testadresse ersetzt im ausgelieferten
Text die echte Domain durch die Testdomain. Auf der Seite steht deshalb
`info@magenta-crocodile-313036.hostingersite.com`. Im Quelltext ist die Adresse
fest und richtig eingetragen (`index.html:1259`), und nirgends wird sie aus dem
Hostnamen gebaut — kein Fehler der Seite.

### Schritt 5 — die genauen Werte (02.09.2026 bei Hostinger abgelesen)

Hostinger bietet **zwei** Wege an. **Der empfohlene ist fuer uns der falsche.**

| Weg | Was passiert | Folge fuer die Mail |
|---|---|---|
| „Ueber Nameserver verbinden" | Die ganze DNS-Verwaltung wandert zu Hostinger (`athena.dns-parking.com`, `apollo.dns-parking.com`) | **Toedlich, solange MX und SPF dort nicht angelegt sind.** Die alten Eintraege bei Wix werden nicht mehr gefragt |
| **„Ueber DNS-Eintraege verbinden"** | Nur A und CNAME aendern, Nameserver bleiben bei Wix | **Sicher** — MX und TXT bleiben unberuehrt |

**Also den zweiten Weg nehmen.** Diese Eintraege sind bei Wix zu setzen:

| Typ | Name | Wert | TTL |
|---|---|---|---|
| A | `@` | `92.113.18.111` | 300 |
| CNAME | `www` | `elektrotechnik-paulus.de` | 300 |

Hostinger weist ausserdem darauf hin: **alle anderen bestehenden A- oder
CNAME-Eintraege fuer `@` und `www` entfernen.** Bei Wix zeigen die heute auf
Wix-Server. Alles andere — MX, TXT, und was sonst noch da ist — bleibt stehen.

Bestaetigt am 02.09.2026 durch Hostinger selbst: die aktuellen Nameserver sind
`ns10.wixdns.net` und `ns11.wixdns.net`. Die Messung aus der frueheren Sitzung
stimmt also.

**Spaeter, vor der Wix-Kuendigung:** Mit der Kuendigung verschwinden auch die
Nameserver bei Wix — die DNS-Verwaltung muss dann umziehen. Reihenfolge dabei:
**erst MX und SPF bei Hostinger anlegen und pruefen, dann die Nameserver
umschalten.** Nie andersherum.

### Mail repariert — nachgemessen am 04.09.2026, 05:34 Uhr

Der Vorfall vom Vorabend ist behoben. Beide autoritativen Nameserver liefern
jetzt:

    MX   @   Prio 10  aspmx.l.google.com
    TXT  @   v=spf1 include:_spf.google.com ~all

Kein Hostinger-MX mehr, kein Hostinger-SPF. Nachgewiesen mit
`python3 docs/werkzeuge/dnsfrage.py --autoritativ`, inklusive
NXDOMAIN-Kontrolle gegen Zwischenspeicher.

**Was am Vorabend passiert war:** Hostinger hat beim Transfer entgegen der
gewaehlten Option eigene Nameserver gesetzt (`aurora`/`nebula.dns-parking.com`)
und die Zone mit eigenen Mail-Eintraegen befuellt — obwohl der
Bestaetigungsdialog vorher die richtigen Google-Werte anzeigte. Vier Stunden
lang faengt `mx1.hostinger.com` mit Prioritaet 5 die Mail ab.

**Die Lehre, die bleibt:** Ein Bestaetigungsdialog, der die richtigen Werte
anzeigt, ist kein Beweis, dass sie auch angelegt werden. Nach jeder Uebernahme
autoritativ nachmessen. Und: die Loeschungen brauchten mehrere Stunden bis sie
griffen — nicht vorschnell auf „hat nicht geklappt" schliessen, sondern
nachmessen und Zeit geben.

### Die Seite laeuft jetzt ueber Hostingers CDN — offen, ob gewollt

Ebenfalls am 04.09.2026 gemessen:

    elektrotechnik-paulus.de       A      89.116.213.50 , 91.108.127.221
    www.elektrotechnik-paulus.de   CNAME  www.elektrotechnik-paulus.de.cdn.hstgr.net

`hstgr.net` ist Hostinger. Die frueheren Werte (`92.113.18.111`, `www` als
CNAME auf die Domain selbst) sind ersetzt. Unklar, ob Irfan den CDN-Schalter
umgelegt hat oder Hostinger es selbst tat.

**Praktische Folge:** Geaenderte Dateien kommen verzoegert bei Besuchern an,
der Zwischenspeicher liefert erst die alte Fassung. Vor jedem Sichttest nach
einem Upload den CDN-Zwischenspeicher im hPanel leeren.

### CDN und Cache — Wege im hPanel, am 04.09.2026 am Bildschirm gefunden

**hPanel → Websites → in der Zeile der Domain auf „Armaturenbrett".** Nicht ins
Drei-Punkte-Menue daneben: dort steht „Loeschen", das entfernt die Website.

Auf dem Armaturenbrett oben rechts drei Statusabzeichen, alle gruen:
**Malware-geschuetzt, SSL, CDN.** Damit ist zweierlei belegt — Hostinger hat
nach der Domain-Uebernahme ein gueltiges Zertifikat ausgestellt, und das CDN
laeuft tatsaechlich. Hostingers Doku nennt CDN erst ab „Premium", der Tarif
hier ist **Einzel** — die Doku ist also veraltet, nicht die Messung.

**Nachgeprueft am 11.09.2026 auf einem Screenshot der Seite.** Geaendert hat
sich nur der Name des Menuepunktes: er heisst jetzt **Dashboard**, nicht mehr
*Armaturenbrett*. Alles andere steht unveraendert -- Kasten
**Grundausstattung**, Abschnitt **Cache**, beide Knopfnamen gleich.

Der Weg vollstaendig: linkes Menue **Dashboard** (erster Eintrag unter dem
Suchfeld, ueber *Hosting-Plan*), dann im Kasten **Grundausstattung** der
vierte Abschnitt **Cache**, zwischen *Dateimanager* und *Hosting-Plan*. Dort
zwei Knoepfe:

- **„Cache leeren"** — wirft die Kopien auf den CDN-Zwischenservern weg
- **„Vorschau ohne Cache"** — zeigt die Seite direkt vom Server, am
  Zwischenspeicher vorbei

**Reihenfolge nach einem Upload:** erst *Vorschau ohne Cache* (stimmt die
Aenderung?), dann *Cache leeren* (damit Besucher sie auch bekommen). So wird
erst geprueft und dann veroeffentlicht.

`Strg`+`F5` hilft hier nicht — das leert nur den Browser, nicht den
CDN-Server davor.

### Entscheidung 04.09.2026: `elektropersonal-ayar.de` laeuft aus

Irfan nimmt die zweite Domain nicht mit zu Hostinger. Gemessen vor der
Entscheidung, damit klar ist, was verloren geht:

    elektropersonal-ayar.de   NS   ns10/ns11.wixdns.net
                              A    185.230.63.107 / .171 / .186   (Wix)
                              MX   — kein Eintrag
                              TXT  — kein Eintrag

**Kein MX, kein SPF: auf der Domain laeuft keine E-Mail.** Sie leitet nur auf
die Hauptseite um und zeigt dafuer noch auf die alten Wix-Adressen. Verloren
geht also ausschliesslich die Weiterleitung.

**Restrisiko, das Irfan selbst pruefen muss:** Steht die Adresse gedruckt
irgendwo — Visitenkarten, Fahrzeug, Stempel, Branchenbuch, Signatur? Eine freie
`.de` wird oft binnen Tagen neu registriert; danach zeigt eine gedruckte
Adresse auf fremde Inhalte.

Wann sie frei wird, haengt von der Abwicklung der Wix-Kuendigung ab. Bis dahin
ist die Entscheidung umkehrbar: ein Transfer kostet 4,99 € inklusive einem Jahr.

### Urlaubshinweis — wie er funktioniert und wo die Falle liegt

Ueber dem Kontaktformular in `index.html` steht ein Kasten, der einen Urlaub
ankuendigt. Er besteht aus drei Teilen, die zusammengehoeren:

    HTML   <div class="form-urlaub" id="urlaubshinweis" data-bis="2026-09-06">
    CSS    .form-urlaub{...}          heller Grund, gelbe Kante links
    JS     if (new Date() > bis) hinweis.remove();

**`data-bis` ist der letzte Urlaubstag.** Danach nimmt das JavaScript den
Kasten aus der angezeigten Seite. Wichtig zu verstehen: Es loescht ihn **aus
der Anzeige, nicht aus der Datei**. Der Text bleibt im Quelltext stehen und
wird von jedem Besucher mitgeladen. Ohne JavaScript -- etwa bei Suchmaschinen
-- waere ein veralteter Hinweis weiterhin sichtbar. Wer sicher gehen will,
nimmt den Block nach dem Urlaub aus der Datei.

**Die Falle: Das Datum steht an zwei Stellen.** Einmal als `data-bis` im
Attribut, einmal ausgeschrieben im Text. Wer nur eines aendert, bekommt eine
Seite, die etwas anderes sagt als sie tut. Deshalb steht im Code ein Kommentar
darueber, der genau darauf hinweist.

**Beim Entfernen aufpassen:** Der umschliessende `<div data-reveal>` umfasst
Hinweis **und** Formular. Nimmt man ihn mit, verliert das Formular seine
Einblendung und bleibt auf Deckkraft 0 -- also unsichtbar. Nur den inneren
Block anfassen.

**Pruefen ohne zu warten:** Mit Playwright laesst sich die Uhr des Browsers
stellen (`addInitScript`, `Date` ueberschreiben). Am 04.09.2026 so geprueft:
am 4. und 6. September sichtbar, am 7. verschwunden.

**Am 09.09.2026 unter echten Bedingungen bestaetigt**, ohne gestellte Uhr, mit
dem tatsaechlichen Datum drei Tage nach dem letzten Urlaubstag:

| Messwert | Ergebnis |
|---|---|
| `document.getElementById('urlaubshinweis')` | `null` -- Kasten hat sich selbst entfernt |
| Formular Breite x Hoehe | 584 x 568 px |
| Formular `opacity` | **1** -- die Einblendung hat gegriffen |
| Formular `display` | `grid` |
| Eingabefelder | 7, alle vorhanden |

Damit ist die oben beschriebene Falle nachweislich **nicht** eingetreten: Das
JavaScript fasst nur `#urlaubshinweis` an, der umschliessende `<div data-reveal>`
bleibt stehen und blendet das Formular normal ein. Ein Screenshot des Formulars
lag vor.

**Am 10.09.2026 aus der Datei genommen.** Entfernt wurden genau acht Zeilen
in `index.html` (Kommentar plus `div.form-urlaub`), nichts hinzugefuegt.
`<div data-reveal>` davor und `<form>` dahinter blieben stehen.

**Die Mechanik ist absichtlich stehengeblieben** — CSS `.form-urlaub`, das
JavaScript und der Eintrag in der Symbolanimation. Beides ist ohne den Block
nachweislich harmlos: das JavaScript steigt bei `if (!hinweis) return;` aus,
und ein Selektor ohne Treffer liefert eine leere Liste. Der Vorteil: Der
Kasten ist beim naechsten Urlaub mit vier Zeilen wieder da, statt neu gebaut
werden zu muessen. Dieser Schnipsel gehoert dann direkt vor `<form id="kontaktform"`:

```html
<div class="form-urlaub" id="urlaubshinweis" data-bis="JJJJ-MM-TT">
  <b>Wir sind bis zum T. Monat JJJJ im Urlaub.</b>
  <p>Ihre Anfrage erreicht uns trotzdem — wir beantworten sie, sobald wir zurück sind.</p>
</div>
```

**Das Datum steht auch dann wieder an zwei Stellen** (`data-bis` und der Text).
Beide anpassen, sonst sagt die Seite etwas anderes als sie tut.

Nachgemessen ueber 3 Breiten x 2 Bewegungsmodi, **null Befunde**:

| Breite | Formular | opacity | Felder | Symbole ueber dem Formular |
|---|---|---|---|---|
| 390 px | 350 x 718 | 1 | 7 | 0 |
| 768 px | 691 x 544 | 1 | 7 | 0 |
| 1440 px | 575 x 568 | 1 | 7 | 0 |

Die Hoehe 568 px bei 1440 px ist dieselbe wie am 09.09. — das Entfernen hat
das Formular selbst nicht angetastet. Bei `prefers-reduced-motion` waren es
0 statt 2 Symbole, die Entscheidung vom 02.09. gilt also weiter.

### Paket in ein volles public_html entpacken (04.09.2026)

Das Entpacken bricht mit **409 Conflict** ab, wenn die Zieldateien schon
existieren. Der Fehler kommt vom Entpacken, nicht vom Hochladen -- das ZIP ist
danach trotzdem da.

**Loesung: im Extract-Fenster „Overwrite existing files" anhaken.** Am
04.09.2026 geprueft, funktioniert. Beim ersten Upload am 02.09. war
`public_html` noch leer, deshalb war das vorher nie getestet.

**Dateinamen verlieren beim Herunterladen die Bindestriche** --
`seite-04-09-c.zip` kam als `seite0409c.zip` an. Beim ZIP egal, weil die Namen
im Archiv unversehrt bleiben. Genau deshalb immer ein Paket schicken und nie
Einzeldateien.

**Reihenfolge nach dem Entpacken:** F5 (der Dateimanager aktualisiert nicht von
selbst) → Groesse von `index.html` gegen den erwarteten Wert halten → das ZIP
loeschen, sonst liegt es oeffentlich abrufbar herum → *Vorschau ohne Cache*
→ *Cache leeren*.

### Stand der Veroeffentlichung

**11.09.2026: Commit `9420917` ist live.** Paket
`seite-11-09-9420917.zip` von Irfan nach `public_html` entpackt. Belegt ueber
die Dateigroesse: `index.html` zeigt im Dateimanager 142,57 KiB = 145.992
Bytes, genau der erwartete Wert. **Die Seite selbst ist noch nicht im Browser
nachgesehen** -- und von dieser Sitzung aus auch nicht nachsehbar, der
Netzzugang weist beide Adressen ab (`connect_rejected`, Richtlinie des
Proxys). Offen bleibt damit die Sichtpruefung, und ob das ZIP aus
`public_html` geloescht wurde.

Enthalten ist alles seit `04487d7`, darunter: Kontaktseite beginnt mit dem
Band, Baender am Seitenende, enge Bandhoehen, Anliegen-Feld anfangs leer,
Standort Koeln Nippes, Einleitungssatz aus dem Hero entfernt, Hero-Animation
schneller und heller, Kopf bleibt beim Klick auf Start gross, Hero-Text am
Handy hoeher.

**Doppelter Ordner nach dem Upload (11.09.2026).** In
`domains/elektrotechnik-paulus.de` liegen seither zwei Ordner nebeneinander:

    public.html    mit Punkt        das Versehen
    public_html    mit Unterstrich  das echte Webverzeichnis

Beide enthalten `index.html` mit 142,57 KiB, das Entpacken lief also zweimal.
Ausgeliefert wird nur der Unterstrich-Ordner; der Punkt-Ordner liegt
ausserhalb des Webverzeichnisses und ist von aussen nicht abrufbar. Er ist
damit kein Sicherheitsproblem, sondern Ballast: 3,45 MB von 10 GiB.

Entstanden vermutlich, weil im Extract-Feld einmal ein Punkt statt eines
Unterstrichs stand. **Merkmal fuer die naechste Sitzung:** in der nach Name
aufsteigend sortierten Liste steht der Punkt-Ordner **oben**, weil der Punkt
im Zeichensatz vor dem Unterstrich kommt. Punkt und Unterstrich sind auf
einem Screenshot nicht sicher zu unterscheiden -- die Reihenfolge schon.

**Vor dem Loeschen erst umbenennen.** Umbenennen ist umkehrbar, Loeschen ist
es nur ueber *Trash bin*. Faellt die Seite nach dem Umbenennen aus, war es der
falsche Ordner.

**Dazu ein zweiter, aelterer Fund (11.09.2026).** Im Dateimanager, Pfadzeile
`domains > elektrotechnik-paulus.de > public_html`, liegt **noch ein**
`public_html`, geaendert vor 7 Tagen -- also aus einem frueheren Upload, nicht
aus dem von heute. Damit sind es drei Ordner:

    domains/elektrotechnik-paulus.de/
      pub.html               11.09.  Versehen, ausserhalb des Webverzeichnisses
                                     (hiess zuerst public.html)
      public_html/                   das echte Webverzeichnis
        public_html/         ~04.09. alte Kopie, INNERHALB des Webverzeichnisses
        .gitignore     90 B  ~04.09. gehoert nicht aufs Ziel, paket.py schliesst sie aus

**Zum Namen des ueberzaehligen Ordners, aufgeklaert von Irfan.** Er hiess
`public.html` und wurde von ihm auf `pub.html` umbenannt -- nach dem Verfahren
"erst umbenennen, dann pruefen, dann loeschen". Die zwei verschiedenen
Lesungen aus den Screenshots waren also kein Lesefehler, sondern zwei
Zeitpunkte.

Die Lehre bleibt trotzdem stehen: **aus einem Bild allein war nicht zu
entscheiden, welche der beiden Lesungen gilt.** Verlaesslich ist die
**Position** -- in der nach Name aufsteigend sortierten Liste steht der
ueberzaehlige Ordner ueber `public_html`, weil sowohl der Punkt als auch die
kuerzere Zeichenfolge im Zeichensatz vor dem Unterstrich einsortiert werden.

Der Unterschied zaehlt: `public.html` ist von aussen nicht erreichbar, der
innere `public_html` dagegen schon -- erwartbar unter
`elektrotechnik-paulus.de/public_html/`. **Nicht gemessen**, diese Sitzung
kommt nicht an den Server. Falls er ausgeliefert wird, steht dort eine zweite,
alte Fassung der Seite im Netz: schlecht fuer die Suchmaschine, weil derselbe
Inhalt zweimal auffindbar ist.

Der Upload vom 11.09. ist richtig gelandet, belegt ueber die Zeitstempel:
`band-01-steckdose.jpg` und die uebrigen Banddateien zeigen im echten
`public_html` "vor 18 Minuten".

**04.09.2026: Commit `04487d7` war live auf `elektrotechnik-paulus.de`.**
Enthalten: Waermepumpe als fuenftes Hero-Bild, Urlaubshinweis bis 06.09.,
Datenschutz-Ueberschrift *Kontakt in Datenschutzfragen*, Pruefplakette ohne
*auf Wunsch*, `kontakt.php` mit der Passwort-Korrektur. Vom Nutzer im Browser
bestaetigt.

Ablauf, der funktioniert hat: ZIP nach `public_html`, Extract **mit**
*Overwrite existing files*, F5, ZIP loeschen, *Vorschau ohne Cache* zur
Kontrolle, dann *Cache leeren*.

### Noch offen — kurz und konkret

Offene Punkte zuerst, danach das Erledigte zum Nachschlagen.

| | Was | Warum es zaehlt |
|---|---|---|
| 1 | **Testmail an `info@elektrotechnik-paulus.de`** | DNS ist nur die Wegbeschreibung, die Mail ist der Beweis |
| 2 | Wix kuendigen | **erst wenn 1 gruen ist** |
| 3 | `.gitignore` in `public_html` entfernen | 90 Bytes Ballast aus einem alten Upload. **Ungeprueft**, ob noch vorhanden: die Liste zeigte zuletzt keine Dateien mit fuehrendem Punkt, das kann Loeschung sein oder eine Anzeigeeinstellung |
| 4 | Zertifikatswarnung im Browser pruefen | seit dem CDN nicht geprueft. Auf dem Dashboard stehen *SSL* und *CDN* gruen — das ist Hostingers eigene Anzeige, kein Beweis im Browser |
| 5 | Untermenue *Leistungen* ragt rechts aus dem Fenster | gemessen 38 px bei 1024 und 1440, 24 px bei 1200, 12 px bei 1366. Am Handy nicht. Gefunden beim Messen, nicht beauftragt, deshalb nicht angefasst |

Erledigt:

| | Was | |
|---|---|---|
| — | ~~Paket hochladen~~ **04.09.2026** — Commit `04487d7` live. Die verirrte `leistungvde.html` wurde beim Ueberschreiben gegenstandslos | |
| — | ~~`elektropersonal-ayar.de` mitnehmen?~~ **04.09.2026: auslaufen lassen** | siehe unten |
| — | ~~Ersparnis neu rechnen~~ **09.09.2026: mindestens 223,63 €/Jahr** | drei Zahlen fehlen noch, siehe Kostenkapitel — **zwei davon nur bis zur Wix-Kuendigung ablesbar** |
| — | ~~`seite-10-09.zip` hochladen~~ **ueberholt 11.09.2026** — hochgeladen wurde `seite-11-09-9420917.zip` mit demselben Inhalt und allem Spaeteren | |
| — | ~~Seite im Browser nachsehen~~ **11.09.2026** — ueber *Vorschau ohne Cache*, "sieht gut aus" | |
| — | ~~ZIP aus `public_html` loeschen~~ **11.09.2026** | |
| — | ~~Inneren `public_html` entfernen~~ **11.09.2026** — im Dateimanager stehen nur noch `elektrosymbole`, `png`, `schriften` und die Seitendateien | |
| — | ~~Cache leeren~~ **11.09.2026**, von Irfan bestaetigt. Damit ist `9420917` fuer Besucher ausgeliefert, nicht nur auf dem Server | |
| — | ~~`pub.html` loeschen~~ **11.09.2026** — damit ist die Ebene ueber `public_html` wieder aufgeraeumt | |
| — | ~~Automatische Verlaengerung einschalten~~ **steht auf AN**, am 11.09.2026 auf zwei Seiten gesehen: *Domain-Portfolio* und *Domain-Uebersicht*. Der Eintrag "steht auf AUS" vom 04.09. war veraltet | |

### Domain-Uebersicht, gesehen am 11.09.2026

Werte von der Seite `Domains → Portfolio → Verwalten`:

    Status             Aktiv, mit Schloss-Zeichen (Transfersperre)
    Ablaufdatum        2027-10-01
    Automatische
    Verlaengerung      AN
    Namenserver        aurora.dns-parking.com
                       nebula.dns-parking.com
    Inhaber            Irfan Ayar, ayar@elektrotechnik-paulus.de

`dns-parking.com` ist Hostingers eigene Namenserver-Domain, kein Parken im
Sinne von "Domain liegt brach". Die Zone wird also bei Hostinger verwaltet --
so, wie es sein soll. **Kein Handlungsbedarf.**

**Zwei Fallen auf genau dieser Seite:**

1. In der *Domain-Checkliste* steht als offener dritter Punkt *"Richten Sie ein
   geschaeftliches @elektrotechnik-paulus.de E-Mail-Konto ein"* mit einem Knopf
   *Kostenlos testen*. **Nicht anklicken.** Die Geschaeftsmail laeuft ueber
   Google Workspace; ein Hostinger-Mailkonto wuerde die MX-Eintraege
   umschreiben. Das ist genau der Eintrag, der laut Uebergabe nie angefasst
   werden darf.
2. Rechts unten steht *Uebertragung → Autorisierungscode → Code abrufen*. Der
   Code gehoert **nie** in einen Chat und nie ins Repository. Es gibt derzeit
   keinen Grund, ihn abzurufen.

Der **Verlaengerungspreis** der `.de` steht auf dieser Seite **nicht**. Er
fehlt weiterhin fuer die Ersparnisrechnung.


### Paket `seite-10-09.zip` gebaut (10.09.2026)

246 Dateien, 3,44 MiB. Gebaut mit `python3 docs/werkzeuge/paket.py`, an Irfan
geschickt. **Hochgeladen ist es noch nicht** — das steht als Punkt 8 offen.

Inhalt gegenueber dem Live-Stand `04487d7`: genau zwei Aenderungen. Das
reparierte `marke-hager.png` und der aus `index.html` entfernte
Urlaubshinweis. Sonst nichts.

Gegengeprueft, ueber die Selbstpruefung des Werkzeugs hinaus:

| Pruefung | Ergebnis |
|---|---|
| Dateiverweise in HTML, CSS und JSON | **242 geprueft, null fehlend** |
| Markdown, `docs/`, `.github/` im Paket | keine |
| HTML-Seiten | 10 |
| Urlaubstext in `index.html` | nicht mehr enthalten |
| `marke-hager.png` | 21.961 Bytes, Pruefsumme stimmt mit der Arbeitskopie |

**Messfalle dabei, gleich beim ersten Versuch eingetreten:** Ein Test, der
`content="..."` als Dateiverweis wertet, meldet 70 Geisterbefunde — das sind
die `<meta>`-Beschreibungen, also Fliesstext. `content` zaehlt nur dann als
Pfad, wenn der Wert keine Leerzeichen hat und auf eine echte Dateiendung
endet. Erst mit dieser Einschraenkung kommt der Test auf dieselben 242 wie
die Messung vom 06.09. — **zwei unabhaengig geschriebene Tests, dieselbe
Zahl.** Der Test war falsch, nicht das Paket.

**Erwartete Groessen nach dem Upload** (zur Kontrolle im Dateimanager, weil
die Speicheranzeige unzuverlaessig ist):

    index.html        135.837 Bytes = 132,65 KiB
    marke-hager.png    21.961 Bytes =  21,45 KiB
    kontakt.php        10.774 Bytes =  10,52 KiB

Ablauf wie am 04.09.: ZIP nach `public_html`, Extract **mit** *Overwrite
existing files*, F5, Groessen vergleichen, ZIP loeschen, *Vorschau ohne Cache*,
dann *Cache leeren*.

### Paket `seite-11-09-9420917.zip` gebaut (11.09.2026)

246 Dateien, 3.615.507 Bytes = 3,45 MiB. Gebaut mit
`python3 docs/werkzeuge/paket.py`, Hash von Hand angehaengt, an Irfan
geschickt und am selben Tag von Irfan hochgeladen, siehe *Stand der
Veroeffentlichung*.

Enthaelt alles seit dem Live-Stand `04487d7`, darunter die Arbeit dieser
Sitzung: Kontaktseite mit Band, Baender am Seitenende, enge Bandhoehen,
Anliegen-Feld leer, Standort Koeln Nippes, Einleitungssatz aus dem Hero
raus, schnellere und hellere Hero-Animation, grosser Kopf beim Klick auf
Start, hoeherer Hero-Text am Handy.

Gegengeprueft, unabhaengig von der Selbstpruefung des Werkzeugs:

| Pruefung | Ergebnis |
|---|---|
| Dateien | 246, davon 10 HTML-Seiten |
| Markdown, `docs/`, `.github/`, `CLAUDE.md` im Paket | keins |
| `index.html` im Paket gegen Arbeitskopie | bytegleich, 145.992 Bytes |
| Dateiverweise in HTML, CSS und JSON | 459 geprueft, null fehlend |
| "Meisterbetrieb" | nicht enthalten |

**Erwartete Groessen nach dem Upload** (zur Kontrolle im Dateimanager, weil
die Speicheranzeige unzuverlaessig ist):

    index.html        145.992 Bytes = 142,57 KiB
    marke-hager.png    21.961 Bytes =  21,45 KiB
    kontakt.php        10.774 Bytes =  10,52 KiB

Ablauf wie am 04.09.: ZIP nach `public_html`, Extract **mit** *Overwrite
existing files*, F5, Groessen vergleichen, ZIP loeschen, *Vorschau ohne Cache*,
dann *Cache leeren*.

### Paket mit Commit-Hash im Namen (11.09.2026)

Die Lehre vom 10.09. umgesetzt: Der Paketname traegt jetzt den Commit-Kurzhash,
`seite-10-09-95c6103.zip` statt `seite-10-09.zip`. Ein Name nur aus dem Datum
kollidiert zwangslaeufig, sobald zwei Sitzungen am selben Tag bauen.

**Achtung:** `docs/werkzeuge/paket.py` bildet den Namen weiterhin allein aus dem
Datum. Der Hash wurde nach dem Bauen von Hand angehaengt. Wer das vergisst, hat
wieder einen kollidierenden Namen.

Gegenprobe gegen die am 10.09. hinterlegten Erkennungsmerkmale, alle drei
stimmen:

    ZIP gesamt        3.612.227 Bytes   = der als richtig dokumentierte Wert
    index.html          135.837 Bytes   Urlaubshinweis ist raus
    marke-hager.png      21.961 Bytes   repariertes Logo ist drin

Damit belegt: Ein aus `95c6103` frisch gebautes Paket ist inhaltlich identisch
mit dem korrekten Paket vom 10.09. Wer eines von beiden hochlaedt, bekommt
denselben Stand.

### Hager-Logo repariert (06.09.2026)

Gemeldet als "beim Hager-Logo fehlt das r". Die Messung fand **drei** Fehler,
nicht einen. Die Datei `marke-hager.png` (235 x 120) war ein Fehlausschnitt:

| Befund | Messwert |
|---|---|
| "r" fehlt, rechts mitten im Strich abgeschnitten | Inhalt lief bis Spalte x=234 von 235 |
| orange Fremdfragmente links, Rest eines Nachbarlogos | Farbe 236,118,15 ab x=0 |
| undurchsichtiger weisser Kasten statt Transparenz | Alpha 255 im ganzen Band y=16..104 |

`git log --follow` zeigte: einmal hinzugefuegt in `1b988d1`, es gibt also keine
heile Vorgaengerfassung. Reparieren war unmoeglich, die Pixel fehlen schlicht.
Ein Herstellerlogo nachzeichnen kommt nicht in Frage.

**Wie die Vorlage doch noch ankam.** Der Kunde fuegte das richtige Logo dreimal
in den Chat ein, aber ein eingefuegtes Bild ist keine Datei auf der Maschine --
`/mnt/user-data/working/` blieb leer. Der Ausweg: Eingefuegte Bilder liegen als
base64 im Gespraechsprotokoll unter `/root/.claude/projects/`. Dort waren 84
Bilder, die letzten zwei mit gleicher Pruefsumme -- die beiden Einfuegungen.

Fuer kuenftige Faelle: **Bilder aus dem Protokoll holen, wenn der Anhang
fehlschlaegt.** Das spart dem Kunden den zweiten Anlauf.

**Warum das Freistellen ueber die Buntheit lief.** Die Vorlage war ein
Vorschaubild einer Logo-Sammelseite: 360 x 360, Palettenmodus, **nur fuenf
Farben**, und das Karomuster war als echte Pixel eingebrannt statt als
Transparenz. Alpha war ueberall 255. Ueber die Helligkeit liess sich der Grund
also nicht trennen, wohl aber ueber die Buntheit:

    Karopixel sind grau       -> Rot = Gruen = Blau -> Buntheit 0
    Logopixel sind blau       -> 206 - 86           -> Buntheit 120
    Deckung = Buntheit / 120

Mischpixel am Rand wurden auf die reine Logofarbe zurueckgerechnet. Auch die
durchsichtigen Pixel tragen die Logofarbe, sonst zieht das Verkleinern einen
weissen Saum in die Kanten. Gegen die groben Treppenstufen der Fuenf-Farben-
Vorlage erst glatt vervierfacht, dann auf Zielgroesse herunter.

**Warum 120 Pixel Hoehe die richtige Zielgroesse ist.** Alle zwanzig
Markenlogos sind exakt 120 Pixel hoch, die Breite variiert. `.brand-logo` setzt
`height:34px`. Die Datei liefert also die 3,5-fache Anzeigegroesse -- reichlich
auch fuer scharfe Bildschirme, und die grobe Vorlage faellt bei 34 Pixel nicht
auf. Im Browser gegengeprueft: natuerlich 303 x 120, angezeigt 86 x 34.

**Sackgassen, die nichts gebracht haben.** Das Logo aus dem Netz zu holen
scheitert an der Ausgangssperre: `hager.de` und `commons.wikimedia.org` liefern
beide `CONNECT tunnel failed, 403`. Paketquellen wie PyPI sind erlaubt, normale
Webseiten nicht. Ein frueherer Test auf "laeuft der Inhalt bis zum Bildrand"
war ebenfalls wertlos: **alle** Logos sind randlos zugeschnitten, das ist
normal und beweist nichts.

### Paket bauen: `docs/werkzeuge/paket.py` (06.09.2026)

Bis hierher wurden die Pakete von Hand gepackt. Jetzt gibt es ein Werkzeug:

    python3 docs/werkzeuge/paket.py     ->  seite-TT-MM.zip

Aufnahme-Regel: **alles, was Git kennt, minus `docs/`, `.github/`, `CLAUDE.md`,
`AGENTS.md`, `.gitignore`.** So kann nichts vergessen werden und nichts
Fremdes hineinrutschen. Das Werkzeug prueft sich selbst: Archiv lesbar, und
jede vorgesehene Datei wirklich enthalten.

Stand 06.09.2026: 246 Dateien, 3,45 MiB, alle zehn HTML-Seiten drin,
Entwicklungsdateien draussen. Zusaetzlich gegengeprueft: 242 Dateiverweise in
den HTML-Seiten und in `marke.css`, **242 davon im Paket, null fehlend**.

### Schritt 6 — Transfer eingeleitet am 03.09.2026, 20:44 Uhr

Wix: Domains → ... → *Von Wix wegtransferieren* → *Domain transferieren*. Das
entsperrt die Domain und schickt den AuthInfo-Code per Mail an die
**Eigentuemer-Adresse** `ayar@elektrotechnik-paulus.de` — nicht an die
Login-Adresse. Der Code wird nirgends am Bildschirm angezeigt.

Hostinger: Domains → *Uebertragen*. Die Domain wurde sofort als **„Bereit zur
Uebertragung"** erkannt, die Wix-Entsperrung hatte also gegriffen.
**Preis 4,99 €**, eine einjaehrige Verlaengerung inbegriffen. Das Ablaufdatum
wird auf ein Jahr ab Transfertag gesetzt — vorher 08.04.2027, danach also etwa
September 2027. Kein Laufzeitverlust, rund fuenf Monate Gewinn.

**Der wichtigste Moment lief besser als geplant:** Hostinger liest die
bestehende Zone selbst aus und legt sie unter *DNS-Eintraege Ihrer Domain
verwalten* zur Bestaetigung vor. Alle vier Eintraege stimmten auf die Stelle
mit der Messung von 13:15 Uhr ueberein:

    A      @      92.113.18.111
    CNAME  www    elektrotechnik-paulus.de.
    MX     @      Prio 10  aspmx.l.google.com.
    TXT    @      v=spf1 include:_spf.google.com ~all

**„Bestaetigen" behaelt sie. „Standardeintraege verwenden" waere der Knopf, der
den Google-MX ersetzt** — der ist der gefaehrliche, nicht die Nameserver-Frage.

**Messung direkt nach dem Absenden (20:44 Uhr): nichts veraendert.** A, MX, TXT
und die Nameserver stehen unveraendert, die Zone wird weiter von Wix bedient.

**Noch zu pruefen, sobald die Nameserver auf Hostinger zeigen:** In der
MX-Zeile stand die `10` doppelt — einmal in der Spalte *Prioritaet*, einmal am
Anfang von *Inhalte*. Vermutlich nur eine Anzeigeform. Waere sie echt doppelt
gespeichert, waere der MX ungueltig und die Mail tot. Also nach dem
Nameserver-Wechsel sofort `dnsfrage.py` laufen lassen.

**Laufzeit laut Hostinger: bis zu 5-7 Werktage.** Bei `.de` oft schneller.

**Bestaetigungsschritt erledigt am 03.09.2026:** Hostinger schickt eine Mail an
`ayar@elektrotechnik-paulus.de`, deren Link die Kontaktadresse verifiziert
(„Your email is verified — verified for all domain(s) linked to it"). Ohne
diesen Klick laeuft der Antrag in eine Frist. Ist angeklickt.

### Google Workspace laeuft NICHT ueber Wix — geprueft am 03.09.2026

Wix warnt beim Wegtransferieren: „Verknuepfte E-Mail-Konten werden
deaktiviert." Weil Wix Google Workspace auch als Wiederverkaeufer anbietet,
musste das geklaert werden, bevor der Transfer startet.

**Ergebnis: Wix verwaltet null Postfaecher.** Unter *Kontoeinstellungen →
Geschaeftliche E-Mail-Adresse* steht in allen vier Reitern eine 0 (Alle
Abonnements, Aktiv, Handlung erforderlich, Abgelaufen), und Wix bietet
darunter an, fuer beide Domains erst eine einzurichten. Auch in den vier
Premium-Abos taucht kein Mail-Produkt auf: Brand Maker, Premiumpaket und zwei
Domain-Abos.

**`info@elektrotechnik-paulus.de` laeuft also direkt bei Google.** Die Warnung
ist fuer dieses Konto gegenstandslos. Bitte nicht nochmal nachforschen.

Nebenbefund: Die beiden Domains sind **eigene, bezahlte Abos**, nicht
kostenlose Paketbestandteile wie frueher notiert. Die Ersparnisrechnung weiter
unten ist damit zu niedrig angesetzt und muss nachgerechnet werden.

Anzeigefehler bei Wix, nicht erschrecken: Auf der leeren Mail-Seite steht
`cairo.emptyState.additem.title` statt einer Ueberschrift. Ein
Uebersetzungsplatzhalter, keine Kontostoerung.

### Restrisiko beim Transfer: die DNS-Zone bleibt bei Wix

Der MX-Eintrag liegt in der Wix-Zone. Ob Wix diese Zone nach dem Transfer
weiter bedient, ist ungeklaert. Faellt sie weg, ist Mail **verzoegert, nicht
verloren** — sendende Server versuchen es ueber Stunden und Tage erneut.
Reparatur: Nameserver auf Hostinger, MX anlegen, rund ein bis zwei Stunden.
Deshalb direkt nach dem Transfer den MX messen (Skript `dnsfrage.py`).

### Schritte 6 und 7 — die Reihenfolge, an der die Mail haengt (03.09.2026)

**Der Denkfehler, den ich zuerst hatte:** Ich wollte MX und SPF bei Hostinger
anlegen, *bevor* die Domain uebertragen wird. Das geht nicht. Solange die
Domain bei Wix registriert ist und die Nameserver auf Wix zeigen, hat Hostinger
fuer sie **gar keine DNS-Zone** — die Seite „DNS / Nameserver" im hPanel zeigt
nur die Knoepfe *Uebertragen* und *Leitfaden ansehen*.

**Der Grund, warum die Reihenfolge trotzdem lebenswichtig ist**, im Wortlaut
der Hostinger-Anleitung: „Changing nameservers to Hostinger removes existing
custom DNS records, such as TXT verification records, and email records will be
set to Hostinger Mail values." Der Nameserver-Wechsel **loescht MX und TXT** und
setzt Hostingers eigene Mail-Werte ein. Ohne Vorbereitung waere die
Google-Workspace-Mail in diesem Moment tot.

**Die Rettung** steht in derselben Quelle: Beim Transfer bietet Hostinger eine
Nameserver-Option an, die „keeps your current nameservers and all existing DNS
records unchanged". Diese Option waehlen. Waehrend des Transfers sind die
Nameserver gesperrt, die Wahl muss also gleich sitzen.

**Verbindliche Reihenfolge:**

1. AuthInfo-Code bei Wix holen, Domainsperre aus
2. Transfer bei Hostinger starten, dabei **bestehende Nameserver behalten**
3. Transfer abwarten (Nameserver sind gesperrt)
4. DNS-Zone bei Hostinger fuellen: A, CNAME, **MX, TXT**
5. Erst jetzt Nameserver auf Hostinger umstellen
6. **Testmail an `info@elektrotechnik-paulus.de`** — der eigentliche Beweis
7. Erst danach Wix kuendigen

**Die Werte fuer Schritt 4**, am 03.09.2026 aus der laufenden Zone gemessen:

    A      @      92.113.18.111                          TTL 3600
    CNAME  www    elektrotechnik-paulus.de               TTL 3600
    MX     @      aspmx.l.google.com          Prio 10    TTL 3600
    TXT    @      v=spf1 include:_spf.google.com ~all    TTL 3600

Eins zu eins uebernehmen, nichts „verbessern". Nach Schritt 5 nachmessen, ob
Hostinger die MX-Werte trotzdem ueberschrieben hat.

**Der AuthInfo-Code geht nie durch den Chat.** Er ist das Passwort der Domain.

Quellen: support.hostinger.com/en/articles/8925103 sowie
hostinger.com/support/1696789 und /1583436.

### Falle beim Nachladen einzelner Dateien (03.09.2026)

Beim Nachladen von drei Dateien nach Hostinger sind zwei Dinge schiefgegangen,
beide kosten Zeit, wenn man sie nicht kennt:

**1. Der Upload wurde mit `403 Forbidden` (openresty) abgewiesen.** Nicht die
Dateien waren das Problem. Nach einem Neuladen des Dateimanagers und erneuter
Anmeldung lief derselbe Upload durch — es war die abgelaufene Sitzung.

**2. Ein Dateiname verlor beim Herunterladen den Bindestrich.** Aus
`leistung-vde.html` wurde `leistungvde.html`. Damit kollidiert die Datei mit
nichts, wird also als neue Datei angelegt, waehrend die echte Seite alt bleibt.
Das sieht nach Erfolg aus und ist keiner.

**Frueherkennung:** Der Ueberschreiben-Dialog nennt die Zahl der
Namenskonflikte. Sind es weniger als hochgeladene Dateien, ist mindestens ein
Name verkehrt. Vor dem Bestaetigen die Namen im Download-Ordner pruefen.
Achtung, Windows blendet bekannte Endungen aus: `index` ist `index.html`,
`kontakt.php` wird voll angezeigt, weil `.php` nicht registriert ist.

**Konsequenz fuer die Zukunft: mehrere Dateien immer als ZIP uebergeben.**
Innerhalb eines ZIPs bleiben Namen unveraendert. Der ZIP-Weg umgeht ausserdem
Schutzregeln, die den direkten Upload von `.php` blocken koennen.

**Reparatur ohne neuen Upload:** alte Datei loeschen, die falsch benannte
umbenennen. Die Datei liegt ja schon auf dem Server.

### Schritt 5 — erledigt am 03.09.2026

Die vier Handgriffe bei Wix sind ausgefuehrt. Nachgemessen ueber eine direkte
DNS-Abfrage aus der Sitzung heraus, rund eine Viertelstunde nach dem
Speichern — die Umstellung war da schon durch:

    A      elektrotechnik-paulus.de      92.113.18.111          TTL 3600
    CNAME  www.elektrotechnik-paulus.de  elektrotechnik-paulus.de  TTL 3600
    MX     elektrotechnik-paulus.de      Prio 10 aspmx.l.google.com  TTL 3600
    TXT    elektrotechnik-paulus.de      v=spf1 include:_spf.google.com ~all
    NS     elektrotechnik-paulus.de      ns10/ns11.wixdns.net   TTL 21600

**MX, TXT und NS sind unveraendert** — die Geschaeftsmail haengt weiter an
Google Workspace, die Nameserver weiter bei Wix. Genau so soll es bis zur
Domain-Uebertragung bleiben.

Werkzeug-Hinweis fuer die naechste Sitzung: `dig` gibt es im Container nicht,
und die DNS-ueber-HTTPS-Dienste (`dns.google`, `cloudflare-dns.com`) sperrt die
Egress-Richtlinie mit 403. Was geht: `socket.getaddrinfo` in Python, und eine
selbstgebaute UDP-Abfrage an den Resolver aus `/etc/resolv.conf` — das Skript
dafuer steht im Scratchpad als `dnsfrage.py`. **Die Domain selbst
(`elektrotechnik-paulus.de:443`) ist ebenfalls per Richtlinie gesperrt**, ein
Seitenabruf von hier aus ist also nicht moeglich. Der Sichttest laeuft ueber
den Browser von Irfan.

### Schritt 5 — die Abhakliste, wie sie ausgefuehrt wurde (Wix-Stand vom 02.09.2026)

**Vorher — so sieht es bei Wix aus:**

| Kasten | Host-Name | Wert | TTL |
|---|---|---|---|
| A | elektrotechnik-paulus.de | `185.230.63.107` | 1 Stunde |
| A | elektrotechnik-paulus.de | `185.230.63.186` | 1 Stunde |
| A | elektrotechnik-paulus.de | `185.230.63.171` | 1 Stunde |
| CNAME | www.elektrotechnik-paulus.de | `cdn3.wixdns.net` | 1 Stunde |
| TXT | elektrotechnik-paulus.de | `v=spf1 include:_spf.google.…` | 1 Stunde |
| MX | elektrotechnik-paulus.de | `aspmx.l.google.…`, Prio 10 | 1 Stunde |
| NS | elektrotechnik-paulus.de | `ns10`/`ns11.wixdns.net` | 1 Tag |

**Die Aenderung — vier Handgriffe in dieser Reihenfolge:**

1. A-Eintrag `185.230.63.186` **loeschen**
2. A-Eintrag `185.230.63.171` **loeschen**
3. A-Eintrag `185.230.63.107` **bearbeiten** → Wert `92.113.18.111`
4. CNAME `www` **bearbeiten** → Wert `elektrotechnik-paulus.de`

Diese Reihenfolge ist bewusst: Nach den ersten beiden Schritten zeigt die
Domain immer noch geschlossen auf Wix. Erst Schritt 3 schaltet um. Wuerde man
zuerst umschalten und dann loeschen, bekaemen Besucher zwischendurch zufaellig
mal Wix und mal Hostinger.

**Unberuehrt bleiben: TXT, MX, NS.** Der MX-Kasten hat einen Link
„MX-Eintraege bearbeiten" — nicht anklicken. Daran haengt die Geschaeftsmail
und damit auch das Kontaktformular.

**Der Rueckweg, falls etwas klemmt.** Diese Werte wiederherstellen:

    A      elektrotechnik-paulus.de      185.230.63.107
    A      elektrotechnik-paulus.de      185.230.63.186
    A      elektrotechnik-paulus.de      185.230.63.171
    CNAME  www.elektrotechnik-paulus.de  cdn3.wixdns.net

**Danach pruefen**, in dieser Reihenfolge:

1. `https://elektrotechnik-paulus.de` aufrufen — zeigt sie die neue Seite?
   DNS-Aenderungen brauchen bis zu einigen Stunden, die alte TTL steht auf
   1 Stunde.
2. `https://www.elektrotechnik-paulus.de` — dasselbe.
3. **Testmail an `info@elektrotechnik-paulus.de` schicken und Ankunft
   pruefen.** Das ist der wichtigste Test.
4. Kontaktformular auf der neuen Adresse absenden.

**MX und TXT NICHT anfassen.** Daran haengt die Geschaeftsmail. Gemessen:
Nameserver bei Wix (`ns10/ns11.wixdns.net`), Mail bei Google Workspace, genau
ein MX, ein SPF (`v=spf1 include:_spf.google.com ~all`), **kein DKIM, kein
DMARC**.

**Erst uebertragen, dann kuendigen.** Die Domain ist bei Wix registriert (in
der Rechnung 0,00 € als Paketbestandteil). Wer vorher kuendigt, verliert sie.

### Was der Wechsel bringt (neu gerechnet 09.09.2026)

**Warum ueberhaupt neu gerechnet wurde.** Die alte Tabelle nannte 210,83 €/Jahr
und ab 2030 132,29 €/Jahr, zeigte aber ihren Rechenweg nicht. Nachgerechnet
geht sie nur auf, wenn man auf der Hostinger-Seite **17,79 €/Jahr** hinzunimmt,
in beiden Zeilen denselben Betrag. Der steht nirgends im Dokument. Vermutlich
ein angenommener Domain-Verlaengerungspreis -- aber eine Kostenrechnung mit
einem unbelegten Posten ist keine Rechnung. Dazu kam der Nebenbefund vom
03.09.: die beiden Wix-Domains waren **eigene, bezahlte Abos**, nicht
kostenlose Paketbestandteile. Die Wix-Seite war also zu niedrig angesetzt.

**Wix -- faellt weg**

| Posten | Betrag | Stand |
|---|---|---|
| Premiumpaket | 178,50 €/Jahr | belegt |
| Brand Maker | 71,40 €/Jahr | belegt |
| Domain-Abo `elektrotechnik-paulus.de` | **?** | **fehlt** |
| Domain-Abo `elektropersonal-ayar.de` | **?** | **fehlt** |
| belegte Summe | **249,90 €/Jahr** | Untergrenze |

**Hostinger -- kommt dafuer**

| Posten | Betrag | Stand |
|---|---|---|
| Miete bis 31.08.2030 | 85,11 € / 4 Jahre = **21,28 €/Jahr** | belegt |
| Miete ab 01.09.2030 | 99,82 €/Jahr | belegt |
| Domain bis 01.10.2027 | 4,99 € einmalig, ein Jahr inbegriffen | belegt |
| Domain ab 01.10.2027 | **?** | **fehlt** |
| zweite Domain | 0,00 € | entfaellt, laeuft aus |

**Ersparnis im ersten Jahr, nur mit belegten Zahlen:**

    249,90 - (21,28 + 4,99) = 223,63 €/Jahr

Das sind **12,80 € mehr** als die alte Zahl, und es ist eine **Untergrenze**:
die zwei Wix-Domain-Abos kommen noch obendrauf. Ab 01.09.2030 bleiben
249,90 - 99,82 = 150,08 €/Jahr, davon geht der Domain-Verlaengerungspreis ab.

**Die drei fehlenden Zahlen und wo sie stehen:**

1. + 2. die zwei Wix-Domain-Abos -- Wix, `Premium-Abonnements`, dort stehen
   vier Eintraege: Brand Maker, Premiumpaket und **zwei Domain-Abos**. Vor der
   Kuendigung ablesen, danach ist die Seite weg.
3. der Domain-Verlaengerungspreis bei Hostinger -- hPanel,
   `Domains → Portfolio`, Zeile `elektrotechnik-paulus.de`. Dieselbe Seite, auf
   der die automatische Verlaengerung eingeschaltet werden muss.

### Zwei Sitzungen am selben Branch (10.09.2026)

Am 10.09. arbeiteten **zwei Sitzungen gleichzeitig** an
`claude/home-page-rdyw91`. Die eine wusste nichts von der anderen. Was dabei
schiefging und wie man es vermeidet:

**Der Vorfall.** Sitzung A baute aus ihrem lokalen Stand ein Paket namens
`seite-10-09.zip` und schickte es. Sitzung B hatte zwei Commits vorher schon
gepusht -- darunter den entfernten Urlaubshinweis -- und ebenfalls ein
`seite-10-09.zip` geschickt. Zwei Dateien, gleicher Name, **verschiedener
Inhalt**. Unterschied 177 Bytes, im Explorer beide "3,4 MB". Wer das falsche
hochlaedt, macht den entfernten Urlaubshinweis wieder rueckgaengig.

**Wie es auffiel.** `git status -sb` zeigte `[behind 2]`. Nichts anderes haette
es gezeigt -- `git log` allein sieht nur den lokalen Stand und meldet nichts.

**Regel daraus, als Erstes in jeder Sitzung:**

    git fetch origin claude/home-page-rdyw91
    git status -sb                                    # [behind N]?
    git merge --ff-only origin/claude/home-page-rdyw91

Ein `--ff-only`-Nachziehen ist erlaubt: Es setzt nichts zurueck und
ueberschreibt nichts. Verboten bleiben `reset --hard` und `push --force`.

**Zweite Regel: den Paketnamen nicht nur aus dem Datum bilden**, solange
mehrere Sitzungen laufen. Ein Datumsname kollidiert am selben Tag zwangslaeufig.
Aufgeloest wurde es mit `seite-KORRIGIERT-10-09.zip` -- ein Name, der sich
nicht verwechseln laesst.

**Erkennungsmerkmal fuer das richtige Paket vom 10.09.:**

| | richtig | veraltet |
|---|---|---|
| ZIP gesamt | 3.612.227 Bytes | 3.612.404 Bytes |
| `index.html` | **135.837** Bytes | 136.340 Bytes |
| `marke-hager.png` | 21.961 Bytes | 21.961 Bytes |

Die verlaessliche Zahl ist `index.html`: 135.837 heisst Urlaubshinweis raus.

### Sitzung auf dem falschen Branch (11.09.2026)

Eine Sitzung startete mit dem von aussen vorgegebenen Arbeitsbranch
`claude/cool-curie-ypqas8`. Der haengt an `master` und liegt **201 Commits**
hinter `claude/home-page-rdyw91`. Dort entstanden drei Commits, alle drei
gegenstandslos:

| Dort gebaut | Wirklichkeit auf dem Arbeitsbranch |
|---|---|
| Formspree-Weiterleitung korrigiert | Formular laeuft ueber `kontakt.php`, Formspree ist raus |
| `index (2).html` geloescht | Die Datei existiert dort nicht |
| Bilderband vor den Kontakt geschoben | Stand dort laengst davor |

**Ursache.** Der Branchname kam aus der Sitzungsvorgabe, nicht aus dieser
Datei. Auf `master` liegt weder `CLAUDE.md` noch `docs/` — es gab also keine
Regel im Arbeitsverzeichnis, die haette widersprechen koennen.

**Erkennungsmerkmal, vor dem ersten Handgriff:**

    git rev-list --count HEAD..origin/claude/home-page-rdyw91

Eine dreistellige Zahl heisst: falscher Branch. `git status -sb` allein reicht
hier **nicht** — es vergleicht nur mit dem eigenen Upstream, und der war in
Ordnung. Die Pruefung aus dem Uebergabe-Prompt faengt den Fall also nicht.

### Der Weg im hPanel (02.09.2026 am Bildschirm mitverfolgt)

`Websites` → Zeile `elektrotechnik-paulus.de` → `Werkzeuge` → `Dateimanager` →
Karte **„Auf alle Dateien von Single Web Hosting zugreifen"** (die rechte, nicht
die linke) → `domains` → `elektrotechnik-paulus.de`.

Dort liegen `public_html`, `.trash` und die leere Markierungsdatei
`HIER NICHT HOCHLADEN`. **Diese Ebene ist der Ort fuer `kontakt-konfig.php`** —
der Webserver liefert sie nicht aus. `kontakt.php` sucht dort zuerst
(`dirname(__DIR__) . '/kontakt-konfig.php'`), danach erst im eigenen Ordner.

### Hochladen erleichtern (noch nicht eingerichtet)

- **FTP mit FileZilla** — Zugangsdaten im hPanel unter `Dateien → FTP-Konten`.
- **Git-Auslieferung** — im hPanel soll es eine GitHub-Anbindung geben. Ob der
  Einzel-Plan das kann, ist **ungeprueft**.

---

## 7. Bitte nicht nochmal suchen

Jeder Punkt hier hat schon einmal Zeit gekostet.

- **`kontakt.php` hat das Passwort ins Fehlerprotokoll geschrieben** (behoben am
  02.09.2026). Die Schutzabfrage pruefte auf `AUTH` am Zeilenanfang, das
  Passwort geht aber als nackte Base64-Zeichenkette ueber die Leitung und fiel
  durch. Base64 ist keine Verschluesselung. Jeder `sagen()`-Aufruf benennt sich
  jetzt selbst, es wird nichts mehr aus dem Befehl abgeleitet.
  **Wer das Protokoll `.logs/error_log_*` aus dieser Zeit noch hat, muss die
  darin genannten App-Passwoerter widerrufen.**
- **Aus dem Protokoll laesst sich die Passwortlaenge ablesen**, ohne es zu
  entschluesseln: 24 Base64-Zeichen mit `==` am Ende bedeuten genau 16 Bytes.
  Damit war belegt, dass keine Leerzeichen mitkopiert waren — die naechstliegende
  Vermutung war also falsch.
- **Eine Sitzung kann auf einem fremden, viel aelteren Branch starten.** Am
  10.09.2026 oeffnete die Sitzung `claude/gracious-einstein-kd9y44` — 198
  Commits hinter der Arbeit, ohne `CLAUDE.md` und ohne `docs/`. Genau daran
  war es zu erkennen: **Fehlt `CLAUDE.md`, ist es der falsche Branch**, nicht
  eine geloeschte Datei. Gemessen mit
  `git merge-base --is-ancestor <alt> origin/claude/home-page-rdyw91`: reiner
  Vorfahre, null eigene Commits, also war nichts verloren. Behoben durch
  Vorspulen mit `git merge --ff-only` und einem gewoehnlichen Push —
  **kein `--force`**. Verweigert Git das Vorspulen, ist es *kein* Vorfahre und
  es darf auf keinen Fall nachgeholfen werden. Der Branch `home-page-rdyw91`
  wurde dabei nicht angefasst.
- **Der Agent-Proxy sperrt die Zieladressen.** Gemessen am 02.09.2026:
  `ayar20260808.github.io` und `magenta-crocodile-313036.hostingersite.com`
  antworten mit `connect_rejected`. **Eine KI-Sitzung kann die
  veroeffentlichte Seite nicht selbst pruefen** — nur lokal. Dafuer braucht es
  Irfan oder eine Freischaltung in der Netzwerkeinstellung.
- **Chrome uebersetzt im Hostinger-Dateimanager die Dateinamen mit.** Aus
  `schriften` wird `Schriften`, aus `domains` wird `Domaenen`. Das sah wie ein
  Gross-/Kleinschreibungsfehler aus und war keiner. **Uebersetzung abschalten,
  bevor irgendetwas im Dateimanager gemacht wird.** Die Adresszeile zeigt
  immer die Wahrheit.
- **Die Speicheranzeige von Hostinger ist unzuverlaessig.** Sie meldete 760 KiB,
  waehrend ueber 4 MB auf dem Server lagen, und sprang spaeter ohne Zutun von
  8,5 auf 9,54 MiB. **Nie als Beweis benutzen.** Stattdessen Dateigroessen
  gegen das Paket rechnen — das hat am 02.09. fuenf von fuenf auf die
  Nachkommastelle bestaetigt.
- **Bilder mit `loading="lazy"` sind nicht kaputt, nur noch nicht geladen.**
  Ein Test, der `!img.complete` als Fehler wertet, meldet Geisterbefunde
  (22 Stueck am 02.09.). Richtig ist `img.complete && img.naturalWidth === 0`.
- Kein `dig` / `host` / `nslookup` in der Umgebung, keine PDF-Werkzeuge.
  `potrace`, `numpy`, `scipy`, `scikit-image`, `Pillow` und `pngjs` lassen sich
  bei Bedarf per `pip` / `npm` nachinstallieren.

---

## 8. Wie geprueft wird

```sh
cd /home/user/elektrotechnik-paulus-homepage
/opt/node22/bin/node /opt/node22/lib/node_modules/http-server/bin/http-server -p 8080 -c-1 --silent &
NODE_PATH=/opt/node22/lib/node_modules /opt/node22/bin/node <skript>.js
```

Playwright ist global installiert, Chromium liegt unter
`/opt/pw-browsers/chromium-1194/chrome-linux/chrome` und braucht `--no-sandbox`.

**Die Standardpruefung:** 10 Seiten × 3 Breiten (390/768/1440) × 2
Bewegungsmodi = 60 Laeufe. Geprueft werden JS-Fehler, fehlgeschlagene
Anfragen, HTTP ≥ 400, kaputte Bilder und fehlende Sprungziele.
**Stand 02.09.2026: null Befunde.**

Die Pruefskripte liegen im Scratchpad und muessen in einer neuen Sitzung neu
geschrieben werden — der Container ist jedes Mal frisch.

### Fallen, die schon Messungen verfaelscht haben

- `html{scroll-behavior:auto!important}` einspritzen, sonst laufen Messungen in
  eine Animation.
- Nach dem Scrollen **1200 ms** warten (400 ms Rueckkehr + 250 ms Blende +
  Reserve). Fuer die Symbolanimation **3000 ms**, wegen der 850-ms-Verzoegerung
  im `rebuild()`.
- Zu jedem Symbolbereich **einzeln hinscrollen und dort verweilen**. Wer nur
  ans Seitenende und zurueck springt, misst null Symbole und haelt das faelsch-
  licherweise fuer ein Ergebnis.
- Bei Kontrastmessungen ueber Bildern den Text ausblenden, sonst misst man
  Weiss gegen Weiss.
- `locator.screenshot()` schneidet am Elementrand ab — fuer die Frage „wird
  etwas abgeschnitten" den ganzen Bildschirm aufnehmen.
- Synthetische `MouseEvent`s greifen nicht mehr; mit `page.mouse.move()`
  testen.
- **In jedem Zustand messen, nicht nur im Ruhezustand.** Kopf normal *und*
  geschrumpft, Hover, offenes Burger-Menue, `prefers-reduced-motion`. Der
  Einzug im Untermenue war ueber alle zehn Seiten richtig — aber nur, solange
  der Kopf nicht geschrumpft war.

### Zwei bewaehrte Griffe

**Aenderung ueber alle zehn Seiten nachweisen:**

```sh
for f in index.html leistung-*.html; do
  git diff -U0 $f | grep '^[+-]' | grep -v '^+++\|^---' | md5sum
done | sort | uniq -c -w32
```

Eine einzige Zeile mit Zaehler 10 heisst: ueberall bytegleich.

**Ein Upload-Paket auf Vollstaendigkeit pruefen:** jeden im Code genannten
Pfad gegen das Paket halten — dabei `src`, `href`, `srcset`, `content`,
`url(...)`, `data-bild`, `action` und JSON-Felder beruecksichtigen. Wer
`data-bild` vergisst, uebersieht fehlende Hero-Bilder.

---

## 9. Prompt zum Kopieren

```text
Homepage von Elektrotechnik Paulus.

Repository: ayar20260808/elektrotechnik-paulus-homepage
Branch:     claude/home-page-rdyw91
            (gleichwertig: claude/gracious-einstein-kd9y44 -- selber Commit)

Falls die Sitzung in einem anderen Repository geoeffnet wurde: dieses hier
zuerst dazuholen. Es gibt ein zweites Projekt (elektrotechnik-hub, die
Betriebs-App) -- das ist ein anderes System und hat mit der Homepage nichts
zu tun. Beim letzten Mal war die Sitzung versehentlich dort geoeffnet.

Lies zuerst, in dieser Reihenfolge:
  1. CLAUDE.md            -- die Arbeitsregeln, sie gelten uneingeschraenkt
  2. docs/ARBEITSSTAND.md -- Stand, Entscheidungen, Offenes, Sackgassen,
                             Pruefgriffe

Dann sag mir in ein paar Zeilen, wo wir stehen und was du als naechstes
vorschlaegst. Fang noch nichts an.

Ich bin Programmier-Anfaenger und will mitlernen: erklaere kurz, was du
tust und warum.
```
