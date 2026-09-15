<?php
/*
 * Zeigt die Upload-Grenzen des Webservers an.
 *
 * WOZU: Das Kontaktformular erlaubt Anhaenge bis 15 MB. Ob der Server
 * das ueberhaupt durchlaesst, entscheiden zwei PHP-Einstellungen, die
 * der Webhoster setzt. Liegen sie niedriger, weist PHP die Absendung ab,
 * bevor kontakt.php sie zu sehen bekommt.
 *
 * SO WIRD SIE BENUTZT:
 *   1. Diese Datei nach public_html hochladen.
 *   2. Im Browser aufrufen:
 *      https://www.elektrotechnik-paulus.de/php-grenzen.php
 *   3. Die Zahlen ablesen.
 *   4. DIE DATEI WIEDER LOESCHEN. Sie muss nicht dauerhaft im Netz
 *      stehen -- nicht weil sie gefaehrlich waere (sie zeigt nur diese
 *      wenigen Werte, kein phpinfo), sondern weil auf einem Webserver
 *      nichts liegen soll, das niemand braucht.
 *
 * Diese Datei liegt bewusst in docs/werkzeuge/ und NICHT im
 * Projektstamm: paket.py nimmt docs/ nicht auf, sie kann also nicht
 * versehentlich mit einem Paket auf den Server wandern und dort
 * liegenbleiben.
 */

declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');

/** "8M" oder "512K" in Bytes umrechnen. PHP schreibt diese Werte
 *  abgekuerzt, ein Vergleich mit einer Zahl geht sonst schief. */
function in_bytes(string $wert): int {
    $wert = trim($wert);
    if ($wert === '') return 0;
    $zahl = (int) $wert;
    return match (strtolower(substr($wert, -1))) {
        'g' => $zahl * 1024 * 1024 * 1024,
        'm' => $zahl * 1024 * 1024,
        'k' => $zahl * 1024,
        default => $zahl,
    };
}

function mb(int $bytes): string {
    return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
}

$upload  = in_bytes((string) ini_get('upload_max_filesize'));
$post    = in_bytes((string) ini_get('post_max_size'));
$anzahl  = (int) ini_get('max_file_uploads');
$zeit    = (int) ini_get('max_execution_time');
$speicher= in_bytes((string) ini_get('memory_limit'));

// Das Formular setzt diese Grenzen. Stimmen sie mit dem Server ueberein?
const FORMULAR_EINZEL = 10485760;   // 10 MB
const FORMULAR_GESAMT = 15728640;   // 15 MB
const FORMULAR_ANZAHL = 5;

echo "Upload-Grenzen dieses Servers\n";
echo "=============================\n\n";
printf("  PHP-Version            %s\n\n", PHP_VERSION);
printf("  upload_max_filesize    %-10s  (je einzelne Datei)\n", mb($upload));
printf("  post_max_size          %-10s  (ganze Absendung, inkl. Text)\n", mb($post));
printf("  max_file_uploads       %-10s  (Dateien gleichzeitig)\n", (string) $anzahl);
printf("  memory_limit           %-10s\n", $speicher > 0 ? mb($speicher) : 'unbegrenzt');
printf("  max_execution_time     %-10s\n\n", $zeit > 0 ? $zeit . ' s' : 'unbegrenzt');

echo "Vergleich mit dem Kontaktformular\n";
echo "---------------------------------\n\n";

$urteil = [];
$urteil[] = ['je Datei',        FORMULAR_EINZEL, $upload];
// post_max_size muss die ganze Absendung tragen, nicht nur die Dateien.
$urteil[] = ['zusammen',        FORMULAR_GESAMT, $post];
$urteil[] = ['Anzahl Dateien',  FORMULAR_ANZAHL, $anzahl];

$engpass = false;
foreach ($urteil as [$was, $soll, $ist]) {
    $einheit = $was === 'Anzahl Dateien';
    $ok = $ist >= $soll;
    if (!$ok) $engpass = true;
    printf("  %-16s Formular %-10s Server %-10s  %s\n",
        $was,
        $einheit ? (string) $soll : mb($soll),
        $einheit ? (string) $ist  : mb($ist),
        $ok ? 'passt' : '>>> SERVER IST ENGER <<<');
}

echo "\n";
if ($engpass) {
    $wirklich = min($post, $upload * FORMULAR_ANZAHL);
    echo "ERGEBNIS: Der Server laesst weniger durch als das Formular ankuendigt.\n";
    echo "Wirklich moeglich sind hoechstens rund " . mb($wirklich) . " je Absendung.\n";
    echo "Der Hinweistext im Formular sollte auf diesen Wert geaendert werden,\n";
    echo "sonst verspricht die Seite mehr, als sie halten kann.\n";
} else {
    echo "ERGEBNIS: Der Server traegt die Grenzen des Formulars. Nichts zu tun.\n";
}
echo "\nDiese Datei jetzt wieder loeschen.\n";
