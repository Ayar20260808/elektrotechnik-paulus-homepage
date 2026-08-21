# Zusammenfassung: Ablage Angebot ANG10201 (Hosseini)

Datum: 2026-08-21

## Anfrage

Im Chat wurde eine hochgeladene Datei `ANG10201_Hosseini_final.pdf` übergeben mit der Bitte,
sie "in der App an der richtigen Stelle" anzulegen.

## Prüfung des Repos

Dieses Repository (`elektrotechnik-paulus-homepage`) ist ausschließlich die öffentliche
Marketing-Website (statisches HTML, kein Backend, kein Kunden-/Login-Bereich). Alles, was hier
committet wird, ist nach dem Push öffentlich im Internet abrufbar — auch ohne Verlinkung von der
Startseite. Es gibt keinen bestehenden Bereich für Kundenangebote.

Da das PDF vermutlich Kundenname, Adresse und Preise enthält, wurde entschieden, die Datei
**nicht** in dieses öffentliche Repo zu legen.

## Zielort: Google Drive

Stattdessen wurde der passende Ordner in Google Drive gesucht. Fündig geworden in:

**Ordner "Referenzen für KI"** (Drive-Root), da dort bereits vergleichbare Angebotsdateien mit
gleichem Namensschema liegen (z. B. `Angebot-ANG-10183_selbstrechnend.xlsx`).

Link: https://drive.google.com/drive/folders/10xOXFEp5i2aEiv4p2K7uHfWb7f0r5o_Y

## Warum kein automatischer Upload möglich war

Das PDF ist ca. 278 KB groß. Base64-codiert sind das ca. 372.000 Zeichen. Da sich diese Art von
Zufallsdaten fast 1:1 in Tokens umrechnen lässt, würde ein Hochladen über den Chat-Kontext
(Lesen + erneutes Ausgeben in einem Werkzeugaufruf) weit über 700.000 Tokens benötigen — das
sprengt die Grenzen eines einzelnen Werkzeugaufrufs und ist nicht zuverlässig durchführbar
(Risiko von Bit-Fehlern/Beschädigung der Datei).

## Empfohlene manuelle Aktion

Datei `ANG10201_Hosseini_final.pdf` manuell (Drag & Drop im Browser) in den Ordner
"Referenzen für KI" in Google Drive ziehen:
https://drive.google.com/drive/folders/10xOXFEp5i2aEiv4p2K7uHfWb7f0r5o_Y

## Prompt zum Kopieren

Für eine Session/ein Tool mit direktem Dateisystem- und Google-Drive-Zugriff (z. B. Claude
Desktop mit lokalem Dateizugriff und Google-Drive-Verbindung):

```
Lade die lokale Datei ANG10201_Hosseini_final.pdf in den Google-Drive-Ordner
"Referenzen für KI" hoch (https://drive.google.com/drive/folders/10xOXFEp5i2aEiv4p2K7uHfWb7f0r5o_Y).
Die Datei ist ein Kundenangebot und folgt dem bestehenden Namensschema für Angebote in diesem
Ordner (vgl. Angebot-ANG-10183_selbstrechnend.xlsx). Lege sie unverändert dort ab.
```
