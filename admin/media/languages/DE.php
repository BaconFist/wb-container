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
 * Description of DE
 * @package      CoreTranslation
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: DE.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

if (!defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }

$HEADING['HEADING_MEDIA_MANAGEMENT'] = 'Medien Verwaltung';

$MESSAGE['MEDIA_BLANK_EXTENSION'] = 'Sie haben keine Dateiendung angegeben';
$MESSAGE['MEDIA_BLANK_NAME'] = 'Sie haben keinen neuen Namen angegeben';
$MESSAGE['MEDIA_CANNOT_DELETE_DIR'] = 'Das ausgewählte Verzeichnis konnte nicht gelöscht werden';
$MESSAGE['MEDIA_CANNOT_DELETE_FILE'] = 'Die ausgewählte Datei konnte nicht gelöscht werden';
$MESSAGE['MEDIA_CANNOT_RENAME'] = 'Das Umbenennen war nicht erfolgreich';
$MESSAGE['MEDIA_CONFIRM_DELETE_DIR'] = 'Sind Sie sicher, dass Sie das Verzeichnis %s komplett löschen möchten?';
$MESSAGE['MEDIA_CONFIRM_DELETE_FILE'] = 'Sind Sie sicher, dass Sie die Datei %s löschen möchten?';
$MESSAGE['MEDIA_DELETED_DIR'] = 'Das Verzeichnis wurde gelöscht';
$MESSAGE['MEDIA_DELETED_FILE'] = 'Die Datei wurde gelöscht';
$MESSAGE['MEDIA_DIR_ACCESS_DENIED'] = 'Verzeichnis existiert nicht oder Zugriff verweigert.';
$MESSAGE['MEDIA_DIR_DOES_NOT_EXIST'] = 'Verzeichnis existiert nicht';

$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'] = 'Der Verzeichnisname darf nicht ../ enthalten';
$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'] = 'Der Verzeichnisname liegt nicht innerhalb des/der zulässigen Pfades/Pfade:';

$MESSAGE['MEDIA_DIR_EXISTS'] = 'Ein Verzeichnis mit dem angegebenen Namen existiert bereits';
$MESSAGE['MEDIA_DIR_MADE'] = 'Das Verzeichnis wurde erfolgreich angelegt';
$MESSAGE['MEDIA_DIR_NOT_MADE'] = 'Das Verzeichnis konnte nicht angelegt werden';
$MESSAGE['MEDIA_FILE_EXISTS'] = 'Eine Datei mit dem angegebenen Namen existiert bereits';
$MESSAGE['MEDIA_FILE_NOT_FOUND'] = 'Die Datei konnte nicht gefunden werden';

$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH'] = 'Der Name darf nicht ../ enthalten';
$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH'] = 'Datei(/) liegt nicht innerhalb des/der zulässigen Pfades/Pfade:';

$MESSAGE['MEDIA_NAME_INDEX_PHP'] = 'Der Dateiname index.php kann nicht verwendet werden';
$MESSAGE['MEDIA_NAME_FILETYPE'] = '%s Unerlaubter Dateiname oder Dateitype in Eingabefeld %d ';
$MESSAGE['MEDIA_NONE_FOUND'] = 'Im aktuellen Verzeichnis konnten keine Dateien (z.B. Bilder) gefunden werden';
$MESSAGE['MEDIA_NO_FILE_UPLOADED'] = 'Es wurde keine Datei hochgeladen.'."\n".'Entweder keine Datei ausgewählt oder Dateigröße überprüfen!';

$MESSAGE['MEDIA_RENAMED'] = 'Das Umbenennen war erfolgreich';
$MESSAGE['MEDIA_SINGLE_UPLOADED'] = '%d Datei wurde erfolgreich übertragen';
$MESSAGE['MEDIA_MULTI_UPLOADED'] = '%d Dateien wurden erfolgreich übertragen';
$MESSAGE['MEDIA_ZIP_UPLOADED'] = '%d Verzeichnisse und %d Dateien wurden erfolgreich übertragen';
$MESSAGE['MEDIA_TARGET_DOT_DOT_SLASH'] = 'Der Name des Zielverzeichnisses darf nicht ../ enthalten';
$MESSAGE['MEDIA_UPLOADED'] = '%d Verzeichnisse und %d Dateien wurden erfolgreich übertragen';
$MESSAGE['MEDIA_SIZE_INFO'] = "Globale Bildgrößenänderung auf %dpx X %dpx\nBildgrößen Einstellungen in Media Optionen überschreibt die Globale Einstellung";
$MESSAGE['MEDIA_NO SIZE_INFO'] = "Keine Bildgrößenänderungeinstellung unter System Einstellungen gefunden!";

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

$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] =
'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini. Dies ist ein Fehler, der auf Ihrer WebsiteBaker Installation auftritt, wenn Sie eine Datei hochladen, die die von Ihrem Webserver gesetzten Beschränkungen überschreitet. Bitten Sie Ihren Provider, die Beschränkungen zu erhöhen. Ihre Dateigröße ist %s und upload_max_filesize %s';

$TEXT['RESIZE_UP'] = 'Vergrößern der Bilder erlauben';
$TEXT['DELETE_ARCHIVE'] = "Archiv nach Entpacken Löschen";

$errorTypes = [
    1 => "\n".'Die hochgeladene Datei %s überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe von %s. ',
    2 => "\n".'Die hochgeladene Datei %s überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße von %s. ',
    3 => "\n".'Die Datei %s wurde nur teilweise hochgeladen.  %s',
//    4 => 'Es wurde keine Datei hochgeladen. %s %s',
    6 => "\n".'Fehlender temporärer Ordner. %s %s',
    7 => "\n".'Speichern der Datei %s auf die Festplatte ist fehlgeschlagen. %s',
    8 => "\n".'Eine PHP Erweiterung hat den Upload der Datei %s gestoppt. %s'
];
