<?php
/**
 *
 * @category        module
 * @package         Form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP7.4.x and higher
 * @version         $Id: DE.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/languages/DE.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 * @description
 */
/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
/* -------------------------------------------------------- */

//Modulbeschreibung
$module_description = 'Mit diesem Modul können sie ein beliebiges Formular für ihre Seite erzeugen';

//Variablen fuer backend Texte
$MOD_FORM['SETTINGS'] = 'Formular Einstellungen';
$MOD_FORM['SAVE_SETTINGS'] = 'Formular Einstellungen speichern';
$MOD_FORM['CONFIRM'] = 'Bestätigung';
$MOD_FORM['SUBMIT_FORM']   = 'Absenden';
$MOD_FORM['EMAIL_SUBJECT'] = 'Sie haben eine Nachricht über {{WEBSITE_TITLE}} erhalten';
$MOD_FORM['SUCCESS_EMAIL_SUBJECT'] = 'Sie haben ein Forumlar über {{WEBSITE_TITLE}} gesendet';
$MOD_FORM['REPLACE_EMAIL_SUBJECT'] = 'Ersetze Betreffzeile mit ';

$MOD_FORM['SUCCESS_EMAIL_TEXT']  = 'Vielen Dank für die Übermittlung Ihrer Nachricht an {{WEBSITE_TITLE}}. '.PHP_EOL;
$MOD_FORM['SUCCESS_EMAIL_TEXT'] .= 'Wir setzen uns schnellstens mit Ihnen in Verbindung.';

$MOD_FORM['SUCCESS_EMAIL_TEXT_GENERATED'] = "\n"
."******************************************************************************\n"
."Dies ist eine automatisch generierte E-Mail. Die Absenderadresse dieser E-Mail\n"
."ist nur zum Versand, und nicht zum Empfang von Nachrichten eingerichtet!\n"
."Falls Sie diese E-Mail versehentlich erhalten haben, setzen Sie sich bitte\n"
."mit uns in Verbindung und löschen diese Nachricht von Ihrem Computer.\n"
."******************************************************************************\n";

$MOD_FORM['FROM'] = 'Absender';
$MOD_FORM['TO']   = 'Empfänger';

$MOD_FORM['EXCESS_SUBMISSIONS'] = 'Dieses Formular wurde zu oft aufgerufen. Bitte versuchen Sie es später noch einmal.';
$MOD_FORM['INCORRECT_CAPTCHA']  = 'Die eingegebene Prüfziffer stimmt nicht überein. Wenn Sie Probleme mit dem Lesen der Prüfziffer haben, bitte schreiben Sie eine E-Mail an den <a href="mailto:{{WEBMASTER_EMAIL}}">Webmaster</a>';

$MOD_FORM['PRINT']  = 'SPAM-Schutz!! Der Versand einer Bestätigung an eine ungeprüfte E-Mail Adresse ist nicht möglich! ';
$MOD_FORM['SPAM_PROTECT'] = 'SPAM-Schutz!!';
$MOD_FORM['DELETE_SUBS_AFTER']   = 'Löschen nach';
$MOD_FORM['SUBS_DELETED']   = '%d Einträge älter als %d Tag(e) erfolgreich gelöscht';
$MOD_FORM['DAY_CONUTER']   = 'Anzahl Tage berechnen, wird nur einmal benötigt';
$MOD_FORM['MINUTES']  = 'Minute(n)';
$MOD_FORM['DAYS']  = 'Tage(n)';
$TEXT['MAX_SUBMISSIONS_PER_HOUR']= 'Max. Eintragungen';
$MOD_FORM['PRINT']  = 'Der Versand einer Bestätigung an eine ungeprüfte E-Mail-Adresse ist nicht möglich! ';
$MOD_FORM['PRINT'] .= 'Bitte drucken Sie diese Seite aus, wenn eine Kopie für Ihre Unterlagen gewünscht wird.!';

$MOD_FORM['RECIPIENT']   = 'Die E-Mail Bestätigung erfolgt nur an angemeldete Benutzer!';
$MOD_FORM['LOAD_LAYOUT'] = 'Standard Layout laden';
$MOD_FORM['IMPORT_LAYOUT'] = 'Layouttitel und Beschreibung werden nur als xml-Datei hinzugefügt! Um das in der Auswahl Box ausgewählte Layout zu laden, bestätigen Sie die Schaltfläche Importieren. Ohne eine Auswhal wird das aktuell geladene Layout als xml-Datei exportiert!';
$MOD_FORM['CAPTCHA_PLACEHOLDER'] = 'Neuer Platzhalter zum Einbinden von Captcha im Layout';
$MOD_FORM['DSGVO_PLACEHOLDER']   = 'Neuer Platzhalter zum Einbinden der Datenschutzrichtlinie im Layout';
$MOD_FORM['REQUIRED_FIELDS']     = 'Bitte folgende Angaben ergänzen!';
$MOD_FORM['ERROR'] = 'Nachricht konnte nicht gesendet werden!!';
$MOD_FORM['SPAM']  = 'ACHTUNG! Beantworten einer ungeprüften Formular Absende Adresse kann als Spam abgemahnt werden! ';
$MOD_FORM['DSGVO'] = 'Fehlende Bestätigung und Zustimmung zur Datenschutzrichtlinie';

