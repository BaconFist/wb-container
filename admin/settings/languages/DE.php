<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of \admin\addons\DE
 * @package      CoreTranslation
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: DE.php 252 2019-03-17 17:58:36Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */

//declare(strict_types = 1);

//declare(encoding = 'UTF-8');

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

// Set the language information
$language_code     = 'DE';
$language_name     = 'Deutsch';
$language_version  = '4.0.0';
$language_platform = '2.11.0';
$language_author   = 'Manuela v.d.Decken, Dietmar Wöllbrink';
$language_license  = 'GNU General Public License 2.0';

$TEXT['PAGE_LANGUAGES'] = 'Mehrsprachigkeit';
$TEXT['PAGES_ACCESSSTYLE'] = 'Seiten Zugriffs Datei';
$TEXT['TWIG_VERSION'] = 'Twig Version';
$TEXT['JQUERY_VERSION'] = 'jQuery Version';
$TEXT['JQUERY_CDN_LINK'] = 'Externe Datei (CDN)';
$TEXT['CDN_EXTERN'] = 'Externe (CDN)';
$TEXT['RENAME_FILES_ON_UPLOAD'] = 'Verbotene Dateitypen';
$TEXT['MEDIA_RESIZE'] = 'max. Media Bilderabmessung';
$TEXT['MEDIA_WIDTH'] = 'px Breite';
$TEXT['MEDIA_HEIGHT'] = 'px Höhe';
$TEXT['MEDIA_COMPRESS'] = 'JPEG Kompression';

$TEXT['EDITOR_ENABLE'] = 'Editor einschalten';
$TEXT['EDITOR_DISABLE'] = 'Editor ausschalten';
$TEXT['PAGES_OLDSTYLE'] = 'Retro Format';
$TEXT['PAGES_NEWSTYLE'] = 'Neues Format';

$HEADING['LOGIN_SETTINGS'] = 'Frontend Anmeldung, Registrierung, Datenschutz-Grundverordnung';

$TEXT['DSGVO'] = 'Datenschutzrichtlinie';
$TEXT['DSGVO_LINK'] = 'Datenschutz Url';
$TEXT['DSGVO_DISABLED'] = 'Datenschutz Richtlinie zurücksetzen';
$TEXT['TOGGLE'] = 'Richtlinie ein- oder ausschalten';
$TEXT['WBMAILER_DEBUG'] = 'Mail Debug';
$TEXT['WBMAILER_SENDMAIL'] = 'Sendmail';
$TEXT['WBMAILER_NOTICE'] = "Damit WebsiteBaker Benachrichtigungen per Email versenden kann, benötigen Sie einen gültigen Email-Account. Ob es ein Postfach auf Ihrem eigenen Server oder eines beliebigen anderen Anbieters ist, spielt keine Rolle. Einzige Bedingung ist, dass das Postfach/der Account einen Mailversand per SMTP ermöglicht. Die Einstellung der Zugangsdaten erfolgt vergleichbar zu anderen Email-Clients wie Outlook, Thunderbird, Evolution, usw.. Dieses Postfach wird grundsätzlich für jeglichen Email-Versand aus WebsiteBaker incl. aller Zusatzmodule verwendet.";

$TEXT['WBMAILER_DEFAULT_SENDER_MAIL'] = 'Adresse des Standard Empfängers';
$TEXT['WBMAILER_DEFAULT_SENDER_NAME'] = 'Name des Standard Empfängers';
$TEXT['WBMAILER_DEFAULT_SETTINGS_NOTICE'] = 'Nachrichten per E-Mail';
$TEXT['WBMAILER_FUNCTION'] = 'Versandmethode';
$TEXT['WBMAILER_PHP'] = 'PHP MAIL';
$TEXT['WBMAILER_SMTP'] = 'SMTP';
$TEXT['WBMAILER_SMTP_AUTH'] = 'SMTP Authentifikation';
$TEXT['WBMAILER_SMTP_HOST'] = 'Url des Mailserver';
$TEXT['WBMAILER_SMTP_SECURE'] = 'SMTP Verschlüsselung';
$TEXT['WBMAILER_ENABLE_SSL_SECURE'] = 'Zertifkat Prüfung einschalten';
$TEXT['WBMAILER_DISABLE_SSL_SECURE'] = 'Zertifikatsprüfung ausschalten';

$INFO = [
    'DSGVO' => 'Mehrfachauswahl, es sind mehrere Einträge auswählbar. Wählen sie für jede Sprache einen Datenschutzhinweis aus!',
    'ENABLED_SIGNUP' => 'Für Registrierungsformular aktivieren',
    'LABEL_ENABLED_SIGNUP' => 'DSGVO: Datenschutz auf dem Registrierungsformular aktivieren',
    'ENABLED_LOGINFORM' => 'Für Anmeldeformular aktivieren',
    'LABEL_ENABLED_LOGINFORM' => 'DSGVO: Datenschutz auf dem Anmeldeformular aktivieren',
    'ENABLED_LOSTPASSWORD' => 'Für Passwort Vergessen Formular aktivieren',
    'LABEL_ENABLED_LOSTPASSWORD' => 'DSGVO: Datenschutz auf dem Formular zum Rücksetzen des Passworts aktivieren',
    'SMTP_DEBUG_OFF' => "<code>SMTP::DEBUG_OFF</code> (0): Normale Produktionseinstellung; keine Debug-Ausgabe",
    'SMTP_DEBUG_CLIENT' => "<code>SMTP::DEBUG_CLIENT</code> (1): zeige Client -&gt; nur Server-Nachrichten. Benutzen sie dies nicht - es ist sehr unwahrscheinlich, dass es etwas Nützliches zeigen wird",
    'SMTP_DEBUG_SERVER' => "<code>SMTP::DEBUG_SERVER</code> (2): zeige Client -&gt; Server und Server -&gt; Client-Nachrichten - dies ist normalerweise die Einstellung, die gewünscht wird",
    'SMTP_DEBUG_CONNECTION' => "<code>SMTP::DEBUG_CONNECTION</code> (3): Wie 2, aber zeigt auch Details über die anfängliche Verbindung an, nur verwenden, wenn Probleme mit der Verbindung auftreten (z.B. Zeitüberschreitung der Verbindung)",
    'SMTP_DEBUG_LOWLEVEL' => "<code>SMTP::DEBUG_LOWLEVEL</code> (4): Wie 3, zeigt aber auch detaillierten Low-Level-Verkehr. Nur wirklich nützlich für die Analyse von Bugs auf Protokollebene, sehr wortreich, wahrscheinlich nicht das, was gebraucht wird",
    'SMTP_GMAIL' => 'Bitte stellen sie sicher, dass das Versenden per SMTP von diesem Server von ihrem E-Mail Provider nicht als SPAM eingestuft wird!',
    'SHOW_DATETIME' => 'Datumsanzeige Startseite',
];
