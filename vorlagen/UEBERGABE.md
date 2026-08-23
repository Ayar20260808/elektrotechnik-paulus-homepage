# Übergabe: Dokumentvorlagen für elektrotechnik-hub

Datum: 2026-08-21
Repo dieser Session: `Ayar20260808/elektrotechnik-paulus-homepage`, Branch `claude/new-session-cpv0ml`

## Ausgangslage

Der Nutzer hat `elektrotechnikhubvorlagen.zip` hochgeladen, ohne Begleittext. Die
ZIP enthält Vorlagen für Kundendokumente (Angebot, Auftragsbestätigung, Rechnung,
Lieferschein, Mahnung) im Erscheinungsbild der Firmenwebsite. Sie sind laut
enthaltenem README die Grundlage für Belegarten in einer separaten App namens
**elektrotechnik-hub** — diese App liegt in einem anderen Repo, das in dieser
Session nicht angebunden war (nicht in `add_repo` geholt).

## Was in dieser Session gemacht wurde

1. ZIP entpackt und geprüft (`vorlagen/basis`, `vorlagen/angebot`, `vorlagen/marke`,
   `vorlagen/README.md`).
2. Die Logo-Dateien aus `vorlagen/marke/` per `diff` gegen die kanonischen
   Root-Dateien dieses Repos verglichen (`logo-quer-farbe.svg`,
   `logo-quer-einfarbig.svg`, `logo-gestapelt-farbe.svg`, `logo-quer-weiss.svg`,
   `bildmarke-farbe.svg`) — **byte-identisch**. Das bestätigt die Aussage im
   Vorlagen-README, dass dieses Repo die verbindliche Quelle für das Logo ist.
3. Nutzer gefragt, was mit der ZIP geschehen soll (nur Prüfung vs. ins Repo
   aufnehmen) — Antwort: `vorlagen/` ins Repo aufnehmen.
4. `vorlagen/` komplett unverändert in dieses Repo kopiert (inkl.
   `vorlagen/marke/`, da die Stempel-Skripte per relativem Pfad `../marke/...`
   darauf zugreifen — keine Duplikat-Bereinigung vorgenommen, bewusst
   beibehalten als in sich geschlossenes Paket).
5. Committet und auf `claude/new-session-cpv0ml` gepusht.
6. PR erstellt: **https://github.com/Ayar20260808/elektrotechnik-paulus-homepage/pull/82**
   (Base: `master`). Kein PR-Template im Repo vorhanden, daher freie Struktur mit
   Summary/Test-plan.

## Offene Punkte / nicht erledigt

- **PDF-Erzeugung ungetestet**: Die Vorlagen benötigen `wkhtmltopdf`,
  `fonts-ibm-plex`, `librsvg2-bin`, `reportlab`, `pypdf` (siehe
  `vorlagen/README.md`). In dieser Session nicht installiert/ausprobiert.
- **PR-Monitoring**: Nutzer wurde gefragt, ob die PR beobachtet werden soll
  (CI-Fehler autofixen, Reviews beantworten) — noch keine Antwort erhalten, bevor
  das Thema wechselte. Falls gewünscht: `subscribe_pr_activity` für PR #82
  aufrufen.
- **elektrotechnik-hub-App selbst**: nicht Teil dieser Session. Falls dort
  weitergearbeitet werden soll (z. B. PDF-Erzeugung aus den Vorlagen bauen),
  muss das App-Repo erst per `add_repo` angebunden werden — Name/Owner ist
  bisher nicht bekannt.
- **E-Rechnung-Pflicht**: Laut `vorlagen/README.md` muss die App bis spätestens
  2027/2028 zusätzlich ZUGFeRD oder XRechnung für Rechnungen erzeugen können
  (aktuell nur PDF). Reine Terminlage, keine Code-Aufgabe in diesem Repo.
- **Mahnungs-Beträge**: Mahnpauschale/Verzugszinsen stehen in der Vorlage nur als
  Platzhalter (§288 BGB, abhängig von Unternehmer/Verbraucher) — laut README
  fachlich zu prüfen, nicht zu raten.

## Wichtige Pfade in diesem Repo

```
vorlagen/
├── README.md                         Gestaltungsregeln, Nummernkreise, Fallstricke
├── basis/
│   ├── dokument-basis.html           Gerüst für AB/Rechnung
│   ├── dokument_stempeln.py          Logo+Fußzeile-Overlay
│   ├── lieferschein-vorlage.html
│   └── mahnung-vorlage.html
├── angebot/
│   ├── angebot-vorlage.html
│   ├── logo_fusszeile_stempeln.py
│   └── beispiel/ANG-10201-hosseini.html
└── marke/                            Logo-Kopien + gerendertes @2400px-PNG
```

Referenz für Kontext, nicht duplizieren: `vorlagen/README.md` im Repo enthält
alle Gestaltungsregeln (Farben, Schrift, Ränder, Fußzeilen-Layout) und die
Tabelle der fünf Belegarten mit Nummernkreisen.

## Vorschlag für nächste Schritte

Falls die Arbeit an der PDF-Erzeugung/App fortgesetzt werden soll:

1. PR #82 Status prüfen (gemergt? offene Reviews?).
2. Ziel-Repo für `elektrotechnik-hub` klären und per `add_repo` anbinden.
3. Testweise ein Dokument aus `basis/dokument-basis.html` gemäß README-Anleitung
   rendern, um den `wkhtmltopdf`+Stempel-Workflow zu verifizieren.

## Vorgeschlagene Skills

- **implement** — falls als Nächstes die PDF-Erzeugung/Belegarten-Logik in der
  elektrotechnik-hub-App gebaut werden soll (Spec/Tickets vorhanden oder aus
  diesem Dokument ableitbar).
- **tdd** — falls die App testgetrieben entwickelt werden soll, insbesondere für
  die Stempel-/Rendering-Skripte.
- **domain-modeling** — falls Begriffe wie „Bedarfsposition ohne Gesamtbetrag“,
  Nummernkreise (ANG-/AB-/RE-/LS-/ZE-) oder die E-Rechnung-Fristen als
  Domänenmodell/Glossar festgehalten werden sollen.

Kein spezialisierter Video-, Design- oder Marketing-Skill ist hier einschlägig.
