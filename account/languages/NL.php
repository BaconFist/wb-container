<?php
/**
 *
 * @category        backend
 * @package         language
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2018, WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.6.0 and higher
 * @version         $Id: EN.php 2 2017-07-02 15:14:29Z Manuela $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.10.x/branches/main/languages/EN.php $
 * @lastmodified    $Date: 2018-08-20 11:14:29 +0200 (Mo, 20. Aug 2018) $ rn
 *
 */
 /* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
$MESSAGE['SIGNUP2_ADMIN_INFO'] = "\r\nEen nieuwe gebruiker heeft zich geregistreerd. \r\n\r\nLogin Naam: {LOGIN_NAME}\r\n{DISPLAY_NAME}: {LOGIN_ID}\r\nE-Mail: {LOGIN_EMAIL}\r\nRegistratie datum: {SIGNUP_DATE}\r\n----------------------------------------\r\nDit bericht is automatisch gegenereerd!\r\n\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT'] = "\r\nHallo {LOGIN_DISPLAY_NAME},\r\n\r\nDeze  email is verzonden omdat de 'wachtwoord vergeten' functie is gebruikt voor uw account.\r\n\r\nUw nieuwe '{LOGIN_WEBSITE_TITLE}' inloggegevens zijn:\r\n\r\nInlognaam: xxxxx\r\nWachtwoord: {LOGIN_PASSWORD}\r\n\r\nUw wachtwoord is veranderd in het wachtwoord hier boven.\r\nDit betekent dat uw oude wachtwoord niet langer werkt!\r\nAls u vragen of problemen hebt met betrekking tot uw nieuwe inloggegevens \r\nmoet u contact opnemen met het websiteteam of de beheerder  van  '{LOGIN_WEBSITE_TITLE}'.\r\nVergeet u alstublieft niet om uw browsercache te legen voordat u uw nieuwe gegevens gebruikt om onverwachte fouten te voorkomen. \r\n\r\nMet vriendelijke groet\r\n------------------------------------\r\nDit bericht is automatisch gegenereerd\r\n\r\n";
$MESSAGE['SIGNUP2_BODY_LOGIN_INFO'] = "\r\nHallo {LOGIN_DISPLAY_NAME},\r\n\r\nWelkom op '{LOGIN_WEBSITE_TITLE}'.\r\n\r\nUw '{LOGIN_WEBSITE_TITLE}' inloggegevens zijn:\r\nInlognaam: xxxxx\r\nWachtwoord: {LOGIN_PASSWORD}\r\n\r\nMet vriendelijke groet\r\n\r\nAlstublieft:\r\nals dit bericht niet voor u bedoeld is, gooit u het dan direct weg!\r\n-------------------------------------\r\nDit bericht is automatisch gegenereerd!\r\n";
$MESSAGE['SIGNUP2_NEW_USER'] = "Een nieuwe gebruiker is geregistreerd voor {WB_URL}";
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Uw inloggegevens...';
$MESSAGE['SIGNUP2_SUBJECT_NEW_USER'] = 'Dank voor uw registratie';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'U moet een emailadres opgeven';
$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'] = 'Uw inloggegevens voor {{WB_URL}}...';
$MESSAGE['SIGNUP_NO_EMAIL'] = 'U moet een emailadres opgeven';
$MESSAGE['MOD_FORM_INCORRECT_CAPTCHA'] = 'Het verificatienummer (ook bekend als Captcha) dat U opgaf  is niet juist. Als u problemen hebt met het lezen van de Captcha, stuurt u dan een email naar: <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';
$MESSAGE['FORGOT_PASS_ALREADY_RESET'] = 'Het wachtwoord kan maar een keer per uur worden opgevraagd';
$MESSAGE['FORGOT_PASS_CANNOT_EMAIL'] = 'Het wachtwoord kan niet per email worden verzonden, neemt u contact op met de systeembeheerder';
$MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'] = 'Het emailadres dat U opgaf kan niet worden gevonden in de databaase';
$MESSAGE['FORGOT_PASS_NO_DATA'] = 'Geef uw emailadres hieronder op alstublieft';
$MESSAGE['FORGOT_PASS_PASSWORD_RESET'] = 'Uw wachtwoord is naar uw emailadres gestuurd';
$MESSAGE['USERS_DISPLAYNAME_TAKEN'] = 'De websitenaam die u opgaf is al in gebruik';
$MESSAGE['LOGIN_BOTH_BLANK'] = 'Vul alstublieft uw loginnaam en uw wachtwoord in';
$MESSAGE['INCORRECT_CAPTCHA'] = 'Het verificatienummer ( ook bekend als Captcha ) dat u opgaf is niet juist. Als U problemen hebt met het lezen van de Captcha, stuurt u dan een email aan de <a href="mailto:{SERVER_EMAIL}">Webmaster</a>';

$MESSAGE['DSGVO'] = 'Ik bevestig dat ik de <a href="%s" target="_blank" rel="noopener">Privacyverklaring</a> heb gelezen en er mee akkoord ga door het indienen van dit formulier';
$MESSAGE['DSGVO_ERROR'] = 'Uw bevestiging en akkoord op de Privacyverklaring ontbreekt';
$MESSAGE['DSGVO_ENABLED'] = 'Akkoord met de Privacyverklaring %s';
$MESSAGE['DSGVO_DIABLED'] = 'Niet akkoord met de Privacyverklaring %s';
$MESSAGE['DSGVO_NOT_INUSE'] = 'Akkoord met de Privacyverklaring is geblokkeerd %s';

