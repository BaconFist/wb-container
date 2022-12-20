<?php

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;

if (!function_exists('getUpgradeTemplate'))
{
    function getUpgradeTemplate ($bDebug=false)
    {
        global $OK ,$FAIL; // needed for upgrade-script
        try {
/* -------------------------------------------------------- */
            $sAddonPath   = str_replace('\\','/',__DIR__).'/';
            $sModulesPath = \dirname($sAddonPath).'/';
            $sModuleName  = basename($sModulesPath);
            $sAddonName   = basename($sAddonPath);
            $ModuleRel    = ''.$sModuleName.'/';
            $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
            $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
            $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
/* -------------------------------------------------------- */
            $sLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
            $sSecureToken = (!is_readable($sAddonPath.'.setToken'));
            $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
            $sqlEOL       = ($sLocalDebug ? "\n" : "");
/* -------------------------------------------------------- */
            $oReg           = WbAdaptor::getInstance();
            $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
            $ModuleUrl      = $oReg->AppUrl.$ModuleRel;
            $sAddonUrl      = $oReg->AppUrl.$sAddonRel;
/* -------------------------------------------------------- */
            $oApp     = $oReg->getApplication();
            $oDb      = $oReg->getDatabase();
            $sDomain  = $oApp->getDirNamespace(__DIR__);
            $oTrans   = $oReg->getTranslate();
            $oTrans->enableAddon($sDomain);
            $aLang    = $oTrans->getLangArray();
            $isAuth   = $oApp->is_authenticated();
            $globalStarted = preg_match('/upgrade\-script\.php$/', $sCallingScript);
/* -------------------------------------------------------- */
            $msg = [];
            $succes = [];
            $aOutputMsg = [];
/* -------------------------------------------------------- */
            $aFilesToDelete = [
                'css/CookieNotice.css',
                'css/content.css',
                'css/demo.css',
                'css/theme.css',
                '/images.ico/',
                '/images.org/',
                'js/CookieNotice.js',
                'js/carhartl-jquery-cookie-92b7715/',
                'old.functions.php',
                ];

            if (\is_writable($sAppPath.'/temp/cache')) {
                \Translate::getInstance()->clearCache();
            }
            PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);
            if ($globalStarted) {$msg[] = $sAddonName.' upgrade successfull finished '.$OK;}

        } catch (\Exception $ex) {
            $sErrMsg = Precheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
            $msg[] = $sErrMsg;
        }
        return $msg;
    }// end of function getUpgradeTemplate
}// exists getUpgradeTemplate
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */
//  set by upgrade-script to surpress echo msg

    $sCallingScript = WbAdaptor::getInstance()->Request->getServerVar('SCRIPT_NAME');
    $globalStarted = \preg_match('/upgrade\-script\.php$/', $sCallingScript);
    $aMsg = getUpgradeTemplate();
    if (!$globalStarted && sizeof($aMsg)) {print implode("\n", $aMsg)."\n";}

