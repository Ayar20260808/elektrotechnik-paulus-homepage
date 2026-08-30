# Arbeitsregeln für dieses Projekt

## Grundregel — Grundlage für alles

**Wie eine Maschine arbeiten. Nicht raten. Nicht menscheln.**

Das ist keine Stilfrage und keine Vorliebe, sondern die Arbeitsweise, auf der
alles andere aufbaut. Kein Beschönigen, kein Vermuten, kein Ausschmücken, keine
Entschuldigungen, keine Selbstkommentare. Fakten, Messwerte, klare Aussagen.

1. **Tu genau das, was verlangt wurde.** Nicht weniger, nicht mehr. Keine
   ungefragten Verbesserungen, keine Zusatzleistungen, keine Vorschläge
   mitten in der Ausführung.
2. **Bei Unsicherheit: sagen, dass du unsicher bist.** Benennen, was genau
   unklar ist, und eine einzige, kurze Frage stellen. Dann warten.
3. **Nie auf einer Vermutung weiterbauen.** Lieber stehenbleiben und fragen
   als etwas liefern, das auf einer Annahme beruht.
4. **Fakten prüfen statt annehmen.** Was messbar ist, wird gemessen. Was nicht
   überprüfbar ist, wird als unüberprüft gekennzeichnet.
5. **Was du nicht selbst kannst, sagst du zu.** Nicht umschreiben, nicht
   andeuten, nicht so tun, als sei es erledigt. Klar benennen: was geht nicht,
   warum nicht, und **welches Werkzeug oder welche KI es stattdessen kann**.
   Wenn kein Werkzeug nötig ist, sondern nur ein Handgriff des Nutzers, dann
   genau diesen Handgriff nennen.
6. **Nichts übersehen.** Vollständig prüfen, nicht stichprobenartig. Wer eine
   Sache repariert, prüft danach das Ganze — nicht nur die reparierte Stelle.
   In diesem Projekt ist beim Beheben eines Fehlers zweimal ein neuer entstanden,
   weil nur der Ausschnitt geprüft wurde.
7. **Einen eigenen Befund erst melden, wenn er bestätigt ist.** Ein Messwert aus
   einem fehlerhaften Test ist kein Befund. Erst den Test prüfen, dann das
   Ergebnis.

Diese Regel steht über allem anderen in dieser Datei.

Konkret aufgetreten und zu vermeiden:

- „Zeig mir X" wurde mehrfach falsch gedeutet und mit Erklärungen statt eines
  Links beantwortet. **Auf „zeig mir" gehört ein Link, kein Absatz.**
- Eine Verbesserung wurde eingebaut, die niemand verlangt hatte (ganze
  Leistungskarte klickbar). Der Nutzer wollte es so, wie es war. **Nicht
  eigenmächtig erweitern.**
- Eine Diagnose wurde gepusht, bevor sie am aktuellen Stand geprüft war.
  **Erst gegen `origin/master` prüfen, dann pushen.**

## Vorschau-Links

Jede Vorschau-Adresse wird als **nackte Adresse in einem Codeblock** ausgegeben,
nie als eingebetteter Text-Link:

```
https://beispiel.de/vorschau/
```

Grund: So laesst sie sich kopieren und in einem eigenen Tab oeffnen. Ob ein
eingebetteter Link einen neuen Tab oeffnet, entscheidet der Client -- darauf
gibt es keinen Einfluss.

**Nach jeder Aenderung sofort die Adresse ausgeben, ungefragt und als erste
Zeile der Antwort.** Nicht warten, bis danach gefragt wird. Auch dann, wenn die
Antwort hauptsaechlich eine Rueckfrage oder ein Bericht ist.

Die Adresse des Arbeitsstands:

```
https://ayar20260808.github.io/elektrotechnik-paulus-homepage/vorschau/claude-home-page-rdyw91/
```

## Antwortstil

Direkt und knapp, aber mit dem „Warum". Der Nutzer ist Anfänger und will
verstehen, nicht nur ausführen. Keine langen Aufzählungen, wenn eine Zeile
reicht.

## Bevor du einen Fehler diagnostizierst

**Frage zuerst, welche Adresse der Nutzer vor sich hatte.** Das war hier die
häufigste Fehlerquelle:

| Adresse | Welcher Stand |
|---|---|
| `…github.io/elektrotechnik-paulus-homepage/` | `master` |
| `…github.io/elektrotechnik-paulus-homepage/vorschau/<branch>/` | der jeweilige Arbeitsbranch |

Ein Fehler auf der ersten Adresse bedeutet oft nur, dass die Korrektur noch
nicht gemergt ist — nicht, dass sie nicht funktioniert.

## In jedem Zustand messen, nicht nur im Ruhezustand

Ein Element kann in einem Zustand richtig aussehen und in einem anderen falsch.
Wer nur den Ruhezustand misst, meldet „stimmt" und liegt daneben.

Konkret passiert: Der Einzug im Leistungs-Untermenü (`padding:10px 36px`) war
richtig gesetzt und auf allen zehn Seiten gleich — **aber nur, solange der
Kopf nicht geschrumpft war.** Sobald man scrollte, griff

    header.geschrumpft .nav-links a{padding:5px 0;}

auch auf die Links im Untermenü, weil der Selektor ohne Kindzeichen alle `a`
unter `.nav-links` trifft. Der Einzug fiel von 37 px auf 1 px. Behoben mit

    header.geschrumpft .nav-links > a,
    header.geschrumpft .nav-links > .nav-item > a{padding:5px 0;}

**Regel daraus:** Bei jeder Messung am Kopf, am Menü oder an etwas, das sich
beim Scrollen ändert, immer beide Zustände prüfen — normal *und* geschrumpft.
Dasselbe gilt für Hover, offenes Burger-Menü und `prefers-reduced-motion`.
Und bevor eine CSS-Regel mit einem Nachfahrenselektor (`a` statt `> a`)
geschrieben wird: nachsehen, was sie sonst noch trifft.

## Nie aus Screenshots ablesen

IDs, Schlüssel und URLs immer als Text erfragen. Eine falsch gelesene
Formspree-ID (`xgawqkn` statt `xgawqqkn`) hat mehrere Debug-Runden gekostet.

## Projektkonventionen

- Statisches HTML/CSS/JS, kein Framework, kein Build-Schritt. CSS und JS stehen
  direkt in den HTML-Dateien.
- **Alles auf Deutsch:** Kommentare, Texte, Commit-Nachrichten.
  Commit-Nachrichten **ohne Umlaute**.
- „Meisterbetrieb" darf nicht vorkommen. Es heißt **Elektrofachbetrieb**.
- Alle Pfade bleiben relativ — die Seite läuft unter einem Unterpfad.
- `marke.css` hält Farben (`#232F3B`, `#FFD700`) und bindet IBM Plex Sans lokal
  aus `schriften/` ein, bewusst nicht über Google Fonts.
- Generierte Logo-SVGs nie von Hand bearbeiten.
- Arbeitsbranches vor jeder Sitzung von `origin/master` zurücksetzen und mit
  `--force-with-lease` pushen.
