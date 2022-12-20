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
 * Description of /admin/templates/index.php
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.11.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

    if ( !defined( 'SYSTEM_RUN' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }
// Include the WB functions file
//    if (!function_exists('get_modul_version')){require(WB_PATH.'/framework/functions.php');}

    // register addon vars
    $sAddonType   =  'template';
    $sAddonAppDir = '/templates/';

    $admin = new admin('Addons', $sAddonType.'s_view', true);

    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\addons');
    $aTrans = $oTrans->getLangArray();

// get request method
    $oRequest = (object) filter_input_array (
                (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? INPUT_POST : INPUT_GET), FILTER_UNSAFE_RAW
    );

try {

    $aDebug = [];
    $aPreventFromUninstall = [
        'DefaultTheme',
        'DefaultTemplate',
//        'ccc',
//        'ddd',
    ];

// needed to set the advanced block
    $show_block = isset($oRequest->advanced)&&(int)$oRequest->advanced;
    $sAddonBackUrl  = WB_URL.'/'.ADMIN_DIRECTORY.'/'.basename(__DIR__).'/index.php'.($show_block ? '?advanced='.$show_block : '');

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source($sAddonType.'s.htt')));
// $template->debug = true;
    $template->set_file ('page', $sAddonType.'s.htt');
    $template->set_block('page', 'main_block', 'main');

/*----------------------- show button in top to change addon type ---------------------------*/

    $template->set_block('main_block', 'addon_module_block', 'addon_module');
    if($admin->get_permission('modules_view') != true) {
        $template->set_block ('addon_module', '');
    } else {
        $template->set_var(array(
            'URL_MODULES' => $admin->get_permission('modules') ? ADMIN_URL . '/modules/index.php' : '&#160;',
            'MENU_MODULES' => $admin->get_permission('modules') ? $MENU['MODULES'] : '&#160;',
            ));
        $template->parse('addon_module', 'addon_module_block', true);
    }

    $template->set_block('main_block', 'addon_language_block', 'addon_language');
    if($admin->get_permission('languages_view') != true) {
        $template->set_block ('addon_language', '');
    } else {
        $template->set_var(array(
            'URL_LANGUAGES'  => $admin->get_permission('languages') ? ADMIN_URL . '/languages/index.php'  : '&#160;',
            'MENU_LANGUAGES' => $admin->get_permission('languages') ? $MENU['LANGUAGES'] : '&#160;',
            ));
        $template->parse('addon_language', 'addon_language_block', true);
    }

    $template->set_block('main_block', 'addon_template_block', 'addon_template');
    $template->parse('addon_template', 'addon_template_block', true);

    $template->set_var(array(
        'URL_ADVANCED' => '&#160;',
        ));

/*------------------------- addon section  ------------------------------------------------*/
// show upload input to install or upgrade an addon as archiv
    $template->set_block('main_block', 'addon_install_block', 'addon_install');
// Insert permissions values and show or hidden blocks
    if($admin->get_permission($sAddonType.'s_install') != true) {
        $template->set_block ('addon_install', '');
    } else {
        $template->set_var('INSTALL_ADVANCED', $show_block);
        $template->parse('addon_install', 'addon_install_block', true);
    }
    //
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
          . 'WHERE `type`=\''.$sAddonType.'\' '
          . 'ORDER BY `name`'
          . '';
    if (!$oAddons = $database->query($sql)) {
        throw new \Exception ($database->get_error());
    }

    $aAddons = $oAddons->fetchAll(MYSQLI_ASSOC);
//
// Insert values into addon list
    $template->set_block('main_block', 'addon_detail_block', 'addon_detail');
    $template->set_block('addon_detail_block', 'addon_detail_select_block', 'addon_detail_select');

    $template->set_block('main_block', 'addon_uninstall_block', 'addon_uninstall');
    $template->set_block('addon_uninstall_block', 'addon_uninstall_select_block', 'addon_uninstall_select');

    foreach ($aAddons as $iIndex=>$aAddon){
        if (!$admin->get_permission( $aAddon['directory'], $sAddonType )) { continue; }
        $sAddonIdKey = \bin\SecureTokens::getIDKEY($aAddon['addon_id']);
        $template->set_var('DETAIL_ADVANCED', $show_block);
        $template->set_var('DETAIL_VALUE', $sAddonIdKey);
        $template->set_var('DETAIL_NAME', $aAddon['name']);
        $template->parse('addon_detail_select', 'addon_detail_select_block', true);
//        $aDebug['details'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
        $sAddsonsPath = WB_PATH.$sAddonAppDir.$aAddon['directory'];
        if (is_readable($sAddsonsPath . '/info.php')) {
            if (!preg_match('/'.$aAddon['directory'].'/si', implode('|', $aPreventFromUninstall))) {
//                $aDebug['uninstall'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
                $template->set_var('UNINSTALL_ADVANCED', $show_block);
                $template->set_var('UNINSTALL_VALUE', $sAddonIdKey);
                $template->set_var('UNINSTALL_NAME', $aAddon['name']);
                $template->parse('addon_uninstall_select', 'addon_uninstall_select_block', true);
            }
        }
    } // foreach

    if ($admin->get_permission($sAddonType.'s_uninstall') != true) {
        $template->set_var('DISPLAY_UNINSTALL', '');
        $template->set_block('addon_uninstall', '');
    } else {
        $template->parse('addon_uninstall', 'addon_uninstall_block', true);
    }

    if($admin->get_permission($sAddonType.'s_view') != true) {
        $template->set_var('DISPLAY_LIST', '');
        $template->set_block('addon_detail', '');
    } else {
        $template->parse('addon_detail', 'addon_detail_block', true);
    }

// insert urls
    $template->set_var(array(
            'ADMIN_URL' => ADMIN_URL,
            'WB_URL' => WB_URL,
            'THEME_URL' => THEME_URL,
        )
    );

// Insert language vars
    $template->set_var($aTrans);

    $template->set_ftan(\bin\SecureTokens::getFTAN());

// Parse template object
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');

}catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
