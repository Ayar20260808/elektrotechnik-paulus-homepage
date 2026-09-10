# Uebergabe-Prompt fuer eine neue Sitzung

Diese Datei ist zum **Kopieren** gedacht. Der Teil zwischen den Linien wird in
den ersten Beitrag eines neuen Chats eingefuegt. Alles davor und danach ist
Erklaerung fuer dich, nicht fuer das Modell.

**Wozu das gut ist.** Ein Chat endet, und mit ihm das Gedaechtnis. Das
eigentliche Wissen liegt deshalb im Repository -- in `CLAUDE.md` und in
`docs/ARBEITSSTAND.md`. Dieser Prompt ist nur der Einstieg: Er sagt der neuen
Sitzung, wo sie steht, was sie nicht anfassen darf, und wohin sie zum Nachlesen
gehen muss.

**Pflege.** Nach groesseren Schritten die Abschnitte *Wo die Arbeit liegt*,
*Was zuletzt passiert ist* und *Offen* nachziehen. Der Rest bleibt meist
gleich. Wer das vergisst, schickt die naechste Sitzung mit veralteten Zahlen
los -- das faellt oft erst auf, wenn schon etwas Falsches gebaut wurde.

---

# Homepage Elektrotechnik Paulus -- Uebergabe, Stand 10.09.2026

Ich bin Irfan Ayar, Elektrotechnik Paulus GmbH, Koeln. **Ich bin
Programmier-Anfaenger und will mitlernen** -- erklaer mir kurz, was du tust und
warum, nicht nur das Ergebnis.

## Zuerst pruefen: arbeitet noch jemand am selben Branch?

**An diesem Branch arbeitet mehr als eine Sitzung.** Am 10.09.2026 lagen zwei
fremde Commits bei `origin`, die lokal fehlten -- und ein daraus gebautes Paket
war bereits veraltet, bevor es verschickt wurde.

**Deshalb als Allererstes, vor jeder Aussage ueber den Stand:**

    git fetch origin claude/home-page-rdyw91
    git status -sb        # steht da [behind N], fehlt dir etwas

Bei `[behind N]`: `git merge --ff-only origin/claude/home-page-rdyw91`.
Das ist ein Nachziehen, kein Zuruecksetzen -- erlaubt. Was **nicht** erlaubt
ist: `reset --hard` und `push --force`.

## Zuerst lesen, bevor du irgendetwas anfasst

1. `CLAUDE.md` -- die verbindlichen Regeln
2. `docs/ARBEITSSTAND.md` -- das Gedaechtnis des Projekts: Stand,
   Entscheidungen, Offenes, **Sackgassen** (dort steht, was schon vergeblich
   versucht wurde), Pruefgriffe

Sag mir danach in ein paar Zeilen, wo wir stehen und was du vorschlaegst.
**Fang noch nichts an.**

## Wo die Arbeit liegt

- Verzeichnis: `/home/user/elektrotechnik-paulus-homepage`
  (**Achtung:** Das Arbeitsverzeichnis der Sitzung zeigt evtl. auf
  `/home/user/elektrotechnik-hub` -- das ist ein **anderes** Projekt.)
- Repo: `Ayar20260808/elektrotechnik-paulus-homepage`
- Branch: `claude/home-page-rdyw91` -- **nicht zuruecksetzen, nicht force-pushen**
- Repo-Stand: `git log --oneline -1` fragen, nicht raten
- **Live auf dem Server: `04487d7`** vom 04.09.2026. Am 10.09. von mir
  bestaetigt: `seite-06-09.zip` wurde **nie hochgeladen**. Diese Zeile ist die
  wichtigste der Datei: Wenn ich einen Fehler melde, ist die erste Frage immer,
  **welchen Stand ich vor mir hatte**.
- An **ausgelieferten** Dateien unterscheiden sich vom Live-Stand nur zwei
  Dinge: das reparierte `marke-hager.png` und der aus `index.html` entfernte
  Urlaubshinweis. Nachpruefbar mit
  `git diff --stat 04487d7 HEAD -- . ':(exclude)docs' ':(exclude)*.md'`.

Statische Seite, kein Framework, kein Build. CSS und JS stehen **inline und
mehrfach** in `index.html` plus neun `leistung-*.html`. Aenderungen an
Rechtstexten, Hero und Kontaktformular betreffen nur `index.html`.

## Harte Regeln

- **MX und TXT nicht anfassen.** Daran haengt die Geschaeftsmail
  (Google Workspace, `aspmx.l.google.com` Prio 10).
