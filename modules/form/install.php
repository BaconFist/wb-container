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
 * @version         $Id: install.php 313 2019-03-28 13:25:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/install.php $
 * @lastmodified    $Date: 2019-03-28 14:25:16 +0100 (Do, 28. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 not found'; \flush(); exit;}

    $oReg = WbAdaptor::getInstance();
    $oDb = $oReg->getDatabase();
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
            $oDb->SqlImport($sAddonPath.'/install-struct.sql.php', TABLE_PREFIX, 'install' );
        }
//        require(WB_PATH.'/framework/functions.php');
        $sTemplateDir  = $oReg->AppPath.'modules/'.$sAddonName.'/templates/';
        $sTemplateName = (($oReg->DefaultTemplate !== 'DefaultTemplate') && !\is_dir($sTemplateDir.$oReg->DefaultTemplate) ? $oReg->DefaultTemplate : 'default');
        $sTemplatePath = $sTemplateDir.$sTemplateName;
        if (!\is_dir($sTemplatePath)){
            if (!make_dir($sTemplatePath)){
                $msg[] = sprintf('couldn\'t create %s','/templates/'.$sTemplateName);
            }
        }
    }

