<?php
/**
 *
 * @category        modules
 * @package         menu_link
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: add.php 290 2019-03-26 16:01:51Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/menu_link/add.php $
 * @lastmodified    $Date: 2019-03-26 17:01:51 +0100 (Di, 26. Mrz 2019) $
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
/* obselete no needed anymor
    // Insert new row into database
    $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_menu_link` SET '
         . '`page_id` = '.(int)$page_id.', '
         . '`section_id` = '(int)$section_id.', '
         . '`target_page_id` = 0, '
         . '`redirect_type`  = \'301\', '
         . '`anchor` = 0, '
         . '`extern` = \'\' ';
    $database->query($sql);
*/
