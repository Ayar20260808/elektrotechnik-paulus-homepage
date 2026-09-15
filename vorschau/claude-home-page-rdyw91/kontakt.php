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

// Anhaenge: Ein Foto der Verteilung sagt mehr als drei Absaetze Text.
//
// Die Grenzen richten sich nach den Werkseinstellungen von PHP:
// upload_max_filesize 2 MB je Datei, post_max_size 8 MB je Absendung.
// Was der Webhoster wirklich erlaubt, ist nicht bekannt und war ohne
// groesseren Aufwand nicht zu erfahren. Deshalb bewusst der unguenstigste
// Fall: Was hierunter passt, passt auf jedem Server. Erlaubt Hostinger
// mehr, lassen sich diese beiden Zahlen jederzeit anheben -- die
// Obergrenze ist dann Googles Nachrichtengrenze von 25 MB, bei rund
// 37 Prozent Aufschlag durch die Kodierung also etwa 18 MB Rohdaten.
//
// Damit die 2 MB kein Hindernis sind, verkleinert die Seite Fotos schon
// im Browser, bevor sie abgeschickt werden. Ein Handyfoto von 4 MB wird
// dabei zu wenigen hundert Kilobyte, ohne dass man auf dem Bild etwas
// vermisst. Der Server sieht also nur noch kleine Dateien.
//
// Diese Grenzen bleiben trotzdem stehen: Sie greifen, wenn jemand das
// JavaScript abgeschaltet hat oder eine Datei schickt, die sich nicht
// verkleinern laesst.
const ANHANG_FELD       = 'Anhang';
const ANHANG_MAX_ANZAHL = 5;
const ANHANG_MAX_EINZEL = 2097152;    // 2 MB je Datei
const ANHANG_MAX_GESAMT = 6291456;    // 6 MB zusammen

// Geprueft wird der tatsaechliche Inhalt, nicht die Endung und nicht
// das, was der Browser behauptet. Beides laesst sich faelschen.
const ANHANG_TYPEN = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'image/heic'      => 'heic',
    'image/heif'      => 'heif',
    'application/pdf' => 'pdf',
    'video/mp4'       => 'mp4',
    'video/quicktime' => 'mov',
];

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

/* ---------- Anhaenge ---------- */

/** Dateiname auf etwas reduzieren, das in einer Kopfzeile unfallfrei
 *  steht: keine Pfade, keine Anfuehrungszeichen, keine Umlaute. Ein
 *  fremder Dateiname darf nie ungeprueft in die Mail wandern. */
function dateiname_saeubern(string $name, string $endung, int $nummer): string {
    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/\.[^.]*$/', '', $name) ?? '';
    // Umlaute umschreiben statt wegwerfen: aus "Kueche" wird sonst
    // "K-che", und der Empfaenger raetselt, was gemeint war.
    $name = strtr($name, ['ä'=>'ae','ö'=>'oe','ü'=>'ue','Ä'=>'Ae','Ö'=>'Oe','Ü'=>'Ue','ß'=>'ss']);
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $name) ?? '';
    $name = trim($name, '-.');
    if ($name === '') $name = 'anhang-' . $nummer;
    return mb_substr($name, 0, 60) . '.' . $endung;
}

/**
 * Hochgeladene Dateien einsammeln und pruefen.
 * Rueckgabe: [Liste der Anhaenge, Grund fuer eine Ablehnung oder '']
 */
