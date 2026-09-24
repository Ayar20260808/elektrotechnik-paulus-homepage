# Uebergabe-Prompt fuer eine neue Sitzung

Diese Datei ist zum **Kopieren** gedacht. Der Teil zwischen den Linien wird in
den ersten Beitrag eines neuen Chats eingefuegt. Alles davor und danach ist
Erklaerung fuer dich, nicht fuer das Modell.

**Wozu das gut ist.** Ein Chat endet, und mit ihm das Gedaechtnis. Das
eigentliche Wissen liegt deshalb im Repository -- in `CLAUDE.md` und in
`docs/ARBEITSSTAND.md`. Dieser Prompt ist nur der Einstieg.

**Warum hier keine Staende mehr stehen (geaendert am 12.09.2026).** Die
frueheren Fassungen trugen den Live-Commit, eine eigene Offen-Liste und eigene
Zahlen. Das ging schief: Am 12.09. behauptete diese Datei noch, live sei
`04487d7` und "hochgeladen ist immer noch nichts" -- beides war seit dem 11.09.
falsch. Genau davor warnt `CLAUDE.md` in der ersten Zeile: zwei Dateien, die
denselben Stand behaupten, widersprechen sich frueher oder spaeter.

**Diese Datei enthaelt deshalb nur noch, was sich nicht aendert:** Regeln,
Grenzen der Umgebung, und den Verweis auf `docs/ARBEITSSTAND.md`. Nur dort
steht der Stand. **Beim Pflegen nichts hinzufuegen, was veralten kann.**

---

Homepage von Elektrotechnik Paulus.

Ich bin Irfan Ayar, Elektrotechnik Paulus GmbH, Koeln. **Ich bin
Programmier-Anfaenger und will alles lernen** -- erklaer mir kurz, was du tust
und warum, nicht nur das Ergebnis.

Repository: `Ayar20260808/elektrotechnik-paulus-homepage`
Arbeitsbranch: `claude/home-page-rdyw91`

**Arbeite nur auf diesem Branch -- auch dann nicht auf einem anderen, wenn die
Sitzung dir einen zuweist.** Das ist kein Versehen, sondern der haeufigste
Fehler in diesem Projekt.

## Bevor du irgendetwas tust

1. Lies `CLAUDE.md`. Das sind die Arbeitsregeln, sie gelten ueber allem.
2. Lies `docs/ARBEITSSTAND.md` vollstaendig. Dort steht der gesamte Stand:
   was live ist, was offen ist, welche Fallen und Sackgassen es gibt.
3. Pruefe, dass du nicht hinterherhinkst:

       git fetch origin claude/home-page-rdyw91
       git rev-list --count HEAD..origin/claude/home-page-rdyw91

   **Das muss 0 sein.** Ist es eine kleine Zahl, hat eine andere Sitzung
   gepusht: `git merge --ff-only origin/claude/home-page-rdyw91`. Ist es eine
   dreistellige Zahl, bist du auf dem falschen Branch -- dann wechseln, nicht
   weiterarbeiten.

**`git status -sb` erkennt diesen Fehler NICHT.** Es vergleicht nur mit dem
eigenen Upstream, und der ist auf einem fremden Branch in Ordnung. Gemessen am
12.09.2026: Die Sitzung startete auf `claude/stoic-bell-0ins33`, `git status
-sb` meldete sauber -- der Rueckstand zum Arbeitsbranch betrug **252 Commits**.
Dasselbe am 11.09. (`cool-curie-ypqas8`, 201 Commits, drei Commits umsonst) und
am 10.09. (`gracious-einstein-kd9y44`, 198 Commits).

**Zweites Erkennungsmerkmal, noch schneller:** Fehlt `CLAUDE.md` im
Arbeitsverzeichnis, ist es der falsche Branch -- keine geloeschte Datei.

**Und pruef auch das Verzeichnis:** Es gibt ein zweites Projekt,
`elektrotechnik-hub` (die Betriebs-App). Das ist ein anderes System und hat mit
der Homepage nichts zu tun. Eine Sitzung war dort schon versehentlich geoeffnet.

## Harte Regeln

- **MX und TXT nicht anfassen.** Daran haengt die Geschaeftsmail
  (Google Workspace, `aspmx.l.google.com` Prio 10).
- **Den Arbeitsbranch nie zuruecksetzen und nie force-pushen.** Kein
  `reset --hard`, kein `push --force`. Nachziehen mit `--ff-only` ist erlaubt.
