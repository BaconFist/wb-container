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
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: DE.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */

//declare(strict_types = 1);

//declare(encoding = 'UTF-8');

if (!defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}

// Set the language information
$language_code     = 'DE';
$language_name     = 'Deutsch';
$language_version  = '4.0.0';
$language_platform = '2.11.0';
$language_author   = 'Manuela v.d.Decken, Dietmar Wöllbrink';
$language_license  = 'GNU General Public License 2.0';

$HEADING['ADDON_PRECHECK_FAILED']         = 'Add-On Voraussetzungen nicht erfüllt';
$HEADING['GENERAL_SETTINGS']              = 'Allgemeine Optionen';
$HEADING['INSTALL_LANGUAGE']              = 'Sprache hinzufügen';
$HEADING['INSTALL_MODULE']                = 'Modul installieren';
$HEADING['INSTALL_TEMPLATE']              = 'Designvorlage installieren';
$HEADING['INVOKE_LANGUAGE_FILES']         = 'Sprachdateien manuell ausführen';
$HEADING['INVOKE_MODULE_FILES']           = 'Moduldateien manuell ausführen';
$HEADING['INVOKE_TEMPLATE_FILES']         = 'Templatedateien manuell ausführen';
$HEADING['addon_DETAILS']                 = 'Details zur Sprache';
$HEADING['MANAGE_SECTIONS']               = 'Abschnitte verwalten';
$HEADING['MODIFY_ADVANCED_PAGE_SETTINGS'] = 'Erweiterte Seitenoptionen ändern';
$HEADING['MODULE_DETAILS']                = 'Details zum Modul';
$HEADING['TEMPLATE_DETAILS']              = 'Details zur Designvorlage';
$HEADING['UNINSTALL_LANGUAGE']            = 'Sprache löschen';
$HEADING['UNINSTALL_MODULE']              = 'Modul deinstallieren';
$HEADING['UNINSTALL_TEMPLATE']            = 'Designvorlage deinstallieren';
$HEADING['UPGRADE_LANGUAGE']              = 'Sprache registrieren/aktualisieren (Upgrade)';
$HEADING['UPLOAD_FILES']                  = 'Datei(en) übertragen';

$MENU['ADDON']     = 'Add-on';
$MENU['ADDONS']    = 'Erweiterungen';
$MENU['LANGUAGES'] = 'Sprachen';
$MENU['TEMPLATES'] = 'Designvorlagen';

