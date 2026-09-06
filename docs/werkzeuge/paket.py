#!/usr/bin/env python3
"""Baut das ZIP-Paket fuer den Hostinger-Dateimanager.

Warum ein ZIP und nicht einzelne Dateien: Beim Herunterladen einzelner
Dateien verliert der Browser die Bindestriche im Namen. Aus
leistung-vde.html wird leistungvde.html, und die Seite ist dann nicht mehr
erreichbar. Innerhalb eines Archivs bleiben Namen unversehrt.

Aufnahme-Regel: alles, was Git kennt, minus der Entwicklungsdateien. So
kann nichts vergessen werden und nichts Fremdes hineinrutschen.

Aufruf:  python3 docs/werkzeuge/paket.py
"""
import os, subprocess, sys, zipfile
from datetime import date

# Diese gehoeren zur Entwicklung, nicht auf den Webserver.
DRAUSSEN_ORDNER = ('docs/', '.github/')
DRAUSSEN_DATEI  = ('AGENTS.md', 'CLAUDE.md', '.gitignore')

wurzel = subprocess.run(['git', 'rev-parse', '--show-toplevel'],
                        capture_output=True, text=True, check=True).stdout.strip()
os.chdir(wurzel)

alle = subprocess.run(['git', 'ls-files'], capture_output=True, text=True,
                      check=True).stdout.splitlines()
dabei = [p for p in alle
         if not p.startswith(DRAUSSEN_ORDNER) and p not in DRAUSSEN_DATEI]

ziel = f'seite-{date.today():%d-%m}.zip'
with zipfile.ZipFile(ziel, 'w', zipfile.ZIP_DEFLATED) as z:
    for p in dabei:
        z.write(p, p)

groesse = os.path.getsize(ziel)
print(f'{ziel}   {len(dabei)} Dateien   {groesse/1048576:.2f} MiB')

# Gegenprobe: laesst sich das Archiv lesen und ist alles drin?
with zipfile.ZipFile(ziel) as z:
    kaputt = z.testzip()
    drin = set(z.namelist())
print('Archiv lesbar:', 'ja' if kaputt is None else f'NEIN, defekt: {kaputt}')
fehlt = set(dabei) - drin
print('Fehlende Dateien:', 'keine' if not fehlt else sorted(fehlt))
if kaputt is not None or fehlt:
    sys.exit(1)
