<?php
/**
 *
 * @category        modules
 * @package         code
 * @author          WebsiteBaker Project
 * @copyright       Website Baker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 7.4.28 and higher
 * @version         $Id: modify.php 280 2019-03-22 01:03:06Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/modify.php $
 * @lastmodified    $Date: 2019-03-22 02:03:06 +0100 (Fr, 22. Mrz 2019) $
 *
 */

use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
// check if module language file exists for the language set by the user (e.g. DE, EN)
    $sAddonName = \basename(__DIR__);
    require(WB_PATH .'/modules/'.$sAddonName.'/languages/EN.php');
    if (\file_exists(WB_PATH .'/modules/'.$sAddonName.'/languages/'.LANGUAGE .'.php')) {
    require(WB_PATH .'/modules/'.$sAddonName.'/languages/'.LANGUAGE .'.php');
}
$sModulName = \basename(__DIR__);
if( !$admin->get_permission($sModulName,'module' ) ) {
      die($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES']);
}
require(WB_PATH . '/include/editarea/wb_wrapper_edit_area.php');

// Setup template object
$template = new Template(WB_PATH.'/modules/'.$sAddonName);
$template->set_file('page', 'htt/modify.htt');
$template->set_block('page', 'main_block', 'main');

// Get page content
$query = "SELECT `content` FROM `".TABLE_PREFIX."mod_code` WHERE `section_id` = '$section_id'";
$get_content = $database->query($query);
$content = $get_content->fetchAssoc();
$content = ($content['content'] ?? '');  // \htmlspecialchars

    $aInitEditArea = [
      'id' => 'content'.$section_id,
      'syntax' => 'php',
      'syntax_selection_allow' => false,
      'allow_resize' => true,
      'allow_toggle' => true,
      'start_highlight' => true,
      'toolbar' => 'search, go_to_line, fullscreen, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, |, help',
      'font_size' => '14'
    ];
    $aEditAreaData = [
        'ADDON_NAME'            => $sAddonName,
        'PAGE_ID'               => $page_id,
        'SECTION_ID'            => $section_id,
        'REGISTER_EDIT_AREA'    => (\function_exists('registerEditArea') ? registerEditArea($aInitEditArea) : ''),//'content'.$section_id, 'php', false
        'WB_URL'                => WB_URL,
        'CONTENT'               => $content,
        'TEXT_SAVE'             => $TEXT['SAVE'],
        'TEXT_BACK'             => $TEXT['BACK'],
        'TEXT_CANCEL'           => $TEXT['CANCEL'],
        'SECTION'               => $section_id,
        'FTAN'                  => $admin->getFTAN()
    ];
// Insert vars
$template->set_var($aEditAreaData);

// Parse template object
$template->set_unknowns('keep');
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page', false);
