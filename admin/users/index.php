<?php
/**
 *
 * @category        admin
 * @package         users
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/users/index.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use vendor\phplib\Template;

if (!\defined('SYSTEM_RUN')) {require( \dirname(__DIR__,2).'/config.php');}

    $admin = new \admin('Access', 'users');

    $oReg   = WbAdaptor::getInstance();
    $oDb    = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans = $oReg->getTranslate();
    $sDomain = basename(dirname(__DIR__)).'\\'.basename(__DIR__);
    $oTrans->enableAddon($sDomain);
/*
    $sAbsAddonPath = str_replace(['//','\\'],'/',__DIR__);
    if (\is_readable($sAbsAddonPath.'/languages/EN.php')) {require($sAbsAddonPath.'/languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.LANGUAGE.'.php');}
*/
    $iUserStatus = (($oRequest->getParam('status') ?? 0) + 1) % 2;
// Setup template object, parse vars to it, then parse it
// Create new template object
    $oTpl = new Template(dirname($admin->correct_theme_source('users.htt')));
// $oTpl->debug = true;

    $oTpl->set_file('page', 'users.htt');
    $oTpl->set_block('page', 'main_block', 'main');
    $oTpl->set_block("main_block", "manage_groups_block", "groups");
    $oTpl->set_var('ADMIN_URL', ADMIN_URL);
    $oTpl->set_var('FTAN', $admin->getFTAN());
    $oTpl->set_var('USER_STATUS', $iUserStatus );

    $UserStatusActive = 'url('.THEME_URL.'/images/user.png)';
    $UserStatusInactive = 'url('.THEME_URL.'/images/user_red.png)';

    $sUserTitle  = ($iUserStatus == 0) ? ($oTrans->TEXT_SHOW_ACTIVED_USER) : ($oTrans->TEXT_SHOW_DEACTIVED_USER);
    //$sUserTitle .= ' '.$oTrans->MENU_USER;

    $oTpl->set_var('TEXT_USERS', $sUserTitle );
    //$oTpl->set_var('TEXT_USERS', $oTrans->TEXT_SHOW.' '.$sUserTitle );
    $oTpl->set_var('STATUS_ICON', ( ($iUserStatus==0) ? $UserStatusActive : $UserStatusInactive) );
    $oTpl->set_var('USER_CHECKED', ( ($iUserStatus==0) ? '' : ' checked="checked"') );

// Get existing value from database
    $sql  = 'SELECT `user_id`, `username`, `display_name`, `active` FROM `'.TABLE_PREFIX.'users` ' ;
    $sql .= 'WHERE user_id != 1 ';
    $sql .=   'AND active = '.$iUserStatus.' ';
    $sql .= 'ORDER BY `display_name`,`username`';

    $results = $database->query($sql);
    if ($database->is_error()) {
        $admin->print_error($database->get_error(), 'index.php');
    }

    $sUserList    = $oTrans->TEXT_LIST_OPTIONS.' ';
    $sUserList   .= ($iUserStatus == 1) ? \strtolower($oTrans->TEXT_ACTIVATED) : \strtolower($oTrans->TEXT_DEACTIVED) ;
    $sUserList   .= ' '.$oTrans->TEXT_USER;
    $sSelectTitle = ($iUserStatus == 1) ? $oTrans->TEXT_NONE_ACTIVE_FOUND : $oTrans->TEXT_NONE_INACTIVE_FOUND;
    // Insert values into the modify/remove menu
    $oTpl->set_block('main_block', 'list_block', 'list');
    if ($results->numRows() > 0) {
        // Insert first value to say please select
        $oTpl->set_var('VALUE', '');
        $oTpl->set_var('NAME', $sUserList);
        $oTpl->set_var('STATUS', 'class="user-active"' );
        $oTpl->parse('list', 'list_block', true);
        // Loop through users
        while ($user = $results->fetchRow(MYSQLI_ASSOC)) {
            $oTpl->set_var('VALUE',SecureTokens::getIDKEY($user['user_id']));
            $oTpl->set_var('STATUS', ($user['active']==false ? 'class="user-inactive"' : 'class="user-active"') );
            $oTpl->set_var('NAME', $user['display_name'].' ('.$user['username'].')');
            $oTpl->parse('list', 'list_block', true);
        }
    } else {
        // Insert single value to say no users were found
        $oTpl->set_var('NAME', $sSelectTitle);
        $oTpl->parse('list', 'list_block', true);
        $oTpl->set_var('DISPLAY_MODIFY', 'hide');
        $oTpl->set_var('DISPLAY_DELETE', 'hide');
    }

