<?php
/**
 *
 * @category        modules
 * @package         code
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: delete.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/delete.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 *
*/
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {
  die('Cannot access '.basename(__DIR__).'/'.basename(__FILE__).' directly');
/* -------------------------------------------------------- */
} else {
// Delete record from the database
    $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_code` '
          . 'WHERE `section_id` = '.$database->escapeString($section_id);
    $database->query($sql);
}
