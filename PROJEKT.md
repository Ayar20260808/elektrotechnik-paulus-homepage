# Projekt: „Mehr erfahren" auf der Startseite klickbar machen

**Stand:** 21.08.2026 · **Branch:** `claude/home-page-rdyw91` · **Commit:** `1f6408e`
**Basis:** `master` bei `d84f9ac` (#78)

---

## Ausgangslage

Gemeldet wurde: Der Link **„Mehr erfahren"** in den Leistungskarten der Startseite
sei „sehr schwer anzuklicken", er solle „sehr leicht" sein.

Die Startseite ist eine statische Seite (`index.html`, rund 107 KB) mit acht
Leistungs-Unterseiten. In den Leistungen liegen neun Karten in einem 3×3-Raster.
Beim Überfahren vergrößert ein Skript die Karte und dunkelt die übrigen ab.

---

## Diagnose

Gemessen im Browser (Chromium über Playwright, 1440×900), nicht geschätzt.

### Befund 1 — der Link lag außerhalb des Fensters

Beim Überfahren wächst die Karte. Begrenzt wurde das im Skript nur am
**Abschnitt**, nie am **Bildschirm**. Ergebnis:

| Kartenreihe | Lage von „Mehr erfahren" nach dem Vergrößern |
|---|---|
| Reihe 1 (#0–#2) | erreichbar |
| Reihe 2 (#3–#5) | 72 px unter dem Fensterrand |
| Reihe 3 (#6–#8) | 243 px unter dem Fensterrand |

Bei **6 von 9 Karten** war der Link damit gar nicht erreichbar: Die Maus nach
unten zu bewegen heißt, das Fenster zu verlassen — die Karte schrumpft, der Link
springt weg. In einem simulierten Mausweg lief er 344 px davon.

(Diese Zahlen stammen von `SCALE = √6 ≈ 2,449`. Nach dem master-Update auf
`SCALE = 1,8` blieb der Fehler bestehen, mit 122–125 px Überstand.)

### Befund 2 — die eigentliche Ursache liegt tiefer

Ein Deckel auf den Skalierungsfaktor half nicht. Grund: Die Klasse `.ep-gross`
ändert die Kartenhöhe im **Layout**, nicht nur optisch — von 297 auf rund 495 px,
weil das Foto seit Commit #76 mit `object-fit:contain` vollständig gezeigt wird.
Selbst bei Faktor 1,08 reichte die Karte noch bis y = 1039 bei 900 px
Fensterhöhe. Gegen eine Layout-Änderung wirkt kein Skalierungs-Deckel.

---

## Was ausgeliefert wurde

Sieben Zeilen CSS in `index.html`, kein Eingriff ins Vergrößerungsskript:

```css
.service-card{position:relative; …}
.service-more::after{content:"";position:absolute;inset:0;z-index:3;}
```

Der Link spannt sich unsichtbar über die ganze Karte. Ein Klick an beliebiger
Stelle — Bild, Überschrift, Beschreibungstext — führt zur passenden
Leistungsseite. Auf dem Handy ebenso, dort wird gar nicht vergrößert. Der
Tastaturfokus bleibt auf dem sichtbaren Schriftzug.

### Nachgeprüft

- **18 von 18** Klicks auf Bild, Überschrift, Text und Schriftzug landeten auf
  der richtigen Seite (Testlauf auf der damaligen Basis).
- **10 von 11** Klicks auf der aktuellen master-Basis — zur Abweichung siehe
  „Offene Punkte".
- Mobil (390 px): keine Vergrößerung, Tipp aufs Bild navigiert korrekt.
- Reduzierte Bewegung: keine Vergrößerung, Klick funktioniert.
- Tastatur: Link fokussierbar, Enter navigiert.
- Optisch unverändert gegenüber master.

---

## Eine Korrektur, die zurückgenommen wurde

Der erste Versuch enthielt zusätzlich eine Begrenzung der Vergrößerung am
Fensterrand. Die Annahme dahinter — die Skalierung schiebe den Link aus dem Bild
— war nur zur Hälfte richtig (siehe Befund 2). Der Code war wirkungslos und
wurde vollständig entfernt. Der ursprüngliche Commit `60032d2` ist durch
`1f6408e` ersetzt.

---

## Offene Punkte

### 1. Der Merge nach `master` fehlt — die Änderung ist nicht live

Der Push auf `master` wurde von der Berechtigungsprüfung blockiert. Solange der
Commit nur auf dem Branch liegt, sieht ihn kein Besucher.

→ Erledigen über: `compare/master...claude/home-page-rdyw91` auf GitHub,
„Create pull request" → „Merge".

### 2. Klick weit oben auf einer vergrößerten Karte kann danebengehen

Weil die Vergrößerung das Raster umfließen lässt, kann der Hover kippen. Im Test
landete ein Klick auf der Bildfläche von *Smart Home* auf *Elektroinstallation*
— einmal in 11 Klicks.

Das ist eine bestehende Unruhe aus #75/#76, nicht neu. Die Folge verschiebt sich
allerdings: Vorher passierte bei so einem Klick nichts, jetzt kann er auf die
falsche Leistungsseite führen. **Entscheidung steht aus**, ob die Ursache
angegangen wird — das wäre ein Eingriff in das Vergrößerungsverhalten.

### 3. GitHub Pages — Status unbekannt

Ob Pages für das Repo eingeschaltet ist, ließ sich nicht prüfen: Die
Arbeitsumgebung blockt `github.io` und die eigene Domain. Im Repo liegen weder
ein `CNAME` noch Workflows unter `.github/workflows`. Falls die Adresse nach dem
Merge 404 zeigt: *Settings → Pages*, Source Branch `master`, Ordner `/root`.

### 4. Kleinere Beobachtungen am Rande

- Die drei Fotos im Referenzen-Bereich liegen auf `images.unsplash.com` — die
  einzigen externen Ressourcen der Seite. Die Seite schreibt an der Stelle
  selbst: „Platzhalterbilder — hier kommen eigene Projektfotos hin."
- Das Kontaktformular trägt den Hinweis, dass ein E-Mail-Dienst noch fehlt.
- Die ganzflächig klickbare Karte hat einen Preis: Text darin lässt sich nicht
  mehr bequem markieren. Üblich für klickbare Karten.

---

## Vorschau zum Herzeigen

Die Startseite wurde als eigenständige HTML-Datei gebaut — alle Fotos,
Schriften und die rund 170 Elektrosymbole eingebettet, rund 5,4 MB, kein Server
nötig. Für das Vorführen aufbereitet:

- Die drei externen Fotos zeigen eine ruhige Fläche „Projektfoto folgt" statt
  eines kaputten Bildes.
- Ein Klick auf eine Leistung blendet kurz „Diese Vorschau zeigt nur die
  Startseite" ein, statt auf einer Browser-Fehlerseite zu landen.

---

## Zeitleiste

| Schritt | Ergebnis |
|---|---|
| Startseite gerendert und gezeigt | Screenshots Desktop/Mobil, interaktive Vorschau |
| Fehler gemeldet | „Mehr erfahren" schwer klickbar |
| Diagnose im Browser | 6 von 9 Links außerhalb des Fensters |
| Erste Korrektur | zur Hälfte falsch, zurückgenommen |
| Ausgelieferte Korrektur | 7 Zeilen CSS, ganze Karte klickbar |
| Nachprüfung | Klicks, Mobil, Tastatur, reduzierte Bewegung |
| Offen | Merge nach master, Entscheidung zu Punkt 2 |