$MOD_FORM['DSGVO_ENABLED']   = 'Zustimmung zur Datenschutzrichtlinie erfolgt %s';
$MOD_FORM['DSGVO_DISABLED']  = 'Fehlende Zustimmung zur Datenschutzrichtlinie %s';
$MOD_FORM['DSGVO_NOT_INUSE'] = 'Zustimmung zur Datenschutzrichtlinie ist deaktiviert %s';

$MOD_FORM['EDIT_TPL'] = 'Ändere Erfolgreich Seite';
$MOD_FORM['REPLY_TO'] = 'Antwortadresse';

$MESSAGE['INCORRECT_CAPTCHA'] = 'Die eingegebene Prüfziffer stimmt nicht überein. Wenn Sie Probleme mit dem Lesen der Prüfziffer haben, schreiben Sie bitte eine E-Mail an: <a href="mailto:{{WEBMASTER_EMAIL}}">Webmaster</a>';
$MESSAGE['FIELD_DELETED']     = 'Feld <b>[%s]</b> erfolgreich gelöscht';
// <br />
$MOD_FORM['CONFIRM'] = 'Bestätigung';
$MOD_FORM['WARNING'] = 'Wichtiger Hinweis';
$MOD_FORM['EMAIL_RECIPIENT'] = 'E-Mail Empfänger';
$MOD_FORM['DIVIDER_SEPERATOR'] = 'Sie haben die Möglickeit ein Trennzeichen zwischen Label und Inhalt im Formular sowie einen Zeilenumbruch einzugeben, Trennzeichen kann ein beliebiges Zeichen sein wie z.B. ein Doppelunkt. Für den Zeilenumbruch geben sie \n ein. Dann steht der Label über den Inhalt, ansonsten in einer Reihe. Dies gilt nur im E-Mail Inhalt!';
$MOD_FORM['PLACEHOLDER'] = 'Title in Eingabefeldern als Platzhalter einfügen';
$MOD_FORM['REQUIRED'] = 'Erforderliche Formular Felder vor dem Absenden prüfen';
$MOD_FORM['FIELD_EXPORT'] = 'Exportieren Sie Formularfelder für diesen Abschnitt. Wählen sie eine Datei aus obiger Auswahl aus. Ohne Auswahl werden die Felder aus der Datenbank exportiert. Überschreiben der xml-Datei ist nicht möglich. Bei übereinstimmenden Dateinamen wird immer eine Kopie angelegt! ';
$MOD_FORM['CSS_REQUIRED'] = 'Frontend.css nicht laden! (Vergessen Sie nicht, Stylesheets zu Ihren Frontend-Vorlagen oder in eine frontendUser.css hinzuzufügen)';

