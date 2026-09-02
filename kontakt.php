<?php
/*
 * Kontaktformular: nimmt die Absendung entgegen und verschickt sie als
 * E-Mail. Ersetzt den Umweg ueber Formspree -- die Daten verlassen das
 * Haus nicht mehr.
 *
 * Verschickt wird ueber einen angemeldeten Postausgangsserver (SMTP),
 * nicht ueber die PHP-Funktion mail(). Grund: Der SPF-Eintrag der Domain
 * lautet "v=spf1 include:_spf.google.com ~all". Nur Googles Server
 * duerfen also im Namen der Domain verschicken. Eine Mail, die der
 * Webserver selbst abschickt, steht nicht in dieser Liste und landet im
 * Spam-Ordner -- ohne Fehlermeldung, man merkt es erst an ausbleibenden
 * Anfragen.
 *
 * Zugangsdaten stehen in kontakt-konfig.php, die nicht im Projekt liegt.
 * Vorlage: kontakt-konfig-beispiel.php
 */

declare(strict_types=1);

// Pflicht sind nur Vor- und Nachname. E-Mail und Nachricht sind
// freiwillig -- so gewuenscht. Folge: eine Anfrage kann ohne jede
// Rueckmeldemoeglichkeit ankommen. Wenn keine Adresse dabei ist, faellt
// unten der Reply-To weg; ein leerer waere ein ungueltiger Kopfeintrag.
const FELDER_PFLICHT = ['Vorname', 'Nachname'];
const FELDER_ALLE    = ['Vorname', 'Nachname', 'E-Mail', 'Telefon', 'Anliegen', 'Nachricht'];
const MAX_LAENGE     = 5000;

/* ---------- Hilfsmittel ---------- */

/** Zeilenumbrueche raus. Ohne das koennte jemand ueber ein Eingabefeld
 *  eigene Kopfzeilen einschleusen und die Mail an Dritte umleiten. */
function eine_zeile(string $wert): string {
    return trim(str_replace(["\r", "\n", "\0"], ' ', $wert));
}

/** Umlaute in Kopfzeilen muessen kodiert werden, sonst kommen sie als
 *  Buchstabensalat an. */
function kopf_kodieren(string $text): string {
    $text = eine_zeile($text);
    if (preg_match('/^[\x20-\x7E]*$/', $text)) return $text;
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}

/** Absenderadresse fuer eine Kopfzeile: Name kodiert, Adresse roh. */
function adresse_mit_namen(string $adresse, string $name): string {
    $adresse = eine_zeile($adresse);
    return $name === '' ? $adresse : kopf_kodieren($name) . ' <' . $adresse . '>';
}

function ist_mail(string $wert): bool {
    return (bool) filter_var($wert, FILTER_VALIDATE_EMAIL);
}

/* ---------- SMTP ---------- */

class SmtpFehler extends RuntimeException {}

/**
 * Kleiner SMTP-Client ohne Fremdbibliothek. Absichtlich ohne Composer:
 * auf einfachem Webspace ist oft keiner vorhanden, und eine Abhaengigkeit
 * weniger ist eine Fehlerquelle weniger.
 */
final class Smtp {
    private $verbindung;
    private array $k;

    public function __construct(array $konfig) { $this->k = $konfig; }

    private function lesen(): string {
        $antwort = '';
        while (($zeile = fgets($this->verbindung, 515)) !== false) {
            $antwort .= $zeile;
            // Bei mehrzeiligen Antworten steht am vierten Zeichen ein
            // Bindestrich, bei der letzten Zeile ein Leerzeichen.
            if (strlen($zeile) < 4 || $zeile[3] !== '-') break;
        }
        if ($antwort === '') throw new SmtpFehler('Keine Antwort vom Postausgangsserver.');
        return $antwort;
    }

