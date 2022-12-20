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
 * @version         $Id: uninstall.php 280 2019-03-22 01:03:06Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/uninstall.php $
 * @lastmodified    $Date: 2019-03-22 02:03:06 +0100 (Fr, 22. Mrz 2019) $
 *
 */
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
    // delete tables from sql dump file
    if (\is_readable(__DIR__.'/install-struct.sql.php')) {
        $database->SqlImport(__DIR__.'/install-struct.sql.php', TABLE_PREFIX, 'uninstall' );
    }

