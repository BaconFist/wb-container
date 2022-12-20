<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: add_group.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/add_group.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

if (!\defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}
// suppress to print the header, so no new FTAN will be set
$admin_header = false;
// Tells script to update when this page was last updated
$update_when_modified = false;
// show the info banner
//$print_info_banner = true;
// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

if(!$admin->checkFTAN('GET')) {
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL );
}

// After check print the header
$admin->print_header();

// Include the ordering class
require(WB_PATH.'/framework/class.order.php');
// Get new order
$order = new order(TABLE_PREFIX.'mod_news_groups', 'position', 'group_id', 'section_id');
$position = $order->get_new($section_id);

// Insert new row into database

    // Insert new row into database
    $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_groups` SET '
          . '`section_id` = '.$database->escapeString($section_id).', '
          . '`page_id` = '.$database->escapeString($page_id).', '
          . '`position` = '.$database->escapeString($position).', '
          . '`active` = 1, '
          . '`title` = \'\' ';

$database->query($sql);

// Get the id
$group_id = $admin->getIDKEY(intval($database->getLastInsertId()));

// Say that a new record has been added, then redirect to modify page
if($database->is_error()) {
   $admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id );
} else {
   $admin->print_success($TEXT['SUCCESS'], WB_URL.'/modules/'.basename(__DIR__).'/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
}

// Print admin footer
$admin->print_footer();
