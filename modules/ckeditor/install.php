<?php
/**
 *
 * @category       modules
 * @package        ckeditor
 * @authors        WebsiteBaker Project, Michael Tenschert, Dietrich Roland Pehlke
 * @copyright      WebsiteBaker Org. e.V.
 * @link           http://websitebaker.org/
 * @license        http://www.gnu.org/licenses/gpl.html
 * @platform       WebsiteBaker 2.8.3
 * @requirements   PHP 5.3.6 and higher
 * @version        $Id: install.php 276 2019-03-22 00:06:26Z Luisehahne $
 * @filesource     $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/ckeditor/install.php $
 * @lastmodified   $Date: 2019-03-22 01:06:26 +0100 (Fr, 22. Mrz 2019) $
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
    $sCallingScript = HttpRequester::getInstance()->getServerVar('SCRIPT_NAME');
    $globalStarted   = preg_match('/upgrade\-script\.php$/', $sCallingScript);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sWbRevision     = (($globalStarted && defined('REVISION')) ? REVISION : WB_REVISION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new Exception ($sErrorMsg);
        }
    }
    $aInfo = [];
    $aInfo['ModulVersion'] = PreCheck::getAddonVariable($sAddonName);
    $aInfo['WBversion']    = $sWbVersion;
    $aInfo['WBrevision']   = $sWbRevision;

/*------needed for javascript plugins ------------------------------ */
    $sVersionFile = str_replace('\\','/',__DIR__).'/version.json';
    if (!is_readable($sVersionFile)){}
    $oXmlInfo = json_encode($aInfo, JSON_OBJECT_AS_ARRAY | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    if ((file_put_contents($sVersionFile,$oXmlInfo."\n",LOCK_EX)===false)){}
    unset($oXmlInfo);
