<?php
// $Id: DE.php 68 2018-09-17 16:26:08Z Luisehahne $

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2009, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

// Deutsche Modulbeschreibung
$module_description     = 'Droplets sind relativ frei programmierbare Ausgabefilter vom Typ "Suchen&Ersetzen". Der jeweilige Droplet-Tag wird bei der Ausgabe der Seite durch das berechnete Ergebnis ersetzt.';
// Ueberschriften und Textausgaben
$DR_TEXT['ADD_DROPLET']   = 'Droplet hinzufügen';
$DR_TEXT['ADMIN_EDIT']    = 'bearbeiten';
$DR_TEXT['ADMIN_VIEW']    = 'ansehen';
$DR_TEXT['ARCHIV_LOAD']   = 'Archivdatei laden';
$DR_TEXT['ACTION']        = 'Droplets '.PHP_EOL.'umbenennen'.PHP_EOL.''.'einschalten/ausschalten'.PHP_EOL.'löschen';
$DR_TEXT['BACKUP']        = 'Droplets sichern (Zip)';
$DR_TEXT['COPY']          = 'Ein Droplet kopieren';
$DR_TEXT['DELETE']        = 'Löschen';
$DR_TEXT['DROPLET']       = 'Droplet';
$DR_TEXT['DROPLETS']      = 'Droplets';
$DR_TEXT['DROPLETS_DELETED'] = 'Droplets erfolgreich gelöscht.';
$DR_TEXT['HELP']          = 'Hilfe';
$DR_TEXT['IMPORT']        = 'Droplets importieren';
$DR_TEXT['INVALIDCODE']   = 'Dieses Droplet enthält ungültigen PHP code';
$DR_TEXT['INVALID_BACK']  = 'Ungültige Auswahl. Zurück zur Übersicht';
$DR_TEXT['MODIFY']        = 'Editieren';
$DR_TEXT['MODIFIED_WHEN'] = 'Bearbeitet';
$DR_TEXT['RESTORE']       = 'Droplets wiederherstellen (Zipped)';
$DR_TEXT['README']        = 'readme.html';
$DR_TEXT['SHOW']          = 'Übersicht';
$DR_TEXT['SAVE']          = 'Speichern';
$DR_TEXT['NOTUNIQUE']     = 'Dieser Dropletname ist bereits vorhanden!';
$DR_TEXT['WYSIWYG']       = 'Wysiwyg';
$DR_TEXT['UPLOAD']        = 'Hochladen';
$DR_TEXT['USED']          = 'Dieses Droplet wird auf folgenden Seiten benutzt (-s):<br />';
$DR_TEXT['PLEASE_SELECT'] = 'Bitte eine Archivdatei auswählen';
$DR_TEXT['INACTIVE']      = 'Inaktiv';
$DR_TEXT['VALID_CODE']    = 'Code valide';
$DR_TEXT['INVALID_CODE']  = 'Code fehlerhaft';
$DR_TEXT['COPY_DROPLET']  = 'Duplizieren';
$TEXT['INACTIVE']         = 'Inaktiv';
$TEXT['ACTIVE']           = 'Aktiv';

$DROPLET_MESSAGE = array (
    'ARCHIVE_DELETED' => 'Archivdatei erfolgreich gelöscht.',
    'ARCHIVE_NOT_DELETED' => 'Archivdatei konnte nicht gelöscht werden.',
    'CONFIRM_DROPLET_DELETING' => 'Möchten Sie ausgewählte Droplets wirklich löschen?',
    'DELETED' => 'Droplets erfolgreich gelöscht.',
    'DELETE_DROPLETS' => 'Droplets löschen',
    'MISSING_UNMARKED_ARCHIVE_FILES' => 'Sie haben keine Droplets zum importieren ausgewählt.',
    'GENERIC_MISSING_ARCHIVE_FILE' => 'Sie haben keine Archiv Datei ausgewählt.',
    'GENERIC_MISSING_TITLE' => 'Geben sie bitte einen Dropletnamen ein.',
    'GENERIC_LOCAL_DOWNLOAD' => 'Archiv herunterladen',
    'GENERIC_LOCAL_UPLOAD' => 'Lokales Archiv laden und wiederherstellen',
    );

$DROPLET_HEADER = array (
    'INDEX' => 'Id',
    'PATH' => 'Verzeichnis',
    'FILENAME' => 'Dropletname',
    'DESCRIPTION' => 'Beschreibung',
    'SIZE' => 'Größe',
    'DATE' => 'Datum',
    'COPY_DROPLET' => 'Droplet kopieren/umbenennen',
    'RENAME_DROPLET' => 'Droplet umbenennen',
    'SELECT_DROPLET' => 'Droplet auswählen',
    );

$DROPLET_SELECT_ORDER = array (
    'CHOOSE_ORDER'=>'Droplets sortieren nach',
    'ASC' => 'Aufsteigend',
    'DESC'=> 'Absteigend',
    );

$DROPLET_SELECT_OPTION = array (
        'ASC' => 'Aufsteigend', array(
        '1' => 'Dropletname',
        '2' => 'Zuletzt bearbeitet'
        ),
        'DESC' => 'Absteigend', array(
        '4' => 'Dropletname',
        '8' => 'Zuletzt bearbeitet'
        )
    );

$DROPLET_HELP = array (
    'DROPLET_DELETE' => 'Löschen von Droplets. Klick löscht das entsprechende Droplet in der ausgewählten Zeile. Durch Auswahl lassen sich auch mehrere Droplets auf einmal löschen. ',
    'DROPLET_RENAME' => 'Sie können jetzt das Droplet umbenennen. ',
    'DROPLET_COPY' => 'Sie können jetzt das Droplet kopieren oder umbenennen. ',
    'DROPLET_RENAME_ADD' => 'Geben Sie jetzt einen neuen Dropletnamen ein. ',
);

$DROPLET_IMPORT = array (
      'ARCHIV_LOADED' => 'Archivdatei erfolgreich geladen! Wählen Sie ein odere mehrere Droplets zur Wiederherstellung aus.',
      'ARCHIV_IMPORTED' => 'Ausgewählte Droplets in Datenbank importiert! ',
      'UPATE_EXISTING_DROPLETS' => 'Sollen bestehende Droplets überschrieben werden?',
      );
