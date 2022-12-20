<?php
/**
 *
 * @category        backend
 * @package         language
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: DE.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/languages/DE.php $     rn
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */
 /* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
$MESSAGE['SIGNUP2_ADMIN_INFO'] = "\r\nEs wurde ein neuer User regisriert.\r\n\r\nLogin Name: {LOGIN_NAME}\r\n{DISPLAY_NAME}: {LOGIN_ID}\r\nE-Mail: {LOGIN_EMAIL}\r\nAnmeldedatum: {SIGNUP_DATE}\r\n----------------------------------------\r\nDiese E-Mail wurde automatisch erstellt!\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT'] = "\r\nHallo {LOGIN_DISPLAY_NAME},\r\n\r\nSie erhalten diese E-Mail, weil sie ein neues Passwort angefordert haben.\r\n\r\nIhre neuen Logindaten für {LOGIN_WEBSITE_TITLE} lauten:\r\nLoginname: xxxxxx\r\nPasswort: {LOGIN_PASSWORD}\r\nDas bisherige Passwort wurde durch das neue Passwort oben ersetzt.\r\n\r\nSollten Sie kein neues Kennwort angefordert haben, löschen Sie bitte diese E-Mail.\r\n\r\nMit freundlichen Grüssen\r\n----------------------------------------\r\nDiese E-Mail wurde automatisch erstellt!\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_INFO'] = "\r\nHallo {LOGIN_DISPLAY_NAME},\r\n\r\nHerzlich willkommen bei {LOGIN_WEBSITE_TITLE}\r\n\r\nIhre Logindaten für {LOGIN_WEBSITE_TITLE} lauten:\r\nLoginname: xxxxxx\r\nPasswort: {LOGIN_PASSWORD}\r\n\r\nVielen Dank für Ihre Registrierung\r\n\r\nWenn Sie diese E-Mail versehentlich erhalten haben, löschen Sie bitte diese E-Mail.\r\n----------------------------------------\r\nDiese E-Mail wurde automatisch erstellt!\r\n";
$MESSAGE['SIGNUP2_NEW_USER'] = "Es wurde ein neuer User für {WB_URL} regisriert";
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Deine WB Logindaten ...';
$MESSAGE['SIGNUP2_SUBJECT_NEW_USER'] = 'Vielen Dank für Ihre Registrierung';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'Bitte geben Sie Ihre E-Mail Adresse an';
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Deine Logindaten für {{WB_URL}}...';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'Bitte geben Sie Ihre E-Mail Adresse an';
$MESSAGE['MOD_FORM_INCORRECT_CAPTCHA'] = 'Die eingegebene Prüfziffer stimmt nicht überein. Wenn Sie Probleme mit dem Lesen der Prüfziffer haben, schreiben Sie bitte eine E-Mail an: <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';
$MESSAGE['FORGOT_PASS_ALREADY_RESET'] = 'Das Passwort kann nur einmal pro Stunde zurückgesetzt werden';
$MESSAGE['FORGOT_PASS_CANNOT_EMAIL'] = 'Das Passwort konnte nicht versendet werden, bitte kontaktieren Sie den Systemadministrator';
$MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'] = 'Die angegebene E-Mail Adresse wurde nicht in der Datenbank gefunden';
$MESSAGE['FORGOT_PASS_NO_DATA'] = 'Bitte geben Sie nachfolgend Ihre E-Mail Adresse an';
$MESSAGE['FORGOT_PASS_PASSWORD_RESET'] = 'Ihr Passwort wurde an Ihre E-Mail Adresse gesendet';
$MESSAGE['USERS_DISPLAYNAME_TAKEN'] = 'Der angegebene Anzeigename wird bereits verwendet';
$MESSAGE['LOGIN_BOTH_BLANK'] = 'Bitte geben Sie Ihren Loginnamen und Passwort ein';
$MESSAGE['INCORRECT_CAPTCHA'] = 'Die eingegebene Prüfziffer stimmt nicht überein. Wenn Sie Probleme mit dem Lesen der Prüfziffer haben, schreiben Sie bitte eine E-Mail an: <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';
$MESSAGE['LOGIN_FAILED'] = 'UPS...Anmeldung fehlgeschlagen!';
$MESSAGE['USERS_NAME_INVALID_CHARS'] = 'Es wurden keine oder ungültige Zeichen für den Loginnamen gefunden';
$MESSAGE['USERS_DISPLAYNAME_INVALID_CHARS'] = 'Es wurden keine oder ungültige Zeichen für den Anzeigenamen gefunden';

$MESSAGE['DSGVO'] = 'Hiermit bestätige ich, dass ich die <a class="iframe" href="%s" target="_blank" rel="noopener">Datenschutzerklärung</a> gelesen habe und stimme dieser durch Absenden des Formulars zu.';
$MESSAGE['DSGVO_ERROR'] = 'Fehlende Bestätigung und Zustimmung zur Datenschutzrichtlinie';

$MESSAGE['DSGVO_ENABLED'] = 'Zustimmung zur Datenschutzrichtlinie erfolgt %s';
$MESSAGE['DSGVO_DIABLED'] = 'Fehlende Zustimmung zur Datenschutzrichtlinie %s';
$MESSAGE['DSGVO_NOT_INUSE'] = 'Zustimmung zur Datenschutzrichtlinie ist deaktiviert %s';

$TEXT['FORGOT_DETAILS'] = 'Eingabedaten vergessen?';
$TEXT['RESET_INPUTS'] = 'Eingaben zurücksetzen';
$TEXT['PAGE_RELOAD'] = 'Seite neu laden';
