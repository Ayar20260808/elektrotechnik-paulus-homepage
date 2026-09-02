# Arbeitsregeln für dieses Projekt

Hier stehen **nur Regeln**. Alles, was veralten kann — Branchnamen, Commits,
Adressen, Stände, offene Punkte — steht in
[`docs/ARBEITSSTAND.md`](docs/ARBEITSSTAND.md) und **nirgendwo sonst**. Zwei
Dateien, die denselben Stand behaupten, widersprechen sich früher oder später;
genau das ist hier passiert und hat Arbeit gekostet.

## Grundregel — Grundlage für alles

**Wie eine Maschine arbeiten. Nicht raten. Nicht menscheln.**

Das ist keine Stilfrage, sondern die Arbeitsweise, auf der alles andere
aufbaut. Kein Beschönigen, kein Vermuten, kein Ausschmücken, keine
Entschuldigungen, keine Selbstkommentare. Fakten, Messwerte, klare Aussagen.

1. **Tu genau das, was verlangt wurde.** Nicht weniger, nicht mehr. Keine
   ungefragten Verbesserungen, keine Zusatzleistungen, keine Vorschläge mitten
   in der Ausführung.
2. **Bei Unsicherheit: sagen, dass du unsicher bist.** Benennen, was genau
   unklar ist, und eine einzige, kurze Frage stellen. Dann warten.
3. **Nie auf einer Vermutung weiterbauen.** Lieber stehenbleiben und fragen als
   etwas liefern, das auf einer Annahme beruht.
4. **Fakten prüfen statt annehmen.** Was messbar ist, wird gemessen. Was nicht
   überprüfbar ist, wird als ungeprüft gekennzeichnet.
5. **Was du nicht selbst kannst, sagst du zu.** Nicht umschreiben, nicht
   andeuten, nicht so tun, als sei es erledigt. Klar benennen: was geht nicht,
   warum nicht, und **welches Werkzeug oder welcher Handgriff es stattdessen
   kann**.
6. **Nichts übersehen.** Vollständig prüfen, nicht stichprobenartig. Wer eine
   Sache repariert, prüft danach das Ganze — nicht nur die reparierte Stelle.
   In diesem Projekt ist beim Beheben eines Fehlers zweimal ein neuer
   entstanden, weil nur der Ausschnitt geprüft wurde.
7. **Einen eigenen Befund erst melden, wenn er bestätigt ist.** Ein Messwert
   aus einem fehlerhaften Test ist kein Befund. Erst den Test prüfen, dann das
   Ergebnis.

Diese Regel steht über allem anderen in dieser Datei.

Konkret aufgetreten und zu vermeiden:

- „Zeig mir X" wurde mehrfach falsch gedeutet und mit Erklärungen statt eines
  Links beantwortet. **Auf „zeig mir" gehört ein Link, kein Absatz.**
- Eine Verbesserung wurde eingebaut, die niemand verlangt hatte (ganze
  Leistungskarte klickbar). Der Nutzer wollte es so, wie es war. **Nicht
  eigenmächtig erweitern.**
- Eine Diagnose wurde gepusht, bevor sie am aktuellen Stand geprüft war.
- Zwei Messungen lieferten Geisterbefunde, weil der **Test** falsch war, nicht
  die Seite. Beide Male wäre der Nutzer auf eine Reparatur angesetzt worden,
  die es nicht braucht.

## Vorschau-Links

Jede Vorschau-Adresse wird als **nackte Adresse in einem Codeblock** ausgegeben,
nie als eingebetteter Text-Link:

```
https://beispiel.de/vorschau/
```

Grund: So lässt sie sich kopieren und in einem eigenen Tab öffnen. Ob ein
eingebetteter Link einen neuen Tab öffnet, entscheidet der Client.

**Nach jeder Änderung sofort die Adresse ausgeben, ungefragt und als erste
Zeile der Antwort.** Auch dann, wenn die Antwort hauptsächlich eine Rückfrage
oder ein Bericht ist. Die gültige Adresse steht in `docs/ARBEITSSTAND.md`.

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
| `…github.io/…/vorschau/<branch>/` | der jeweilige Arbeitsbranch |
| `…hostingersite.com/` | was zuletzt hochgeladen wurde |

Ein Fehler auf der ersten Adresse bedeutet oft nur, dass die Korrektur noch
nicht gemergt ist — nicht, dass sie nicht funktioniert.

## In jedem Zustand messen, nicht nur im Ruhezustand

Ein Element kann in einem Zustand richtig aussehen und in einem anderen falsch.
Wer nur den Ruhezustand misst, meldet „stimmt" und liegt daneben.

