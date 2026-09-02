# Arbeitsstand — Homepage Elektrotechnik Paulus

Stand: 02.09.2026 · Branch `claude/home-page-rdyw91` · Commit `67a8b97`

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
| Arbeitsbranch | `claude/home-page-rdyw91` |
| Vorschau (dieser Branch) | `https://ayar20260808.github.io/elektrotechnik-paulus-homepage/vorschau/claude-home-page-rdyw91/` |
| Fertige Seite (`master`) | `https://ayar20260808.github.io/elektrotechnik-paulus-homepage/` |
| Hostinger-Testadresse | `https://magenta-crocodile-313036.hostingersite.com/` |
| Zieladresse | `https://elektrotechnik-paulus.de` — zeigt noch auf Wix |

`master` steht bei `250809a` und hat **keine eigenen Commits**; der
Arbeitsbranch liegt 163 Commits davor. Ein Vorspulen waere also sauber
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

Vier Bildebenen uebereinander, sichtbar ist immer genau eine. Alle vier
Sekunden wechselt die aktive Ebene, Ueberblendung 1,1 s, dazu ein sehr
langsamer Zoom ueber 90 s. Bildebene Deckkraft `.78`.

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
| 02.09.2026 | Homepage wird **nicht** nach `master` gemergt, solange nicht ausdruecklich gewuenscht | Entscheidung Irfan, offen gelassen |
| frueher | Pflichtfelder im Formular sind **nur Vorname und Nachname** | So gewuenscht. Folge: eine Anfrage kann ohne Rueckmeldeweg ankommen, `Reply-To` entfaellt dann — ist so gebaut und geprueft |
| frueher | Standort im Kontaktblock heisst **„Koeln"**, nicht „Koeln Nippes" | Gekuerzt auf Wunsch |
| frueher | Alle Knopfbeschriftungen heissen **„Angebot anfragen"** (53 Stueck) | Vereinheitlicht auf Wunsch |
| frueher | Kein Formspree mehr, eigener Versand ueber `kontakt.php` | Unabhaengigkeit, keine Drittdaten |
| frueher | `hero-5.jpg` (Foto des Inhabers) ist aus der Hero-Folge genommen | Auf Wunsch |

---

## 5. Was offen ist

### Braucht eine Entscheidung von Irfan

1. **Bilderband-Tempo.** Laeuft es zu schnell (dann 168 s), oder waren die
   Bilder zu gross (dann Bandhoehe zurueck)? Zweimal gefragt, noch nicht
   beantwortet. **Nichts anfassen, bevor das geklaert ist.**
2. **Herkunft der Koelner Silhouette.** Kam als Bild aus dem Chat. Stammt sie
   aus einer Bilddatenbank, braucht die gewerbliche Nutzung eine Lizenz.
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
| 5 | DNS bei Wix: A-Record auf `92.113.18.111`, `www` als CNAME | Irfan |
| 6 | AuthInfo-Code bei Wix holen, Domain uebertragen | Irfan |
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

**Noch aufzuraeumen:** In `public_html` liegen zwei ZIP-Dateien oeffentlich
abrufbar — `homepagehostinger.zip` (3,43 MiB) und `elektrotechnikpauluswebsite.zip`
(3,41 MiB, vom Vortag). Beide loeschen.

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

### Schritt 5 — fertige Abhakliste (Wix-Stand vom 02.09.2026 abgelesen)

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

### Was der Wechsel bringt

| | |
|---|---|
| Wix Premiumpaket | 178,50 €/Jahr |
| Wix Brand Maker | 71,40 €/Jahr, wiederkehrend |
| **Wix gesamt** | **249,90 €/Jahr** |
| Hostinger Einzel | 85,11 € fuer 4 Jahre (bis 2030-08-31), danach 99,82 €/Jahr |
| **Ersparnis** | **210,83 €/Jahr**, ab 2030 132,29 €/Jahr |

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
Ich arbeite an der Homepage von Elektrotechnik Paulus
(Repository ayar20260808/elektrotechnik-paulus-homepage,
Branch claude/home-page-rdyw91).

Lies zuerst AGENTS.md und docs/ARBEITSSTAND.md. Dort steht der Stand, die
getroffenen Entscheidungen, was offen ist, welche Sackgassen schon erforscht
wurden und wie geprueft wird. CLAUDE.md enthaelt die Arbeitsregeln.

Meine Aufgabe:
[hier eintragen]
```
