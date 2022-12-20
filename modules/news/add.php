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
 * @version         $Id: add.php 314 2019-03-28 19:37:36Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/add.php $
 * @lastmodified    $Date: 2019-03-28 20:37:36 +0100 (Do, 28. Mrz 2019) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;
/* -------------------------------------------------------- */
} else {
    $commenting = 'none';
    $use_captcha = true;
    $sOrder = 'DESC';
    $sOrderField = 'published_when';
    $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_settings` SET '
         . '`section_id`='.$database->escapeString($section_id).', '
         . '`page_id`='.$database->escapeString($page_id).', '
         . '`posts_per_page`=5, '
          . '`layout` = \''.$oReg->Db->escapeString('default_layout').'\', '
          . '`layout_id` = 1, '
         . '`commenting`=\''.$database->escapeString($commenting).'\', '
         . '`resize`=0, '
         . '`use_captcha`='.$database->escapeString($use_captcha).', '
         . '`order`=\''.$database->escapeString($sOrder).'\', '
         . '`order_field`=\''.$database->escapeString($sOrderField).'\' '
         . ''.'';
    if (!$database->query($sql)){
        //
    }

}