$MESSAGE['ADDON_ERROR_RELOAD']                           = 'Fehler beim Abgleich der Addon Informationen.';
$MESSAGE['ADDON_LANGUAGES_RELOADED']                     = 'Sprachen erfolgreich geladen';
$MESSAGE['ADDON_MANUAL_FTP_LANGUAGE']                    = '<strong>ACHTUNG!</strong> Überspielen Sie Sprachdateien aus Sicherheitsgründen nur über FTP in den Ordner /languages/ und benutzen die Upgrade Funktion zum Registrieren oder Aktualisieren.';
$MESSAGE['ADDON_MANUAL_FTP_WARNING']                     = 'Warnung: Eventuell vorhandene Datenbankeinträge eines Moduls gehen verloren. ';
$MESSAGE['ADDON_MANUAL_INSTALLATION']                    = 'Beim Hochladen oder Löschen von Modulen per FTP (nicht empfohlen), werden eventuell vorhandene Modulfunktionen <tt>install</tt>, <tt>upgrade</tt> oder <tt>uninstall</tt> nicht automatisch ausgeführt. Solche Module funktionieren daher meist nicht richtig, oder hinterlassen Datenbankeinträge beim Löschen per FTP.<br /><br /> Nachfolgend können die Modulfunktionen von per FTP hochgeladenen Modulen manuell ausgeführt werden.';
$MESSAGE['ADDON_MANUAL_INSTALLATION_WARNING']            = 'Warnung: Eventuell vorhandene Datenbankeinträge eines Moduls gehen verloren. Bitte nur bei Problemen mit per FTP hochgeladenen Modulen verwenden. ';
$MESSAGE['ADDON_MANUAL_RELOAD_WARNING']                  = 'Warnung: Eventuell vorhandene Datenbankeinträge eines Moduls gehen verloren. ';
$MESSAGE['ADDON_MODULES_RELOADED']                       = 'Module erfolgreich geladen';
$MESSAGE['ADDON_OVERWRITE_NEWER_FILES']                  = 'Überschreibe neuere Dateien';
$MESSAGE['ADDON_PRECHECK_FAILED']                        = 'Installation fehlgeschlagen. Ihr System erfüllt nicht alle Voraussetzungen die für diese Erweiterung benötigt werden. Um diese Erweiterung nutzen zu können, müssen nachfolgende Updates durchgeführt werden.';
$MESSAGE['ADDON_RELOAD']                                 = 'Abgleich der Datenbank mit den Informationen aus den Addon-Dateien (z.B. nach FTP Upload).';
$MESSAGE['ADDON_TEMPLATES_RELOADED']                     = 'Designvorlagen erfolgreich geladen';
$MESSAGE['GENERIC_ALREADY_INSTALLED']                    = '%s [%s] %s ist bereits installiert';
$MESSAGE['GENERIC_CANNOT_UNINSTALL']                     = 'Deinstallation %s fehlgeschlagen';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE']              = "Deinstallation nicht möglich: Datei wird benutzt\n";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL']         = "%s %s kann nicht deinstalliert werden,\n weil es auf %s benutzt wird:\n";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES']   = 'folgender Seite;folgenden Seiten';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE'] = 'Das Template %s kann nicht deinstalliert werden, weil es das Standardtemplate ist!';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_THEME']    = 'Das Template %s kann nicht deinstalliert werden, weil es das Standardbackendtheme ist!';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_LANGUAGE'] = 'Die Sprache %s kann nicht deinstalliert werden, weil es die Standardsprache ist!';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_LANG_USERS']   = 'folgendem Benutzer;folgenden Benutzern';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_LANG']         = "%s %s kann nicht deinstalliert werden,\n weil es von %s benutzt wird:\n";
$MESSAGE['LANG_INUSE_LINK']                              = '%s'."\n";
$MESSAGE['GENERIC_CANNOT_UNZIP']                         = 'Fehler beim Entpacken';
$MESSAGE['GENERIC_CANNOT_UPLOAD']                        = 'Die Datei kann nicht übertragen werden';
$MESSAGE['GENERIC_COMPARE']                              = ' erfolgreich';
$MESSAGE['GENERIC_ERROR_OPENING_FILE']                   = 'Fehler beim Öffnen der Datei.';
$MESSAGE['GENERIC_FAILED_COMPARE']                       = ' fehlgeschlagen';
$MESSAGE['GENERIC_FILE_TYPE']                            = 'Bitte beachten Sie, dass Sie nur den folgenden Dateityp auswählen können:';
$MESSAGE['GENERIC_FILE_TYPES']                           = 'Bitte beachten Sie, dass Sie nur folgende Dateitypen auswählen können:';
$MESSAGE['GENERIC_FILL_IN_ALL']                          = 'Bitte alle Felder ausfüllen';
$MESSAGE['GENERIC_FORGOT_OPTIONS']                       = 'Sie haben keine Auswahl getroffen!';
$MESSAGE['GENERIC_INSTALLED']                            = '%s [%s] %s wurde erfolgreich installiert';
$MESSAGE['GENERIC_INVALID']                              = 'Die übertragene Datei ist ungültig';
$MESSAGE['GENERIC_INVALID_ADDON_FILE_ZIP']               = 'Ungültige WebsiteBaker Installationsdatei. Bitte *.zip Format prüfen.';
$MESSAGE['GENERIC_INVALID_ADDON_FILE']                   = 'Ungültige WebsiteBaker Installationsdatei. Bitte %s Format/Struktur prüfen.';
$MESSAGE['GENERIC_INVALID_LANGUAGE_FILE']                = 'Ungültige WebsiteBaker Sprachdatei. Bitte Voraussetzungen prüfen.';
$MESSAGE['GENERIC_INVALID_MODULE_FILE']                  = 'Ungültige WebsiteBaker Moduledatei. Bitte Voraussetzungen prüfen.';
$MESSAGE['GENERIC_INVALID_PLATFORM']                     = 'Ein Upgrade oder Installation von einer WebsiteBaker Version kleiner %s ist nicht möglich.';
$MESSAGE['GENERIC_INVALID_TEMPLATE_FILE']                = 'Ungültige WebsiteBaker Templatedatei. Bitte Voraussetzungen prüfen.';
$MESSAGE['GENERIC_IN_USE']                               = ' aber benutzt in ';
$MESSAGE['GENERIC_MISSING_ARCHIVE_FILE']                 = 'Fehlende Archivdatei!';
$MESSAGE['GENERIC_MODULE_VERSION_ERROR']                 = 'Das Addon %s ist nicht ordnungsgemäss installiert!';
$MESSAGE['GENERIC_NOT_COMPARE']                          = ' nicht möglich';
$MESSAGE['GENERIC_NOT_INSTALLED']                        = 'Nicht installiert';
$MESSAGE['PAGE_INUSE_LINK']                              = '<a href="%s/pages/%s.php?page_id=%s">%s</a>'."\n";
$MESSAGE['GENERIC_NOT_UPGRADED']                         = '%s [%s] %s eine Aktualisierung ist nicht möglich';
$MESSAGE['GENERIC_UNINSTALLED']                          = '%s %s erfolgreich deinstalliert';
$MESSAGE['GENERIC_UPGRADED']                             = '%s [%s] %s wurde erfolgreich aktualisiert';
$MESSAGE['GENERIC_VERSION_COMPARE']                      = 'Versionsabgleich';
$MESSAGE['GENERIC_VERSION_GT']                           = 'Upgrade erforderlich!';
$MESSAGE['GENERIC_VERSION_LT']                           = 'Downgrade';
$MESSAGE['GENERIC_WRONG_FORMAT']                         = 'Die hochgeladene Datei hat ein ungültiges Dateiformat [%s]!';
$MESSAGE['MEDIA_CANNOT_DELETE_DIR']                      = 'Das ausgewählte Verzeichnis konnte nicht gelöscht werden';
$MESSAGE['MEDIA_CANNOT_DELETE_FILE']                     = 'Die ausgewählte Datei konnte nicht gelöscht werden';
$MESSAGE['MEDIA_CANNOT_RENAME']                          = 'Das Umbenennen war nicht erfolgreich';
$MESSAGE['MEDIA_CONFIRM_DELETE']                         = 'Sind Sie sicher, dass Sie die folgende Datei oder Verzeichnis löschen möchten?';
$MESSAGE['MEDIA_DELETED_DIR']                            = 'Das Verzeichnis wurde gelöscht';
$MESSAGE['MEDIA_DELETED_FILE']                           = 'Die Datei wurde gelöscht';
$MESSAGE['MEDIA_DIR_ACCESS_DENIED']                      = 'Verzeichnis existiert nicht oder Zugriff verweigert.';
$MESSAGE['MEDIA_DIR_DOES_NOT_EXIST']                     = 'Verzeichnis existiert nicht';
$MESSAGE['MEDIA_DIR_MADE']                               = 'Das Verzeichnis wurde erfolgreich angelegt';
$MESSAGE['MEDIA_DIR_NOT_MADE']                           = 'Das Verzeichnis konnte nicht angelegt werden';
$MESSAGE['MEDIA_FILE_EXISTS']                            = 'Eine Datei mit dem angegebenen Namen existiert bereits';
$MESSAGE['MEDIA_FILE_NOT_FOUND']                         = 'Die Datei konnte nicht gefunden werden';
$MESSAGE['MEDIA_NO_FILE_UPLOADED']                       = 'Es wurde keine Datei empfangen';
$MESSAGE['MEDIA_UPLOADED']                               = 'Dateien wurden erfolgreich übertragen';
$MESSAGE['MOD_FORM_REQUIRED_FIELDS']                     = 'Bitte folgende Angaben ergänzen';
$MESSAGE['SETTINGS_UNABLE_OPEN_CONFIG']                  = 'Konfigurationsdatei konnte nicht geöffnet werden';
$MESSAGE['THEME_COPY_CURRENT']                           = 'Das aktuell aktive Backend-Theme kopieren und unter einem neuem Namen abspeichern.';
$MESSAGE['THEME_DESTINATION_READONLY']                   = 'Ungenügende Rechte um das Zielverzeichnis zu erstellen!';
$MESSAGE['THEME_IMPORT_HTT']                             = 'Zusätzliche Templatefile(s) in das aktuelle Theme importieren.<br />Mit diesen Templates können die Default-Templates überschrieben werden.';
$MESSAGE['THEME_INVALID_SOURCE_DESTINATION']             = 'Ungültigen Theme-Name angegeben!';

