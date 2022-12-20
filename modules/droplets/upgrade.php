<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 7.4.x and higher
 * @version         $Id: upgrade.php 320 2019-04-01 17:42:13Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/upgrade.php $
 * @lastmodified    $Date: 2019-04-01 19:42:13 +0200 (Mo, 01. Apr 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {
    \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');  echo '404 Not Found'; \flush(); exit;
} else {
/* -------------------------------------------------------- */
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__).'/';
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
        if (!\function_exists('insertDropletFile')) {require('droplets.functions.php');}
        $sTableName = TABLE_PREFIX.'mod_droplets';
        if ($database->index_exists($sTableName,'droplet_name')){$database->index_remove($sTableName,'droplet_name');}
        // create tables from sql dump file
        if (\is_readable(__DIR__.'/install-struct.sql.php')) {
            if (!$database->SqlImport(__DIR__.'/install-struct.sql.php', TABLE_PREFIX, 'upgrade' )){
                echo $msg[] = $database->get_error();
            } else {
            }
            $sBaseDir = \realpath(dirname(__FILE__).'/example/');
            $sBaseDir = \rtrim(\str_replace('\\', '/', $sBaseDir), '/').'/';
            $aDropletFiles = getDropletFromFiles($sBaseDir);
            $aDefault = PreCheck::createTwigEnv($sAddonPath);

// First Install missing Droplet
            $bOverwriteDroplets = false;
            insertDropletFile($aDropletFiles, $database, $admin,$msg,$bOverwriteDroplets);
            $aUpgradeDroplets = ($aDefault['droplet-upgrades'] ?? []);
// Second Upgrade Droplets found in Ini File
            if (!empty($aUpgradeDroplets)){
                $aUpdateIniBlock = [];
                foreach ($aUpgradeDroplets as $sDropletFile => $bOverwriteDroplets){
                    insertDropletFile([$sBaseDir.''.$sDropletFile.'.php'], $database, $admin,$msg,$bOverwriteDroplets);
                    $aUpdateIniBlock['droplet-upgrades'][$sDropletFile] =  'false';
                }

                PreCheck::writeIniFile($aUpdateIniBlock,$sAddonPath.'default.ini');
            }
        }

/*--------------------------------------------------------------------------------------------------*/
/**
 * There are files and folder which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*--------------------------------------------------------------------------------------------------*/
        $aFilesToDelete = [
            '/SimpleCommandDispatcher.inc.php',
            '/commands/',
            '/install-data.sql',
            '/install-struct.sql',
            ];
       PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);
    }
}
