<?php
// $Id: EN.php 68 2018-09-17 16:26:08Z Luisehahne $

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

// english module description
$module_description     = 'Droplets are small chunks of php code (just like the code module) that can be included in your template or any other content section. Including a droplet is done by encapsulating the droplet name in double brackets.';
// headers and text
$DR_TEXT['ADD_DROPLET']   = 'Add a Droplet';
$DR_TEXT['ADMIN_EDIT']    = 'Edit a Droplet';
$DR_TEXT['ADMIN_VIEW']    = 'View';
$DR_TEXT['ARCHIV_LOAD']   = 'Load an Archive file';
$DR_TEXT['ACTION']        = 'Droplets '.PHP_EOL.'rename'.PHP_EOL.''.'enabled/disabled'.PHP_EOL.'delete';
$DR_TEXT['BACKUP']        = 'Backup Droplets (Zip)';
$DR_TEXT['COPY']          = 'Copy a Droplet';
$DR_TEXT['DELETE']        = 'Delete a Droplet';
$DR_TEXT['DROPLET']       = 'Droplet';
$DR_TEXT['DROPLETS']      = 'Droplets';
$DR_TEXT['DROPLETS_DELETED'] = 'Droplets deleted successfully.';
$DR_TEXT['HELP']          = 'Help';
$DR_TEXT['IMPORT']        = 'Import selected Droplet';
$DR_TEXT['INVALIDCODE']   = 'This Droplet has invalid PHP code';
$DR_TEXT['INVALID_BACK']  = 'Invalid choice. Back to the overview';
$DR_TEXT['MODIFIED_WHEN'] = 'Modified';
$DR_TEXT['MODIFY']        = 'Edit';
$DR_TEXT['RESTORE']       = 'Droplets restored';
$DR_TEXT['README']        = 'readme.html';
$DR_TEXT['SHOW']          = 'Overview';
$DR_TEXT['SAVE']          = 'Save';
$DR_TEXT['NOTUNIQUE']     = 'This droplet name is used!';
$DR_TEXT['WYSIWYG']       = 'WYSIWYG';
$DR_TEXT['UPLOAD']        = 'Upload';
$DR_TEXT['USED']          = 'This droplet is used on the following page(-s):<br />';
$DR_TEXT['PLEASE_SELECT'] = 'Please select an Archive file';
$DR_TEXT['VALID_CODE']    = 'Valid Code';
$DR_TEXT['INVALID_CODE']  = 'Invalid Code';
$DR_TEXT['COPY_DROPLET']  = 'Duplicate';
$DR_TEXT['INACTIVE']      = 'Disabled';
$TEXT['INACTIVE']         = 'Disabled';
$TEXT['ACTIVE']           = 'Enabled';

$DROPLET_MESSAGE = array (
    'ARCHIVE_DELETED' => 'Zip(s) deleted successfully.',
    'ARCHIVE_NOT_DELETED' => 'Cannot delete the selected Zip(s).',
    'CONFIRM_DROPLET_DELETING' => 'Are you sure you want to delete the selected droplets?',
    'DELETED' => 'Droplets deleted successfully.',
    'DELETE_DROPLETS' => 'Delete a Droplet',
    'MISSING_UNMARKED_ARCHIVE_FILES' => 'No Droplet-File selected to import.',
    'GENERIC_MISSING_ARCHIVE_FILE' => 'No Zip-File selected to delete!',
    'GENERIC_MISSING_TITLE' => 'Insert a Droplet name.',
    'GENERIC_LOCAL_DOWNLOAD' => 'Download Zip',
    'GENERIC_LOCAL_UPLOAD' => 'Load and restore a local Zip',
);

$DROPLET_HEADER = array (
    'INDEX' => 'Id',
    'PATH' => 'Folder',
    'FILENAME' => 'Droplet Name',
    'DESCRIPTION' => 'Description',
    'SIZE' => 'Size',
    'DATE' => 'Date',
    'COPY_DROPLET' => 'Copy/Rename Droplet',
    'RENAME_DROPLET' => 'Rename Droplet',
    'SELECT_DROPLET' => 'Select a Zip',
    );

$DROPLET_SELECT_ORDER = array (
    'CHOOSE_ORDER'=>'Select Droplet Sorting Order',
    'ASC' => 'Ascending',
    'DESC'=> 'Descending',
    );

$DROPLET_SELECT_OPTION = array (
        'ASC' => 'Ascending', array(
        '1' => 'Droplet Name',
        '2' => 'Modified When',
        ),
        'DESC' => 'Ascending', array(
        '4' => 'Droplet Name',
        '8' => 'Modified When',
        )
    );

$DROPLET_HELP = array (
    'DROPLET_DELETE' => 'Delete a Droplet. Click to delete the selected droplet in this row. By selecting, you can also delete multiple droplets at once. ',
    'DROPLET_RENAME' => 'Now you can rename the Droplet',
    'DROPLET_COPY' => 'Now you can copy or rename the droplet. ',
    'DROPLET_RENAME_ADD' => 'Input your new Droplet Name',
);

$DROPLET_IMPORT = array (
      'ARCHIV_LOADED' => 'Zip loaded successfully! Choose one or more droplets to restore.',
      'ARCHIV_IMPORTED' => 'Selected droplets imported into the database ! ',
      'UPATE_EXISTING_DROPLETS' => 'Overwrite existing droplets?',
      );