$MESSAGE['UNKNOW_UPLOAD_ERROR']                          = 'Unbekannter Upload Fehler';
$MESSAGE['UPLOAD_ERR_CANT_WRITE']                        = 'Konnte Datei nicht schreiben. Fehlende Schreibrechte.';
$MESSAGE['UPLOAD_ERR_CANT_WRITE_FOLDER']                 = 'Konnte Datei nicht in %s schreiben. ';
$MESSAGE['UPLOAD_ERR_EXTENSION']                         = 'Erweiterungsfehler';
$MESSAGE['UPLOAD_ERR_FORM_SIZE']                         = 'Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigrösse. ';
$MESSAGE['UPLOAD_ERR_INI_SIZE']                          = 'Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Grösse von %s';
$MESSAGE['UPLOAD_ERR_NO_FILE']                           = 'Es wurde keine Datei hochgeladen';
$MESSAGE['UPLOAD_ERR_NO_TMP_DIR']                        = 'Fehlender temporärer Ordner';
$MESSAGE['UPLOAD_ERR_OK']                                = 'Die Datei wurde erfolgreich hochgeladen';
$MESSAGE['UPLOAD_ERR_PARTIAL']                           = 'Die Datei wurde nur teilweise hochgeladen';
$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] =
'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini. Dies ist ein Fehler, der auf Ihrer WebsiteBaker Installation auftritt, wenn Sie eine Datei hochladen, die die von Ihrem Webserver gesetzten Beschränkungen überschreitet. Bitten Sie Ihren Provider, die Beschränkungen zu erhöhen. Ihre Dateigröße ist %s und upload_max_filesize %s';
$TEXT['EXECUTE'] = '%s %s ausführen';
$TEXT['EXECUTED'] = '%s %s ausgeführt';
$TEXT['NOT_EXECUTED'] = "%s %s konnte nicht ausgeführt werden \n%s";
$TEXT['NONE_FOUND'] = 'Keine gefunden';
$TEXT['NOT_FOUND'] = '%s not found';
$TEXT['NOT_INSTALLED'] = '%s not installed';

$TEXT['ADMIN'] = 'System Verwaltungs Addon';
$TEXT['ADMINISTRATION'] = 'Administration Verwaltungs Addon';
$TEXT['LANGUAGE'] = 'Sprachen Addon';
$TEXT['SCRIPT_NOT_FOUND'] = '%s/%s.php nicht gefunden';
$TEXT['SCRIPT_NOT_INSTALLED'] = '%s/%s.php nicht installiert';
$TEXT['PAGE'] = 'Seiten Addon';
$TEXT['TEMPLATE'] = 'Frontend-Template';
$TEXT['THEME'] = 'Backend-Theme';
$TEXT['TOOL'] = 'System Verwaltungs Addon';
$TEXT['UNKNOWN'] = 'Unbekanntes Addon';
$TEXT['WYSIWYG'] = 'Wysiwyg Editor';
$TEXT['SNIPPET'] = 'Funktionserweiterung';

$OVERVIEW['ADDONS'] = 'Zurück nach Erweiterungen';
$TEXT['MODIFY_CONTENT'] = 'Erweiterungen aktualisieren';
