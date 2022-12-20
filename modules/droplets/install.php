<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://www.websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6.x and higher
 * @version         $Id: install.php 283 2019-03-22 01:52:39Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/install.php $
 * @lastmodified    $Date: 2019-03-22 02:52:39 +0100 (Fr, 22. Mrz 2019) $
 *
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(['\\','\\\\','//'], '/', __DIR__).'/';
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {

        // create tables from sql dump file
        if (\is_readable($sAddonPath.'install-struct.sql.php')) {
            $database->SqlImport($sAddonPath.'install-struct.sql.php', TABLE_PREFIX, 'install' );
        }

        if (!\function_exists('insertDropletFile')) { require('droplets.functions.php'); }
        $sBaseDir = \rtrim(\str_replace('\\', '/',\realpath($sAddonPath.'example/')), '/').'/';

        $aDropletFiles = getDropletFromFiles($sBaseDir);
        $bOverwriteDroplets = false;
        insertDropletFile($aDropletFiles, $database, $admin,$msg,$bOverwriteDroplets);
    }