$TEXT['GUEST']   = 'Gast';
$TEXT['UNKNOWN'] = 'unbekannt';
$TEXT['PRINT_PAGE']  = 'Seite drucken';
$TEXT['REQUIRED_JS'] = 'Javascript erforderlich';
$TEXT['SUBMISSIONS_PERPAGE'] = 'Anzeige Einträge';
$TEXT['PERPAGE'] = ' pro Seite';
$TEXT['ADMIN']  = 'Admin';
$MENU['USERS']  = 'Benutzer';
$TEXT['BACKUP'] = 'Frontend Vorlagen Exportieren/Importieren';
$TEXT['BACKUP_FIELDS'] = 'Felder Exportieren/Importieren';
$TEXT['EXPORT'] = 'Exportieren';
$TEXT['IMPORT'] = 'Importieren';
$TEXT['FIELD_EXPORT'] = 'Exportiere Felder';
$TEXT['FIELD_IMPORT'] = 'Importiere Felder';
$TEXT['FORM_NONE_FOUND'] ='Keine Einträge gefunden.';
$TEXT['MODIFY_FIELD']    = 'Feld bearbeiten';
$TEXT['ADD_FIELD']       = 'Feld hinzufügen';
$TEXT['MODIFY_DELETE_FIELD'] = 'Feld ändern oder löschen';
$TEXT['ADD_GROUP'] = 'Gruppe hinzufügen';
$TEXT['MODIFY_DELETE_GROUP'] = 'Gruppe ändern oder löschen';
$TEXT['DSGVO'] = 'Datenschutzrichtlinie';
$TEXT['DSGVO_LINK'] = 'Datenschutz Url';
$TEXT['EMPTY'] = '&#160;';
$TEXT['EXTRA'] = 'Extra Felder';
$TEXT['USE_CAPTCHA_AUTH'] = 'Kein Captcha wenn User eingeloggt';
$TEXT['INFO_DSGVO_IN_MAIL'] = 'Kein Hinweis der DSGVO in E-Mail Bestätigung.';
$TEXT['GO_TO'] = 'Gehe zu';
$TEXT['GO_TOP'] = 'nach Oben';
$TEXT['PREVENT_USER_CONFIRMATION'] = 'Keine E-Mail Bestätigung an Formular Absender';
$TEXT['USER_CONFIRMATION'] = 'Formular Absender Bestätigung';
$TEXT['SUBMESSAGE_FILE'] = 'Bearbeite %s ';
$TEXT['SUBJECT'] = 'Betreff';
$TEXT['BACK_TO_FORM'] = 'Zurück zum Formular';
$TEXT['MESSAGE'] = 'Nachricht';
$TEXT['EMAIL_RECIPIENT'] = 'Formular Nachricht an Empfänger';
$TEXT['EMAIL_SENDER'] = 'Formular Bestätigung an Absender';
$TEXT['DIVIDER'] = 'Trennzeichen';
$TEXT['PLACEHOLDER'] = 'Platzhalter';
$TEXT['FORM_REQUIRED'] = 'Erforderlich';
$TEXT['XML_FILES'] = 'XML Datei';
$TEXT['NEW_XML_FILE'] = 'Dateiname';
$TEXT['FORM_FRONTEND_CSS'] = 'Frontend Styles';

$FORM_MESSAGE = [
    'ARCHIVE_DELETED' => 'Archivdatei erfolgreich gelöscht.',
    'ARCHIVE_NOT_DELETED' => 'Archivdatei konnte nicht gelöscht werden.',
    'CONFIRM_FIELD_DELETING' => 'Möchten Sie ausgewähltes Feld wirklich löschen?',
    'DELETED' => 'Feld erfolgreich gelöscht.',
    'ALL_DELETED' => 'Alle Felder erfolgreich gelöscht.',
    'DELETE_FIELDS' => 'Felder löschen',
    'MISSING_UNMARKED_ARCHIVE_FILES' => 'Keine Fields-Vorlage zu importieren ausgewählt.',
    'GENERIC_MISSING_ARCHIVE_FILE' => 'Sie haben keine Archiv Datei ausgewählt.',
    'GENERIC_MISSING_TITLE' => 'Geben sie bitte einen Feldnamen ein.',
    'GENERIC_LOCAL_DOWNLOAD' => 'Archiv herunterladen',
    'GENERIC_LOCAL_UPLOAD' => 'Lokales Archiv laden und wiederherstellen',
    'DELETE_LAYOUT' => 'Alle Formular Felder entfernen.',
    'CONFIRM_DELETE_LAYOUT' => 'Sind sie sicher? Sollen alle Formular Felder entfernt werden.',
    'CAPTCHA_STYLE' => 'Captcha Style Attribute',
    'CAPTCHA_ACTION' => 'Captcha Anzeige',
    'ALL' => 'Ausgabes eines div-Container mit variierenden Spalten (Standard).',
    'IMAGE' => 'Ausgabe &lt;img&gt;-Tag nur für das Bild.',
    'IMAGE_IFRAME' => 'Output only a &lt;img&gt;-tag.',
    'INPUT' => 'Geben Sie nur das Eingabefeld aus, Sie können das Attribut style="...;" oder class="..." hinzufügen',
    'TEXT' => 'Text Ausgabe',
    'SUBJECT' => 'Subject Ausgabe',
    'FILE_TITLE_VALUE' => 'Datei nicht gefunden oder keinen Namen eingegeben!',
    'LSETTINGS' => 'Layouteinstellungen',
    'TEXT_SELECT_BOX' => 'Auswahl Layout',
    'LAYOUT_TITLE' => 'Layout Dateiname',
    'LAYOUT_TITLE_NEW' => 'Neuer Layout Dateiname',
    'LAYOUT_DESCRIPTION' => 'Layout Beschreibung',
    'LAYOUT_SETTINGS' => 'Layout Einstellungen',
    'LAYOUT' => 'Layout Ausgabe',
    'DOWNLOAD' => 'Herunterladen',
    'IMPORT_DELETED' => "%s wurde erfolgreich gelöscht\n",
    'IMPORT_SUCCESS' => "%s wurde erfolgreich importiert\n",
    'EXPORT_SUCCESS' => "%s wurde erfolgreich exportiert\n",
    'FIELD_SUCCESS'  => "Das Feld <b>%s</b> wurde erfolgreich gespeichert\n",
    'ADDED_SUCCESS' => "%s wurde erfolgreich angelegt\n",
    'MODIFIED_SUCCESS' => "%s wurde erfolgreich bearbeitet\n",
    'MODIFIED_FAILED' => "%s konnte nicht aktualisiert werden\n",
    'GENERIC_FILL_TITLE' => "Fehlender Beschreibungstitel\n",
    'GENERIC_FILL_TYPE' => "Bitte die Art des Feldes auswählen\n",
    'EMAIL_TAKEN' => "Die ausgewählte E-Mail Art wird bereits verwendet.\nEs ist nur eine E-Mail Adresse im Formular erlaubt\n",
    'DSGVO' => 'Hiermit bestätige ich, dass ich die <a href="%s" target="_blank" rel="noopener">Datenschutzerklärung</a> gelesen habe und stimme dieser durch Absenden des Formulars zu.',
    'NO_DSGVO' => 'Hiermit bestätige ich, dass ich die Datenschutzerklärung gelesen habe und stimme dieser durch Absenden des Formulars zu.',
    'PAGE_RELOADED' => "<b>Abgelaufene Sitzung!!</b> Nach dem An- oder Abmelden erst Seite neu laden oder F5 drücken ",
    ];

