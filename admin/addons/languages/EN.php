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
 * Description of \admin\addons\EN
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

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

// Set the language information
$language_code = 'EN';
$language_name = 'English';
$language_version = '4.0.0';
$language_platform = '2.11.0';
$language_author = 'Manuela v.d.Decken, Dietmar Wöllbrink';
$language_license = 'GNU General Public License 2.0';

$HEADING['ADDON_PRECHECK_FAILED'] = "Add-On requirements not met";
$HEADING['GENERAL_SETTINGS'] = "General Settings";
$HEADING['INSTALL_LANGUAGE'] = "Install Language";
$HEADING['INSTALL_MODULE'] = "Install Module";
$HEADING['INSTALL_TEMPLATE'] = "Install Template";
$HEADING['INVOKE_LANGUAGE_FILES'] = "Execute language files manually";
$HEADING['INVOKE_MODULE_FILES'] = "Execute module files manually";
$HEADING['INVOKE_TEMPLATE_FILES'] = "Execute template files manually";
$HEADING['addon_DETAILS'] = "Language Details";
$HEADING['MANAGE_SECTIONS'] = "Manage Sections";
$HEADING['MODIFY_ADVANCED_PAGE_SETTINGS'] = "Modify Advanced Page Settings";
$HEADING['MODULE_DETAILS'] = "Module Details";
$HEADING['TEMPLATE_DETAILS'] = "Template Details";
$HEADING['UNINSTALL_LANGUAGE'] = "Uninstall Language";
$HEADING['UNINSTALL_MODULE'] = "Uninstall Module";
$HEADING['UNINSTALL_TEMPLATE'] = "Uninstall Template";
$HEADING['UPGRADE_LANGUAGE'] = "Language register/upgrading";
$HEADING['UPLOAD_FILES'] = "Upload File(s)";

$MENU['ADDON'] = "Add-on";
$MENU['ADDONS'] = "Add-ons";
$MENU['LANGUAGES'] = "Languages";
$MENU['TEMPLATES'] = "Templates";

