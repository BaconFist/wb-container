<?php
//:Puts a Login / Logout box on your page.
//:Use: [[LoginBox?redirect=url]]
//:Absolute or relative url possible
//:Remember to enable frontend login in your website settings!!
//:@changed 2022-04-09

//global $wb;
$oReg = \bin\WbAdaptor::getInstance();
$database = $oReg->getDatabase();
$oLang = $oReg->getTranslate();
$oRequest = $oReg->getRequester();
$oApp  = $oReg->getApplication();
$oLang->enableAddon('templates\\'.TEMPLATE);
$return_value = '<div class="login-box">'.PHP_EOL;
$return_admin = ' ';
// Return a system permission
$get_permission = function ($name, $type = 'system')use ($oApp)
{
    // Append to permission type
    $type .= '_permissions';
    // Check if we have a section to check for
    if ($name == 'start') {
        return true;
    } else {
        // Set system permissions var
        $system_permissions = $oApp->get_session('SYSTEM_PERMISSIONS');
        // Set module permissions var
        $module_permissions = $oApp->get_session('MODULE_PERMISSIONS');
        // Set template permissions var
        $template_permissions = $oApp->get_session('TEMPLATE_PERMISSIONS');
        // Return true if system perm = 1
        if (isset($$type) && is_array($$type) && is_numeric(array_search($name, $$type))) {
            if ($type == 'system_permissions') {
                return true;
            } else {
                return false;
            }
        } else {
            if ($type == 'system_permissions') {
                return false;
            } else {
                return true;
            }
        }
    }
}
;
$get_page_permission = function ($page, $action = 'admin')use ($database, $oApp)
{
    if ($action != 'viewing') {$action = 'admin';}
    $action_groups = $action.'_groups';
    $action_users  = $action.'_users';
    if (is_array($page)) {
        $groups = $page[$action_groups];
        $users = $page[$action_users];
    } else {
        $sql = 'SELECT '.$action_groups.','.$action_users.' FROM `'.TABLE_PREFIX.'pages` '.'WHERE `page_id` = '.(int)$page.' ';
        if ($oResults = $database->query($sql)) {
            $aResult  = $oResults->fetchRow(\MYSQLI_ASSOC);
            $groups   = explode(',', str_replace('_', '', $aResult[$action_groups]));
            $users    = explode(',', str_replace('_', '', $aResult[$action_users]));
        }
    }
    $in_group = false;
    foreach ($oApp->get_groups_id() as $cur_gid) {
        if (in_array($cur_gid, $groups)) {
            $in_group = true;
        }
    }
    if (!$in_group && !is_numeric(array_search($oApp->getUserId(), $users))) {
        return false;
    }
    return true;
}
;
// Get redirect
$redirect_url = ((isset($_SESSION['HTTP_REFERER']) && $_SESSION['HTTP_REFERER'] != '') ? $_SESSION['HTTP_REFERER'] : WB_URL);
$redirect_url = (isset($redirect) && ($redirect != '') ? $redirect : $redirect_url);
if ((FRONTEND_LOGIN == 'enabled') && (defined('VISIBILITY') && (VISIBILITY != 'private')) && ($oApp->get_session('USER_ID') == '')) {
    $return_value .= '<form action="'.LOGIN_URL.'" method="post" class="login-table">'.PHP_EOL;
    $return_value .=     '<input type="hidden" name="redirect" value="'.$redirect_url.'" />'.PHP_EOL;
    $return_value .=     '<input type="hidden" name="page_id" value="'.$oApp->page_id.'" />'.PHP_EOL;
    $return_value .=     '<fieldset>'.PHP_EOL;
    $return_value .=         '<h3>'.$oLang->TEXT_LOGIN.'</h3>'.PHP_EOL;
    $return_value .=         '<label for="username">'.$oLang->TEXT_USERNAME.':</label>'.PHP_EOL;
    $return_value .=         '<p><input type="text" name="username" id="username"  /></p>'.PHP_EOL;
    $return_value .=         '<label for="password">'.$oLang->TEXT_PASSWORD.':</label>'.PHP_EOL;
    $return_value .=         '<p><input class="input form-control password-field-dr" type="password" name="password" autocomplete="off"/><span toggle=".password-field-dr" class="fa fa-fw fa-eye-slash field-icon toggle-password"></span></p>'.PHP_EOL;
    $return_value .=         '<p><input type="submit" id="submit" value="'.$oLang->TEXT_LOGIN.'" class="dbutton" /></p>'.PHP_EOL;
    $return_value .=         '<ul class="login-advance">'.PHP_EOL;
    $return_value .=             '<li class="forgot"><a href="'.FORGOT_URL.'"><span>'.$oLang->TEXT_FORGOT_DETAILS.'</span></a></li>'.PHP_EOL;
    if (intval(FRONTEND_SIGNUP) > 0) {
        $return_value .=         '<li class="sign"><a href="'.SIGNUP_URL.'">'.$oLang->TEXT_SIGNUP.'</a></li>'.PHP_EOL;
    }
    $return_value .=         '</ul>'.PHP_EOL;
    $return_value .=     '</fieldset>'.PHP_EOL;
    $return_value .= '</form>'.PHP_EOL;
} elseif ((FRONTEND_LOGIN == 'enabled') && (is_numeric($oApp->get_session('USER_ID')))) {
    $return_value .= '<form action="'.LOGOUT_URL.'" method="post" class="login-table">'.PHP_EOL;
    $return_value .=     '<input type="hidden" name="redirect" value="'.$redirect_url.'" />'.PHP_EOL;
    $return_value .=     '<input type="hidden" name="page_id" value="'.$oApp->page_id.'" />'.PHP_EOL;
    $return_value .=     '<fieldset>'.PHP_EOL;
    $return_value .=         '<h3>'.$oLang->TEXT_LOGGED_IN.'</h3>'.PHP_EOL;
    $return_value .=         '<label>'.$oLang->TEXT_WELCOME_BACK.', '.$oApp->get_display_name().'</label>'.PHP_EOL;
    $return_value .=         '<p><input type="submit" name="submit" value="'.$oLang->MENU_LOGOUT.'" class="dbutton" /></p>'.PHP_EOL;
    $return_value .=         '<ul class="logout-advance">'.PHP_EOL;
    $return_value .=             '<li class="preference"><a href="'.PREFERENCES_URL.'" title="'.$oLang->MENU_PREFERENCES.'">'.$oLang->MENU_PREFERENCES.'</a></li>'.PHP_EOL;
    //  change ot the group that should get special links
    if ($oApp->ami_group_member('1')){
        $return_value .=         '<li class="admin"><a href="'.ADMIN_URL.'/index.php" title="'.$oLang->TEXT_ADMINISTRATION.'" class="blank_target">'.$oLang->TEXT_ADMINISTRATION.'</a></li>'.PHP_EOL;
        //you can add more links for your users like userpage, lastchangedpages or something
    }
    //change ot the group that should get special links
    if ($get_permission('pages_modify') && $get_page_permission(PAGE_ID)) {
        $return_value .=        '<li class="modify"><a  href="'.ADMIN_URL.'/pages/modify.php?page_id='.PAGE_ID.'" title="'.$oLang->HEADING_MODIFY_PAGE.'" class="blank_target">'.$oLang->HEADING_MODIFY_PAGE.
            '</a></li>'.PHP_EOL;
    }
    $return_value .=         '</ul>'.PHP_EOL;
    $return_value .=     '</fieldset>'.PHP_EOL;
    $return_value .= '</form>'.PHP_EOL;
}
$return_value .= '</div>'.PHP_EOL;
return $return_value;
