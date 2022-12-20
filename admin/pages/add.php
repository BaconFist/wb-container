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
 * Description of admin/pages/add.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: add.php 191 2019-01-29 17:14:41Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use addon\WBLingual\Lingual;

//  Create new admin object and print admin header

    if (!\defined('SYSTEM_RUN')) {require( \dirname(\dirname((__DIR__))).'/config.php');}
//  suppress to print the header, so no new FTAN will be set
    $admin = new \admin('Pages', 'pages_add', false);

    if (!\bin\SecureTokens::checkFTAN ()) {
         $admin->print_header();
        $admin->print_error(sprintf('[%03d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],__LINE__), ADMIN_URL );
    }

//  Include the WB functions file
//    if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}
//  testmodus without adding/creating page - TODO Debug Info Popup
    $bTestModus = false;
//  Work-out if we should check for existing page_code
    $sMultiLingualPath = Lingual::getLingualRel();
    $bIsMultilingual   = \file_exists(WB_PATH.$sMultiLingualPath);
// Get values
    $title  = $admin->StripCodeFromText($admin->get_post('title'));
//    $title  = \htmlspecialchars($title);
    $module = \preg_replace('/[^a-z0-9_-]/i', "", $admin->get_post('type')); // fix secunia 2010-93-4
    $parent = (int)$admin->get_post('parent'); // fix secunia 2010-91-2
    $visibility = $admin->StripCodeFromText($admin->get_post('visibility'));
    if (!\in_array($visibility, array('public', 'private', 'registered', 'hidden', 'none'))) {$visibility = 'public';} // fix secunia 2010-91-2
    $admin_groups   = $admin->get_post('admin_groups');
    $viewing_groups = $admin->get_post('viewing_groups');

//  add Admin to admin and viewing-groups
    $admin_groups[] = 1;
    $viewing_groups[] = 1;

//  After check print the header
    $admin->print_header();
//  check parent page permissions:
    if ($parent != 0) {
        if (!$admin->get_page_permission($parent,'admin')){
            $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
            $admin->print_error($sErrorMsg, ADMIN_URL);
        }

    } elseif (!$admin->get_permission('pages_add_l0','system'))
    {
        $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }

//  check module permissions:
    if (!$admin->get_permission($module, 'module')){
        $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }

//  Validate data
    if($title == '' || \substr($title,0,1)=='.'){
        $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_BLANK_PAGE_TITLE']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }

//  Check to see if page created has needed permissions
    if (!\in_array(1, $admin->get_groups_id())){
        $admin_perm_ok = false;
        foreach ($admin_groups as $adm_group){
            if (in_array($adm_group, $admin->get_groups_id())){
                $admin_perm_ok = true;
            }
        }
        if ($admin_perm_ok == false){
            $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
            $admin->print_error($sErrorMsg, ADMIN_URL);
        }
        $admin_perm_ok = false;
        foreach ($viewing_groups as $view_group){
            if (\in_array($view_group, $admin->get_groups_id()))
            {
                $admin_perm_ok = true;
            }
        }
        if ($admin_perm_ok == false){
            $sErrorMsg = \sprintf('[%03d] %s %s',__LINE__,basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
            $admin->print_error($sErrorMsg, ADMIN_URL);
        }
    }

    $admin_groups   = \implode(',', $admin_groups);
    $viewing_groups = \implode(',', $viewing_groups);
    $bPageStyle     = (\defined('PAGE_NEWSTYLE') ? constant("PAGE_NEWSTYLE") : true);

//  Work-out what the link and page filename should be  $bPageStyle
    if ($parent == '0'){
//        $link = '/'.page_filename($title);
        $link = '/'.PreCheck::sanitizeFilename($title);
        //  rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php
        if($link == '/index' || $link == '/intro'){
            $link .= '_0';
//            $filename = WB_PATH .PAGES_DIRECTORY .'/' .page_filename($title) .'_0' .PAGE_EXTENSION;
            $filename = WB_PATH .PAGES_DIRECTORY .'/'.PreCheck::sanitizeFilename($title).'_0';
        } else {
//            $filename = WB_PATH.PAGES_DIRECTORY.'/'.page_filename($title).PAGE_EXTENSION;
            $filename = WB_PATH .PAGES_DIRECTORY .'/'.PreCheck::sanitizeFilename($title).PAGE_EXTENSION;
        }
    } else {
        $parent_section = '';
        $parent_links = \array_reverse(get_parent_links($parent));
        $parent_section = implode('/',$parent_links).'/';

        if($parent_section == '/') { $parent_section = ''; }
//        $link = '/'.$parent_section.page_filename($title);
        $link = '/'.$parent_section.PreCheck::sanitizeFilename($title);
//        $filename = WB_PATH.PAGES_DIRECTORY.'/'.$parent_section.page_filename($title).PAGE_EXTENSION;
        $filename = WB_PATH.PAGES_DIRECTORY.'/'.$parent_section.PreCheck::sanitizeFilename($title).PAGE_EXTENSION;
        if (!$bTestModus) {
            make_dir(WB_PATH.PAGES_DIRECTORY.'/'.$parent_section);
        } //  end $bTestModus
    }

//  Check if a page with same page filename exists
    $sql = 'SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` '
         . 'WHERE `link`=\''.$link.'\'';
    if (
        ($get_same_page = $database->get_one($sql)) ||
        \file_exists(WB_PATH.PAGES_DIRECTORY.$link.PAGE_EXTENSION) ||
        \file_exists(WB_PATH.PAGES_DIRECTORY.$link.'/')
    ) {
        $admin->print_error(sprintf('[%03d] '.$MESSAGE['PAGES_PAGE_EXISTS'],__LINE__));
    }

//  Include the ordering class
    $order = new \order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
//  First clean order
    $order->clean($parent);
//  Get new order
    $position = $order->get_new($parent);

//  Work-out if the page parent (if selected) has a seperate template or language to the default
    $sql='SELECT `template`, `language` FROM `'.TABLE_PREFIX.'pages` '
        . 'WHERE `page_id` = '.(int)$parent;
    if ($query_parent = $database->query($sql)){
        if (($query_parent->numRows() > 0) && (!is_null($fetch_parent = $query_parent->fetchRow( MYSQLI_ASSOC )))) {
            $template = $fetch_parent['template'];
            $language = $fetch_parent['language'];
        } else {
            $template = '';
            $language = DEFAULT_LANGUAGE;
        }
    }
//  Insert page into pages table
    $sql = 'INSERT INTO `'.TABLE_PREFIX.'pages` '."\n"
         . 'SET `parent`='.(int)$parent.', '."\n"
//         .     '`link` = \'\', '."\n"
         .     '`link` = \''.$database->escapeString($link).'\', '."\n"
         .     '`description`=\'\', '."\n"
         .     '`keywords`=\'\', '."\n"
         .     '`page_trail`=\'\', '."\n"
         .     '`admin_users`=\'\', '."\n"
         .     '`viewing_users`=\'\', '."\n"
         .     '`target`=\'_top\', '."\n"
         .     '`page_title`=\''.$database->escapeString($title).'\', '."\n"
         .     '`menu_title`=\''.$database->escapeString($title).'\', '."\n"
         .     '`template`=\''.$database->escapeString($template).'\', '."\n"
         .     '`visibility`=\''.$database->escapeString($visibility).'\', '."\n"
         .     '`position`='.(int)$position.', '."\n"
         .     '`menu`=1, '."\n"
         .     '`language`=\''.$database->escapeString($language).'\', '."\n"
         .     '`searching`=1, '."\n"
         .     '`modified_when`='.time().', '."\n"
         .     '`modified_by`='.(int)$admin->getUserId().', '."\n"
         .     '`admin_groups`=\''.$database->escapeString($admin_groups).'\', '."\n"
         .     '`viewing_groups`=\''.$database->escapeString($viewing_groups).'\', '."\n"
         .     '`page_icon` = \'\', '."\n"
         .     '`menu_icon_0` = \'\', '."\n"
         .     '`menu_icon_1` = \'\', '."\n"
         .     '`tooltip` = \'\', '."\n"
         .     '`custom01` = \'\', '."\n"
         .     '`custom02` = \'\', '."\n"
         .     '`page_code` = 0 '."\n"
         .     '';
    if (!$bTestModus) {
        if (!$database->query($sql)) {
            $admin->print_error($database->get_error());
        }
  //  Get the new page id
        $page_id = $database->getLastInsertId();
  //  Work out level
        $level = level_count($page_id);
  //  Work out root parent
        $root_parent = root_parent($page_id);
  //  Work out page trail
        $page_trail = get_page_trail($page_id);
  //  Update page with new level and link
        $sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
              . '`root_parent` = '.(int)$root_parent.', '
              . '`level` = '.(int)$level.', '
              . '`link` = \''.$database->escapeString($link).'\', '
              . ((\defined('PAGE_LANGUAGES') && PAGE_LANGUAGES)
                         && $bIsMultilingual
                         && ($language == DEFAULT_LANGUAGE)
                 ? '`page_code` = '.(int)$page_id.', '
                 : '')
        .      '`page_trail`=\''.$database->escapeString($page_trail).'\' '
              . 'WHERE `page_id` = '.$page_id;
        if (!$database->query($sql)) {
            $admin->print_error($database->get_error());
        }
        $sPagesPath = WB_PATH.PAGES_DIRECTORY;
        $sAccessFileRootPath = \rtrim($sPagesPath,'/').'/';
        if (!is_dir($sAccessFileRootPath)){make_dir($sAccessFileRootPath);}
//    Create a new file in the /pages dir
          create_access_file($filename, $page_id, $level);
  //  add position 1 to new page
          $position = 1;
          $sTitle  = '';
          $publ_start = 0;
          $publ_end = 2147483647;
          // Add new record into the sections table
          $sql = 'INSERT INTO `'.TABLE_PREFIX.'sections` '
               . 'SET `page_id`='.(int)$page_id.', '
               .     '`position`='.(int)$position.', '
               .     '`module`=\''.$database->escapeString($module).'\', '
               .     '`publ_start`='.(int)$publ_start.', '
               .     '`publ_end`='.(int)$publ_end.', '
               .     '`title`=\''.$database->escapeString($sTitle).'\', '
               .     '`anchor`=0, '
               .     '`active`=1, '
               .     '`attribute`=\'\', '
               .     '`block`=1';
          if (!$database->query($sql)) {
              $admin->print_error($database->get_error());
          }
  // Get the section id
          if (!($section_id = $database->getLastInsertId())) {
              $admin->print_error($database->get_error());
          }
// Include the selected modules add file if it exists
          if (
              \file_exists(WB_PATH.'/modules/'.$module.'/addon.php') &&
              (\file_exists(WB_PATH.'/modules/'.$module.'/cmd/cmdModify.inc') ||
              \file_exists(WB_PATH.'/modules/'.$module.'/cmd/Modify.inc') ||
              \file_exists(WB_PATH.'/modules/'.$module.'/cmd/Modify.inc.php'))
              ) {
                  $sCommand = 'modify';
          //    require WB_PATH.'/modules/'.$module.'/addon.php';
                  $admin->print_success($MESSAGE['PAGES_ADDED'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
          } else {
              if (\file_exists(WB_PATH.'/modules/'.$module.'/add.php')) {
                  require WB_PATH.'/modules/'.$module.'/add.php';
              }
              $admin->print_success($MESSAGE['PAGES_ADDED'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
          }
      } //  end $bTestModus
      else {
              $admin->print_success($MESSAGE['PAGES_ADDED'], ADMIN_URL.'/pages/index.php');
      }
// Print admin footer
$admin->print_footer();
