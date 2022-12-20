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
 * Description of admin/addons/reload.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: reload.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

/**
 * check if there is anything to do
 */
$post_check = array('reload_languages', 'reload_modules', 'reload_templates');
foreach ($post_check as $index => $key) {
    if (!isset($_POST[$key])) unset($post_check[$index]);
}
if (\count($post_check) == 0) die(\header('Location: index.php?advanced'));

/**
 * check if user has permissions to access this file
 */
// include WB configuration file and WB admin class
if (!\defined( 'SYSTEM_RUN')){require( \dirname(\dirname((__DIR__))).'/config.php');}
// check user permissions for admintools (redirect users with wrong permissions)
$admin = new admin('Admintools', 'admintools', false, false);

if ($admin->get_permission('admintools') == false) die(\header('Location: ../../index.php'));

// check if the referer URL if available
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] :
    (isset($HTTP_SERVER_VARS['HTTP_REFERER']) ? $HTTP_SERVER_VARS['HTTP_REFERER'] : '');
$referer = '';
// if referer is set, check if script was invoked from "admin/modules/index.php"
$required_url = ADMIN_URL . '/addons/index.php';
if ($referer != '' && (!(\strpos($referer, $required_url) !== false || \strpos($referer, $required_url) !== false)))
    die(\header('Location: ../../index.php'));

// include WB functions file
//if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

// create Admin object with admin header
$admin = new admin('Addons', '', false, false);
$js_back = ADMIN_URL.'/addons/index.php?advanced';

if (!$admin->checkFTAN())
{
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL);
}

if (\count($post_check)==3){
   if (!$database->query('TRUNCATE `'.TABLE_PREFIX.'addons`')){
    $admin->print_header();
    $admin->print_error(\sprintf('%[d] %s',__LINE__,$database->get_error()), $js_back);
   }
} else {
/**
 * delete no existing addons in table
 */
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
          . 'ORDER BY `type`, `directory` ';
    if ($oAddons = $database->query($sql)) {
        while ($aAddon = $oAddons->fetchRow(MYSQLI_ASSOC )) {
            $delAddon = 'DELETE  FROM `'.TABLE_PREFIX.'addons` WHERE `addon_id`='.(int)$aAddon['addon_id'];
            $sAddonFile = WB_PATH.'/'.$aAddon['type'].'s/'.$aAddon['directory'];
            switch ($aAddon['type']):
                case 'language':
                    if (!\file_exists($sAddonFile.'.php')) {
                        $oDelResult = $database->query($delAddon);
                    }
                    break;
                default:
                    if (!\file_exists($sAddonFile)) {
                        $oDelResult = $database->query($delAddon);
                    }
                break;
            endswitch;
        }
    }
}

/**
 *
 * Reload all specified Addons
 */
$msg = [];
$table = TABLE_PREFIX . 'addons';

foreach ($post_check as $key) {
    switch ($key) {

        case 'reload_languages':
            $aAddonList = \glob(WB_PATH.'/languages/*.php' );
            foreach( $aAddonList as $sAddonFile ) {
                if (\is_readable( $sAddonFile )) {
                    load_language( $sAddonFile );
                }
            }
            // add success message
            $msg[] = $MESSAGE['ADDON_LANGUAGES_RELOADED'];
            unset($aAddonList);
            break;

        case 'reload_modules':
            $aAddonList = \glob(WB_PATH.'/modules/*', GLOB_ONLYDIR);
            foreach( $aAddonList as $sAddonFile ) {
                if (\is_readable($sAddonFile)) {
                    load_module($sAddonFile);
                }
            }
            // add success message
            $msg[] = $MESSAGE['ADDON_MODULES_RELOADED'];
            unset($aAddonList);
            break;

        case 'reload_templates':
            $aAddonList = \glob(WB_PATH.'/templates/*', GLOB_ONLYDIR );
            foreach( $aAddonList as $sAddonFile ) {
                if (\is_readable($sAddonFile)) {
                    load_template($sAddonFile);
                }
            }
            // add success message
            $msg[] = $MESSAGE['ADDON_TEMPLATES_RELOADED'];
            unset($aAddonList);
            break;

    }
}

// output success message
$admin->print_header();
$admin->print_success(\implode('<br />', $msg), $js_back);
$admin->print_footer();
