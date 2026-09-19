import "jsr:@supabase/functions-js/edge-runtime.d.ts";
import { createClient } from "jsr:@supabase/supabase-js@2";

// Kundenportal -- liefert genau einen freigegebenen Auftrag aus.
//
// Warum es diese Funktion ueberhaupt gibt:
// Die Tabellen Auftraege, Kontakte, Kundendokumente und Termine erlauben
// jedem eingeloggten Benutzer das Lesen aller Zeilen (Regel "true"). Ein
// Kunde darf deshalb NIE ein Konto in dieser Datenbank bekommen -- er waere
// sonst sofort berechtigt, alle Daten aller Kunden zu lesen.
//
// Stattdessen: Der Kunde hat gar keinen Datenbankzugang. Er schickt nur den
// Token aus seinem Link. Diese Funktion arbeitet mit dem Service-Schluessel
// und entscheidet selbst, was herausgeht. Die gesamte Sicherheit des
// Portals steckt damit in dieser Datei.
//
// Grundsatz: Es geht nichts raus, was in der Freigabe nicht ausdruecklich
// erlaubt ist. Im Zweifel weglassen.

// Dokumentarten, die ein Kunde sehen darf. Eine Positivliste, keine
// Sperrliste -- eine neue, unbekannte Dokumentart bleibt dadurch von selbst
// draussen statt versehentlich drinnen.
//
// Bewusst NICHT enthalten:
//   calculation  Kalkulation      -- Einkaufspreise und Marge
//   information  Lohnzeitenliste  -- Lohndaten der Mitarbeiter
//   order_form   Bestellschein    -- Lieferanten und Konditionen
//   generic      Allgemein        -- Inhalt unbekannt
//   letter       Brief            -- Inhalt unbekannt
//   invoice_notice / dunning      -- Zahlungserinnerung und Mahnung
//        gehoeren in die Hand des Inhabers, nicht stillschweigend ins Portal
const DOKUMENTE_ERLAUBT = new Set([
  "offer",            // Angebot
  "confirmation",     // Auftragsbestaetigung
  "invoice",          // Rechnung, Teilrechnung
  "reversal_invoice", // Stornorechnung
  "delivery_note",    // Lieferschein
  "measurement",      // Aufmassdokument
]);

// Zweite Huerde: der Bearbeitungsstand. Die Art allein reicht nicht -- eine
// geloeschte Rechnung ist immer noch eine Rechnung. In der Datenbank sind
// 495 von 1334 Dokumenten geloescht und 33 noch Entwurf; beides darf ein
// Kunde nicht zu sehen bekommen. Ein Entwurf ist noch nicht abgestimmt, ein
// geloeschtes Dokument gilt nicht mehr.
//
// "Storniert" bleibt drin: die Stornierung gehoert zum Vorgang, den der
// Kunde ohnehin kennt, und ohne sie stuende eine aufgehobene Rechnung
// weiter unkommentiert im Portal.
const DOKUMENT_STATUS_ERLAUBT = new Set([
  "Erstellt",
  "Erstellt (Hochgeladen)",
  "Versendet",
  "Storniert",
]);

// Die internen Pipelinenamen sagen dem Kunden nichts und verraten unnoetig
// etwas ueber den Innenbetrieb. Hier stehen die Worte, die er stattdessen
// zu sehen bekommt, plus die Stufe fuer die Fortschrittsanzeige.
const STATUS_FUER_KUNDE: Record<string, { text: string; schritt: number }> = {
  "Detailgespräch":   { text: "In Planung",                           schritt: 1 },
  "Umsetzungsbeginn": { text: "Start steht bevor",                    schritt: 2 },
  "In Umsetzung":     { text: "In Arbeit",                            schritt: 3 },
  "Kundenrechnung":   { text: "Arbeiten fertig, Rechnung unterwegs",  schritt: 4 },
  "Abgeschlossen":    { text: "Abgeschlossen",                        schritt: 5 },
};
const STATUS_STUFEN = 5;

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
  "Access-Control-Allow-Methods": "POST, GET, OPTIONS",
};

