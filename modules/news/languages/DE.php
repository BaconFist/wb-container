<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2019, WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: DE.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/languages/DE.php $
 * @lastmodified    $Date: 2019-06-11 19:55:53 +0200 (Di, 11. Jun 2019) $
 *
 */

//Modul Description
$module_description = 'Mit diesem Modul können sie eine News Seite ihrer Seite hinzufügen.';

//Variables for the backend
$HEADING['GENERAL_SETTINGS'] = 'Allgemeine Einstellungen';
$HEADING['GENERAL_LAYOUTS'] = 'Frontend- und Kommenar Vorlage ändern oder hinzufügen';
$HEADING['GENERAL_COMMENTS'] = 'Kommentar Einstellungen';
$HEADING['LAYOUT_COMMENTS'] = 'Kommentar Vorlage';
$HEADING['CHOOSE_LAYOUTS'] = 'Vorlage auswählen';

$MOD_NEWS['SETTINGS'] = 'News Einstellungen';

//Variables for the frontend
$MOD_NEWS['TEXT_READ_MORE'] = 'Weiterlesen';
$MOD_NEWS['TEXT_POSTED_BY'] = 'Veröffentlicht von';
$MOD_NEWS['TEXT_ON'] = 'am';
$MOD_NEWS['TEXT_ORDER_ASC']    = 'Aufsteigend Sortieren';
$MOD_NEWS['TEXT_ORDER_DESC']   = 'Absteigend Sortieren';
$MOD_NEWS['TEXT_ORDER']        = 'Drag und Drop in der Übersicht ist nur für das Feld Position bei Aufsteigender Sortierung aktiviert, Einstellung unter Optionen';
$MOD_NEWS['TEXT_ORDER_TO']     = 'Sortierungs Art';
$MOD_NEWS['TEXT_ORDER_FROM']   = 'Sortierung nach Feld';
$MOD_NEWS['TEXT_POSITION']     = 'Position';
$MOD_NEWS['TEXT_PUBLISHED_WHEN']  = 'Veröffentlichung';
$MOD_NEWS['TEXT_LAST_CHANGED'] = 'Zuletzt geändert am';
$MOD_NEWS['TEXT_title'] = 'Titel';
$MOD_NEWS['TEXT_AT'] = 'um';
$MOD_NEWS['TEXT_BACK'] = 'Zurück zur Übersicht';
$MOD_NEWS['TEXT_COMMENTS'] = 'Kommentare';
$MOD_NEWS['TEXT_COMMENT'] = 'Kommentar';
$MOD_NEWS['TEXT_COMMENTING'] = 'kommentieren';
$MOD_NEWS['TEXT_ADD_COMMENT'] = 'Kommentar hinzufügen';
$MOD_NEWS['TEXT_ADD_POST'] = 'Beitrag hinzufügen';
$MOD_NEWS['TEXT_ADD_GROUP'] = 'Gruppe hinzufügen';
$MOD_NEWS['TEXT_DELETE_POST'] = 'Beitrag %s löschen?';
$MOD_NEWS['TEXT_DELETE_GROUP'] = 'Gruppe %s löschen?';

$MOD_NEWS['TEXT_BY'] = 'von';
$MOD_NEWS['PAGE_NOT_FOUND'] = 'Seite nicht gefunden';
$MOD_NEWS['NO_COMMENT_FOUND'] = 'Kein Kommentar gefunden';
$MOD_NEWS['NO_POSTS_FOUND'] = 'Kein Beitrag gefunden';
$MOD_NEWS['NO_GROUP_FOUND'] = 'Keine Gruppe gefunden';
$MOD_NEWS['SUCCESS_POST'] = 'Beitrag "%s" erfolgreich gespeichert!';
$MOD_NEWS['SUCCESS_GROUP'] = 'Gruppe "%s" erfolgreich gespeichert!';
$MOD_NEWS['SUCCESS_COMMENT'] = 'Kommentar erfolgreich gespeichert!';
$MOD_NEWS['DELETED_POST'] = 'Der Beitrag "%s" wurde erfolgreich gelöscht!';
$MOD_NEWS['NO_DELETED_POST'] = 'Beitragsdatei "%s" konnte nicht gelöscht werden!';
$MOD_NEWS['DELETED_GROUP'] = 'Gruppe "%s" wurde erfolgreich gelöscht!';
$MOD_NEWS['DELETED_COMMENT'] = 'Kommentar wurde erfolgreich gelöscht!';
$MOD_NEWS['LOAD_LAYOUT'] = 'Standard Layout laden';
$MOD_NEWS['LOAD'] = 'Laden';
$MOD_NEWS['MODERATED_COMMENT'] = 'Moderiertes kommentieren';
$MOD_NEWS['REQUIRED_FIELDS'] = 'Bitte um folgende Angaben ergänzen';
$MOD_NEWS['TEXT_MODIFY_POST'] = 'Beitrag ändern oder löschen';
$MOD_NEWS['TEXT_MODIFY_GROUP'] = 'Gruppe ändern oder löschen';
$MOD_NEWS['DSGVO'] = 'Fehlende Bestätigung und Zustimmung zur Datenschutzrichtlinie';

$MESSAGE['INCORRECT_CAPTCHA'] = 'Die eingegebene Prüfziffer stimmt nicht überein. Wenn Sie Probleme mit dem Lesen der Prüfziffer haben, schreiben Sie bitte eine E-Mail an: <a href="mailto:{{WEBMASTER_EMAIL}}">Webmaster</a>';

$TEXT['UNKNOWN'] = 'Gast';
$TEXT['DSGVO'] = 'Datenschutzrichtlinie';
$TEXT['DSGVO_LINK'] = 'Datenschutz Url';
$TEXT['MODIFIED'] = 'geändert';
$TEXT['LAYOUT'] = 'Template';

$NEWS_MESSAGE = [
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
    'ALL' => 'Output an div container with varying columns (default).',
    'IMAGE' => 'Output the &lt;img&gt;-tag for the image only.',
    'IMAGE_IFRAME' => 'Output only a &lt;img&gt;-tag.',
    'INPUT' => 'Output only the input-field, you can add style="...;" or class="..." Attribute',
    'ADD_LAYOUT' => 'Namen eingeben oder leeres Feld erzeugt einen neuen eindeutigen Namen aus "%2$s" wird "%1$s"',
    'TEXT' => 'Text output',
    'FILE_TITLE_VALUE' => 'Datei nicht gefunden oder keinen Namen eingegeben!',
    'LSETTINGS' => 'Layouteinstellungen',
    'TEXT_SELECT_BOX' => 'Auswahl Layout',
    'LAYOUT_TITLE' => 'Layout Dateiname',
    'LAYOUT_TITLE_NEW' => 'Neuer Layout Dateiname',
    'LAYOUT_DESCRIPTION' => 'Layout Beschreibung',
    'LAYOUT_SETTINGS' => 'Layout Einstellungen',
    'LAYOUT' => 'Layout Ausgabe',
    'DOWNLOAD' => 'Herunterladen',
    'IMPORT_DELETED' => '%s wurde erfolgreich gelöscht',
    'IMPORT_SUCCESS' => '%s wurde erfolgreich importiert',
    'EXPORT_SUCCESS' => '%s wurde erfolgreich exportiert',
    'DSGVO' => 'Hiermit bestätige ich, dass ich die <a href="%s" target="_blank" rel="noopener">Datenschutzerklärung</a> gelesen habe und stimme dieser durch Absenden des Formulars zu.',
   ];