    private function sagen(string $befehl, string $erwartet): string {
        if ($befehl !== '') fwrite($this->verbindung, $befehl . "\r\n");
        $antwort = $this->lesen();
        if (strncmp($antwort, $erwartet, strlen($erwartet)) !== 0) {
            // Das Passwort darf nie in einer Fehlermeldung auftauchen.
            $gezeigt = str_starts_with($befehl, 'AUTH') || $befehl === '' ? '(Anmeldung)' : explode(' ', $befehl)[0];
            throw new SmtpFehler('Server lehnte ab bei ' . $gezeigt . ': ' . trim($antwort));
        }
        return $antwort;
    }

    public function senden(string $von, string $an, string $kopf, string $koerper): void {
        $ziel = $this->k['smtp_sicherheit'] === 'ssl'
            ? 'ssl://' . $this->k['smtp_server'] . ':' . $this->k['smtp_port']
            : 'tcp://' . $this->k['smtp_server'] . ':' . $this->k['smtp_port'];

        $this->verbindung = @stream_socket_client($ziel, $fehlernr, $fehlertext, 20);
        if (!$this->verbindung) throw new SmtpFehler('Keine Verbindung: ' . $fehlertext);
        stream_set_timeout($this->verbindung, 20);

        $this->sagen('', '220');
        $this->sagen('EHLO ' . $this->eigener_name(), '250');

        if ($this->k['smtp_sicherheit'] === 'starttls') {
            $this->sagen('STARTTLS', '220');
            if (!stream_socket_enable_crypto($this->verbindung, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new SmtpFehler('Verschluesselung fehlgeschlagen.');
            }
            $this->sagen('EHLO ' . $this->eigener_name(), '250');
        }

        if (($this->k['smtp_passwort'] ?? '') !== '') {
            $this->sagen('AUTH LOGIN', '334');
            $this->sagen(base64_encode($this->k['smtp_benutzer']), '334');
            $this->sagen(base64_encode($this->k['smtp_passwort']), '235');
        }

        $this->sagen('MAIL FROM:<' . $von . '>', '250');
        $this->sagen('RCPT TO:<' . $an . '>', '250');
        $this->sagen('DATA', '354');

        // Leerzeile zwischen Kopf und Text: ohne sie liest das
        // Mailprogramm die ersten Textzeilen als Kopfzeilen weiter, und
        // die Anfrage kommt fast leer an.
        // Punkt am Zeilenanfang verdoppeln, sonst gilt er als Ende.
        $daten = $kopf . "\r\n\r\n" . $koerper;
        $daten = preg_replace('/^\./m', '..', $daten);
        fwrite($this->verbindung, $daten . "\r\n.\r\n");
        $this->lesenPruefen('250');

        $this->sagen('QUIT', '221');
        fclose($this->verbindung);
    }

    private function lesenPruefen(string $erwartet): void {
        $antwort = $this->lesen();
        if (strncmp($antwort, $erwartet, strlen($erwartet)) !== 0) {
            throw new SmtpFehler('Server nahm die Nachricht nicht an: ' . trim($antwort));
        }
    }

    private function eigener_name(): string {
        $name = $_SERVER['SERVER_NAME'] ?? 'localhost';
        return preg_match('/^[A-Za-z0-9.\-]+$/', $name) ? $name : 'localhost';
    }
}

/* ---------- Nachricht bauen ---------- */

function nachricht_bauen(array $eingaben, array $k): array {
    $absender  = adresse_mit_namen($k['absender'], $k['absender_name'] ?? '');
    $kundenmail = eine_zeile($eingaben['E-Mail']);
    $kundenname = eine_zeile($eingaben['Vorname'] . ' ' . $eingaben['Nachname']);

    $betreff = 'Anfrage von ' . $kundenname;
    if (($eingaben['Anliegen'] ?? '') !== '') $betreff .= ' - ' . eine_zeile($eingaben['Anliegen']);

    $zeilen = [];
    foreach (FELDER_ALLE as $feld) {
        $wert = trim($eingaben[$feld] ?? '');
        if ($wert === '') continue;
        // Nur die Nachricht darf mehrzeilig sein. Bei den kurzen Feldern
        // wuerde ein Umbruch die naechste Zeile wie eine Kopfzeile
        // aussehen lassen -- harmlos, aber verwirrend zu lesen.
        if ($feld !== 'Nachricht') $wert = eine_zeile($wert);
        $zeilen[] = $feld . ': ' . $wert;
    }
    $zeilen[] = '';
    $zeilen[] = '-- ';
    $zeilen[] = 'Gesendet ueber das Kontaktformular der Homepage';
    $zeilen[] = date('d.m.Y H:i');
    $koerper = implode("\r\n", str_replace("\r\n", "\n", $zeilen));
    $koerper = str_replace("\n", "\r\n", $koerper);

    $kopfzeilen = [
        'From: ' . $absender,
        'To: ' . eine_zeile($k['empfaenger']),
    ];
    // Damit "Antworten" beim Kunden landet und nicht bei uns selbst.
    // Ohne Adresse entfaellt die Zeile -- "Name <>" waere ungueltig.
    if ($kundenmail !== '') {
        $kopfzeilen[] = 'Reply-To: ' . adresse_mit_namen($kundenmail, $kundenname);
    }
    $kopf = implode("\r\n", array_merge($kopfzeilen, [
        'Subject: ' . kopf_kodieren($betreff),
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . substr(strrchr($k['absender'], '@') ?: '@localhost', 1) . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ]));

    return [$kopf, $koerper];
}

/* ---------- Ablauf ---------- */

function pruefen(array $post): array {
    $fehler = [];
    // Honigtopf: ein Feld, das kein Mensch sieht. Nur Maschinen fuellen es.
    if (trim($post['website'] ?? '') !== '') $fehler[] = 'abgelehnt';

    $eingaben = [];
    foreach (FELDER_ALLE as $feld) {
        $wert = (string) ($post[$feld] ?? '');
        if (mb_strlen($wert) > MAX_LAENGE) $wert = mb_substr($wert, 0, MAX_LAENGE);
        $eingaben[$feld] = trim($wert);
    }
    foreach (FELDER_PFLICHT as $feld) {
        if ($eingaben[$feld] === '') $fehler[] = $feld . ' fehlt';
    }
    if ($eingaben['E-Mail'] !== '' && !ist_mail($eingaben['E-Mail'])) $fehler[] = 'E-Mail ungueltig';

    return [$eingaben, $fehler];
}

// Beim direkten Einbinden aus einem Test wird hier nichts ausgefuehrt.
if (PHP_SAPI !== 'cli' || !empty($_SERVER['KONTAKT_ECHT'])) {
    // Die Zugangsdaten am liebsten eine Ebene ueber dem Web-Ordner:
    // was dort liegt, kann der Browser nicht abrufen. Faellt PHP auf dem
    // Server einmal aus, wuerde eine Datei im Web-Ordner sonst als
    // Klartext ausgeliefert -- mitsamt Passwort.
    $konfigPfade = [dirname(__DIR__) . '/kontakt-konfig.php', __DIR__ . '/kontakt-konfig.php'];
    $k = null;
    foreach ($konfigPfade as $pfad) {
        if (is_readable($pfad)) { $k = require $pfad; break; }
    }
    if (!is_array($k)) {
        error_log('Kontaktformular: kontakt-konfig.php nicht gefunden.');
        http_response_code(500);
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Location: ' . $k['ziel_fehler'], true, 303);
        exit;
    }

    [$eingaben, $fehler] = pruefen($_POST);

    if ($fehler !== []) {
        header('Location: ' . $k['ziel_fehler'], true, 303);
        exit;
    }

    try {
        [$kopf, $koerper] = nachricht_bauen($eingaben, $k);
        (new Smtp($k))->senden($k['absender'], $k['empfaenger'], $kopf, $koerper);
        header('Location: ' . $k['ziel_erfolg'], true, 303);
    } catch (Throwable $e) {
        // Der Besucher bekommt keine technische Meldung zu sehen; die
        // Einzelheiten stehen im Fehlerprotokoll des Webservers.
        error_log('Kontaktformular: ' . $e->getMessage());
        header('Location: ' . $k['ziel_fehler'], true, 303);
    }
    exit;
}