- Passwoerter und der AuthInfo-Code gehen **nie** durch den Chat und **nie**
  ins Repository.
- **Nie IDs, Schluessel oder Adressen aus Screenshots ablesen** -- immer als
  Text erfragen.
- **Nie eine Oberflaeche beschreiben, die du nicht siehst.**
- "Meisterbetrieb" ist verboten, es heisst **Elektrofachbetrieb**.
- Alles auf Deutsch. Commit-Nachrichten ohne Umlaute.
- Nach jeder Aenderung die Vorschau-Adresse ungefragt als **erste Zeile** in
  einem Codeblock ausgeben.

## Umgebungsgrenzen dieser Sitzung

- `elektrotechnik-paulus.de` ist **von der Maschine aus nicht erreichbar**
  (Ausgangssperre, `curl` liefert 000). Nur mein Browser sieht die Live-Seite.
- Normale Webseiten sind gesperrt, Paketquellen wie PyPI nicht.
- DNS geht trotzdem: `python3 docs/werkzeuge/dnsfrage.py`
- Paket bauen: `python3 docs/werkzeuge/paket.py` -> `seite-TT-MM.zip`
- Von mir eingefuegte Bilder landen **nicht** als Datei auf der Maschine. Sie
  stecken aber als base64 im Protokoll unter `/root/.claude/projects/`.

## Was zuletzt passiert ist

- **Hager-Logo repariert** (`cdf19d0`). Die alte Datei hatte drei Fehler:
  fehlendes "r", orange Fremdfragmente, undurchsichtiger weisser Grund.
  Ersatz aus meiner Vorlage freigestellt, 303 x 120.
- **`docs/werkzeuge/paket.py`** gebaut (`0278f16`).
- **Ersparnis neu gerechnet** (`93a031f`): mindestens **223,63 EUR/Jahr**.
  Die alte Zahl 210,83 EUR enthielt einen unbelegten Posten von 17,79 EUR/Jahr.
- **Urlaubshinweis** hat sich am 09.09. selbst entfernt, Formular unversehrt
  geprueft. Am 10.09. dann ganz aus `index.html` genommen (`2127879`), damit
  kein veralteter Text mehr mitgeladen wird. CSS und JS bleiben stehen, der
  Mechanismus ist fuer den naechsten Urlaub wiederverwendbar.
- **Namenskollision beim Paket, 10.09.** Zwei Sitzungen bauten am selben Tag
  je ein `seite-10-09.zip` aus verschiedenen Staenden. Unterschied: 177 Bytes.
  Im Explorer stehen beide als "3,4 MB" -- nicht unterscheidbar. Aufgeloest
  durch ein Paket mit eindeutigem Namen. **Merke: den Paketnamen nicht nur
  nach dem Datum bilden, wenn mehrere Sitzungen laufen.**
- **Hochgeladen ist immer noch nichts.** Ob ich es inzwischen getan habe,
  weisst du nicht -- frag mich.

## Offen

**Bei mir:**
0. **Paket hochladen** -- haengt seit dem 06.09., nichts davon ist live
1. Testmail an `info@elektrotechnik-paulus.de` -- der Beweis, der die
   Wix-Kuendigung freigibt
2. Automatische Verlaengerung einschalten (steht auf **AUS**, Ablauf
   **01.10.2027**)
3. Seite im Browser pruefen, auch auf Zertifikatswarnung
4. **Zeitkritisch: die zwei Wix-Domain-Abo-Betraege ablesen, BEVOR ich
   kuendige** -- danach ist die Seite weg und die Zahlen unwiederbringlich
5. Erst danach Wix kuendigen

**Spaeter:** Datenschutz-Entwurf (`docs/entwurf-datenschutz.md`) pruefen lassen,
fuenf Luecken fuellen, vor allem die beiden Auftragsverarbeitungsvertraege ·
Jimdo-Vertrag pruefen · Formspree-Konto stilllegen (haelt noch Kundenanfragen) ·
`master` liegt 197 Commits zurueck (Stand 09.09.2026).

## Wie ich arbeiten moechte

Miss, statt zu schaetzen -- der Browser ist zum Nachmessen da (Playwright liegt
unter `/opt/node22/lib/node_modules/playwright`, Chromium unter
`/opt/pw-browsers/chromium-1194/`, immer `--no-sandbox`). Wenn du dich geirrt
hast, sag es klar und korrigier es. Schreib neue Erkenntnisse und Sackgassen in
`docs/ARBEITSSTAND.md`, damit sie die Sitzung ueberleben.
