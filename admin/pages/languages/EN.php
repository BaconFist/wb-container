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
 * @package      ModuleTranslation
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      4.0.0
 * @revision     $Id: EN.php 375 2019-06-21 14:34:41Z Luisehahne $
 * @since        File available since 18.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

$MESSAGE['FRONTEND_SORRY_NO_ACTIVE_SECTIONS'] = 'The URL (%s) you have accessed has no content';
$MESSAGE['FRONTEND_SORRY_NO_VIEWING_PERMISSIONS'] = 'Sorry, you do not have permission to view this page (%s)';
$MESSAGE['GENERIC_SECURITY_ACCESS'] = 'Security offense!! Access denied!';
$MESSAGE['GENERIC_SECURITY_OFFENSE'] = 'Security offense!! data storing was refused!!';
$MESSAGE['PAGES_ADDED'] = 'Page added successfully';
$MESSAGE['PAGES_ADDED_HEADING'] = 'Page heading added successfully';
$MESSAGE['PAGES_BLANK_MENU_TITLE'] = 'Please enter a menu title';
$MESSAGE['PAGES_BLANK_PAGE_TITLE'] = 'Please enter a page title';
$MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE'] = 'Error creating access file in the /pages directory (insufficient privileges)';
$MESSAGE['PAGES_CANNOT_DELETE_ACCESS_FILE'] = 'Error deleting access file in the /pages directory (insufficient privileges)';
$MESSAGE['PAGES_CANNOT_REORDER'] = 'Error re-ordering page';
$MESSAGE['PAGES_DELETED'] = 'Page deleted successfully';
$MESSAGE['PAGES_MARKED_DELETED'] = 'Page has been marked successfully for deletion';
$MESSAGE['PAGES_DELETE_CONFIRM'] = 'Are you sure you finally want to delete the selected page and its sub-pages';
$MESSAGE['PAGES_MARKED_CONFIRM'] = 'Are you sure you want to mark the selected page and its subpages for deletion ';
$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS'] = 'You do not have permissions to modify this page';
$MESSAGE['PAGES_INTRO_LINK'] = 'Click HERE to modify the intro page';
$MESSAGE['PAGES_INTRO_NOT_WRITABLE'] = 'Cannot write to file /pages/intro.php (insufficient privileges)';
$MESSAGE['PAGES_INTRO_SAVED'] = 'Intro page successfully saved';
$MESSAGE['PAGES_LAST_MODIFIED'] = 'Last modification by';
$MESSAGE['PAGES_NOT_FOUND'] = 'Page not found';
$MESSAGE['PAGES_NOT_SAVED'] = 'Error saving page';
$MESSAGE['PAGES_PAGE_EXISTS'] = 'A page with the same or similar title exists';
$MESSAGE['PAGES_REORDERED'] = 'Page successfully re-ordered';
$MESSAGE['PAGES_RESTORED'] = 'Page successfully restored';
$MESSAGE['PAGES_RETURN_TO_PAGES'] = 'Return to pages';
$MESSAGE['PAGES_SAVED'] = 'Page saved successfully';
$MESSAGE['PAGES_SAVED_SETTINGS'] = 'Page settings successfully saved';
$MESSAGE['PAGES_SECTIONS_PROPERTIES_SAVED'] = 'Section properties successfully saved';
$MESSAGE['PAGES_SECTIONS_ORGA'] = 'Structure';
$MESSAGE['PAGES_SECTIONS_TITLE'] = 'Description';
$MESSAGE['SECTIONS_NO_ACTIVE'] = 'Enable section management in settings';

$HEADING['MODIFY_ADVANCED_PAGE_SETTINGS'] = 'Modify Advanced Page Settings';
$HEADING['MODIFY_INTRO_PAGE'] = 'Modify Intro Page';
$HEADING['MODIFY_PAGE'] = 'Modify Page';
$HEADING['MODIFY_PAGE_SETTINGS'] = 'Modify Page Settings';
$HEADING['MODIFY_DELETE_PAGE'] = 'Modify or Delete Pages';

