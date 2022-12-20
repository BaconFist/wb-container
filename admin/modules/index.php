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
 * Description of /admin/modules/index.php
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
    $sAddonType   =  'module';
    $sAddonAppDir = '/modules/';

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
        'captcha_control',
        'jsadmin',
        'menu_link',
        'output_filter',
        'show_menu2',
        'wysiwyg',
    ];

// needed to set the advanced block
    $show_block = isset($oRequest->advanced) && (int)$oRequest->advanced;
    $sAddonBackUrl  = ADMIN_URL.'/'.basename(__DIR__).'/index.php'.($show_block ? '?advanced='.$show_block:'');

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source($sAddonType.'s.htt')));
// $template->debug = true;
    $template->set_file ('page', $sAddonType.'s.htt');
    $template->set_block('page', 'main_block', 'main');

/*----------------------- show button in top to change addon type ---------------------------*/

    $template->set_block('main_block', 'addon_template_block', 'addon_template');
    if($admin->get_permission('templates_view') != true) {
        $template->set_block ('addon_template', '');
    } else {
        $template->set_var(array(
            'URL_TEMPLATES'  => $admin->get_permission('templates') ? ADMIN_URL . '/templates/index.php' : '&#160;',
            'MENU_TEMPLATES' => $admin->get_permission('templates') ? $MENU['TEMPLATES'] : '#&#160;',
            ));
        $template->parse('addon_template', 'addon_template_block', true);
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
    $template->set_block('main_block', 'addon_module_block', 'addon_module');
    $template->parse('addon_module', 'addon_module_block', true);


    $template->set_var(array(
        'URL_ADVANCED'   => $admin->get_permission('admintools') ? ADMIN_URL . '/modules/index.php?advanced=1' : '#',
        'TEXT_ADVANCED'  => $admin->get_permission('admintools') ? $TEXT['ADVANCED'] : '&#160;',
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
    $template->set_block('main_block', 'addon_uninstall_block', 'addon_uninstall');
    $template->set_block('addon_uninstall_block', 'addon_uninstall_select_block', 'addon_uninstall_select');
    $template->set_block('main_block', 'addon_detail_block', 'addon_detail');
    $template->set_block('addon_detail_block', 'addon_detail_select_block', 'addon_detail_select');
    foreach ($aAddons as $iIndex=>$aAddon){
        if (!$admin->get_permission( $aAddon['directory'], $sAddonType )) { continue; }
        $sAddonIdKey = \bin\SecureTokens::getIDKEY($aAddon['addon_id']);
        $template->set_var('DETAIL_ADVANCED', $show_block);
        $template->set_var('DETAIL_VALUE', $sAddonIdKey);
        $template->set_var('DETAIL_NAME', $aAddon['name']);
        $template->parse('addon_detail_select', 'addon_detail_select_block', true);
//        $aDebug['details'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
        $sAddsonsPath = WB_PATH.'/modules/'.$aAddon['directory'];
        if (is_readable($sAddsonsPath.'/uninstall.php') && is_readable($sAddsonsPath . '/info.php')) {
            if (!preg_match('/'.$aAddon['directory'].'/si', implode('|', $aPreventFromUninstall))) {
//                $aDebug['uninstall'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
                $template->set_var('UNINSTALL_ADVANCED', $show_block);
                $template->set_var('UNINSTALL_VALUE', $sAddonIdKey);
                $template->set_var('UNINSTALL_NAME', $aAddon['name']);
                $template->parse('addon_uninstall_select', 'addon_uninstall_select_block', true);
            }
        }
    } // foreach

    if($admin->get_permission($sAddonType.'s_view') != true) {
        $template->set_block('addon_detail', '');
    } else {
        $template->parse('addon_detail', 'addon_detail_block', true);
    }
    if($admin->get_permission($sAddonType.'s_uninstall') != true) {
        $template->set_var('DISPLAY_UNINSTALL', '');
        $template->set_block('addon_uninstall', '');
    } else {
        $template->parse('addon_uninstall', 'addon_uninstall_block', true);
    }

/*-------------------------  manuell section ------------------------------------------------*/

    $template->set_block('main_block', 'addon_advanced_block', 'addon_advanced');
if (isset($oRequest->advanced )){
    $aAddonFiles = glob(WB_PATH.'/modules/*', GLOB_ONLYDIR|GLOB_NOSORT );
    natcasesort($aAddonFiles);
    $template->set_block('addon_advanced_block', 'manuell_install_block', 'manuell_install');
// Insert modules which includes a install.php file to install list
    $template->set_block('manuell_install_block', 'manuell_install_select_block', 'manuell_install_select');
    $iInstall   = 0;
    foreach ($aAddonFiles as $iIndex => $sAddsonsPath)
    {
        $sAddonName = basename($sAddsonsPath);
        if( !$admin->get_permission( $sAddonName, 'module' )) { continue; }
        if (is_dir($sAddsonsPath)) {
            $action = 'uninstall';
            $action = (is_readable($sAddsonsPath.'/install.php')?'install':$action);
            $action = (is_readable($sAddsonsPath.'/upgrade.php')?'install':$action);
            if (($action!='uninstall') && is_readable($sAddsonsPath . '/info.php')) {
                require $sAddsonsPath.'/info.php';
                $sAddonIdKey = \bin\SecureTokens::getIDKEY($sAddonName);
                $aDebug['manuell_install'][$sAddonIdKey] = ' ['.$sAddonName.']';
                $template->set_var('ADVANCED', $show_block);
                $template->set_var('INSTALL_VISIBLE', '');
                $template->set_var('INSTALL_FILES',  sprintf('%1d Files found',$iInstall++));
                $template->set_var('INSTALL_VALUE', $sAddonIdKey);
                $template->set_var('INSTALL_NAME', ($module_name ?: $sAddonName) );
                $template->parse('manuell_install_select', 'manuell_install_select_block', true);
            }
        } else {
            unset($aAddonFiles);
        }
    }// end foreach
// Insert permissions values and show or hidden blocks
    if($admin->get_permission($sAddonType.'s_install') != true) {
        $template->set_block ('manuell_install', '');
    } else {
        $template->parse('manuell_install', 'manuell_install_block', true);
    }

    $template->set_block('addon_advanced_block', 'manuell_upgrade_block', 'manuell_upgrade');
    $template->set_block('manuell_upgrade_block', 'manuell_upgrade_select_block', 'manuell_upgrade_select');
    $template->set_block('addon_advanced_block', 'manuell_uninstall_block', 'manuell_uninstall');
    $template->set_block('manuell_uninstall_block', 'manuell_uninstall_select_block', 'manuell_uninstall_select');
    $iUninstall = 0;
    $iUprage    = 0;
    foreach ($aAddons as $iIndex=>$aAddon){
        if (!$admin->get_permission( $aAddon['directory'], $sAddonType )) { continue; }
        $sAddsonsPath = WB_PATH.'/modules/'.$aAddon['directory'];
// upgrade list
        if (is_readable($sAddsonsPath.'/upgrade.php') && is_readable($sAddsonsPath.'/info.php')) {
//            $show_block = true;
            $sAddonIdKey = \bin\SecureTokens::getIDKEY($aAddon['addon_id']);
            $aDebug['manuell_upgrade'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
            $template->set_var('ADVANCED', $show_block);
            $template->set_var('UPGRADE_VISIBLE', '');
            $template->set_var('UPGRADE_FILES', sprintf('%1d Files found',$iUprage++));
            $template->set_var('UPGRADE_VALUE', $sAddonIdKey);
            $template->set_var('UPGRADE_NAME', $aAddon['name']);
            $template->parse('manuell_upgrade_select', 'manuell_upgrade_select_block', true);
        }
// uninstall list
        if (is_readable($sAddsonsPath.'/uninstall.php') && is_readable($sAddsonsPath.'/info.php')) {
            if (!preg_match('/'.$aAddon['directory'].'/si', implode('|', $aPreventFromUninstall))) {
                $sAddonIdKey = \bin\SecureTokens::getIDKEY($aAddon['addon_id']);
                $aDebug['manuell_uninstall'][$sAddonIdKey] = $aAddon['addon_id'].' ['.$aAddon['directory'].']';
                $template->set_var('ADVANCED', $show_block);
                $template->set_var('UNINSTALL_VISIBLE', '');
                $template->set_var('UNINSTALL_FILES',  sprintf('%1d Files found',$iUninstall++));
                $template->set_var('UNINSTALL_VALUE', $sAddonIdKey);
                $template->set_var('UNINSTALL_NAME', $aAddon['name']);
                $template->parse('manuell_uninstall_select', 'manuell_uninstall_select_block', true);
            }
        }
    }  // end foreach
// enable/disable manuell blocka
    if($admin->get_permission($sAddonType.'s_view') != true) {
        $template->set_block('manuell_upgrade', '');
    } else {
        $template->parse('manuell_upgrade', 'manuell_upgrade_block', true);
    }
    if($admin->get_permission($sAddonType.'s_uninstall') != true) {
        $template->set_block ('manuell_uninstall', '');
    } else {
        $template->parse('manuell_uninstall', 'manuell_uninstall_block', true);
    }
}

// only show advanced block if there is something to show
    if (!$show_block || count($aAddonFiles) == 0 || !isset($oRequest->advanced) || $admin->get_permission('admintools') != true) {
        $template->set_block('addon_advanced', '');
    } else {
        $template->parse('addon_advanced', 'addon_advanced_block', true);
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

    $template->set_var('TEXT_EXECUTE', sprintf($oTrans->TEXT_EXECUTE, '', ''));

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
