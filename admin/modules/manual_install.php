<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of RequesterInterface
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: manual_install.php 268 2019-03-21 17:14:03Z Luisehahne $
 * @since        File available since 04.11.2017
 * @deprecated   since 2017/11/08
 * @description  xxx
 */

//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

/* -------------------------------------------------------- */
      $sAddonPath   = str_replace('\\','/',__DIR__).'/';
      $sModulesPath = \dirname($sAddonPath).'/';
      $sModuleName  = basename($sModulesPath);
      $sAddonName   = basename($sAddonPath);
      $ModuleRel    = ''.$sModuleName.'/';
      $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
      $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
      $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
      if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/* -------------------------------------------------------- */
      $sLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
      $sSecureToken = (!is_readable($sAddonPath.'.setToken'));
      $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
      $sqlEOL       = ($sLocalDebug ? "\n" : "");
/* -------------------------------------------------------- */
/**
 * check if user has permissions to access this file
 */
    $admin = new \admin('Admintools', 'admintools', true);
/* -------------------------------------------------------- */
    $oReg           = WbAdaptor::getInstance();
    $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
    $ModuleUrl      = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl      = $oReg->AppUrl.$sAddonRel;
/* -------------------------------------------------------- */
    $oApp     = $oReg->getApplication();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $sDomain  = $oApp->getDirNamespace(str_replace($sAddonName,'addons',$sAddonPath));
    $oTrans   = $oReg->getTranslate();
    $oTrans->enableAddon($sDomain);
    $aTrans    = $oTrans->getLangArray();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */
    $aVarnames = $oRequest->getParamNames();
    foreach ($aVarnames as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }

// get request method TODO change to Requester