const antwort = (koerper: unknown, status = 200) =>
  new Response(JSON.stringify(koerper), {
    status,
    headers: { ...corsHeaders, "Content-Type": "application/json" },
  });

// Eine einzige Fehlermeldung fuer jeden abgelehnten Fall: unbekannter Token,
// abgeschaltete Freigabe, abgelaufene Freigabe, geloeschter Auftrag. Wer
// Tokens durchprobiert, soll aus der Antwort nicht ablesen koennen, ob er
// einen echten erwischt hat.
const ABGELEHNT = {
  ok: false,
  fehler: "Dieser Link ist nicht (mehr) gueltig. Bitte wenden Sie sich an uns.",
};

Deno.serve(async (req: Request) => {
  if (req.method === "OPTIONS") {
    return new Response("ok", { headers: corsHeaders });
  }

  try {
    // Der Token darf aus dem Nachrichtenkoerper oder aus der Adresszeile
    // kommen -- so laesst sich die Funktion auch im Browser schnell pruefen.
    let token: unknown = null;
    if (req.method === "POST") {
      const koerper = await req.json().catch(() => ({}));
      token = (koerper ?? {}).token;
    } else {
      token = new URL(req.url).searchParams.get("token");
    }

    // Form pruefen, bevor die Datenbank ueberhaupt gefragt wird: genau 32
    // Hex-Zeichen, so wie die Tabelle sie erzeugt. Alles andere ist ein
    // Tippfehler oder ein Versuch und wird sofort abgewiesen.
    if (typeof token !== "string" || !/^[0-9a-f]{32}$/.test(token)) {
      return antwort(ABGELEHNT, 404);
    }

    const supabase = createClient(
      Deno.env.get("SUPABASE_URL")!,
      Deno.env.get("SUPABASE_SERVICE_ROLE_KEY")!,
    );

    const { data: freigabe, error: freigabeFehler } = await supabase
      .from("portal_freigaben")
      .select("*")
      .eq("token", token)
      .maybeSingle();

    if (freigabeFehler) {
      return antwort({ ok: false, fehler: "Serverfehler" }, 500);
    }
    if (!freigabe || !freigabe.aktiv) {
      return antwort(ABGELEHNT, 404);
    }
    if (freigabe.gueltig_bis && new Date(freigabe.gueltig_bis) < new Date()) {
      return antwort(ABGELEHNT, 404);
    }

    // Der Auftrag. Es werden nur die Spalten geholt, die ueberhaupt in Frage
    // kommen -- "notizen", "projekttitel" und "mitarbeiter" stehen bewusst
    // nicht dabei und koennen dadurch gar nicht erst durchrutschen.
    const { data: auftrag } = await supabase
      .from("Auftraege")
      .select("id, display_id, projektnr, name, status, adresse, erstellt_hero, projektvolumen, is_deleted")
      .eq("id", freigabe.auftrag_id)
      .maybeSingle();

    if (!auftrag || auftrag.is_deleted) {
      return antwort(ABGELEHNT, 404);
    }

    // Anrede. Aus dem Kontakt wird ausschliesslich der Name gelesen --
    // Adresse, Telefon, E-Mail und Notizen bleiben hier draussen.
    let anrede: string | null = null;
    if (freigabe.kontakt_id) {
      const { data: kontakt } = await supabase
        .from("Kontakte")
        .select("\"Name der Firma\", \"Vorname\", \"Nachname\"")
        .eq("id", freigabe.kontakt_id)
        .maybeSingle();
      if (kontakt) {
        const firma = (kontakt as Record<string, string | null>)["Name der Firma"];
        const vorname = (kontakt as Record<string, string | null>)["Vorname"] ?? "";
        const nachname = (kontakt as Record<string, string | null>)["Nachname"] ?? "";
        anrede = (firma && firma.trim()) || `${vorname} ${nachname}`.trim() || null;
      }
    }

    // Antwort Stueck fuer Stueck zusammensetzen. Jedes Feld haengt an seinem
    // eigenen Schalter aus der Freigabe.
    const status = STATUS_FUER_KUNDE[auftrag.status ?? ""] ?? null;

    const ergebnis: Record<string, unknown> = {
      ok: true,
      auftragsnummer: auftrag.projektnr ?? auftrag.display_id ?? null,
      angelegt_am: auftrag.erstellt_hero ?? null,
      anrede,
      nachricht: freigabe.nachricht ?? null,
      beschreibung: freigabe.zeigt_beschreibung ? (auftrag.name ?? null) : null,
      adresse: freigabe.zeigt_adresse ? (auftrag.adresse ?? null) : null,
      status: freigabe.zeigt_status && status
        ? { text: status.text, schritt: status.schritt, stufen: STATUS_STUFEN }
        : null,
      termine: [] as unknown[],
      dokumente: [] as unknown[],
      zeigt_betraege: freigabe.zeigt_betraege === true,
    };

    if (freigabe.zeigt_termine) {
      const { data: termine } = await supabase
        .from("Termine")
        .select("id, titel, start_zeit, ende_zeit, ganztaegig, is_done, is_deleted")
        .eq("auftrag_id", auftrag.id)
        .order("start_zeit", { ascending: true });

      // "beschreibung", "mitarbeiter" und "ressourcen" bleiben draussen:
      // interne Einsatzplanung geht den Kunden nichts an.
      ergebnis.termine = (termine ?? [])
        .filter((t) => !t.is_deleted)
        .map((t) => ({
          titel: t.titel ?? "Termin",
          start_zeit: t.start_zeit,
          ende_zeit: t.ende_zeit,
          ganztaegig: t.ganztaegig === true,
          erledigt: t.is_done === true,
        }));
    }

    if (freigabe.zeigt_dokumente) {
      const { data: dokumente } = await supabase
        .from("Kundendokumente")
        .select("id, nummer, grundart, dokumentart, status, datum, wert, mwst, waehrung, zahlungsstatus, faellig_am")
        .eq("auftrag_id", auftrag.id)
        .order("datum", { ascending: false });

      ergebnis.dokumente = (dokumente ?? [])
        .filter((d) =>
          DOKUMENTE_ERLAUBT.has(d.grundart ?? "") &&
          DOKUMENT_STATUS_ERLAUBT.has(d.status ?? "")
        )
        .map((d) => {
          const zeile: Record<string, unknown> = {
            nummer: d.nummer ?? null,
            art: d.dokumentart ?? null,
            datum: d.datum ?? null,
            status: d.status ?? null,
          };
          // Geld nur, wenn ausdruecklich freigegeben.
          if (freigabe.zeigt_betraege) {
            zeile.wert = d.wert ?? null;
            zeile.mwst = d.mwst ?? null;
            zeile.waehrung = d.waehrung ?? "EUR";
            zeile.zahlungsstatus = d.zahlungsstatus ?? null;
            zeile.faellig_am = d.faellig_am ?? null;
          }
          return zeile;
        });
    }

    // Das Projektvolumen ist eine Geldangabe und haengt am selben Schalter.
    if (freigabe.zeigt_betraege && auftrag.projektvolumen != null) {
      ergebnis.projektvolumen = auftrag.projektvolumen;
    }

    // Protokoll: wann zuletzt geoeffnet und wie oft. Der Zaehler wird
    // gelesen und zurueckgeschrieben; bei zwei Aufrufen in derselben
    // Sekunde kann einer verlorengehen. Fuer einen Aufrufzaehler reicht das.
    await supabase
      .from("portal_freigaben")
      .update({
        zuletzt_geoeffnet: new Date().toISOString(),
        anzahl_aufrufe: (freigabe.anzahl_aufrufe ?? 0) + 1,
      })
      .eq("id", freigabe.id);

    return antwort(ergebnis);
  } catch (e) {
    return antwort({ ok: false, fehler: "Serverfehler", detail: String(e) }, 500);
  }
});