$MESSAGE['ADDON_ERROR_RELOAD'] = "Error while updating the Add-On information.";
$MESSAGE['ADDON_LANGUAGES_RELOADED'] = "Languages reloaded successfully";
$MESSAGE['ADDON_MANUAL_FTP_LANGUAGE'] = '<strong>ATTENTION!</strong> For security reasons, only transfer language files via FTP into the /languages​​/ folder and use the Upgrade Function to register or update.';
$MESSAGE['ADDON_MANUAL_FTP_WARNING'] = 'Warning: Existing module database entries will be lost. ';
$MESSAGE['ADDON_MANUAL_INSTALLATION'] = 'When modules are uploaded via FTP (not recommended), the module installation functions <code>install</code>, <code>upgrade</code> or <code>uninstall</code> will not be executed automatically. Those modules may not work correctly or uninstall properly.<br /><br />You can execute the module functions manually for modules uploaded via FTP below.';
$MESSAGE['ADDON_MANUAL_INSTALLATION_WARNING'] = 'Warning: Existing module database entries will be lost. Only use this option if you experience problems with modules uploaded via FTP.';
$MESSAGE['ADDON_MANUAL_RELOAD_WARNING'] = 'Warning: Existing module database entries will be lost. ';
$MESSAGE['ADDON_MODULES_RELOADED'] = 'Modules reloaded successfully';
$MESSAGE['ADDON_OVERWRITE_NEWER_FILES'] = 'Overwrite newer Files';
$MESSAGE['ADDON_PRECHECK_FAILED'] = 'Add-on installation failed. Your system does not meet the requirements of this Add-on. To make this Add-on work on your system, please fix the issues summarized below.';
$MESSAGE['ADDON_RELOAD'] = 'Update database with information from Add-on files (e.g. after FTP upload).';
$MESSAGE['ADDON_TEMPLATES_RELOADED'] = 'Templates reloaded successfully';
$MESSAGE['GENERIC_ALREADY_INSTALLED'] = "%s [%s] %s already installed";
$MESSAGE['GENERIC_CANNOT_UNINSTALL'] = "Cannot uninstall %s";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE'] = "Cannot Uninstall: the selected file is in use\n";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL'] = "%s %s could not be uninstalled, because it is still in use on %s.\n";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES'] = "this page;these pages";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE'] = "Can't uninstall the template %s, because it is the default template!";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_THEME'] = "Can't uninstall the template %s, because it is the default backend theme!";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_LANGUAGE'] = "Can't uninstall the Language %s, because it is the default language!";
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_LANG_USERS'] = 'this user;these users';
$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_LANG'] = "%s %s can not uninstall,\n because it is in use by %s:\n";
$MESSAGE['LANG_INUSE_LINK'] = '%s}'."\n";
$MESSAGE['GENERIC_CANNOT_UNZIP'] = "Cannot unzip file";
$MESSAGE['GENERIC_CANNOT_UPLOAD'] = "Cannot upload file";
$MESSAGE['GENERIC_COMPARE'] = " successful";
$MESSAGE['GENERIC_ERROR_OPENING_FILE'] = "Error opening file.";
$MESSAGE['GENERIC_FAILED_COMPARE'] = " failed";
$MESSAGE['GENERIC_FILE_TYPE'] = "Please note that the file you upload must be of the following format:";
$MESSAGE['GENERIC_FILE_TYPES'] = "Please note that the file you upload must be in one of the following formats:";
$MESSAGE['GENERIC_FILL_IN_ALL'] = "Please go back and fill-in all fields";
$MESSAGE['GENERIC_FORGOT_OPTIONS'] = "You have not made a selection!";
$MESSAGE['GENERIC_INSTALLED'] = "%s [%s] %s installed successfully";
$MESSAGE['GENERIC_INVALID'] = "The file you uploaded is invalid";
$MESSAGE['GENERIC_INVALID_ADDON_FILE_ZIP'] = "Invalid WebsiteBaker installation file. Please check the *.zip format.";
$MESSAGE['GENERIC_INVALID_ADDON_FILE'] = "Invalid WebsiteBaker installation file. Please check the %s</b format/structure.";
$MESSAGE['GENERIC_INVALID_LANGUAGE_FILE'] = "Invalid WebsiteBaker language file. Please check the text file.>";
$MESSAGE['GENERIC_INVALID_MODULE_FILE'] = "Invalid WebsiteBaker module file. Please check the text file.";
$MESSAGE['GENERIC_INVALID_PLATFORM'] = 'It is not possible to upgrade or install from a WebsiteBaker Versions before %s';
$MESSAGE['GENERIC_INVALID_TEMPLATE_FILE'] = "Invalid WebsiteBaker template file. Please check the text file.";
$MESSAGE['GENERIC_IN_USE'] = " but used in ";
$MESSAGE['GENERIC_MISSING_ARCHIVE_FILE'] = "Missing Archive file!";
$MESSAGE['GENERIC_MODULE_VERSION_ERROR'] = "The addon %s is not installed properly!";
$MESSAGE['GENERIC_NOT_COMPARE'] = " not possible";
$MESSAGE['GENERIC_NOT_INSTALLED'] = "Not installed";
$MESSAGE['GENERIC_NOT_UPGRADED'] = "Update for %s [%s] %s not possible";
$MESSAGE['PAGE_INUSE_LINK'] = '<a href="%s/pages/%s.php?page_id=%s}">%s</a>'."\n";
$MESSAGE['GENERIC_UNINSTALLED'] = "%s %s uninstalled successfully";
$MESSAGE['GENERIC_UPGRADED'] = "%s [%s] %s upgraded successfully";
$MESSAGE['GENERIC_VERSION_COMPARE'] = "Version comparison";
$MESSAGE['GENERIC_VERSION_GT'] = "Upgrade necessary!";
$MESSAGE['GENERIC_VERSION_LT'] = "Downgrade";
$MESSAGE['GENERIC_WRONG_FORMAT'] = 'Uploaded file has an invalid File Format [%s]!';
$MESSAGE['MEDIA_CANNOT_DELETE_DIR'] = "Cannot delete the selected folder";
$MESSAGE['MEDIA_CANNOT_DELETE_FILE'] = "Cannot delete the selected file";
$MESSAGE['MEDIA_CANNOT_RENAME'] = "Rename unsuccessful";
$MESSAGE['MEDIA_CONFIRM_DELETE'] = "Are you sure you want to delete the following file or folder?";
$MESSAGE['MEDIA_DELETED_DIR'] = "Folder deleted successfully";
$MESSAGE['MEDIA_DELETED_FILE'] = "File deleted successfully";
$MESSAGE['MEDIA_DIR_ACCESS_DENIED'] = "Specified directory does not exist or is not allowed.";
$MESSAGE['MEDIA_DIR_DOES_NOT_EXIST'] = "Directory does not exist";
$MESSAGE['MEDIA_DIR_MADE'] = "Folder created successfully";
$MESSAGE['MEDIA_DIR_NOT_MADE'] = "Unable to create folder";
$MESSAGE['MEDIA_FILE_EXISTS'] = "A file matching the name you entered already exists";
$MESSAGE['MEDIA_FILE_NOT_FOUND'] = "File not found";
$MESSAGE['MEDIA_NO_FILE_UPLOADED'] = "No file was received";
$MESSAGE['MEDIA_UPLOADED'] = " files were successfully uploaded";
$MESSAGE['MOD_FORM_REQUIRED_FIELDS'] = "You must enter details for the following fields";
$MESSAGE['SETTINGS_UNABLE_OPEN_CONFIG'] = "Unable to open the configuration file";
$MESSAGE['THEME_COPY_CURRENT'] = "Copy the current active theme and save it with a new name.";
$MESSAGE['THEME_DESTINATION_READONLY'] = "No rights to create new theme directory!";
$MESSAGE['THEME_IMPORT_HTT'] = "Import additional templates into the current active theme.<br />Use these templates to overwrite the corresponding default template.";
$MESSAGE['THEME_INVALID_SOURCE_DESTINATION'] = "Invalid descriptor for the new theme given!";

