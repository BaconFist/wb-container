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
 * Description of addon uninstall
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: uninstall.php 73 2018-09-17 17:17:45Z Luisehahne $
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
    if (!\defined( 'SYSTEM_RUN')) {require(\dirname(\dirname((__DIR__))).'/config.php');}
// Include the WB functions file
//    if (!\function_exists('get_modul_version')) {require(WB_PATH.'/framework/functions.php');}

// register addon vars
    $sAddonType            =  'template';
    $sAddonAppDir          = '/templates/';
    $sFileInUseTable       =  'pages';
//    $aAllowedAddons        = ['theme', '', 'template'];
    $aPreventFromUninstall = ['DefaultTemplate', 'DefaultTheme'];

    $admin = new \admin('Addons', $sAddonType.'s_uninstall', true);

    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\addons');
    $aTrans = $oTrans->getLangArray();

// get request method
    $oRequest = (object) filter_input_array (
                (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? INPUT_POST : INPUT_GET), FILTER_UNSAFE_RAW
    );

try {

// Set temp vars
    $show_block = isset($oRequest->advanced)&&(int)$oRequest->advanced;
    $sAddonBackUrl  = WB_URL.'/'.ADMIN_DIRECTORY.'/'.basename(__DIR__).'/index.php'.($show_block?'?advanced='.$show_block:'');

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
        $sAddonDir = preg_replace('/[^a-z0-9_-]/i', "", $sAddonDir);  // fix secunia 2010-92-2
    }

    if (is_readable(WB_PATH.$sAddonAppDir.$sAddonDir.'/info.php')) {
      include WB_PATH.$sAddonAppDir.$sAddonDir.'/info.php';
      $sAddonName = ${$sAddonType.'_name'};
      $sAddonFunction = (isset(${$sAddonType.'_function'}) ? ${$sAddonType.'_function'} : $sAddonFunction);
    }

// Check if the addon exists
    if (!\is_dir(WB_PATH.$sAddonAppDir.$sAddonDir) ) {
        throw new \Exception ($oTrans->MESSAGE_GENERIC_NOT_INSTALLED);
    } else
// Check if we have permissions on the directory
    if (!\is_writable(WB_PATH.$sAddonAppDir.$sAddonDir)) {
        $aTemp = [
            'name' => $sAddonName
        ];
        throw new \Exception (vsprintf($oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL,$aTemp));
    }

    $sAction = 'uninstall';
// check whether the addon is core
    if(
        \preg_match('/'.$sAddonDir.'/si', implode('|', $aPreventFromUninstall ))
    ) {
        $aTemp = [
            'name' => $sAddonDir
        ];
        throw new \Exception (vsprintf($oTrans->MESSAGE_MEDIA_CANNOT_DELETE_DIR,$aTemp));
     }

    if(
        \preg_match('/'.$sAddonDir.'/si', implode('|', [DEFAULT_TEMPLATE, DEFAULT_THEME] ))
    ) {
        $aTemp = ['name' => $sAddonDir];
        $sErrorMessage = vsprintf($oTrans->{'MESSAGE_GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_'.strtoupper($sAddonFunction)},$aTemp);
        throw new \Exception ($sErrorMessage);
     }

    $sql  = 'SELECT `page_id` FROM `'.TABLE_PREFIX.$sFileInUseTable.'` '
          . 'WHERE `'.$sAddonType.'`=\''.$database->escapeString($sAddonDir).'\'';
    if (!($oSearch = $database->query($sql))) {
        throw new \Exception ($database->get_error()."\n".$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    if ($oSearch->numRows() > 0) {
            $aTemp = explode(";",$oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES);
            $add = $oSearch->numRows() == 1 ? $aTemp[0] : $aTemp[1];

        /**
        *    The template-string for displaying the Page-Titles ... in this case as a link
        */
        $aReplace = [
            'type'  => ucfirst($sAddonType),
            'name'  => $sAddonName,
            'pages' => $add
        ];
        $sMessage = vsprintf ($oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL,$aReplace);
        $sPageTitle = '';
        while ($aSearch = $oSearch->fetchRow(MYSQLI_ASSOC) ) {
            $sql  = 'SELECT `page_title` FROM `'.TABLE_PREFIX.'pages` '
                  . 'WHERE `page_id`= '.(int)$aSearch['page_id'];
            $oPage = $database->query($sql);
            $aPage = $oPage->fetchRow( MYSQLI_ASSOC );
            $aPageInfo = [
                'ADMIN_URL' => ADMIN_URL,
                'link'      => 'settings',
                'id'        => $aSearch['page_id'],
                'title'     => $aPage['page_title'].',',
            ];
            $sPageTitle .= vsprintf ($oTrans->MESSAGE_PAGE_INUSE_LINK,$aPageInfo);
        }

        /**
        *    Printing out the error-message and die().
        */
        $sErrorMessage = (str_replace ($oTrans->TEXT_FILE, "Modul", $sMessage).$sPageTitle);
        throw new \Exception ($sErrorMessage);
    }

// Run the modules uninstall script if there is one
    if (file_exists(WB_PATH.$sAddonAppDir.$sAddonDir.'/uninstall.php')) {
        require(WB_PATH.$sAddonAppDir.$sAddonDir.'/uninstall.php');
    }

// Try to delete the module dir $sAddonsFile
    if (!rm_full_dir(WB_PATH.$sAddonAppDir.$sAddonDir)) {
        $aTemp = [
            'name' => $sAddonDir
        ];
        throw new \Exception (vsprintf( $oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL,$aTemp));
    } else {
// Remove entry from DB
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'addons` '
              . 'WHERE `type` = \''.$sAddonType.'\' '
              .   'AND `directory` = \''.$sAddonDir.'\' ';
        if (!$database->query($sql)){
            $aTemp = ['name' => $sAddonDir];
            throw new \Exception ($database->get_error()."\n".vsprintf($oTrans->MESSAGE_GENERIC_CANNOT_UNINSTALL,$aTemp));
        }
    }
    if ($sAddonDir && file_exists(WB_PATH.$sAddonAppDir.$sAddonDir)){}
// Print success message
    $aTemp = [
      'type'   => ucfirst($sAddonType),
      'name'   => $sAddonName,
    ];
    $sMsg = vsprintf($oTrans->MESSAGE_GENERIC_UNINSTALLED,$aTemp);
    $admin->print_success ($sMsg, $sAddonBackUrl);

}catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
