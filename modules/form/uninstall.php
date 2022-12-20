<?php
/**
 *
 * @category        modules
 * @package         form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: uninstall.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/uninstall.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @description
 */
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 not found'; \flush(); exit;}
    // delete tables from sql dump file
    if (\is_readable(__DIR__.'/install-struct.sql.php')) {
        $database->SqlImport(__DIR__.'/install-struct.sql.php', TABLE_PREFIX, 'uninstall' );
    }
