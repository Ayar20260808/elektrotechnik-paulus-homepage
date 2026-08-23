# Projektstand — Homepage Elektrotechnik Paulus

Stand: 18.08.2026 · `master` bei `d719b2f` · Pull Requests #52–#81

Diese Datei fasst zusammen, wie die Seite aufgebaut ist, welche Entscheidungen
dahinterstehen und was noch offen ist. Sie ist als Einstieg gedacht, wenn die
Arbeit in einer neuen Sitzung weitergeht. Ganz unten steht ein fertiger Prompt
zum Kopieren.

---

## 1. Was das Projekt ist

Statische Website ohne Build-Schritt. Kein Framework, kein npm, keine
Abhängigkeiten — HTML, CSS und JavaScript stehen direkt in den Dateien.

| Datei | Inhalt |
|---|---|
| `index.html` | Die komplette Startseite, rund 2.050 Zeilen. CSS im `<style>`-Block im Kopf, JavaScript im `<script>`-Block am Ende. Nahezu die gesamte Arbeit passiert hier. |
| `leistung-*.html` | Acht Unterseiten, eine je Leistung. |
| `elektrosymbole/` | 169 PNG-Symbole aus der Jetplan-Bibliothek plus `manifest.json`. |
| `*.jpg` | 43 Fotos: Hero, Leistungskarten, Kunden, Bilderband, Porträts. |
| `marke.css`, `schriften/`, `logo-*.svg` | Markenmittel. |

Veröffentlicht über GitHub Pages:
<https://ayar20260808.github.io/elektrotechnik-paulus-homepage/>

Beim Aufrufen ein `?frisch=NN` anhängen und die Zahl bei jeder Änderung
hochzählen — sonst zeigt der Browser den gespeicherten alten Stand.

---

## 2. Aufbau der Startseite

Die Abschnitte wechseln bewusst zwischen dunkel und hell:

1. **Hero** — dunkel, Bilderfolge aus vier Fotos im Hintergrund
2. **Vertrauensleiste** — hell, vier Punkte mit Symbol
3. **Leistungen** — dunkel, Planhintergrund mit Symbolanimation, neun Karten
4. **Marken, mit denen wir arbeiten** — weiß, Laufband, rechtsbündige Überschrift
5. **Unsere Kunden** — dunkel, gleiche Fläche wie die Leistungen, vier Karten
6. **Aus dem Arbeitsalltag** — weiß, Bilderband mit 14 Fotos
7. **Ablauf** — hellgrau, vier Schritte
8. **Referenzen** — weiß, drei Projekte
9. **Bewertungen** — hellgrau, vier Stimmen
10. **Ansprechpartner** — weiß, Porträt und Kontaktwege
11. **Kontakt** — hellgrau, Formular und Direktkontakt

---

## 3. Die drei beweglichen Teile

### 3.1 Hero-Bilderfolge

Vier Bildebenen liegen übereinander, sichtbar ist immer genau eine. Alle vier
Sekunden wechselt die aktive Ebene, die Überblendung dauert 1,1 Sekunden. Dazu
läuft ein sehr langsamer Zoom über 90 Sekunden.

- Bildebene: Deckkraft `.78`
- Overlay: Verlauf `rgba(26,34,42,.88)` → `.66` → `.22` von links nach rechts
- Unter 1000 px läuft der Verlauf **senkrecht** (`.86` → `.80` → `.88`), weil
  der Text dort über die volle Breite geht und sonst über der hellen rechten
  Seite stünde. Vorher lag der Kontrast auf dem Handy bei 2,3 : 1.

`hero-5.jpg` (Foto des Inhabers) wurde auf Wunsch aus der Folge genommen und
liegt ungenutzt im Ordner.

### 3.2 Symbolanimation in Leistungen und Kunden

Ein Modul, zweimal aufgerufen — einmal für `#leistungen`, einmal für `#kunden`:

```js
symbolFlaeche({ bg, karten, kopf, raster })
```

Beide Flächen laufen unabhängig, jede mit eigenem Stapel.

**Wie die Plätze entstehen.** Best-Candidate-Sampling: pro Punkt werden 300
Kandidaten gewürfelt und der genommen, der am weitesten von allen schon
gesetzten entfernt ist. Das ergibt eine gleichmäßige Verteilung ohne Raster.

**Was frei bleiben muss.** Karten und Überschriften zählen als belegte Flächen.
Bei der Überschrift wird die tatsächliche Textbreite je Zeile gemessen
(`Range.getClientRects()`), nicht der volle Block.

