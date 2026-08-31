<?php
/*
 * Vorlage fuer die Zugangsdaten des Kontaktformulars.
 *
 * SO WIRD SIE BENUTZT:
 *   1. Diese Datei auf dem Webserver kopieren und in
 *      "kontakt-konfig.php" umbenennen.
 *   2. Dort die echten Werte eintragen.
 *   3. Die echte Datei NIE ins Projekt aufnehmen -- sie steht in
 *      .gitignore, damit das Passwort nicht im Netz landet.
 *
 * Das App-Passwort wird im Google-Konto erzeugt
 * (Konto verwalten -> Sicherheit -> App-Passwoerter). Es ist nicht
 * das normale Anmeldepasswort und laesst sich einzeln widerrufen.
 */
return [
    // Postausgangsserver. Bei Google Workspace:
    'smtp_server'      => 'smtp.gmail.com',
    'smtp_port'        => 587,
    'smtp_sicherheit'  => 'starttls',   // 'starttls' | 'ssl' | 'keine'

    // Anmeldung am Postausgangsserver.
    'smtp_benutzer'    => 'info@elektrotechnik-paulus.de',
    'smtp_passwort'    => 'HIER-DAS-APP-PASSWORT-EINTRAGEN',

    // Absender der Benachrichtigung. Muss zum Konto oben passen,
    // sonst weist Google die Mail ab.
    'absender'         => 'info@elektrotechnik-paulus.de',
    'absender_name'    => 'Homepage Elektrotechnik Paulus',

    // Wohin die Anfragen gehen.
    'empfaenger'       => 'info@elektrotechnik-paulus.de',

    // Wohin der Besucher nach dem Absenden zurueckkehrt.
    // Relativ, damit es auf jeder Adresse funktioniert.
    'ziel_erfolg'      => 'index.html?gesendet=1#kontakt',
    'ziel_fehler'      => 'index.html?fehler=1#kontakt',
];
