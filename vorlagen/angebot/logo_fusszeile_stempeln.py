# -*- coding: utf-8 -*-
"""Stempelt Logo, Fusszeile und Seitenzahl auf jede Seite des Inhalts-PDFs.

Ablauf:
  1) wkhtmltopdf --enable-local-file-access --margin-top 34mm --margin-bottom 26mm \
       --margin-left 16.1mm --margin-right 16.1mm angebot-vorlage.html inhalt.pdf
  2) python3 stamp_footer.py
"""
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import A4
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from pypdf import PdfReader, PdfWriter

pdfmetrics.registerFont(
    TTFont("IBMPlexSans", "/usr/share/fonts/truetype/ibm-plex/IBMPlexSans-Regular.ttf")
)
pdfmetrics.registerFont(
    TTFont("IBMPlexSans-SemiBold", "/usr/share/fonts/truetype/ibm-plex/IBMPlexSans-SemiBold.ttf")
)
FOOTER_FONT = "IBMPlexSans"
FOOTER_FETT = "IBMPlexSans-SemiBold"

PAGE_W, PAGE_H = A4  # 595.27 x 841.89 pt

# --- je Angebot anpassen -------------------------------------------------
ANGEBOTSNR  = "ANG-XXXXX"
CONTENT_PDF = "inhalt.pdf"
LOGO_PATH   = "../marke/logo-quer-farbe@2400.png"
OUT_PDF     = "angebot.pdf"
# -------------------------------------------------------------------------

firmen_zeilen = [
    [("Elektrotechnik Paulus GmbH", True),
     (" | Wartburgstraße 12 | 50733 Köln | Geschäftsführer: Irfan Ayar", False)],
    [("HRB Amtsgericht Köln, HRB95654 | Steuernummer: 217/57271/409 | USt-IdNr.: DE320791990", False)],
    [("SPK Köln Bonn | IBAN: ", False),
     ("DE59 3705 0198 1934 5081 83", True),
     (" | BIC: COLSDE33XXX", False)],
]

MM = 2.83465
MARGIN_L = 16.1 * MM      # mittig: gleiche Breite wie DIN 5008, nur zentriert
MARGIN_R = 16.1 * MM      # (177,8 mm Satzbreite)

LOGO_W  = 105
LOGO_H  = LOGO_W / 4.7714
LOGO_X1 = PAGE_W - MARGIN_R
LOGO_X0 = LOGO_X1 - LOGO_W
LOGO_Y1 = PAGE_H - 40      # 14 mm Abstand zur Blattoberkante
LOGO_Y0 = LOGO_Y1 - LOGO_H


def rgb(hexwert):
    return [int(hexwert[i:i + 2], 16) / 255 for i in (0, 2, 4)]


def mittig_gemischt(c, mitte, y, segmente, groesse):
    """Zeichnet eine zentrierte Zeile aus fetten und normalen Teilen."""
    breite = sum(
        c.stringWidth(t, FOOTER_FETT if fett else FOOTER_FONT, groesse)
        for t, fett in segmente
    )
    x = mitte - breite / 2
    for t, fett in segmente:
        c.setFont(FOOTER_FETT if fett else FOOTER_FONT, groesse)
        c.drawString(x, y, t)
        x += c.stringWidth(t, FOOTER_FETT if fett else FOOTER_FONT, groesse)


def draw_page(c, seite, seiten_gesamt):
    """Zeichnet Logo und Fusszeile fuer EINE Seite."""
    c.drawImage(LOGO_PATH, LOGO_X0, LOGO_Y0, width=LOGO_W, height=LOGO_H,
                mask="auto", preserveAspectRatio=True)

    bottom, line_gap, gr = 34, 9, 6.5
    rule_y = bottom + (len(firmen_zeilen) - 1) * line_gap + 12

    c.setStrokeColorRGB(*rgb("F2F4F6"))
    c.setLineWidth(0.4)
    c.line(MARGIN_L, rule_y, PAGE_W - MARGIN_R, rule_y)

    c.setFillColorRGB(*rgb("6B7480"))
    y = rule_y - 10

    # links die Dokumentennummer (fett), rechts die Seitenzahl
    c.setFont(FOOTER_FETT, gr)
    c.drawString(MARGIN_L, y, ANGEBOTSNR)
    c.setFont(FOOTER_FONT, gr)
    c.drawRightString(PAGE_W - MARGIN_R, y, f"{seite} / {seiten_gesamt}")

    mitte = (MARGIN_L + (PAGE_W - MARGIN_R)) / 2
    for segmente in firmen_zeilen:
        mittig_gemischt(c, mitte, y, segmente, gr)
        y -= line_gap

    c.showPage()


reader = PdfReader(CONTENT_PDF)
seiten_gesamt = len(reader.pages)

c = canvas.Canvas("overlay.pdf", pagesize=A4)
for seite in range(1, seiten_gesamt + 1):
    draw_page(c, seite, seiten_gesamt)
c.save()

overlay = PdfReader("overlay.pdf")

writer = PdfWriter()
for i, page in enumerate(reader.pages):
    page.merge_page(overlay.pages[i])
    writer.add_page(page)

with open(OUT_PDF, "wb") as f:
    writer.write(f)

print("fertig:", seiten_gesamt, "Seiten gestempelt")
