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
 * @version         $Id: delete.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/delete.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
if (!function_exists('mod_news_delete')) {

    function mod_news_delete($database, $page_id, $section_id)
    {
        //get and remove all php files created for the news section
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
              . 'WHERE `section_id` = '.$database->escapeString($section_id);
        $oPosts = $database->query($sql);
        if($oPosts->numRows() > 0) {
            while($aPost = $oPosts->fetchRow(MYSQLI_ASSOC)) {
                if(is_writable(WB_PATH.PAGES_DIRECTORY.$aPost['link'].PAGE_EXTENSION)) {
                unlink(WB_PATH.PAGES_DIRECTORY.$aPost['link'].PAGE_EXTENSION);
                }
            }
        }

        //check to see if any other sections are part of the news page, if only 1 news is there delete it
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'sections` '
              . 'WHERE `page_id` = '.$database->escapeString($page_id);
        $oSection = $database->query($sql);
        if($oSection->numRows() == 1) {
            $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
                  . 'WHERE `page_id` = '.$database->escapeString($page_id);
            $oPages = $database->query($sql);
            $link = $oPages->fetchRow(MYSQLI_ASSOC);
            if(is_writable(WB_PATH.PAGES_DIRECTORY.$link['link'].PAGE_EXTENSION)) {
                unlink(WB_PATH.PAGES_DIRECTORY.$link['link'].PAGE_EXTENSION);
            }
        }

        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_groups` '
              . 'WHERE `section_id` = '.$database->escapeString($section_id);
        $database->query($sql);
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_posts` '
              . 'WHERE `section_id` = '.$database->escapeString($section_id);
        $database->query($sql);
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_comments` '
              . 'WHERE `section_id` = '.$database->escapeString($section_id);
        $database->query($sql);
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_settings` '
              . 'WHERE `section_id` = '.$database->escapeString($section_id);
        $database->query($sql);
    }
}

if( !function_exists('mod_news_delete') ){ mod_news_delete($database, $page_id, $section_id );}

