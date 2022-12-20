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
 * Description of EN
 * @package      CoreTranslation
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: EN.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

if (!defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }

$HEADING['HEADING_MEDIA_MANAGEMENT'] = 'Media Administration';

$MESSAGE['MEDIA_BLANK_EXTENSION'] = 'You did not enter a file extension';
$MESSAGE['MEDIA_BLANK_NAME'] = 'You did not enter a new name';
$MESSAGE['MEDIA_CANNOT_DELETE_DIR'] = 'Cannot delete the selected folder';
$MESSAGE['MEDIA_CANNOT_DELETE_FILE'] = 'Cannot delete the selected file';
$MESSAGE['MEDIA_CANNOT_RENAME'] = 'Rename unsuccessful';
$MESSAGE['MEDIA_CONFIRM_DELETE_DIR'] = 'Are you sure you want to delete the folder %s completely?';
$MESSAGE['MEDIA_CONFIRM_DELETE_FILE'] = 'Are you sure you want to delete the file %s?';
$MESSAGE['MEDIA_DELETED_DIR'] = 'Folder deleted successfully';
$MESSAGE['MEDIA_DELETED_FILE'] = 'File deleted successfully';
$MESSAGE['MEDIA_DIR_ACCESS_DENIED'] = 'Specified directory does not exist or is not allowed.';
$MESSAGE['MEDIA_DIR_DOES_NOT_EXIST'] = 'Directory does not exist';

$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'] = 'Cannot include ../ in the folder name';
$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'] = 'The directory name is not within the allowed path(s):';

$MESSAGE['MEDIA_DIR_EXISTS'] = 'A folder matching the name you entered already exists';
$MESSAGE['MEDIA_DIR_MADE'] = 'Folder created successfully';
$MESSAGE['MEDIA_DIR_NOT_MADE'] = 'Unable to create folder';
$MESSAGE['MEDIA_FILE_EXISTS'] = 'A file matching the name you entered already exists';
$MESSAGE['MEDIA_FILE_NOT_FOUND'] = 'File not found';

$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH'] = 'Cannot include ../ in the name';
$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH'] = 'File(/) is not within the allowed path(s):';

$MESSAGE['MEDIA_NAME_INDEX_PHP'] = 'Cannot use index.php as the name';
$MESSAGE['MEDIA_NAME_FILETYPE'] = '%s Not allowed file name or file type in input field %d ';
$MESSAGE['MEDIA_NONE_FOUND'] = 'No media found in the current folder';
$MESSAGE['MEDIA_NO_FILE_UPLOADED'] = 'No file was received from a total of %d.'."\n".'No File selected or Check file size!';

$MESSAGE['MEDIA_RENAMED'] = 'Rename successful';
$MESSAGE['MEDIA_SINGLE_UPLOADED'] = '%d file was successfully uploaded';
$MESSAGE['MEDIA_MULTI_UPLOADED'] = '%d files were successfully uploaded';
$MESSAGE['MEDIA_ZIP_UPLOADED'] = '%d folder and %d files were successfully uploaded';
$MESSAGE['MEDIA_TARGET_DOT_DOT_SLASH'] = 'Cannot have ../ in the folder target';
$MESSAGE['MEDIA_UPLOADED'] = '%d folder and %d files were successfully uploaded';
$MESSAGE['MEDIA_SIZE_INFO'] = "Global image size resized to %dpx X %dpx\nimage size settings in Media Options overrides the global setting";
$MESSAGE['MEDIA_NO SIZE_INFO'] = "No image resizing setting found under system settings!";

$MESSAGE['UNKNOW_UPLOAD_ERROR'] = "Unknown upload error";
$MESSAGE['UPLOAD_ERR_CANT_WRITE'] = "Failed to write file to disk";
$MESSAGE['UPLOAD_ERR_CANT_WRITE_FOLDER'] = "Failed to write file to %s";
$MESSAGE['UPLOAD_ERR_EXTENSION'] = "File upload stopped by extension";
$MESSAGE['UPLOAD_ERR_FORM_SIZE'] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
$MESSAGE['UPLOAD_ERR_INI_SIZE'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini of %s";
$MESSAGE['UPLOAD_ERR_NO_FILE'] = "No file was uploaded";
$MESSAGE['UPLOAD_ERR_FILE_EXISTS'] = '[%04d] Following File(s) already exists. %s '."\n".'Activate Checkbox (overwrite existing files)'."\n\n";
$MESSAGE['UPLOAD_ERR_NO_TMP_DIR'] = "Missing a temporary folder";
$MESSAGE['UPLOAD_ERR_OK'] = "File was successfully uploaded";
$MESSAGE['UPLOAD_ERR_PARTIAL'] = "The uploaded file was only partially uploaded";
$MESSAGE['UPLOAD_ERR_POST_MAX_SIZE'] = 'One or more of the uploaded file exceeds the post_max_size directive in php.ini of %s';

$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] =
'The uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini. This is an error that occurs on your WebsiteBaker installation when you upload a file that exceeds the limitations set by your webserver. Ask your provider to increase the limitations. Your filesize ist %s and  upload_max_filesize %s';

$TEXT['RESIZE_UP'] = 'Allow enlargement of the images';
$TEXT['DELETE_ARCHIVE'] = "Delete archive after unpacking";

$errorTypes = [
    1 => 'The uploaded file %s exceeds the upload_max_filesize directive %s in php.ini.',
    2 => 'The uploaded file %s exceeds the MAX_FILE_SIZE directive %s that was specified in the HTML form.',
    3 => 'The uploaded file %s was only partially uploaded. %s',
//    4 => 'No file was uploaded. %s %s',
    6 => 'Missing a temporary folder. %s %s',
    7 => 'Failed to write file %s to disk. %s',
    8 => 'A PHP extension stopped the file %s upload. %s'
];