// Insert permissions values
    if ($admin->get_permission('users_add') != true) {
        $oTpl->set_var('DISPLAY_ADD', 'hide');
    }
    if ($admin->get_permission('users_modify') != true) {
        $oTpl->set_var('DISPLAY_MODIFY', 'hide');
    }
    if ($admin->get_permission('users_delete') != true) {
        $oTpl->set_var('DISPLAY_DELETE', 'hide');
    }
    $HeaderTitle  = (($iUserStatus == 1) ? $oTrans->HEADING_MODIFY_ACTIVE_USER.' ' : $oTrans->HEADING_MODIFY_DELETE_USER.' ');
//$HeaderTitle .= (($iUserStatus == 1) ? strtolower($oTrans->TEXT_ACTIVE) : strtolower($oTrans->TEXT_DEACTIVED));
// Insert language headings
    $oTpl->set_var([
        'HEADING_MODIFY_DELETE_USER' => $HeaderTitle,
        'HEADING_ADD_USER' => $oTrans->HEADING_ADD_USER
        ]
    );
// insert urls
    $oTpl->set_var([
        'ADMIN_URL' => ADMIN_URL,
        'WB_URL' => WB_URL,
        'THEME_URL' => THEME_URL
        ]
    );
// Insert language text and messages
    $oTpl->set_var([
        'DISPLAY_WAITING_ACTIVATION' => '',
        'TEXT_MODIFY' => $oTrans->TEXT_MODIFY,
        'TEXT_DELETE' => (($iUserStatus == 1) ? $oTrans->TEXT_DEACTIVE : $oTrans->TEXT_DELETE),
        'TEXT_MANAGE_GROUPS' => ( $admin->get_permission('groups') == true ) ? $oTrans->TEXT_MANAGE_GROUPS : "**",
        'CONFIRM_DELETE' => (($iUserStatus == 1) ? $oTrans->TEXT_ARE_YOU_SURE : $oTrans->MESSAGE_USERS_CONFIRM_DELETE)
        ]
    );

        $oTpl->set_block('main_block', 'show_confirmed_activation_block', 'show_confirmed_activation');
        if($admin->ami_group_member('1')) {
                $oTpl->set_block('show_confirmed_activation_block', 'list_confirmed_activation_block', 'list_confirmed_activation');
                $oTpl->set_var(array(
                        'DISPLAY_WAITING_ACTIVATION' => $oTrans->MESSAGE_USERS_WAITING_ACTIVATION,
                        'TEXT_USER_ACTIVATE' => $oTrans->TEXT_ACTIVATE,
                        'TEXT_USER_DELETE' => (($iUserStatus == 1) ? $oTrans->TEXT_DEACTIVE : $oTrans->TEXT_DELETE),
                        )
                );
                $sql = 'SELECT * FROM `'.$oDb->TablePrefix.'users` '
                     . 'WHERE `confirm_timeout` != 0 AND `active` = 0 AND `user_id` != 1 ';
                if( ($oRes = $oDb->query($sql)) ) {
                    $oTpl->set_var('DISPLAY_DELETE', '');
                    // Loop through users
                    if(($nNumRows = $oRes->numRows())) {
                        while($aUser = $oRes->fetchArray(MYSQLI_ASSOC)) {
                            $oTpl->set_var('CVALUE',$admin->getIDKEY($aUser['user_id']));
                               $oTpl->set_var('CSTATUS', '') ;
                            $oTpl->set_var('CNAME', $aUser['display_name'].' ('.$aUser['username'].')'.' ['.$aUser['email'].']');
                            $oTpl->parse('list_confirmed_activation', 'list_confirmed_activation_block', true);
                        }
                        $oTpl->parse('show_confirmed_activation', 'show_confirmed_activation_block',true);
                    }
                } else { $nNumRows = 0; }
        } else {
            $nNumRows = 0;
        }
        if ( $nNumRows == 0){
            $oTpl->parse('show_confirmed_activation', '');
        }

    if ($admin->get_permission('groups') == true ){
      $oTpl->parse("groups", "manage_groups_block", true);
    }
