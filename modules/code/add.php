<?php
/*
 *
 * @category        modules
 * @package         code
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: add.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/add.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {
  die('Cannot access '.basename(__DIR__).'/'.basename(__FILE__).' directly');
/* -------------------------------------------------------- */
} else {
// Insert an extra row into the database
    $query = 'INSERT INTO `'.TABLE_PREFIX.'mod_code` SET '
           .'`section_id` = \''.$section_id.'\', '
           .'`page_id` = \''.$page_id.'\', '
           .'`content` = \'\' ';

    $database->query($query);
}
