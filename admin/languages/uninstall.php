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
 * Description of install
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: uninstall.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

// Include config file and admin class file
    if (!defined ('SYSTEM_RUN')) { require( dirname (dirname ((__DIR__))) . '/config.php');}
// Include the WB functions file
//    if (!function_exists( 'get_modul_version')) {require(WB_PATH.'/framework/functions.php');}

// register addon vars
    $sAddonType         =  'language';
    $sAddonAppDir       = '/languages/';
    $sFileInUseTable    =  'users';
    $aAllowedAddons     = [];

$admin = new admin('Addons', $sAddonType.'s_uninstall');


    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\addons');
    $aTrans = $oTrans->getLangArray();

// get request method
    $oRequest = (object) filter_input_array (
                (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? INPUT_POST : INPUT_GET), FILTER_UNSAFE_RAW
    );
try {

// Set temp vars
    $show_block = isset($oRequest->advanced)&&(int)$oRequest->advanced;
    $sAddonBackUrl  = ADMIN_URL.'/'.basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');

    if (!\bin\SecureTokens::checkFTAN ()) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    $sAddonDir   = '';
    $sAddonName  = '';
    $sAddonFunction = $sAddonType;
    $sAppTmpPath = WB_PATH . '/temp/';
// Check if user selected template
    if (!isset($oRequest->file) || !$oRequest->file) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_FORGOT_OPTIONS);
    } else {
        $iAddonId = \bin\SecureTokens::checkIDKEY($oRequest->file);
    }
    if ($iAddonId == 0){
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// Get module directory
    $sqlAddons = 'SELECT `directory` FROM `'.TABLE_PREFIX.'addons` '
               . 'WHERE `addon_id`='.(int)$iAddonId.' '
               . ''.'';
    if ($sAddonDir = $database->get_one($sqlAddons)) {
        // fix secunia 2010-93-2
        if (!preg_match('/^[A-Z]{2}$/', $sAddonDir) && $sAddonDir!='' ) {
//            throw new \Exception ($oTrans->MESSAGE_GENERIC_ERROR_OPENING_FILE);
            $sAddonDir = preg_replace('/[^a-z0-9_-]/i', "", $sAddonDir);
        }
    }

    if (is_readable(WB_PATH.$sAddonAppDir.$sAddonDir.'.php')) {
      include WB_PATH.$sAddonAppDir.$sAddonDir.'.php';
      $sAddonName = ${$sAddonType.'_name'};
      $sAddonFunction = (isset(${$sAddonType.'_function'}) ? ${$sAddonType.'_function'} : $sAddonFunction);
    }

/*
*/
// Check if the language exists
    if (!file_exists(WB_PATH.$sAddonAppDir.$sAddonDir.'.php')) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_NOT_INSTALLED);
    }
// Check if the language is in use
    if(
        preg_match('/'.$sAddonDir.'/si', implode('|', [DEFAULT_LANGUAGE, LANGUAGE] ))
    ) {
        $aTemp = ['name' => $sAddonDir];
        $sErrorMessage = vsprintf($oTrans->{'MESSAGE_GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_'.strtoupper($sAddonType)},$aTemp);
//        throw new \Exception ($sErrorMessage);
     }

    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.$sFileInUseTable.'` '
          . 'WHERE `'.$sAddonType.'`=\''.$database->escapeString($sAddonDir).'\'';
    if (!($oUser = $database->query($sql))) {
        throw new \Exception ($database->get_error()."\n".$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    if ($oUser->numRows()>0) {
        $aUserInfo = [];
        $sPageTitle = '';
        $aTemp = explode(";",$oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL_IN_USE_LANG_USERS);
        $add = $oUser->numRows() == 1 ? $aTemp[0] : $aTemp[1];
        $aReplace = [
            'type' => ucfirst($sAddonType),
            'name' => $sAddonName,
            'users' => $add,
        ];
        $sMessage = vsprintf ($oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL_IN_USE_LANG,$aReplace);
        while ($aUser = $oUser->fetchRow(MYSQLI_ASSOC) ) {
            $aUserInfo[] =  $aUser['display_name'];
        }
        $sUserInfo = implode (',',$aUserInfo);
        $aTmp = [
            'display_name' => $sUserInfo,
        ];
        $sPageTitle .= vsprintf ($oTrans->MESSAGE_LANG_INUSE_LINK,$aTmp);
        /**
        *    Printing out the error-message and die().
        */
        $sErrorMessage = (str_replace ($oTrans->TEXT_FILE, 'Language', $sMessage).$sPageTitle);
        throw new \Exception ($sErrorMessage);
    }

// Try to delete the language code
    if (!unlink(WB_PATH.$sAddonAppDir.$sAddonDir.'.php')) {
        $aTemp = ['name' => $sAddonDir];
        throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL,$aTemp));
    } else {
        // Remove entry from DB
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'addons` '
              . 'WHERE `directory` = \''.$sAddonDir.'\' '
              .   'AND `type` = \'language\'';
        if (!$database->query($sql)) {
            throw new \Exception ($database->get_error()."\n".$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        }
    }

// Print success message
    $aTemp = ['short' => $sAddonDir, 'name' => $sAddonName, 'type' => ucfirst($sAddonType) ];
    $sMsg = vsprintf($oTrans->MESSAGE_GENERIC_UNINSTALLED,$aTemp);
    $admin->print_success ($sMsg, $sAddonBackUrl);

}catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