$TEXT['CHANGE_SETTINGS'] = 'Change Settings';
$TEXT['DELETED'] = 'Deleted';
$TEXT['MODULE'] = 'Module';
$TEXT['MODULE_DELETE'] = 'Delete Module {ModuleName} for Section {ID}';
$TEXT['SECTION_DELETE'] = 'Module %s for Section %d successfully deleted';
$TEXT['SUCCESS'] = 'Success';
$TEXT['SEO_TITLE'] = 'Access File';
$TEXT['PAGE_NEWSTYLE'] = 'File Format';
$TEXT['SEO_NEWSTYLE'] = 'If activated, the access file is created in the new format!';
$TEXT['SEO_OLDSTYLE'] = 'If disabled, the access file will be created in 2.8.x format!';
$TEXT['SEO_NEWSTYLE_FORMAT'] = 'Setting access files Format';
$TEXT['MARKED_DELETED'] = 'Page marked as deleted';
$TEXT['MARKED_NONE'] = 'Deactivate page content';
$TEXT['MARKED_HIDDEN'] = 'Do not display page in menu';
$TEXT['MARK_NONE'] = 'Page content disabled';
$TEXT['MARK_HIDDEN'] = 'Page is not displayed in the menu';
$TEXT['HIDDEN'] = 'Hidden';
$TEXT['NONE'] = 'None';
$TEXT['PRIVATE'] = 'Private';
$TEXT['PUBLIC'] = 'Public';
$TEXT['REGISTERED'] = 'Registered';
$TEXT['MARK_PRIVATE'] = 'Display in menu only for logged in users';
$TEXT['MARK_PUBLIC'] = 'Visible to all';
$TEXT['MARK_REGISTERED'] = 'Access for logged in users only';
$TEXT['MARKED_PRIVATE'] = 'Visible for logged in users in the menu';
$TEXT['MARKED_PUBLIC'] = 'Visible to All';
$TEXT['MARKED_REGISTERED'] = 'Visible for logged in users, otherwise show login form';
$TEXT['ALLOW_ANCHOR'] = ' Anchor DIV Container';
$TEXT['FORMAT_DIV_ANCHOR'] = 'Adds: <div id="Sec17" class="section m_form">';
$TEXT['ADD_CLASS_ATTRIBUTES'] = 'Add your own class attributes';
$TEXT['EXPERT_MODE'] = 'Expert Mode';
$TEXT['EXTENDED_PAGE_OPTIONS'] = 'Expert mode for page %d Section %d Addon %s in Block %s';
$TEXT['CLASS_ATTRIBUTES'] = 'Class Attributes';
$TEXT['ACTIVE_ENABLE'] = 'Activate section';
$TEXT['ACTIVE_DISABLE'] = 'Disable section';
$TEXT['ALLOW_ACTIVE'] = ' Enable or disable section';
$TEXT['FORMAT_DIV_ACTIVE'] = 'Enable or disable section visibility!';

$TEXT['CLASS_PANEL_'] = 'To enable a single Anchor Block section, set the section anchor text: none in Server Settings';
$TEXT['CLASS_PANEL_ACTIVE'] = 'To disable all anchors DIVs around sections, write in WB Options > Advanced Options > Server Settings > Section Anchor Text: none (instead of: Sec or Other)';
$TEXT['CLASS_PANEL_NONE'] = 'Section Anchor DIV block for each section is disabled. '
                          .  'If the checkbox Active: Yes, only activate this section anchor DIV. '
                          .  'If you want to enable all section anchors DIVs, go to WB Options > Advanced Options > Server Settings > Section Anchor Text: Sec (or anything else, except: none)';
$TEXT['MODIFIED_PAGE'] = "%s Page that was modified<hr style=\"margin:0;\"> ";
$TEXT['MODIFIED_PAGES'] = "%s Pages that were modified<hr style=\"margin:0;\"> ";
$TEXT['NO_MODIFIED_PAGES'] = "No last modified %s page(s) found";

