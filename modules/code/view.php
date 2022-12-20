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
 * @version         $Id: view.php 280 2019-03-22 01:03:06Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/view.php $
 * @lastmodified    $Date: 2019-03-22 02:03:06 +0100 (Fr, 22. Mrz 2019) $
 *
*/

declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace dispatch;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
// Get content
    $sql  = 'SELECT `content` FROM `'.TABLE_PREFIX.'mod_code` '
          . 'WHERE `section_id` ='.$section_id;
    if ($content = $database->get_one($sql)) {

        eval($content);
    }

