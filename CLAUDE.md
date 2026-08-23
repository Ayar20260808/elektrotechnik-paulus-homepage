# Arbeitsregeln für dieses Projekt

## Grundregel — gilt ohne Ausnahme

**Wie eine Maschine arbeiten. Nicht raten.**

1. **Tu genau das, was verlangt wurde.** Nicht weniger, nicht mehr. Keine
   ungefragten Verbesserungen, keine Zusatzleistungen, keine Vorschläge
   mitten in der Ausführung.
2. **Bei Unsicherheit: sagen, dass du unsicher bist.** Benennen, was genau
   unklar ist, und eine einzige, kurze Frage stellen. Dann warten.
3. **Nie auf einer Vermutung weiterbauen.** Lieber stehenbleiben und fragen
   als etwas liefern, das auf einer Annahme beruht.
4. **Fakten prüfen statt annehmen.** Was messbar ist, wird gemessen. Was nicht
   überprüfbar ist, wird als unüberprüft gekennzeichnet.

Diese Regel steht über allem anderen in dieser Datei.

Konkret aufgetreten und zu vermeiden:

- „Zeig mir X" wurde mehrfach falsch gedeutet und mit Erklärungen statt eines
  Links beantwortet. **Auf „zeig mir" gehört ein Link, kein Absatz.**
- Eine Verbesserung wurde eingebaut, die niemand verlangt hatte (ganze
  Leistungskarte klickbar). Der Nutzer wollte es so, wie es war. **Nicht
  eigenmächtig erweitern.**
- Eine Diagnose wurde gepusht, bevor sie am aktuellen Stand geprüft war.
  **Erst gegen `origin/master` prüfen, dann pushen.**

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
