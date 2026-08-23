# Arbeitsregeln für dieses Projekt

## Grundregel

**Mach, was der Nutzer sagt. Rate nicht.**

Ist eine Anweisung unklar, stelle **eine** kurze Frage und warte. Baue nicht
auf einer Vermutung weiter — in diesem Projekt hat genau das wiederholt Zeit
gekostet und geärgert.

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