Konkret passiert: Der Einzug im Leistungs-Untermenü war auf allen zehn Seiten
richtig gesetzt — **aber nur, solange der Kopf nicht geschrumpft war.** Sobald
man scrollte, griff `header.geschrumpft .nav-links a{padding:5px 0}` auch auf
die Links im Untermenü, weil der Selektor ohne Kindzeichen alle `a` trifft. Der
Einzug fiel von 37 px auf 1 px.

**Regel daraus:** Immer beide Kopfzustände prüfen, dazu Hover, offenes
Burger-Menü und `prefers-reduced-motion`. Und bevor eine CSS-Regel mit einem
Nachfahrenselektor (`a` statt `> a`) geschrieben wird: nachsehen, was sie sonst
noch trifft.

## Nie aus Screenshots ablesen

IDs, Schlüssel und URLs immer als Text erfragen. Eine falsch gelesene
Formspree-ID (`xgawqkn` statt `xgawqqkn`) hat mehrere Debug-Runden gekostet.

## Nie eine Oberfläche beschreiben, die du nicht siehst

Bei fremden Oberflächen (hPanel, Dateimanager, Wix, Google) gibt es keine
Messung — es gibt nur den Screenshot des Nutzers. Alles darüber hinaus ist
geraten und verstößt gegen Grundregel 3.

**Regel:** Erst den Screenshot anfordern, dann handeln. Es werden ausschließlich
Beschriftungen genannt, die auf einem Screenshot **dieser** Sitzung zu sehen
sind. Kein Menüpunkt, kein Knopf und kein Tastenkürzel aus allgemeinem Wissen.

Konkret passiert, alles im selben Arbeitsschritt (Upload zu Hostinger):

| Behauptet | Tatsächlich |
|---|---|
| „Haken oben links: Alle auswählen" | gibt es nicht |
| „Rechtsklick → Verschieben" | Beschriftung ungeprüft |
| „auf *Verwalten* klicken" | heißt *Armaturenbrett* / *Werkzeuge* |
| Onboarding: „Ich habe jemanden beauftragt" | führte in eine Sackgasse |

Vier falsche Wegbeschreibungen, eine Ursache.

**Zusatz:** Prüfe, ob der Browser die fremde Oberfläche übersetzt. Chrome
übersetzt im Hostinger-Dateimanager die **Dateinamen** mit (`schriften` →
`Schriften`). Das sieht wie ein Fehler aus und ist keiner. Die Adresszeile
zeigt immer die Wahrheit.

## Keine Zahl als Beweis nehmen, deren Zuverlässigkeit ungeprüft ist

Die Speicheranzeige von Hostinger wurde als Kontrolle benutzt, obwohl sie sich
vorher schon als unzuverlässig gezeigt hatte: sie meldete 760 KiB, während über
4 MB auf dem Server lagen. Das kostete vier Runden.

**Regel:** Bevor eine fremde Anzeige als Beweis dient, muss sie sich an einem
bekannten Wert bewährt haben. Sonst wird gegen etwas Nachrechenbares geprüft —
etwa Dateigrößen gegen das Paket.

## Projektkonventionen

- Statisches HTML/CSS/JS, kein Framework, kein Build-Schritt. CSS und JS stehen
  direkt in den HTML-Dateien und sind über alle zehn Seiten dupliziert. **Jede
  Änderung an Kopf, Fußzeile, Menü oder einem gemeinsamen Skript muss in alle
  zehn** — und danach als bytegleich nachgewiesen werden.
- **Alles auf Deutsch:** Kommentare, Texte, Commit-Nachrichten.
  Commit-Nachrichten **ohne Umlaute**.
- „Meisterbetrieb" darf nicht vorkommen. Es heißt **Elektrofachbetrieb**.
- Alle Pfade bleiben relativ — die Seite läuft unter einem Unterpfad.
- `marke.css` hält die Farben und bindet IBM Plex Sans lokal aus `schriften/`
  ein, bewusst nicht über Google Fonts.
- Generierte Logo-SVGs nie von Hand bearbeiten.
- Gemessene Werte gehören in die Commit-Nachricht.

## Git

- Entwickelt wird auf dem Arbeitsbranch aus `docs/ARBEITSSTAND.md`.
- **Der Arbeitsbranch wird nicht zurückgesetzt und nicht force-gepusht.** Er
  enthält den gesamten unveröffentlichten Stand. Eine frühere Fassung dieser
  Datei verlangte, ihn vor jeder Sitzung von `origin/master` neu zu setzen —
  das stammt aus der Zeit, als nach jeder Änderung per Squash nach `master`
  gemergt wurde. Es wird seit über 160 Commits nicht mehr gemergt. **Wer diese
  alte Regel heute befolgt, löscht die gesamte Arbeit.**
- Nach `master` wird nur gemergt, wenn der Nutzer es ausdrücklich sagt.
- Nie pauschal `git add -A`. Immer die beabsichtigten Dateien einzeln angeben
  und vorher `git status` prüfen.
