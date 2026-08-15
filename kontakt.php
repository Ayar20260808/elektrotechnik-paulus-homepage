<?php
/**
 * Versand des Kontaktformulars von index.html.
 *
 * Funktioniert ohne JavaScript: das Formular sendet per POST hierher,
 * danach wird zurueck auf die Startseite geleitet. Mit JavaScript wird
 * dieselbe Adresse per fetch() angesprochen und liefert JSON zurueck.
 *
 * Vor dem Livegang pruefen: EMPFAENGER und ABSENDER anpassen.
 * ABSENDER muss eine Adresse der eigenen Domain sein, sonst stufen
 * viele Mailserver die Nachricht als Spam ein.
 */

const EMPFAENGER = 'info@elektrotechnik-paulus.de';
const ABSENDER   = 'noreply@elektrotechnik-paulus.de';
const ZIELSEITE  = 'index.html';

/** Antwort ausliefern: JSON bei fetch(), sonst Weiterleitung. */
function antwort(bool $ok, string $meldung): void
{
    $wantsJson = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'fetch';

    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok, 'meldung' => $meldung], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $status = $ok ? 'ok' : 'fehler';
    header('Location: ' . ZIELSEITE . '?status=' . $status . '#kontakt', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    antwort(false, 'Ungueltiger Zugriff.');
}

// Honigtopf: von Menschen nie ausgefuellt, von Bots fast immer.
if (trim($_POST['website'] ?? '') !== '') {
    antwort(true, 'Danke fuer Ihre Anfrage.');
}

$feld = static fn (string $name): string => trim((string) ($_POST[$name] ?? ''));

$vorname   = $feld('vorname');
$nachname  = $feld('nachname');
$email     = $feld('email');
$telefon   = $feld('telefon');
$anliegen  = $feld('anliegen');
$nachricht = $feld('nachricht');
$einwilligung = isset($_POST['datenschutz']);

if ($vorname === '' || $nachname === '' || $email === '') {
    antwort(false, 'Bitte fuellen Sie Vorname, Nachname und E-Mail-Adresse aus.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    antwort(false, 'Diese E-Mail-Adresse sieht nicht gueltig aus. Bitte pruefen Sie die Schreibweise.');
}

if (!$einwilligung) {
    antwort(false, 'Bitte bestaetigen Sie die Datenschutzerklaerung, damit wir Ihre Anfrage bearbeiten duerfen.');
}

// Kopfzeilen-Injektion verhindern.
$sauber = static fn (string $wert): string => str_replace(["\r", "\n"], ' ', $wert);

$betreff = 'Anfrage über die Website: ' . $sauber($anliegen !== '' ? $anliegen : 'Allgemein');

$zeilen = [
    'Neue Anfrage über elektrotechnik-paulus.de',
    '',
    'Name:      ' . $vorname . ' ' . $nachname,
    'E-Mail:    ' . $email,
    'Telefon:   ' . ($telefon !== '' ? $telefon : '—'),
    'Anliegen:  ' . ($anliegen !== '' ? $anliegen : '—'),
    '',
    'Nachricht:',
    $nachricht !== '' ? $nachricht : '—',
    '',
    '---',
    'Gesendet am ' . date('d.m.Y \u\m H:i') . ' Uhr',
    'IP-Adresse: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unbekannt'),
];

$kopfzeilen = [
    'From: Website Elektrotechnik Paulus <' . ABSENDER . '>',
    'Reply-To: ' . $sauber($vorname . ' ' . $nachname) . ' <' . $sauber($email) . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
];

$gesendet = @mail(
    EMPFAENGER,
    '=?UTF-8?B?' . base64_encode($betreff) . '?=',
    implode("\n", $zeilen),
    implode("\r\n", $kopfzeilen)
);

if (!$gesendet) {
    antwort(false, 'Der Versand hat technisch nicht geklappt. Bitte rufen Sie uns an: 0221 2989 4083.');
}

antwort(true, 'Ihre Anfrage ist angekommen.');