function anhaenge_einsammeln(array $dateien): array {
    if (!isset($dateien[ANHANG_FELD]['tmp_name'])) return [[], ''];

    $roh = $dateien[ANHANG_FELD];
    $anzahl = is_array($roh['tmp_name']) ? count($roh['tmp_name']) : 0;
    if ($anzahl === 0) return [[], ''];

    $anhaenge = [];
    $gesamt = 0;
    $pruefer = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;

    for ($i = 0; $i < $anzahl; $i++) {
        $fehlercode = (int) ($roh['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($fehlercode === UPLOAD_ERR_NO_FILE) continue;

        // Der Server hat die Datei selbst abgewiesen, meist wegen
        // upload_max_filesize. Das ist derselbe Fall wie "zu gross".
        if ($fehlercode === UPLOAD_ERR_INI_SIZE || $fehlercode === UPLOAD_ERR_FORM_SIZE) {
            return [[], 'gross'];
        }
        if ($fehlercode !== UPLOAD_ERR_OK) return [[], 'datei'];

        if (count($anhaenge) >= ANHANG_MAX_ANZAHL) return [[], 'anzahl'];

        $tmp = (string) ($roh['tmp_name'][$i] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) return [[], 'datei'];

        $groesse = (int) filesize($tmp);
        if ($groesse <= 0 || $groesse > ANHANG_MAX_EINZEL) return [[], 'gross'];
        $gesamt += $groesse;
        if ($gesamt > ANHANG_MAX_GESAMT) return [[], 'gross'];

        $typ = $pruefer ? (string) $pruefer->file($tmp) : '';
        if (!isset(ANHANG_TYPEN[$typ])) return [[], 'typ'];

        $inhalt = file_get_contents($tmp);
        if ($inhalt === false) return [[], 'datei'];

        $anhaenge[] = [
            'name'   => dateiname_saeubern((string) ($roh['name'][$i] ?? ''), ANHANG_TYPEN[$typ], count($anhaenge) + 1),
            'typ'    => $typ,
            'inhalt' => $inhalt,
        ];
    }
    return [$anhaenge, ''];
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

    /*
     * $name ist der Text, der im Fehlerfall protokolliert wird -- und er wird
     * bewusst NICHT aus $befehl abgeleitet. Genau das war ein Fehler: die
     * fruehere Fassung prueft auf 'AUTH' am Anfang, das Passwort geht aber als
     * nackte Base64-Zeichenkette ueber die Leitung. Es fiel durch die Pruefung
     * und stand danach im Fehlerprotokoll -- Base64 ist keine Verschluesselung.
     * Deshalb benennt jeder Aufrufer sich selbst; geraten wird hier nichts mehr.
     */
    private function sagen(string $befehl, string $erwartet, string $name): string {
        if ($befehl !== '') fwrite($this->verbindung, $befehl . "\r\n");
        $antwort = $this->lesen();
        if (strncmp($antwort, $erwartet, strlen($erwartet)) !== 0) {
            throw new SmtpFehler('Server lehnte ab bei ' . $name . ': ' . trim($antwort));
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

        $this->sagen('', '220', 'Begruessung');
        $this->sagen('EHLO ' . $this->eigener_name(), '250', 'EHLO');

        if ($this->k['smtp_sicherheit'] === 'starttls') {
            $this->sagen('STARTTLS', '220', 'STARTTLS');
            if (!stream_socket_enable_crypto($this->verbindung, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new SmtpFehler('Verschluesselung fehlgeschlagen.');
            }
            $this->sagen('EHLO ' . $this->eigener_name(), '250', 'EHLO');
        }

        if (($this->k['smtp_passwort'] ?? '') !== '') {
            $this->sagen('AUTH LOGIN', '334', '(Anmeldung)');
            $this->sagen(base64_encode($this->k['smtp_benutzer']), '334', '(Anmeldung)');
            $this->sagen(base64_encode($this->k['smtp_passwort']), '235', '(Anmeldung)');
        }

        $this->sagen('MAIL FROM:<' . $von . '>', '250', 'MAIL FROM');
        $this->sagen('RCPT TO:<' . $an . '>', '250', 'RCPT TO');
        $this->sagen('DATA', '354', 'DATA');

        // Leerzeile zwischen Kopf und Text: ohne sie liest das
        // Mailprogramm die ersten Textzeilen als Kopfzeilen weiter, und
        // die Anfrage kommt fast leer an.
        // Punkt am Zeilenanfang verdoppeln, sonst gilt er als Ende.
        $daten = $kopf . "\r\n\r\n" . $koerper;
        $daten = preg_replace('/^\./m', '..', $daten);
        fwrite($this->verbindung, $daten . "\r\n.\r\n");
        $this->lesenPruefen('250');

        $this->sagen('QUIT', '221', 'QUIT');
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

function nachricht_bauen(array $eingaben, array $k, array $anhaenge = []): array {
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
    if ($anhaenge !== []) {
        // Die Namen auch im Text nennen: Manche Mailprogramme zeigen
        // Anhaenge erst nach dem Aufklappen an.
        $zeilen[] = '';
        $zeilen[] = 'Angehaengte Dateien (' . count($anhaenge) . '):';
        foreach ($anhaenge as $a) {
            $zeilen[] = '  ' . $a['name'] . ' (' . ceil(strlen($a['inhalt']) / 1024) . ' KB)';
        }
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
    $gemeinsam = [
        'Subject: ' . kopf_kodieren($betreff),
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . substr(strrchr($k['absender'], '@') ?: '@localhost', 1) . '>',
        'MIME-Version: 1.0',
    ];

    // Ohne Anhang bleibt alles wie zuvor: eine schlichte Textmail.
    // Das ist der Fall, der seit dem 02.09.2026 nachweislich laeuft --
    // er wird durch diese Erweiterung nicht angefasst.
    if ($anhaenge === []) {
        $kopf = implode("\r\n", array_merge($kopfzeilen, $gemeinsam, [
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ]));
        return [$kopf, $koerper];
    }

    // Mit Anhang wird daraus eine mehrteilige Nachricht: erst der Text,
    // dann je Datei ein Abschnitt. Die Trennzeichenfolge darf im Inhalt
    // nicht vorkommen -- deshalb Zufall, nicht etwas Ausgedachtes.
    $grenze = 'ep-' . bin2hex(random_bytes(16));

    $teile = [];
    $teile[] = '--' . $grenze;
    $teile[] = 'Content-Type: text/plain; charset=UTF-8';
    $teile[] = 'Content-Transfer-Encoding: 8bit';
    $teile[] = '';
    $teile[] = $koerper;

    foreach ($anhaenge as $a) {
        $teile[] = '--' . $grenze;
        $teile[] = 'Content-Type: ' . $a['typ'] . '; name="' . $a['name'] . '"';
        $teile[] = 'Content-Disposition: attachment; filename="' . $a['name'] . '"';
        $teile[] = 'Content-Transfer-Encoding: base64';
        $teile[] = '';
        // Auf 76 Zeichen umbrechen: laengere Zeilen sind laut Norm
        // unzulaessig, und manche Server schneiden sie hart ab.
        $teile[] = rtrim(chunk_split(base64_encode($a['inhalt']), 76, "\r\n"));
    }
    $teile[] = '--' . $grenze . '--';

    $kopf = implode("\r\n", array_merge($kopfzeilen, $gemeinsam, [
        'Content-Type: multipart/mixed; boundary="' . $grenze . '"',
    ]));

    return [$kopf, implode("\r\n", $teile)];
}

/* ---------- Ablauf ---------- */

/** Fehlerziel um einen Grund ergaenzen, damit die Seite eine passende
 *  Meldung zeigen kann statt der allgemeinen. Der Anker muss hinten
 *  bleiben, sonst springt die Seite nicht mehr zum Formular. */
function ziel_mit_grund(string $ziel, string $grund): string {
    if ($grund === '') return $ziel;
    [$pfad, $anker] = array_pad(explode('#', $ziel, 2), 2, null);
    $trenner = str_contains((string) $pfad, '?') ? '&' : '?';
    return $pfad . $trenner . 'grund=' . rawurlencode($grund) . ($anker === null ? '' : '#' . $anker);
}


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

    // Ueberschreitet die Absendung post_max_size, verwirft PHP sie
    // vollstaendig: $_POST und $_FILES sind dann leer, obwohl Daten
    // geschickt wurden. Ohne diese Abfrage sieht das Skript nur
    // fehlende Pflichtfelder und meldet etwas Falsches -- der Besucher
    // sucht den Fehler dann bei sich im Namensfeld.
    if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // Die Grenzen des Servers gleich mitschreiben. Sonst steht im
        // Protokoll nur "war zu gross", und es bleibt offen, ob unsere
        // eigene Grenze gegriffen hat oder eine engere des Webhosters.
        error_log(sprintf(
            'Kontaktformular: Absendung groesser als post_max_size. Gesendet %s Bytes, '
            . 'post_max_size=%s, upload_max_filesize=%s, eigene Grenze=%d Bytes.',
            $_SERVER['CONTENT_LENGTH'] ?? '?',
            ini_get('post_max_size') ?: '?',
            ini_get('upload_max_filesize') ?: '?',
            ANHANG_MAX_GESAMT
        ));
        header('Location: ' . ziel_mit_grund($k['ziel_fehler'], 'gross'), true, 303);
        exit;
    }

    [$eingaben, $fehler] = pruefen($_POST);

    if ($fehler !== []) {
        header('Location: ' . $k['ziel_fehler'], true, 303);
        exit;
    }

    [$anhaenge, $anhangFehler] = anhaenge_einsammeln($_FILES);
    if ($anhangFehler !== '') {
        error_log('Kontaktformular: Anhang abgewiesen (' . $anhangFehler . ').');
        header('Location: ' . ziel_mit_grund($k['ziel_fehler'], $anhangFehler), true, 303);
        exit;
    }

    try {
        [$kopf, $koerper] = nachricht_bauen($eingaben, $k, $anhaenge);
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