**Warum die Größe schwankt.** Bei 1024–1200 px sind die Fugen zwischen den
Karten zu schmal für 54-px-Symbole; es standen nur 1 bis 8 statt 15. Die Größe
wird deshalb schrittweise reduziert: 54 → 46 → 40 → 34 px, bis alle 15 Plätze
besetzt sind.

**Ohne Wiederholung.** Ziehen ohne Zurücklegen: ein gemischter Stapel aller
169 Indizes, erst wenn er leer ist, wird neu gemischt. Zusätzlich wird nie ein
Symbol gezogen, das gerade sichtbar ist.

**Lebenszyklus je Platz.** 1400 ms einblenden, 1500 ms stehen, 220 ms
ausblenden, 600 ms Pause. Der Startversatz ist über den ganzen Zyklus gestreut,
damit die 15 Plätze nicht im Gleichtakt blinken.

**Warum erst verzögert gemessen wird.** Die Karten fahren beim ersten
Sichtbarwerden per CSS-Transition ein. Ein IntersectionObserver wartet die
Einfahrt ab und misst erst 850 ms später — sonst würden Symbole an der noch
nicht fertigen Kartenposition platziert und lägen danach auf der Karte.

### 3.3 Leistungskarte beim Überfahren

Die Karte wächst um 20 Prozent (`SCALE = 1.2`), zeigt das Foto vollständig und
klappt sechs Detailpunkte in zwei Spalten aus. Das steckt an Regeln dahinter:

**Die Schrift wird zurückgerechnet.** Die Skalierung zöge sonst auch den Text
auf. Schriftgrößen, Abstände und Linien werden durch denselben Faktor geteilt,
den das Skript setzt — der Text erscheint dadurch genauso groß wie in einer
normalen Karte, es steht nur mehr darin.

**Die Karte behält ihre Rasterhöhe.** Das Skript friert die Höhe in
`--ep-hoehe` ein; `contain:size` sorgt dafür, dass der überstehende Inhalt
nicht in die Größenberechnung des Rasters einfließt. Ohne das gab die Zeile bei
1024 px um 28 px nach und alles darunter sprang.

**Das weiße Feld darf nach unten hinauslaufen.** Es bekommt dort Rahmen und
Schatten, damit es wie eine Fortsetzung der Karte wirkt. `overflow:hidden` am
Abschnitt musste dafür weg — Planfoto und Symbole werden ohnehin von
`.leistungen-bg` beschnitten.

**Die Bildhöhe ist gedeckelt.** Beim Vergrößern wächst das Foto auf sein volles
Seitenverhältnis; bei breiten Fenstern ist das mehr, als der Platz hergibt. Die
Höhe wird so begrenzt, dass das verlängerte Feld höchstens bis in die Fuge
zwischen zwei Kartenreihen reicht. Das Foto bleibt vollständig sichtbar
(`object-fit:contain`) und behält mindestens 88 Prozent seiner Höhe.

**Die Auswahl folgt der Ruheposition, nicht dem Element unter dem Zeiger.**
Ein `mousemove` auf dem Abschnitt bestimmt anhand von `offsetLeft`/`offsetTop`,
welche Karte gemeint ist. Vorher hielt das verlängerte Feld einer vergrößerten
Karte den Zeiger fest und die Karte darunter war kaum zu treffen.

**Aus unter 768 px.** Kein Vergrößern ohne echten Mauszeiger, bei einspaltigem
Raster oder wenn Bewegung im System abgestellt ist.

---

## 4. Wie geprüft wird

Playwright gegen einen lokalen Server, bei 375, 600, 768, 900, 1024, 1200, 1440
und 1920 px. Geprüft wird jeweils:

- waagerechter Überlauf (`scrollWidth − clientWidth`) muss 0 sein
- keine JS-Fehler (`pageerror`)
- kein Symbol auf einer Karte oder Überschrift, keine Wiederholung
- alle neun Karten vergrößern sich, aus drei Anfahrpunkten je Karte
- kein „Mehr erfahren" von einer anderen Karte verdeckt
- Nachbarkarten verschieben sich nicht
- Textkontrast: Text ausblenden, Bereich fotografieren, hellsten Pixel suchen,
  Kontrast gegen Weiß rechnen, Schwelle 4,5 : 1

**Zwei Fallen bei den Tests.** `html{scroll-behavior:smooth}` — nach
`scrollIntoView` muss gewartet werden, sonst werden veraltete Positionen
gemessen. Und synthetische `MouseEvent`s greifen seit der Umstellung auf
`mousemove` nicht mehr; es muss mit `page.mouse.move()` getestet werden.

