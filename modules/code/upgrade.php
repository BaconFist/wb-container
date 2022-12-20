<?php
/**
 *
 * @category        modules
 * @package         code
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://www.websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: upgrade.php 281 2019-03-22 01:11:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/upgrade.php $
 * @lastmodified    $Date: 2019-03-22 02:11:16 +0100 (Fr, 22. Mrz 2019) $
 *
 */
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $oReg = WbAdaptor::getInstance();
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
        $sTable = TABLE_PREFIX.'mod_code';
        $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
//        $oReg->Db->addReplacement('XTABLE_ENGINE','Engine=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//        $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
        if (!$oReg->Db->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
            $msg[] = $oReg->Db->get_error();
        }
/*  deprecated
        if(($sOldType = $database->getTableEngine($sTable))) {
            if (('innodb' != strtolower($sOldType))) {
                $sqlTable = 'ALTER TABLE `'.$sTable.'`ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
                if (!$oDb->query($sqlTable)) {
                    $msg[] = $database->get_error();
                }
            }
        } else {
            $msg[] = $database->get_error();
        }
*/
//  remove obselete files and folder
            $aRemoveList = [
                '/index.php',
                '/install-data.sql',
                '/install-struct.sql',
            ];
            PreCheck::deleteFiles($sAddonPath,$aRemoveList);

    }
// ------------------------------------