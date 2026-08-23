# Vorlagen

Vorlagen für Kundendokumente von Elektrotechnik Paulus, im Erscheinungsbild der
Firmenwebsite (Logo, Hausfarben, Hausschrift). Grundlage für alle Belegarten in der
App **elektrotechnik-hub**.

```
vorlagen/
├── basis/                            ← Ausgangspunkt für JEDE Belegart
│   ├── dokument-basis.html           Gerüst mit Platzhaltern (BELEGART, EINLEITUNGSSATZ …)
│   ├── dokument_stempeln.py          setzt Logo und Fußzeile auf jede Seite
│   ├── lieferschein-vorlage.html     abweichende Tabelle: ohne Preise, mit Empfangsfeld
│   └── mahnung-vorlage.html          abweichende Tabelle: Liste offener Rechnungen
├── angebot/                          ← daraus abgeleitet, Belegart Angebot
│   ├── angebot-vorlage.html
│   ├── logo_fusszeile_stempeln.py
│   └── beispiel/
│       └── ANG-10201-hosseini.html   ausgefülltes Muster (freigegeben 16.08.2026)
└── marke/
    ├── logo-quer-farbe.svg           Standard für Dokumente (Verhältnis 4,77 : 1)
    ├── logo-quer-farbe@2400.png      daraus gerendert, für die PDF-Erzeugung
    ├── logo-gestapelt-farbe.svg      wenn wenig Breite da ist
    ├── logo-quer-weiss.svg           auf dunklem Grund
    ├── logo-quer-einfarbig.svg       einfarbiger Druck
    └── bildmarke-farbe.svg           nur das Zeichen (Favicon, Icon)
```

Die SVG-Dateien stammen aus dem Repo `elektrotechnik-paulus-homepage`. Das ist die
verbindliche Quelle – die Logodatei im Google-Drive-Ordner „Homepage" ist veraltet.

## Ein Dokument erzeugen

Voraussetzungen einmalig installieren:

```bash
apt-get install -y wkhtmltopdf fonts-ibm-plex librsvg2-bin
pip install reportlab pypdf
```

Dann:

```bash
cd basis
cp dokument-basis.html mein-dokument.html      # Platzhalter ersetzen

wkhtmltopdf --enable-local-file-access \
  --margin-top 34mm --margin-bottom 26mm \
  --margin-left 16.1mm --margin-right 16.1mm \
  mein-dokument.html inhalt.pdf

# DOKUMENTNR im Skript setzen, dann:
python3 dokument_stempeln.py
```

Logo und Fußzeile stehen bewusst **nicht** im HTML: wkhtmltopdf 0.12.6 ignoriert
`--header-html` und `--footer-html`. Deshalb der zweite Schritt.

## Gestaltungsregeln

| Zweck | Wert |
|---|---|
| Fließtext, Tabellenkopf, Linien | `#232F3B` Anthrazit |
| Detailzeilen | `#3A4956` |
| Beschriftungen, Fußzeile | `#6B7480` Grau |
| Flächen, feine Linien | `#F2F4F6` |
| Akzent (nur Bildmarke) | `#FFD700`, auf Weiß `#E6C200` |
| Schrift | IBM Plex Sans, Grundgröße 10 pt |
| Seitenränder | 16,1 mm links und rechts (mittig), oben 34 mm, unten 26 mm |
| Logo | oben rechts, 105 pt breit, 14 mm unter der Blattkante, auf jeder Seite |
| Fußzeile | links Belegnummer (halbfett), mittig Firmenzeilen mit halbfettem Firmennamen und halbfetter IBAN, rechts Seitenzahl `1 / 15` |

Gelb bleibt der Bildmarke vorbehalten. Linien im Dokument laufen in Anthrazit.

## Was sich je Belegart ändert