---

## 5. Zwei Auswahllisten

Beide sind private Seiten zum Anklicken; unten steht die Auswahl als Text zum
Kopieren.

- **Bildbestand**, alle 43 Fotos nach Einsatzort:
  <https://claude.ai/code/artifact/ac49045d-d705-46e6-953c-a5c2a7af65cf>
- **Elektrosymbole**, alle 169 Symbole nach Kategorie:
  <https://claude.ai/code/artifact/cb3f445c-96fc-484c-aff0-13576f7526d1>

---

## 6. Was noch offen ist

| Punkt | Stand |
|---|---|
| **Referenzbilder** | Drei Unsplash-Platzhalter. Es fehlen eigene Projektfotos. |
| **Bewertungen** | Beispieltexte. Es fehlt der Link zum Google-Profil. |
| **Kontaktformular** | Verschickt nichts. Es fehlt ein Mail-Dienst dahinter. Telefon, E-Mail und WhatsApp funktionieren. |
| **Hero-Bildauswahl** | Eine neue Auswahl aus dem Bildbestand wurde angekündigt, aber noch nicht übermittelt. |
| **Hosting und Domain** | Läuft auf GitHub Pages. Eigene Domain noch nicht aufgeschaltet. |
| **Ungenutzte Dateien** | 11 Stück: `hero-5.jpg`, `leistung-waermepumpe.jpg`, `portrait-irfan-2.jpg`, `portrait-irfan-3.jpg` und die sieben alten `icon-*.png`. |
| **Beschädigte Symbole** | Vier Symbole liegen in Supabase selbst unvollständig gespeichert vor und lassen sich nur durch Neu-Upload reparieren. |

---

## 7. Arbeitsweise

- Entwickelt wird auf `claude/homepage-header-layout-2129fu`, gemergt wird per
  Squash nach `master`.
- Weil Squash-Merges die Historie umschreiben, wird der Branch vor jeder neuen
  Änderung von `origin/master` neu gesetzt und mit `--force-with-lease`
  gepusht.
- Commit-Nachrichten und Kommentare im Code sind auf Deutsch, ohne Umlaute in
  Commit-Nachrichten.
- Jede Änderung wird vor dem Commit im Browser gemessen, nicht nur angeschaut.

---

## 8. Prompt zum Kopieren

Für eine neue Sitzung: alles zwischen den Linien kopieren und abschicken.

---

```
Ich arbeite an der Homepage von Elektrotechnik Paulus
(Repository ayar20260808/elektrotechnik-paulus-homepage).

Lies zuerst PROJEKTSTAND.md im Wurzelverzeichnis — dort steht, wie die Seite
aufgebaut ist, welche Entscheidungen dahinterstehen und was noch offen ist.

Rahmen für die Arbeit:

- Statische Seite ohne Build-Schritt. Fast alles steckt in index.html: CSS im
  <style>-Block oben, JavaScript im <script>-Block unten.
- Entwickeln auf dem Branch claude/homepage-header-layout-2129fu, per Squash
  nach master mergen. Den Branch vorher von origin/master neu setzen und mit
  --force-with-lease pushen, weil Squash-Merges die Historie umschreiben.
- Kommentare im Code und Commit-Nachrichten auf Deutsch, in Commit-Nachrichten
  ohne Umlaute.
- Jede Änderung vor dem Commit im Browser messen, nicht nur ansehen: mit
  Playwright bei 375, 600, 768, 900, 1024, 1200, 1440 und 1920 px auf
  waagerechten Überlauf, JS-Fehler, Überlappungen und Textkontrast (Schwelle
  4,5 : 1) prüfen. Die Messwerte in die Commit-Nachricht schreiben.
- Achtung beim Testen: html hat scroll-behavior:smooth, nach scrollIntoView
  also warten. Und die Karten-Vergrößerung reagiert auf mousemove, nicht auf
  synthetische MouseEvents — mit page.mouse.move() testen.
- Unter jede Antwort diese drei Links hängen:
  Homepage https://ayar20260808.github.io/elektrotechnik-paulus-homepage/?frisch=NN
  (NN bei jeder Änderung hochzählen)
  Bildbestand https://claude.ai/code/artifact/ac49045d-d705-46e6-953c-a5c2a7af65cf
  Elektrosymbole https://claude.ai/code/artifact/cb3f445c-96fc-484c-aff0-13576f7526d1
- Nach jeder Aufgabe das Ergebnis als Screenshot zeigen.

Meine Aufgabe:
[hier eintragen, was gemacht werden soll]
```

---