// Parse template object
    $oTpl->parse('main', 'main_block', false);
    $oTpl->pparse('output', 'page');

    if (isset($_SESSION['users'])){
    }

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(\dirname($admin->correct_theme_source('users_form.htt')));
// $template->debug = true;
    $template->set_file('page', 'users_form.htt');
    $template->set_block('page', 'main_block', 'main');

    $template->set_var('DISPLAY_EXTRA', 'display:none;');
    $template->set_var('ACTIVE_CHECKED', ' checked="checked"');
    $template->set_var('ACTION_URL', ADMIN_URL.'/users/add.php');
    $template->set_var('SUBMIT_TITLE', $oTrans->TEXT_ADD);
    $template->set_var('FTAN', $admin->getFTAN());
// {READONLY}
    $template->set_var('READONLY', '' );

// insert urls
    $template->set_var(array(
        'ADMIN_URL' => ADMIN_URL,
        'WB_URL' => WB_URL,
        'THEME_URL' => THEME_URL
        )
    );
//    $template->set_var('USERNAME', '');

// Add groups to list
    $template->set_block('main_block', 'group_list_block', 'group_list');
    $results = $database->query("SELECT `group_id`, `name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` != '1'");
    if ($results->numRows() > 0) {
        $template->set_var('ID', '');
        $template->set_var('NAME', $oTrans->TEXT_PLEASE_SELECT.'...');
//    $template->set_var('SELECTED', ' selected="selected"');
        $template->parse('group_list', 'group_list_block', true);
        while($group = $results->fetchRow(MYSQLI_ASSOC)) {
            $template->set_var('ID', $group['group_id']);
            $template->set_var('NAME', $group['name']);
            $template->set_var('SELECTED', '');
            if ($admin->is_group_match($group['group_id'], (isset($_SESSION['users']['groups_id']) ? $_SESSION['users']['groups_id'] : '')))
            {
                $template->set_var('SELECTED', ' selected="selected"');
            }
            $template->parse('group_list', 'group_list_block', true);
        }
    }
// Only allow the user to add a user to the Administrators group if they belong to it
    if (in_array(1, $admin->get_groups_id())) {
        $users_groups = $admin->get_groups_name();
        $template->set_var('ID', '1');
        $template->set_var('NAME', $users_groups[1]);
        $template->set_var('SELECTED', '');
        $template->parse('group_list', 'group_list_block', true);
    } else {
        if($results->numRows() == 0) {
            $template->set_var('ID', '');
            $template->set_var('NAME', $oTrans->TEXT_NONE_FOUND);
            $template->parse('group_list', 'group_list_block', true);
        }
    }

// Insert permissions values
    if($admin->get_permission('users_add') != true) {
        $template->set_var('DISPLAY_ADD', 'hide');
    }

