<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for WebsiteBaker 2.7
 * @copyright       Stepan Riha, WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: install.php 288 2019-03-26 15:14:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/install.php $
 * @lastmodified    $Date: 2019-03-26 16:14:03 +0100 (Di, 26. Mrz 2019) $
 *
*/
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }

    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
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
        if (is_readable($sAddonPath.'/install-struct.sql.php')) {
            $database->SqlImport($sAddonPath.'/install-struct.sql.php', TABLE_PREFIX, 'install' );
            $database->SqlImport($sAddonPath.'/install-data.sql.php', TABLE_PREFIX, 'install' );
        }
    }