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
 * @version         $Id: uninstall.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/uninstall.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */
if (\defined('SYSTEM_RUN'))
{
//    require (WB_PATH.'/framework/functions.php');
    // create tables from sql dump file
    if (\is_readable(__DIR__.'/install-struct.sql.php')) {
        $database->SqlImport(__DIR__.'/install-struct.sql.php', TABLE_PREFIX, 'uninstall' );
        rm_full_dir(WB_PATH.PAGES_DIRECTORY.'/posts');
        rm_full_dir(WB_PATH.MEDIA_DIRECTORY.'/.news');
    }
}