| Belegart | Nummernkreis | Ausgangsdatei | Besonderheit |
|---|---|---|---|
| Angebot | ANG- | `angebot/angebot-vorlage.html` | Eigentumsvorbehalt, Zahlungsbedingungen, Bindefrist |
| Auftragsbestätigung | AB- | `basis/dokument-basis.html` | voraussichtlicher Ausführungszeitraum |
| Rechnung | RE- | `basis/dokument-basis.html` | Leistungszeitraum-Zeile stehen lassen, Zahlungsziel, §14 UStG |
| Lieferschein | LS- | `basis/lieferschein-vorlage.html` | Empfangsbestätigung, **keine** Preise, kein Summenblock |
| Mahnung | ZE- | `basis/mahnung-vorlage.html` | Liste offener Rechnungen statt Positionen, neue Frist |

Kopf, Fußzeile, Logo, Ränder, Schrift und Farben sind bei allen fünf gleich – nur der
Textkörper unterscheidet sich. Angebot, Auftragsbestätigung und Rechnung nutzen die
Positionstabelle der Basisvorlage unverändert. Lieferschein und Mahnung brauchen eine
andere Tabelle, deshalb liegen sie als eigene Dateien daneben.

Bei **Mahnungen** stehen Mahnpauschale und Verzugszinsen nur als Platzhalter drin. Ihre
Höhe hängt davon ab, ob der Empfänger Unternehmer oder Verbraucher ist (§288 BGB) – das
gehört fachlich geprüft und nicht geraten.

## Drei Regeln, die schon Fehler verursacht haben

**Alternativpositionen** sind Leistungen, die möglicherweise entfallen. Ihr Betrag
gehört **nicht** in die Summe: Betrag in Klammern setzen und im Summenblock eine eigene
Zeile „Alternative Positionen" führen. Der Fachbegriff in Leistungsverzeichnissen dafür
ist „Bedarfsposition ohne Gesamtbetrag".

**Im Anschriftenfeld** des Kunden darf nicht die eigene Firmen-E-Mail stehen. Die gehört
in den Kopfblock rechts.

**Bei Rechnungen** muss der Leistungszeitraum im Kopfblock stehen – nach §14 UStG Pflicht.
Die Zeile ist in `dokument-basis.html` schon angelegt; bei allen anderen Belegarten wird
sie gelöscht.

## E-Rechnung: was auf die App zukommt

Ein PDF ist keine E-Rechnung im Sinne des Gesetzes; dafür braucht es ein strukturiertes
Format nach EN 16931 (XRechnung oder ZUGFeRD).

- seit 01.01.2025: E-Rechnungen **empfangen** und archivieren können – gilt bereits
- bis 31.12.2026: PDF im B2B weiter erlaubt, wenn der Empfänger zustimmt
- ab 01.01.2027: **ausstellen** Pflicht bei Vorjahresumsatz über 800.000 €
- ab 01.01.2028: Pflicht für alle inländischen Unternehmen im B2B
- dauerhaft ausgenommen: Rechnungen unter 250 €, B2C

Für Angebote, Auftragsbestätigungen und Lieferscheine bleibt diese Vorlage dauerhaft
richtig. Für **Rechnungen** muss die App bis spätestens 2028 zusätzlich ZUGFeRD oder
XRechnung erzeugen. ZUGFeRD ist der bequemere Weg: es bettet die strukturierten Daten in
genau so ein PDF ein, das Layout kann also bleiben.

## Fallstricke beim Rendern

- `page-break-before/after` in einer Tabellenzelle wird ignoriert. Soll an einer
  bestimmten Stelle umgebrochen werden: Tabelle beenden, ein
  `<div style="page-break-after: always"></div>` setzen, neue Tabelle **mit eigener
  Kopfzeile** beginnen – dann steht die Spaltenüberschrift auch auf der Folgeseite.
- `page-break-inside: avoid` auf `tbody` wirkt nicht und zerreißt stattdessen Texte.
- Beträge mit `white-space: nowrap` schützen, sonst rutscht das €-Zeichen in die
  nächste Zeile.
- Das Overlay braucht **eine Seite je Inhaltsseite**, sonst steht überall dieselbe
  Seitenzahl.
- Vor dem Versand das PDF als Bild ansehen, nicht nur den Text prüfen.

## Dieselben Inhalte in Supabase

Tabelle `Dokumentvorlagen` (Dateien) und Tabelle `Fähigkeiten`, Einträge 24 bis 29:
Gestaltung, Prüfregeln, Marke, Ablage in Hero, Leistungsverzeichnisse, Belegarten.
