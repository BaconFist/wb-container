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
 * @version         $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/groups/index.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use vendor\phplib\Template;

// Print admin header
if (!defined( 'SYSTEM_RUN') ){require( dirname(dirname((__DIR__))).'/config.php');}
    $admin = new admin('Access', 'groups');
    $requestMethod = ($GLOBALS['_SERVER']['REQUEST_METHOD']);
    $aRequestVars  = ((${'_'.$requestMethod}) ? : null);

    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $bAdvanced  = abs(isset($aRequestVars['advanced']) ? $aRequestVars['advanced'] : 0);
     if (!in_array($bAdvanced, ['0','1'])){
            throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

    $sDefaultModules   = ['ckeditor','wysiwyg','menu_link'];
    $sDefaultTemplates = ['DefaultTheme','DefaultTemplate'];
// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source('groups.htt')));
// $template->debug = true;
    $template->set_file('page', 'groups.htt');
    $template->set_block('page', 'main_block', 'main');
// insert urls
$ftan = $admin->getFTAN();
$template->set_var(array(
    'ADMIN_URL' => ADMIN_URL,
    'WB_URL' => WB_URL,
    'THEME_URL' => THEME_URL,
    'FTAN' => $ftan
    )
);
/*-------------------------------------------------------------------------------------------------------*/
// Get existing value from database
$query = 'SELECT `group_id`, `name` FROM `'.TABLE_PREFIX.'groups` WHERE `group_id` != 1';
$results = $database->query($query);
if($database->is_error()) {
    $admin->print_error($database->get_error(), 'index.php');
}
// Insert values into the modify/remove menu
$template->set_block('main_block', 'list_block', 'list');
if($results->numRows() > 0) {
    // Insert first value to say please select
    $template->set_var('VALUE', '');
    $template->set_var('NAME', $MESSAGE['SELECT_GROUP'].'...');
    $template->parse('list', 'list_block', true);
    // Loop through groups
    while($group = $results->fetchRow(MYSQLI_ASSOC)) {
        $template->set_var('VALUE',$admin->getIDKEY($group['group_id']));
        $template->set_var('NAME', $group['name']);
        $template->parse('list', 'list_block', true);
    }
} else {
    // Insert single value to say no groups were found
    $template->set_var('NAME', $TEXT['NONE_FOUND']);
    $template->parse('list', 'list_block', true);
}
/*-------------------------------------------------------------------------------------------------------*/
// Insert permissions values
if($admin->get_permission('groups_add') != true) {
    $template->set_var('DISPLAY_ADD', 'hide');
}
if($admin->get_permission('groups_modify') != true) {
    $template->set_var('DISPLAY_MODIFY', 'hide');
}
if($admin->get_permission('groups_delete') != true) {
    $template->set_var('DISPLAY_DELETE', 'hide');
}
// Insert language headings
$template->set_var(array(
    'HEADING_MODIFY_DELETE_GROUP' => $HEADING['MODIFY_DELETE_GROUP'],
    'HEADING_ADD_GROUP' => $HEADING['ADD_GROUP']
    )
);
// Insert language text and messages
$template->set_var(array(
    'TEXT_MODIFY' => $TEXT['MODIFY'],
    'TEXT_DELETE' => $TEXT['DELETE'],
    'TEXT_MANAGE_USERS' => ( $admin->get_permission('users') == true ) ? $TEXT['MANAGE_USERS']: "",
    'CONFIRM_DELETE' => $TEXT['GROUP'].' '.$TEXT['DELETE'].', '.$TEXT['ARE_YOU_SURE']
    )
);

$template->set_block('main_block', 'manage_users_block', 'users');
if ( $admin->get_permission('users') == true ) {
    $template->parse("users", "manage_users_block", true);
} else {
    $template->set_block('manage_users_block', '');
}
// Parse template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');
/*-------------------------------------------------------------------------------------------------------*/
// Setup template object, parse vars to it, then parse it
// Create new template object
$template = new Template(dirname($admin->correct_theme_source('groups_form.htt')));
// $template->debug = true;
//$template->set_unknowns('keep');
$template->set_file('page', 'groups_form.htt');
$template->set_block('page', 'main_block', 'main');
$template->set_block('main_block', 'manage_users_block', 'users');
/*-------------------------------------------------------------------------------------------------------*/
$template->set_var('DISPLAY_EXTRA', 'display:none;');
$template->set_var('GROUP_NAME', '');
$template->set_var('ACTION_URL', ADMIN_URL.'/groups/add.php');
$template->set_var('SUBMIT_TITLE', $TEXT['ADD']);
$template->set_var('ADVANCED_LINK', ADMIN_URL.'/groups/index.php');
$template->set_var('CANCEL_LINK', ADMIN_URL.'/access/index.php');
$template->set_var('BACK_LINK', ADMIN_URL.'/access/index.php');
/*-------------------------------------------------------------------------------------------------------*/
// Tell the browser whether or not to show advanced options
$template->set_block('main_block', 'groups_basic_block', 'groups_basic');
$template->set_block('main_block', 'groups_extended_block', 'groups_extended');
if($bAdvanced)
{
    $template->set_var('DISPLAY_ADVANCED', '');
    $template->set_var('DISPLAY_BASIC', 'display:none;');
    $template->set_var('ADVANCED_VALUE', 0);
    $template->set_var('ADVANCED_BUTTON', '&laquo; '.$TEXT['HIDE_ADVANCED']);
    $template->parse('groups_extended', 'groups_extended_block', true);
    $template->set_block('groups_basic', '', '');
} else {
    $template->set_var('DISPLAY_ADVANCED', 'display:none;');
    $template->set_var('DISPLAY_BASIC', '');
    $template->set_var('ADVANCED_VALUE', 1);
    $template->set_var('ADVANCED_BUTTON', $TEXT['SHOW_ADVANCED'].' &raquo;');
    $template->parse('groups_basic', 'groups_basic_block', true);
    $template->set_block('groups_extended', '');
}
    $template->set_block('manage_users_block', '');
/*-------------------------------------------------------------------------------------------------------*/
// Insert permissions values
if($admin->get_permission('groups_add') != true) {
    $template->set_var('DISPLAY_ADD', 'hide');
}
/*-------------------------------------------------------------------------------------------------------*/
    $sPermissions = [];
    $system_permissions   = [];
    // Check system permissions boxes
    $sOldWorkingDir = getcwd();
    // Explode module permissions
    chdir(WB_PATH.'/modules/');
    $aAvailableItemsList = glob('*', GLOB_ONLYDIR|GLOB_NOSORT);
    $module_permissions = array_diff($aAvailableItemsList, $sDefaultModules);
    // Explode template permissions
    chdir(WB_PATH.'/templates/');
    $aAvailableItemsList = glob('*', GLOB_ONLYDIR|GLOB_NOSORT);
    $template_permissions = array_diff($aAvailableItemsList, $sDefaultTemplates);
    chdir($sOldWorkingDir);
/*-------------------------------------------------------------------------------------------------------*/
// Insert values into module list
    $template->set_block('main_block', 'module_list_block', 'module_list');
    $template->set_block('module_list_block', 'module_function_block', 'module_function');
    $aTmp = [];
    $addon = [];
    $aCheckedList = [];
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
          . 'WHERE `type` = \'module\' '
          .   'AND `function` IN (\'page\', \'tool\',\'wysiwyg\') '
//                  . 'GROUP BY `function` '
          . 'ORDER BY `function`, `name`';
    if($result = $database->query($sql))
    {
        $i=0;
        $sGroupsFunction = 'none';
        while($addon = $result->fetchRow(MYSQLI_ASSOC)) {
            if (!is_readable(WB_PATH.'/modules/'.$addon['directory'])){
                $aTmp[] = $addon['directory'];
                continue;
            }
            if (!preg_match('/'.$sGroupsFunction.'$/', trim($addon['function']))){
                $template->set_var('OPTGROUP', ucwords($addon['function']));
                $template->set_var('MODULE_TEST', trim($addon['function']).'!='.$sGroupsFunction);
                $sGroupsFunction = trim($addon['function']);
                $template->parse('module_list', 'module_function_block', true);
            } else {
                $template->set_block('module_function', '');
            }
            $template->set_var('VALUE', $addon['directory']);
            $template->set_var('NAME', $addon['name']);
            if (!is_numeric(array_search($addon['directory'], $module_permissions)))
            {
                $template->set_var('CHECKED', ' checked="checked"');
                $aCheckedList[$i]['directory'] = $addon['directory'];
                $aCheckedList[$i]['name'] = $addon['name'];
                ++$i;
            } else {
                $template->set_var('CHECKED', '');
            }
            $template->parse('module_list', 'module_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------------------*/
// Insert values into template list
    $template->set_block('main_block', 'template_list_block', 'template_list');
    $template->set_block('template_list_block', 'template_function_block', 'template_function');
    $aTmp = [];
    $addon = [];
    $aCheckedList = [];
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
          . 'WHERE `type` = \'template\' '
          .   'AND `function` IN (\'template\', \'theme\') '
          . 'ORDER BY `function`, `name`';
    if($result = $database->query($sql))
    {
        $i=0;
        $GroupsFunction = 'none';
        while ($addon = $result->fetchRow(MYSQLI_ASSOC)) {
            if (!is_readable(WB_PATH.'/templates/'.$addon['directory'])){
                $aTmp[] = $addon['directory'];
                continue;
            }
            if (!preg_match('/'.$sGroupsFunction.'$/i', trim($addon['function']))){
                $template->set_var('OPTGROUP', ucwords($addon['function']));
                $sGroupsFunction = trim($addon['function']);
                $template->parse('template_list', 'template_function_block', true);
            } else {
                $template->set_block('template_function', '');
            }
            $template->set_var('VALUE', $addon['directory']);
            $template->set_var('NAME', $addon['name']   );
            if(!is_numeric(array_search($addon['directory'], $template_permissions)))
            {
                $template->set_var('CHECKED', ' checked="checked"');
                $aCheckedList[$i]['directory'] = $addon['directory'];
                $aCheckedList[$i]['name'] = $addon['name'];
                ++$i;
            } else {
                $template->set_var('CHECKED', '');
            }
            $GroupsFunction = $addon['function'];
            $template->parse('template_list', 'template_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------------------*/
// Insert language text and messages
$template->set_var(array(
            'TEXT_CANCEL' => $TEXT['CANCEL'],
            'TEXT_CLOSE' => $TEXT['CLOSE'],
            'TEXT_RESET' => $TEXT['RESET'],
            'TEXT_BACK' => $TEXT['BACK'],
            'TEXT_DEFAULT_RESET' => $TEXT['DEFAULT_RESET'],
            'TEXT_FILESYSTEM_PERMISSIONS' => $TEXT['FILESYSTEM_PERMISSIONS'],
            'TEXT_ACTIVE' => $TEXT['ACTIVE'],
            'TEXT_DISABLED' => $TEXT['DISABLED'],
            'TEXT_PLEASE_SELECT' => $TEXT['PLEASE_SELECT'],
            'TEXT_USERNAME' => $TEXT['USERNAME'],
            'TEXT_PASSWORD' => $TEXT['PASSWORD'],
            'TEXT_RETYPE_PASSWORD' => $TEXT['RETYPE_PASSWORD'],
            'TEXT_DISPLAY_NAME' => $TEXT['DISPLAY_NAME'],
            'TEXT_EMAIL' => $TEXT['EMAIL'],
            'TEXT_GROUP' => $TEXT['GROUP'],
            'TEXT_GROUPS' => $MENU['GROUPS'],
            'TEXT_SYSTEM_PERMISSIONS' => $TEXT['SYSTEM_PERMISSIONS'],
            'TEXT_MODULE_PERMISSIONS' => $TEXT['MODULE_PERMISSIONS'],
            'TEXT_TEMPLATE_PERMISSIONS' => $TEXT['TEMPLATE_PERMISSIONS'],
            'TEXT_NAME' => $TEXT['NAME'],
            'SECTION_PAGES' => $MENU['PAGES'],
            'SECTION_MEDIA' => $MENU['MEDIA'],
            'SECTION_MODULES' => $MENU['MODULES'],
            'SECTION_TEMPLATES' => $MENU['TEMPLATES'],
            'SECTION_SETTINGS' => $MENU['SETTINGS'],
            'SECTION_LANGUAGES' => $MENU['LANGUAGES'],
            'SECTION_USERS' => $MENU['USERS'],
            'SECTION_GROUPS' => $MENU['GROUPS'],
            'SECTION_ADMINTOOLS' => $MENU['ADMINTOOLS'],
            'TEXT_VIEW' => $TEXT['VIEW'],
            'TEXT_ADD' => $TEXT['ADD'],
            'TEXT_LEVEL' => $TEXT['LEVEL'],
            'TEXT_MODIFY' => $TEXT['MODIFY'],
            'TEXT_DELETE' => $TEXT['DELETE'],
            'TEXT_MODIFY_CONTENT' => $TEXT['MODIFY_CONTENT'],
            'TEXT_MODIFY_SETTINGS' => $TEXT['MODIFY_SETTINGS'],
            'HEADING_MODIFY_INTRO_PAGE' => $TEXT['INTRO_PAGE'],//$HEADING['MODIFY_INTRO_PAGE'],
            'TEXT_CREATE_FOLDER' => $TEXT['CREATE_FOLDER'],
            'TEXT_RENAME' => $TEXT['RENAME'],
            'TEXT_UPLOAD_FILES' => $TEXT['UPLOAD_FILES'],
            'TEXT_BASIC' => $TEXT['BASIC'],
            'TEXT_ADVANCED' => $TEXT['ADVANCED'],
            'CHANGING_PASSWORD' => $MESSAGE['USERS_CHANGING_PASSWORD'],
//                                'CHECKED' => ' checked="checked"',
            'ADMIN_URL' => ADMIN_URL,
            'WB_URL' => WB_URL,
            'THEME_URL' => THEME_URL,
            'FTAN' => $ftan,
            'DEBUG_MSG'=>(isset($DebugOLutput) ? $DebugOLutput :'')
            )
                );

// Parse template for add group form
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Print the admin footer
$admin->print_footer();
