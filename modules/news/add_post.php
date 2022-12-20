<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: add_post.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/add_post.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

if (!\defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}
$sAddonName = basename(__DIR__);
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
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// After check print the header
$admin->print_header();

// Include the ordering class
require(WB_PATH.'/framework/class.order.php');
// Get new order
$order = new order(TABLE_PREFIX.'mod_news_posts', 'position', 'post_id', 'section_id');
$position = $order->get_new($section_id);

// Get default commenting
$sql = 'SELECT `commenting` FROM `'.TABLE_PREFIX.'mod_news_settings` '
     . 'WHERE `section_id`='.(int)$section_id;
$commenting = $database->get_one($sql);
$now = time();
$sUrl = WB_URL.'/modules/'.$sAddonName.'/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id=';
$sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_posts` SET '
      . '`section_id`='.$database->escapeString($section_id).', '
      . '`page_id`='.$database->escapeString($page_id).', '
      . '`position`='.$database->escapeString($position).', '
      . '`active`=1, '
      . '`title`=\'\', '
      . '`link`=\'\', '
      . '`content_short`=\'\', '
      . '`content_long`=\'\', '
      . '`commenting`=\''.$database->escapeString($commenting).'\', '
      . '`created_when`='.$now.', '
      . '`created_by`='.$admin->getUserId().', '
      . '`published_when` ='.$now.', '
      . '`published_until` =0, '
      . '`modified_when`='.$now.', '
      . '`modified_by`='.(int)$admin->getUserId().', '
      . '`posted_when` ='.$now.', '
      . '`posted_by` ='.$admin->getUserId().'';

if (($database->query($sql))) {
    $post_id = $admin->getIDKEY($database->getLastInsertId());
    $admin->print_success($TEXT['SUCCESS'], $sUrl.$post_id);
} else {
    $post_id = $admin->getIDKEY(0);
    $admin->print_error($database->get_error(), $sUrl.$post_id);
}
$admin->print_footer();
