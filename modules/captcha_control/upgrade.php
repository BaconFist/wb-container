<?php
/**
 *
 * @category        modules
 * @package
 * @subpackage
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: upgrade.php 257 2019-03-17 20:00:55Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/captcha_control/upgrade.php $
 * @lastmodified    $Date: 2019-03-17 21:00:55 +0100 (So, 17. Mrz 2019) $
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

if (!function_exists('mod_captcha_control_upgrade')){
    function mod_captcha_control_upgrade($bDebug=false) {
        global $OK ,$FAIL;
        $oReg = WbAdaptor::getInstance();
        $oDb  = \database::getInstance();
        $msg  = [];
        $sErrorMsg = null;
/*
        $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
        $sAddonName = \basename($sAddonPath);
        $sModuleName = basename(__DIR__);
*/
/* -------------------------------------------------------- */
        $sAddonPath   = str_replace('\\','/',(__DIR__)).'/';
        $sModulesPath = \dirname($sAddonPath).'/';
        $sModuleName  = basename($sModulesPath);
        $sAddonName   = basename($sAddonPath);
        $ModuleRel    = ''.$sModuleName.'/';
        $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
        $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
        $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
        $sAddonCaptchaPath = $oReg->AppPath.'include/captcha/';

// check if upgrade startet by upgrade-script to echo a message
        $globalStarted = \preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
        $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
        $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
        if (version_compare($sWbVersion, $sModulePlatform, '<')){
            $msg[] = $sErrorMsg = \sprintf('It is not possible to install/upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
            if ($globalStarted){
                echo $sErrorMsg;
            }else{
                throw new \Exception ($sErrorMsg);
            }
        } else {

            $sTable = TABLE_PREFIX.'mod_captcha_control';
            $sInstallStruct = __DIR__.'/install-struct.sql.php';
//  try to create table if not exists
//            $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//            $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
            if (!$oDb->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade')){
                $msg[] = sprintf('[%05d] %s',$oDb->get_errno(),$oDb->get_error());
            }
//  only if no data row found
            $sInstallDataFile   = __DIR__.'/install-data.sql.php';
            if ( ($oDb->get_one('SELECT COUNT(*) FROM `'.$sTable.'`')==0)){
                if (!$oDb->SqlImport($sInstallDataFile, TABLE_PREFIX, 'upgrade')){
                    $msg[] = sprintf('[%05d] %s',$oDb->get_errno(),$oDb->get_error());
                } else {
                    $msg[] = sprintf("[%05d] %s\n%s",$oDb->get_errno(),'Data inserted',$sInstallDataFile);
                }
            }
/* deprecated
            if (($sOldType = $oDb->getTableEngine($sTable))) {
                if (('innodb' != strtolower($sOldType))) {
                    $sqlTable = 'ALTER TABLE `'.$sTable.'`ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
                    if (!$oDb->query($sqlTable)) {
                        $msg[] = sprintf('[%05d] %s',$oDb->get_errno(),$oDb->get_error());
                    }
                }
            } elseif ($oDb->get_errno()>0) {
                $msg[] = sprintf('[%05d] %s',$oDb->get_errno(),$oDb->get_error());
            }
*/
//  remove obselete files and folder
            $aRemoveList = [
                '/themes/default/css/backend.css',
                '/templates/default/css/3/',
                '/templates/default/css/4/',
                '/themes/default/css/3/',
                '/themes/default/css/4/',
                '/install-data.sql',
                '/install-struct.sql',
            ];
            PreCheck::deleteFiles($sAddonPath,$aRemoveList);
// remove obselete files in include/captcha
            $aRemoveList = [
                '/captchas/calc_image.php',
                '/captchas/calc_text.php',
                '/captchas/calc_ttf_image.php',
                '/captchas/ttf_image.php',
                '/captchas/old_image.php',
            ];
            PreCheck::deleteFiles($sAddonCaptchaPath,$aRemoveList);

            if (count($msg)==0){
            $msg[] = $sAddonName.' upgrade successfull finished '.$OK;
            }
        }
        return ($globalStarted ?: $msg);
    }
}
// ------------------------------------

    $bDebugModus = ((isset($bDebugModus)) ? $bDebugModus : false);
    if (is_array($msg = mod_captcha_control_upgrade($bDebugModus))) {
        if (!$bDebugModus){
            echo '<b>'.implode('<br />',$msg).'</b><br />';
        }
    }
// ------------------------------------
