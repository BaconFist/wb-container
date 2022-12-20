<?php
/**
 *
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: delete.php 302 2019-03-27 10:25:40Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/wysiwyg/delete.php $
 * @lastmodified    $Date: 2019-03-27 11:25:40 +0100 (Mi, 27. Mrz 2019) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;
/* -------------------------------------------------------- */
} else {
// Delete record from the database
    $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_wysiwyg` '
          . 'WHERE `section_id` = '.$database->escapeString($section_id);
    $database->query($sql);
}
