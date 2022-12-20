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
 * Description of admin/login/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 142 2018-10-03 19:03:49Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace Acp\login;


use bin\{WbAdaptor,Login,wb,SecureTokens,Sanitize};


    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = '/'.$sModuleDir.'/'.$sAddonName;
    // \basename(__DIR__).'/'.\basename(__FILE__);
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {require($sAppPath.'config.php');}

    if (!defined('TABLE_PREFIX')){
        if (!function_exists('callInstaller')){
            $sRequestFromInitialize = true;
            require $sAppPath.'framework/functions.php';
        }
        callInstaller();
    }
// ---------------------------------------
    $bLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
    $admin = new \frontend();
    $oReg = WbAdaptor::getInstance();
    $oDb  = $oReg->getDatabase();
    $oApp = $oReg->getApplication();
    $username_fieldname = 'username';
    $password_fieldname = 'password';
    if (\defined('SMART_LOGIN') && SMART_LOGIN == 'true') {
        $sTmp = '_'.\substr(md5(microtime()), -8);
        $username_fieldname .= $sTmp;
        $password_fieldname .= $sTmp;
    }
// ---------------------------------------
if (\defined('FINALIZE_SETUP')) {
    $sql = 'DELETE FROM `'.$oReg->TablePrefix.'settings` WHERE `name`=\'finalize_setup\'';
    if ($oDb->query($sql)) {unset($sql);}
}
// ---------------------------------------
    $aSettings = ['website_title' => 'none','jquery_version'=> ''];
    $sql = 'SELECT * FROM `'.$oReg->TablePrefix.'settings` '
         . 'WHERE `name` IN (\'website_title\',\'jquery_version\') ';
    if ($oSetting = $oDb->query($sql)) {
        while ( $aSetting = $oSetting->fetchAssoc()){
          $aSettings[$aSetting['name']] = $aSetting['value'];
        }
    }

    if ($database->is_error()){
        throw new \DatabaseException($database->get_error());
    }
    $jquery_version = (isset($aSettings['jquery_version']) && !empty(\trim($aSettings['jquery_version'])) ? $aSettings['jquery_version'] : '1.12.4').'/';
// Setup template object, parse vars to it, then parse it
    //$WarnTheme = \str_replace($oReg->AppPath, $oReg->AppUrl, $admin->correct_theme_source('warning.html'));
    $sWarnTheme = (is_readable($oReg->ThemePath.'templates/warning.html') ? 'warning.html' : 'warning.html.php');
    $WarnUrl = $admin->correct_theme_source($sWarnTheme);
    $LoginTpl = 'login.htt';
    $ThemePath = ($admin->correct_theme_source($LoginTpl));  // \dirname
    $aConfigLogin = [
            'MAX_ATTEMPS'           => 3,
            'WARNING_URL'           => $WarnUrl,
            'FORCE_ATTEMPS'         => $bLocalDebug,
            'USERNAME_FIELDNAME'    => $username_fieldname,
            'PASSWORD_FIELDNAME'    => $password_fieldname,
            'REMEMBER_ME_OPTION'    => SMART_LOGIN,
            'MIN_USERNAME_LEN'      => 2,
            'MIN_PASSWORD_LEN'      => 3,
            'MAX_USERNAME_LEN'      => 100,
            'MAX_PASSWORD_LEN'      => 100,
            'WB_URL'                => $oReg->AppUrl,
            'ADMIN_URL'             => $oReg->AcpUrl,
            'THEME_URL'             => $oReg->ThemeUrl,
            'HELPER_URL'            => $oReg->AppUrl.'framework/helpers',
            'JQUERY_VERSION'        => $jquery_version,
            'LOGIN_URL'             => $oReg->AcpUrl."login/index.php",
            'DEFAULT_URL'           => $oReg->AcpUrl."start/index.php",
            'REDIRECT_URL'          => $oReg->AcpUrl."start/index.php",
            'TEMPLATE_DIR'          => $ThemePath,
            'TEMPLATE_FILE'         => $LoginTpl,
            'FRONTEND'              => FALSE,
            'FORGOTTEN_DETAILS_APP' => $oReg->AcpUrl."login/forgot/index.php",
            'USERS_TABLE'           => $oReg->TablePrefix."users",
            'GROUPS_TABLE'          => $oReg->TablePrefix."groups",
        ];

    $thisApp = new Login($aConfigLogin);