// Generate username field name
    $username_fieldname = 'username_';
    $salt = "abchefghjkmnpqrstuvwxyz0123456789";
    \srand((double)\microtime()*1000000);
    $i = 0;
    while ($i <= 7) {
        $num = \rand() % 33;
        $tmp = \substr($salt, $num, 1);
        $username_fieldname = $username_fieldname . $tmp;
        $i++;
    }

    $template->set_block('main_block', 'user_add_block', 'user_add');
    $template->parse('user_add', 'user_add_block', true);
    $template->set_block('main_block', 'user_display_block', 'user_display');
    $template->set_block('user_display', '');

    $template->set_var([
              'USERNAME_FIELDNAME' => $username_fieldname,
              'USERNAME' => (isset($_SESSION['users']) ? $_SESSION['users']['login_name'] : ''),
              'DISPLAY_NAME' => (isset($_SESSION['users']) ? $_SESSION['users']['display_name'] : ''),
              'EMAIL' => (isset($_SESSION['users']) ? $_SESSION['users']['email'] : ''),
              ]
          );

// Include the WB functions file
//    if (!\function_exists('directory_list')){require(WB_PATH.'/framework/functions.php');}
// Work-out if home folder should be shown
        $template->set_block('main_block', 'folder_list_block', 'folder_list');
/*
        if (HOME_FOLDERS) {
            $template->set_var('DISPLAY_HOME_FOLDERS', '');
// Add media folders to home folder list
            $aFiles = directory_list(WB_PATH.MEDIA_DIRECTORY);
            \array_unshift($aFiles, MEDIA_DIRECTORY );
            foreach($aFiles as $name){
                $sItem  = \str_replace(WB_PATH, '', $name);
                $iLevel = (int)\substr_count($sItem, '/')-1;
                $sPrefix = \str_repeat('&#160;&#160;&#160;',$iLevel);
                $template->set_var('NAME', $sPrefix.\basename($sItem));
                $template->set_var('LEVEL', $iLevel);
                $template->set_var('FOLDER', $sItem);
                $template->set_var('SELECTED', '');
                $template->parse('folder_list', 'folder_list_block', true);
            }
        }
*/
        if (!HOME_FOLDERS) {
            $template->set_var('DISPLAY_HOME_FOLDERS', ' style="display: none;"');
        }

// Insert language text and messages
    $template->set_var([
            'TEXT_CANCEL' => $oTrans->TEXT_CANCEL,
            'TEXT_CLOSE' => $oTrans->TEXT_CLOSE,
            'TEXT_RESET' => $oTrans->TEXT_RESET,
            'TEXT_ACTIVE' => $oTrans->TEXT_ACTIVE,
            'TEXT_DISABLED' => $oTrans->TEXT_DISABLED,
            'TEXT_PLEASE_SELECT' => $oTrans->TEXT_PLEASE_SELECT,
            'TEXT_USERNAME' => $oTrans->TEXT_USERNAME,
            'TEXT_PASSWORD' => $oTrans->TEXT_PASSWORD,
            'TEXT_RETYPE_PASSWORD' => $oTrans->TEXT_RETYPE_PASSWORD,
            'TEXT_DISPLAY_NAME' => $oTrans->TEXT_DISPLAY_NAME,
            'TEXT_EMAIL' => $oTrans->TEXT_EMAIL,
            'TEXT_GROUP' => $oTrans->TEXT_GROUP,
            'TEXT_NONE' => $oTrans->TEXT_NONE,
            'TEXT_HOME_FOLDER' => $oTrans->TEXT_HOME_FOLDER,
            'CHANGING_PASSWORD' => $oTrans->MESSAGE_USERS_CHANGING_PASSWORD,
            'CANCEL_LINK' => ADMIN_URL.'/access/index.php',
            'BACK_LINK' => ADMIN_URL.'/access/index.php',
            ]
    );
//            $template->set_var('BACK_LINK', ADMIN_URL.'/access/index.php');

// Parse template for add user form
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
    unset($_SESSION['users']);
    $oTrans->disableAddon();
    $admin->print_footer();