$MESSAGE['UNKNOW_UPLOAD_ERROR'] = "Unknown upload error";
$MESSAGE['UPLOAD_ERR_CANT_WRITE'] = "Failed to write file to disk";
$MESSAGE['UPLOAD_ERR_CANT_WRITE_FOLDER'] = "Failed to write file to %s";
$MESSAGE['UPLOAD_ERR_EXTENSION'] = "File upload stopped by extension";
$MESSAGE['UPLOAD_ERR_FORM_SIZE'] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
$MESSAGE['UPLOAD_ERR_INI_SIZE'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini of %s";
$MESSAGE['UPLOAD_ERR_NO_FILE'] = "No file was uploaded";
$MESSAGE['UPLOAD_ERR_NO_TMP_DIR'] = "Missing a temporary folder";
$MESSAGE['UPLOAD_ERR_OK'] = "File was successfully uploaded";
$MESSAGE['UPLOAD_ERR_PARTIAL'] = "The uploaded file was only partially uploaded";
$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] =
'The uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini. This is an error that occurs on your WebsiteBaker installation when you upload a file that exceeds the limitations set by your webserver. Ask your provider to increase the limitations. Your filesize ist %s and  upload_max_filesize %s';
$TEXT['EXECUTE']  = 'Execute %s %s';
$TEXT['EXECUTED'] = 'Executed %s %s';
$TEXT['NOT_EXECUTED'] = "%s %s could not be executed \n %s";
$TEXT['NONE_FOUND'] = 'None Found';
$TEXT['NOT_FOUND'] = '%s not found';
$TEXT['NOT_INSTALLED'] = '%s not installed';

$TEXT['ADMIN'] = 'System Addon';
$TEXT['ADMINISTRATION'] = 'Addon Administration';
$TEXT['LANGUAGE'] = 'Addon Languages';
$TEXT['SCRIPT_NOT_FOUND'] = '%s/%s.php not found';
$TEXT['SCRIPT_NOT_INSTALLED'] = '%s/%s.php not installed';
$TEXT['PAGE'] = 'Page';
$TEXT['TEMPLATE'] = 'Frontend Template';
$TEXT['THEME'] = 'Backend Theme';
$TEXT['TOOL'] = 'System Addon';
$TEXT['UNKNOWN'] = 'Unknown Addon';
$TEXT['WYSIWYG'] = 'WYSIWYG Editor';
$TEXT['SNIPPET'] = 'Code-snippet';

$OVERVIEW['ADDONS'] = 'Back to Extensions';
$TEXT['MODIFY_CONTENT'] = 'Update Extensions';