$FORM_HELP['IMPORT_FIELDS'] = 'Importieren Sie Formularfelder, die in einer XML-Datei gespeichert sind, um sie in ein leeres Formular einzufügen. Wenn sie eine Datei auswählen können sie diese auf Ihren lokalen Rechner sichern oder löschen, wenn diese nicht in Benutzung ist!';
$FORM_HELP['ADD_FIEDS'] = 'Importiert und fügt Formular Felder in ein leeres Formular ein.';
$FORM_HELP['GDPR'] = '';
$MESSAGE['UNKNOW_UPLOAD_ERROR']          = 'Unbekannter Upload Fehler';
$MESSAGE['UPLOAD_ERR_CANT_WRITE']        = 'Konnte Datei nicht schreiben. Fehlende Schreibrechte.';
$MESSAGE['UPLOAD_ERR_CANT_WRITE_FOLDER'] = 'Konnte Datei nicht in %s schreiben. ';
$MESSAGE['UPLOAD_ERR_EXTENSION']         = 'Erweiterungsfehler';
$MESSAGE['UPLOAD_ERR_FORM_SIZE']         = 'Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigrösse. ';
$MESSAGE['UPLOAD_ERR_INI_SIZE']          = 'Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Grösse von %s';
$MESSAGE['UPLOAD_ERR_NO_FILE']           = 'Es wurde keine Datei hochgeladen';
$MESSAGE['UPLOAD_ERR_FILE_EXISTS']       = '[%04d] Folgende Datei(en) bereits vorhanden. %s '."\n".'Aktiviere Checkbox (Überschreibe bestehende Dateien)'."\n\n";
$MESSAGE['UPLOAD_ERR_NO_TMP_DIR']        = 'Fehlender temporärer Ordner';
$MESSAGE['UPLOAD_ERR_OK']                = 'Die Datei wurde erfolgreich hochgeladen';
$MESSAGE['UPLOAD_ERR_PARTIAL']           = 'Die Datei wurde nur teilweise hochgeladen';
$MESSAGE['UPLOAD_ERR_POST_MAX_SIZE']     = 'Eine(r) der hochgeladenen Datei(en) überschreitet die in der Anweisung post_max_size in php.ini festgelegte Grösse von %s';
$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] = 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini. Dies ist ein Fehler, der auf Ihrer WebsiteBaker Installation auftritt, wenn Sie eine Datei hochladen, die die von Ihrem Webserver gesetzten Beschränkungen überschreitet. Bitten Sie Ihren Provider, die Beschränkungen zu erhöhen. Ihre Dateigröße ist %s und upload_max_filesize %s';
$errorTypes = [
    1 => "\n".'Die hochgeladene Datei %s überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe von %s. ',
    2 => "\n".'Die hochgeladene Datei %s überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße von %s. ',
    3 => "\n".'Die Datei %s wurde nur teilweise hochgeladen.  %s',
//    4 => 'Es wurde keine Datei hochgeladen. %s %s',
    6 => "\n".'Fehlender temporärer Ordner. %s %s',
    7 => "\n".'Speichern der Datei %s auf die Festplatte ist fehlgeschlagen. %s',
    8 => "\n".'Eine PHP Erweiterung hat den Upload der Datei %s gestoppt. %s'
];

