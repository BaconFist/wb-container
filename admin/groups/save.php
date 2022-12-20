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
 * @version         $Id: save.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/groups/save.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 * sanitizeFilename
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Print admin header
if (!defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}
// suppress to print the header, so no new FTAN will be set
    $admin = new admin('Access', 'groups_modify', false);

    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }

    $Message = $MESSAGE['GROUPS_SAVED'];

    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $bAdvanced       = intval(isset($aRequestVars['advanced']) ? $aRequestVars['advanced'] : 0);
    $bAdvancedSave   = intval(isset($aRequestVars['advanced_extended'])  ? $aRequestVars['advanced_extended'] : 0);
    $bResetSystem    = intval(isset($aRequestVars['reset_system'])  ? $aRequestVars['reset_system'] : 0);
    $bResetModules   = intval(isset($aRequestVars['reset_modules'])  ? $aRequestVars['reset_modules'] : 0);
    $bResetTemplates = intval(isset($aRequestVars['reset_templates'])  ? $aRequestVars['reset_templates'] : 0);

    $sPostfix = (($bResetTemplates || $bResetModules || $bResetSystem) ? $MESSAGE['GROUPS_DEFAULT_SAVED'] : '');
    $Message  = ($bResetSystem    ? 'System'   : $Message);
    $Message  = ($bResetModules   ? 'Module'   : $Message);
    $Message  = ($bResetTemplates ? 'Template' : $Message);
    $Message  .= $sPostfix;

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
    $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], $js_back );
}

// Check if group group_id is a valid number and doesnt equal 1
$group_id = intval($admin->checkIDKEY('group_id', 0, $requestMethod));
if( ($group_id < 2 ) )
{
    // if($admin_header) { $admin->print_header(); }
    $admin->print_header();
    $sInfo = strtoupper(basename(__DIR__).'_'.basename(__FILE__, ''.PAGE_EXTENSION).'::');
    $sDEBUG=(defined('DEBUG') && DEBUG ? $sInfo : '');
    $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], $js_back );
}
// Gather details entered
$groupName = PreCheck::sanitizeFilename($admin->get_post('group_name'));
$group_name = preg_replace('/[^a-z0-9_-]/i', "", $groupName);
$group_name = $admin->StripCodeFromText($group_name);
// Check values
if($group_name == "") {
    $admin->print_error($MESSAGE['GROUPS_GROUP_NAME_BLANK'], $js_back);
}

// After check print the header
$admin->print_header();

$system_permissions = [];
$query = 'SELECT `system_permissions` FROM `'.TABLE_PREFIX.'groups` '
       . 'WHERE `group_id` = '.$group_id;
if ($sSystemPermissions = $database->get_one($query)) {
//    $aRes = $oRes->fetchRow(MYSQLI_ASSOC);
    $system_permissions = (explode(',', $sSystemPermissions));
}

// Get system permissions
require(ADMIN_PATH.'/groups/get_permissions.php');

// Update the database
$sql  = 'UPDATE `'.TABLE_PREFIX.'groups` SET '
      .'`name` = \''.$group_name.'\', '
      .'`system_permissions` = \''.$database->escapeString($system_permissions).'\', '
      .'`module_permissions` = \''.$database->escapeString($module_permissions).'\', '
      .'`template_permissions` = \''.$database->escapeString($template_permissions).'\' '
      .'WHERE `group_id` = '.intval($group_id);

$database->query($sql);
if($database->is_error()) {
    $admin->print_error($database->get_error(), $js_back);
} else {
    $group_id = $admin->getIDKEY($group_id);
    $modifyUrl = ADMIN_URL.'/groups/groups.php?modify=&group_id='.$group_id.'&advanced='.!$bAdvanced;
    $admin->print_success($Message, $modifyUrl);
}

// Print admin footer
$admin->print_footer();