- Passwoerter und der AuthInfo-Code gehen **nie** durch den Chat und **nie**
  ins Repository.
- **Nie IDs, Schluessel oder Adressen aus Screenshots ablesen** -- immer als
  Text erfragen.
- **Nie eine Oberflaeche beschreiben, die du nicht siehst.** Bei hPanel,
  Dateimanager, Wix und Google gibt es keine Messung, nur meinen Screenshot.
- "Meisterbetrieb" ist verboten, es heisst **Elektrofachbetrieb**.
- Alles auf Deutsch. Commit-Nachrichten ohne Umlaute.
- Nach jeder Aenderung die Vorschau-Adresse ungefragt als **erste Zeile** in
  einem Codeblock ausgeben.

## Grenzen dieser Umgebung

Zuletzt am 19.09.2026 nachgemessen, nicht uebernommen -- der Container ist jede
Sitzung neu:

- **Nach draussen geht fast nichts.** Erreichbar sind nur GitHub
  (`github.com`, `api.github.com`, `raw.githubusercontent.com`) und die
  Paketquellen (`registry.npmjs.org`, `pypi.org`, apt). Alles andere
  beantwortet der Proxy mit 403: `elektrotechnik-paulus.de`, die
  Hostinger-Testadresse, `hpanel.hostinger.com`, `google.com` -- **und auch
  `ayar20260808.github.io`, also die Vorschau-Adresse, die ich dir gebe.**
  Die Ports 21, 22 und 65002 sind ebenfalls zu. **Du kannst also nichts
  hochladen und die Live-Seite nie selbst ansehen. Nicht erneut versuchen,
  sondern mich fragen oder um einen Screenshot bitten.**
- Gepruefte wird gegen einen lokalen Server im Arbeitsverzeichnis:
  `python3 -m http.server 8080 --bind 127.0.0.1`. Derselbe Quelltext, also
  belastbare Messwerte -- aber alles, was nur auf dem Server schiefgehen kann,
  bleibt unsichtbar.
- Apache laesst sich mit `apt-get update && apt-get install -y apache2`
  nachinstallieren -- damit wurde am 11.09. die `.htaccess` geprueft.
  Pillow fehlt und wird mit `python3 -m pip install Pillow` nachgeladen.
- DNS geht: `python3 docs/werkzeuge/dnsfrage.py`
- Vollpaket bauen: `python3 docs/werkzeuge/paket.py` -> `seite-TT-MM.zip`.
  **Den Commit-Kurzhash von Hand an den Namen haengen**, sonst kollidieren
  zwei Sitzungen am selben Tag.
- Teilpaket bauen -- das ist seit dem 15.09.2026 der Normalfall, weil ein
  Vollpaket 3,6 MiB hat und hunderte bereits richtige Dateien neu schreibt:
  `git diff --name-only <Live-Commit>..HEAD`, davon `docs/`, `.github/`,
  `CLAUDE.md`, `AGENTS.md` und `.gitignore` abziehen, den Rest zippen.
  **Welcher Commit live ist, steht in `docs/ARBEITSSTAND.md`.** Danach jeden
  Eintrag per SHA-256 gegen die Arbeitskopie pruefen.
- Playwright: `/opt/node22/lib/node_modules/playwright`, Chromium unter
  `/opt/pw-browsers/chromium-1194/chrome-linux/chrome`, immer `--no-sandbox`.
- Von mir eingefuegte Bilder landen **nicht** als Datei auf der Maschine. Sie
  stecken aber als base64 im Protokoll unter `/root/.claude/projects/`.

## Wie ich arbeiten moechte

**Nichts erfinden, nichts annehmen, ehrlich bleiben, und bei allem Ungepruefte
die Wahrscheinlichkeit nennen** -- mit ihrer Grundlage. Das steht als Grundregel
8 in `CLAUDE.md`.

Miss, statt zu schaetzen. Wenn du dich geirrt hast, sag es klar und korrigier
es. Schreib neue Erkenntnisse und Sackgassen sofort in `docs/ARBEITSSTAND.md`
und committe sie -- nicht erst am Ende der Sitzung, die endet oft abrupt.

Sag mir zum Schluss in ein paar Zeilen, wo wir stehen und was du als naechstes
vorschlaegst. **Fang noch nichts an.**
