<?php
/**
 *
 * @category        admin
 * @package         groups
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: add.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/groups/add.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Print admin header
if ( !defined( 'WB_PATH' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }

// suppress to print the header, so no new FTAN will be set
    $admin = new admin('Access', 'groups_add', false);
/*
    $requestMethod = ($GLOBALS['_SERVER']['REQUEST_METHOD']);
    $aRequestVars  = ((${'_'.$requestMethod}) ? : null);
*/
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }

    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $bAdvanced       = intval(isset($aRequestVars['advanced']) ? $aRequestVars['advanced'] : 0);
    $bAdvancedSave   = intval(isset($aRequestVars['advanced_extended'])  ? $aRequestVars['advanced_extended'] : 0);
    $bResetSystem    = intval(isset($aRequestVars['reset_system'])  ? $aRequestVars['reset_system'] : 0);
    $bResetModules   = intval(isset($aRequestVars['reset_modules'])  ? $aRequestVars['reset_modules'] : 0);
    $bResetTemplates = intval(isset($aRequestVars['reset_templates'])  ? $aRequestVars['reset_templates'] : 0);
// Create a javascript back link
    $js_back = ADMIN_URL.'/groups/index.php';
    $action = 'save';
    $action = (isset($aRequestVars['cancel']) ? 'cancel' : $action );
    switch ($action):
        case 'cancel':
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$js_back);
            exit;
        default:

        break;
    endswitch;

    if (!$admin->checkFTAN())
    {
        $admin->print_header();
        $sInfo = strtoupper(basename(__DIR__).'_'.basename(__FILE__, ''.PAGE_EXTENSION).'::');
        $sDEBUG=(defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL);
    }
// After check print the header
    $admin->print_header();

// Gather details entered
    $groupName = PreCheck::sanitizeFilename($admin->get_post('group_name'));
    $group_name = preg_replace('/[^a-z0-9_-]/i', "", $groupName);
    $group_name = $admin->StripCodeFromText($group_name);

// Check values
    if($group_name == "") {
        $admin->print_error($MESSAGE['GROUPS_GROUP_NAME_BLANK'], $js_back);
    }
    $sql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'groups` '
         . 'WHERE `name`=\''.$group_name.'\'';
    if ($database->get_one($sql)) {
        $admin->print_error($MESSAGE['GROUPS_GROUP_NAME_EXISTS'], $js_back);
    }
    $system_permissions = [];
// Get system and module permissions
    require(ADMIN_PATH.'/groups/get_permissions.php');

// Update the database
    $sql = 'INSERT INTO `'.TABLE_PREFIX.'groups` SET '
         .     '`name`=\''.$database->escapeString($group_name).'\', '
         .     '`system_permissions`=\''.$database->escapeString($system_permissions).'\', '
         .     '`module_permissions`=\''.$database->escapeString($module_permissions).'\', '
         .     '`template_permissions`=\''.$database->escapeString($template_permissions).'\'';

    if (($database->query($sql))) {
        $group_id = $admin->getIDKEY($database->getLastInsertId());
        $modifyUrl = ADMIN_URL.'/groups/groups.php?modify=&group_id='.$group_id.'&advanced='.!$bAdvanced;
        $admin->print_success($MESSAGE['GROUPS_ADDED'], $modifyUrl);
    } else {
        $admin->print_error($database->get_error(), $js_back);
    }
// Print admin footer
$admin->print_footer();
