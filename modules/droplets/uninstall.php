<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: uninstall.php 283 2019-03-22 01:52:39Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/uninstall.php $
 * @lastmodified    $Date: 2019-03-22 02:52:39 +0100 (Fr, 22. Mrz 2019) $
 *
 */
if (defined('SYSTEM_RUN'))
{
    // delete tables from sql dump file
    if (is_readable(__DIR__.'/install-struct.sql.php')) {
        $database->SqlImport(__DIR__.'/install-struct.sql.php', TABLE_PREFIX, 'uninstall' );
    }
}
