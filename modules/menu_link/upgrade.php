<?php
/**
 *
 * @category        modules
 * @package         menu_link
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: upgrade.php 290 2019-03-26 16:01:51Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/menu_link/upgrade.php $
 * @lastmodified    $Date: 2019-03-26 17:01:51 +0100 (Di, 26. Mrz 2019) $
 *
 */
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */

    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
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
        $sTable = TABLE_PREFIX.'mod_menu_link';
        $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
        if (!$database->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
            $msg[] = $database->get_error();
        }
/*
        if(($sOldType = $database->getTableEngine($sTable))) {
            if(('innodb' != strtolower($sOldType))) {
                //
                if(!$database->query('ALTER TABLE `'.$sTable.'` Engine = \'MyISAM\' ')) {
                    $msg[] = $database->get_error();
                }
            }
        } else {
            $msg[] = $database->get_error();
        }
*/
/*--------------------------------------------------------------------------------------------------*/
/**
 * There are files and folder which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*--------------------------------------------------------------------------------------------------*/
        $aFilesToDelete = [
            '/themes/default/css/backend.css',
            ];
       PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);

    }
// ------------------------------------