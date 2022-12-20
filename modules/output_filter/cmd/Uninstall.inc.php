<?php
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
?><?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @author          Christian Sommer, WB-Project, Werner v.d. Decken
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: cmdUninstall.inc 93 2018-09-20 18:09:30Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/output_filter/cmd/cmdUninstall.inc $
 * @lastmodified    $Date: 2018-09-20 20:09:30 +0200 (Do, 20 Sep 2018) $
 *
 */
if (\defined('SYSTEM_RUN'))
{
    // create tables from sql dump file
    if (\is_readable( $sAddonPath.'/install-struct.sql.php')) {
        $database->SqlImport( $sAddonPath.'/install-struct.sql.php', TABLE_PREFIX, 'install' );
    }
}