try {

    $sAddonDir = '';
    $sAddonName = '';
    $show_block = isset($aRequestVars['advanced']) && (int)$aRequestVars['advanced'];
    $sAddonBackUrl  = ADMIN_URL.'/'.basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');

    $aValideActions = array(
        'install',
        'uninstall',
        'upgrade',
    );
// check whether the module is needed in core
    $aPreventFromUninstall = array (
        'captcha_control',
        'jsadmin',
        'menu_link',
        'output_filter',
        'wysiwyg',
        'WBLingual',
    );

    $sAction = $admin->StripCodeFromText($aRequestVars['action']);
    $sAction = (\in_array($sAction, $aValideActions) ? $sAction : 'upgrade');
    if (!\bin\SecureTokens::checkFTAN() ){
        throw new \Exception($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    if ($admin->get_permission('admintools') == false) {
        throw new \Exception($oTrans->MESSAGE_ADMIN_INSUFFICIENT_PRIVELLIGES);
    }

// check if the referer URL exists
    $referer = isset($_SERVER['HTTP_REFERER'])
        ? $_SERVER['HTTP_REFERER']
        : (isset($HTTP_SERVER_VARS['HTTP_REFERER']) ? $HTTP_SERVER_VARS['HTTP_REFERER'] : '');
    $referer = '';
// if referer is set, check if script was invoked from "admin/modules/index.php"
    $required_url = ADMIN_URL . '/modules/index.php';
    if ($referer != '' && (!(strpos($referer, $required_url) !== false || strpos($referer, $required_url) !== false)))
    {
        throw new \Exception($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

/**
 * Manually execute the specified module file (install.php, upgrade.php or uninstall.php)
 */
    if (!isset($aRequestVars['file']) || !$aRequestVars['file']) {
        throw new \Exception($oTrans->MESSAGE_GENERIC_FORGOT_OPTIONS);
    }
/**
 * install get the addon_directory
 * otherwise get the addon_id
 */
        $mixAddonKey = \bin\SecureTokens::checkIDKEY($aRequestVars['file']);
        if ((\is_numeric($mixAddonKey) && ($mixAddonKey == 0)) || (!$mixAddonKey)){
            throw new \Exception($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        }
        if ($sAction != 'install') {
            $iAddonId = $mixAddonKey;
            $sSqlWhere = 'WHERE `addon_id`='.(int)$mixAddonKey;
        } else {
            $sSqlWhere = 'WHERE `directory`= \''.$database->escapeString($mixAddonKey).'\'';
        }
        $sqlAddons = 'SELECT `directory` FROM `'.TABLE_PREFIX.'addons` '
                   . $sSqlWhere.' '
                   . ''.'';
/**
 * select module directory from table addons
 */
    if (!($sValue = $database->get_one($sqlAddons)) && $database->is_error()) {
/**
 * only throw Exception if there is a database error
 */
        throw new \Exception($mixAddonKey."\n".$database->get_error());
    } else {
/**
 * declare  specified module folder, reinstall only possiblr if entry in table addons already exists
 */
        $sAddonDir = \preg_replace('/[^a-z0-9_-]/i', '', $sValue);  // fix secunia 2010-92-2
        $sAddonRelPath = '/modules/'.$sAddonDir;
    }
/**
 * if entry not found try to register module in table addons
 */
    if (!($sValue) && ($sAction==='install')) {
  //      $aTemp = array ('name' => $mixAddonKey );
        $sAddonRelPath = '/modules/'.$mixAddonKey;
/**
 * force upgrade or register module
 */
        if (!load_module(WB_PATH.$sAddonRelPath, true)){
            throw new \Exception(sprintf($oTrans->MESSAGE_GENERIC_MODULE_VERSION_ERROR, $mixAddonKey));
        }
/**
 * set $sAddonDir with value from checkIDKEY
 */
        $sAddonDir = \preg_replace('/[^a-z0-9_-]/i', '', $mixAddonKey);  // fix secunia 2010-92-2
    }
/**
 * $sAddonDir required
 */
    $sAddonDir = (($sAddonDir) ?: basename($sAddonRelPath));

    if (!\is_readable(WB_PATH.$sAddonRelPath.'/info.php')){
//        $aTemp = ['ACTION' => 'info', 'name' => $sAddonDir ];
        throw new \Exception(sprintf($oTrans->TEXT_NOT_FOUND, 'info',$sAddonDir));
    }
    require WB_PATH.$sAddonRelPath.'/info.php';
    $sAddonName = $module_name;

    if(
        $sAction == 'uninstall' &&
       \preg_match('/'.$sAddonsFile.'/si', implode('|', $aPreventFromUninstall ))
    ) {
//        $aTemp = array ('name' => $sAddonDir );
        $sMsg = sprintf($oTrans->MESSAGE_MEDIA_CANNOT_DELETE_DIR, $sAddonDir);
        throw new \InvalidArgumentException($sMsg);
    }

    if (!file_exists( WB_PATH.$sAddonRelPath.'/'.$sAction. '.php')){
//        $aTemp = ['ACTION' => $sAction, 'name' => $sAddonName ];
        throw new \InvalidArgumentException(sprintf($oTrans->TEXT_NOT_FOUND, $sAction,$sAddonName));
    }
/**
 * include modules install|upgrade|uninstall.php script
 */
    if (in_array($sAction, $aValideActions) ) {
        $bLoaded = true;
        require(WB_PATH.$sAddonRelPath . '/' . $sAction . '.php');
        if (!$bLoaded && !empty($sMsg)){
            $sErrorMessage = \sprintf($oTrans->TEXT_NOT_EXECUTED, $sAction,$sAddonName,$sMsg);
            throw new \InvalidArgumentException($sErrorMessage);
        }
    }

/**
 * register or remove module info in database and output the status message/no physical upgrade
 */
    load_module(WB_PATH.$sAddonRelPath);
    $sMsg = sprintf($oTrans->TEXT_EXECUTED, $sAction,$sAddonName);

    $admin->print_success($sMsg, $sAddonBackUrl);

}catch (\Exception $ex) {
    $sAddonBackUrl  = ADMIN_URL.'/'.\basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
    $admin->print_footer();
