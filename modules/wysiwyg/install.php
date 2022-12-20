<?php
/**
 *
 * @category        modules
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: install.php 302 2019-03-27 10:25:40Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/wysiwyg/install.php $
 * @lastmodified    $Date: 2019-03-27 11:25:40 +0100 (Mi, 27. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $oReg = WbAdaptor::getInstance();
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
        if (\is_readable($sAddonPath.'/install-struct.sql.php')) {
//            $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//            $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
            $database->SqlImport($sAddonPath.'/install-struct.sql.php', TABLE_PREFIX, 'install');
        }
    }