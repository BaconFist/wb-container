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
 * @revision     $Id: DE.php 234 2019-03-17 06:05:56Z Luisehahne $
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

$MESSAGE['GROUPS_ADDED'] = 'Die Gruppe wurde erfolgreich hinzugefügt';
$MESSAGE['GROUPS_CONFIRM_DELETE'] = 'Sind Sie sicher, dass Sie die ausgewählte Gruppe löschen möchten (und alle Benutzer, die dazugehören)?';
$MESSAGE['GROUPS_DELETED'] = 'Die Gruppe wurde erfolgreich gelöscht';
$MESSAGE['GROUPS_GROUP_NAME_BLANK'] = 'Der Gruppenname wurde nicht angegeben';
$MESSAGE['GROUPS_GROUP_NAME_EXISTS'] = 'Der Gruppenname existiert bereits';
$MESSAGE['GROUPS_NO_GROUPS_FOUND'] = 'Keine Gruppen gefunden';
$MESSAGE['GROUPS_SAVED'] = 'Die Gruppe wurde erfolgreich gespeichert';
$MESSAGE['GROUPS_DEFAULT_SAVED'] = ' Grundeinstellung wurde erfolgreich zurückgesetzt';
$MESSAGE['GROUPS_IN_USE_CANNOT_UNINSTALL'] = 'Gruppe %s</b> kann nicht gelöscht werden (mind. 1 User ist noch in der Gruppe)';
$MESSAGE['SELECT_GROUP'] = 'Bitte Gruppe auswählen';

$TEXT['DEFAULT_RESET'] ='Grund-Einstellung wiederherstellen';

