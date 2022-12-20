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
 * @requirements    PHP 5.6 and higher
 * @version         $Id: add.php 250 2019-03-17 16:24:20Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/users/add.php $
 * @lastmodified    $Date: 2019-03-17 17:24:20 +0100 (So, 17. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
//use bin\;
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

// Print admin header
if (!\defined('SYSTEM_RUN')) {require(\dirname(\dirname((__DIR__))).'/config.php');}
// suppress to print the header, so no new FTAN will be set
    $admin    = new \admin('Access', 'users_add',false);
    $oReg     = WbAdaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $sDomain = basename(dirname(__DIR__)).'\\'.basename(__DIR__);
    $oTrans->enableAddon($sDomain);

    $aInputs = [];
    $groups_id = '';
    $sErrorMessage = '';
    $aErrorMessage = [];
// Create a javascript back link
    $sAddonBackUrl = $oReg->AcpUrl.'users/index.php';

try {

 // get request method
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }
    $aInputs = $aRequestVars;

    if (! SecureTokens::checkFTAN())
    {
        $admin->print_header();
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }
// After check print the header
    $admin->print_header();

//$aInputs = array_merge( $_POST );
    foreach ($aInputs as $name=>$value){
        $value = $admin->StripCodeFromText($value);
        switch ($name):
            case 'username_fieldname':
              $username_fieldname = $value;
              $value = $admin->StripCodeFromText($admin->get_post($username_fieldname));
              $username = \preg_replace('/[^a-z0-9&\-.=@_]/i', '',\strtolower($value));
              $aInputs[$username_fieldname] = $username;
              break;
            case 'display_name':
              $display_name = $admin->StripCodeFromText($value);
              $aInputs[$name] = $display_name;
              break;
            case 'password':
              $password = \preg_replace('/[^\x20-\x7E]+$]/', '',$value);
              $aInputs[$name] = $password;
              break;
            case 'password2':
              $password2 = \preg_replace('/[^\x20-\x7E]+$]/', '',$value);
              $aInputs[$name] = $password2;
              break;
            case 'email':
              $email = $value;
              $aInputs[$name] = $email;
              break;
            case 'home_folder':
              $home_folder = $value;
              $aInputs[$name] = $home_folder;
              break;
            case 'groups':
              $aGroups    =  $value;
              $aInputs[$name] = $aGroups;
              $groups_id = (isset($aGroups) ? \implode(",", $aGroups) : '');
              $aInputs['groups_id'] = $groups_id;
              break;
            case 'active':
              $aInputs['active'] = $value;
              $active = \intval(\is_array($aInputs[$name]) ? ($aInputs[$name][0]) : $aInputs[$name]);
              $aInputs[$name] = $active;
              break;
            default:
        endswitch;
    }

    $_SESSION['users'] = $aInputs;
    $_SESSION['users']['login_name'] = $aInputs[$username_fieldname];

    if (isset($_SESSION['users'])) {
        unset($_SESSION['users'][$username_fieldname]);
        unset($_SESSION['users']['password']);
        unset($_SESSION['users']['password2']);
        unset($aInputs['groups']);
    }

    $default_language = DEFAULT_LANGUAGE;
    $default_timezone = DEFAULT_TIMEZONE;
/*----------------------------------------------------------------------------------------------------*/
    // Check values
    // Check if username already exists
    if (empty($username)){
        $sErrorMessage = $oTrans->MESSAGE_GENERIC_FILL_IN_ALL.' ('.$oTrans->TEXT_USERNAME.')';
        throw new \Exception ($sErrorMessage);
    }
    $sql  = 'SELECT `user_id` FROM `'.TABLE_PREFIX.'users` '
          . 'WHERE `username` LIKE \''.$database->escapeString($username).'\' ';
    if ($database->get_one($sql)) {
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_USERNAME_TAKEN;
        throw new \Exception ($sErrorMessage);
    }
    if (!\preg_match('/^[a-z0-9&\-.=@_]{2,}$/i', $username, $match)) {
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_NAME_INVALID_CHARS.'';
        throw new \Exception ($sErrorMessage);
    }

    if (mb_strlen($password) < 2) {
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_PASSWORD_TOO_SHORT;
        throw new \Exception ($sErrorMessage);
    }
    if ($password != $password2) {
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_PASSWORD_MISMATCH;
        throw new \Exception ($sErrorMessage);
    }

    $display_name = $admin->StripCodeFromText(($admin->get_post('display_name')));
    if (! empty($display_name))
    {
        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` ';
        $sql .= 'WHERE  `display_name` LIKE \''.$database->escapeString($display_name).'\'';
        if ($database->get_one($sql) > 0) {
            $sErrorMessage = $aErrorMessage[] = ( $oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN ? : $oTrans->MESSAGE_MEDIA_BLANK_NAME.' ('.$oTrans->TEXT_DISPLAY_NAME.')');
            throw new \Exception ($sErrorMessage);
        }
        if ((bool)$database->get_one($sql)) {
            $sErrorMessage = $aErrorMessage[] = ( @$oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN ? : $oTrans->MESSAGE_MEDIA_BLANK_NAME.' ('.$oTrans->TEXT_DISPLAY_NAME.')');
            throw new \Exception ($sErrorMessage);
        }
    } else { // display_name must be present
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_SIGNUP_NO_DISPLAY_NAME;
        throw new \Exception ($sErrorMessage);
    }

    if (! empty($email))
    {
        // Check if the email already exists
        $sql  = 'SELECT `user_id` FROM `'.TABLE_PREFIX.'users` '
              . 'WHERE `email` LIKE \''.$database->escapeString($email).'\' ';
        if ($database->get_one($sql))
        {
            if(isset($oTrans->MESSAGE_USERS_EMAIL_TAKEN))
            {
                $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_EMAIL_TAKEN;
                throw new \Exception ($sErrorMessage);
            }
        }
        if($admin->validate_email($email) == false)
        {
            $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_INVALID_EMAIL;
            throw new \Exception ($sErrorMessage);
        }
    } else { // e-mail must be present
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_SIGNUP_NO_EMAIL;
        throw new \Exception ($sErrorMessage);
    }
    if (empty($groups_id)) {
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_NO_GROUP;
        throw new \Exception ($sErrorMessage);
    }
/*----------------------------------------------------------------------------------------------------*/
// choose group_id from groups_id - workaround for still remaining calls to group_id (to be cleaned-up)
    $gid_tmp = \explode(',', $groups_id);
    if (\in_array('1', $gid_tmp)) $group_id = '1'; // if user is in administrator-group, get this group
    else $group_id = $gid_tmp[0]; // else just get the first one
    unset($gid_tmp);

    if (!\sizeof($aErrorMessage)) {
    // MD5 supplied password
    $md5_password = \md5($password);
    $now = \time();
    // Insert the user into the database
    $sql = // add the user
         'INSERT INTO `'.TABLE_PREFIX.'users` SET '
        .    '`group_id`='.\intval($group_id).', '
        .    '`groups_id`=\''.$database->escapeString($groups_id).'\', '
        .    '`active`=\''.$database->escapeString($active).'\', '
        .    '`username`=\''.$database->escapeString($username).'\', '
        .    '`password`=\''.$database->escapeString($md5_password).'\', '
        .    '`remember_key`=\'\', '
        .    '`last_reset`=0, '
        .    '`display_name`=\''.$database->escapeString($display_name).'\', '
        .    '`email`=\''.$database->escapeString($email).'\', '
        .    '`timezone`=\''.$database->escapeString($default_timezone).'\', '
        .    '`date_format`=\''.DEFAULT_DATE_FORMAT.'\', '
        .    '`time_format`=\''.DEFAULT_TIME_FORMAT.'\', '
        .    '`language`=\''.$database->escapeString($default_language).'\', '
        .    '`home_folder`=\''.$database->escapeString($home_folder).'\', '
        .    '`login_when`=\''.$now.'\', '
        .    '`login_ip`=\'\' '
        .    '';
        if (!$database->query($sql)) {}
        if ($database->is_error()) {
            $sErrorMessage = $aErrorMessage[] = $database->get_error();
            throw new \Exception ($sErrorMessage);
        }
    }

    if (isset($_SESSION['users'])) {unset($_SESSION['users']);}
    $admin->print_success($oTrans->MESSAGE_USERS_ADDED, $sAddonBackUrl);

} catch (\Exception $ex) {
    $sAddonBackUrl = ADMIN_URL.'/users/index.php';
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
    $oTrans->disableAddon();
// Print admin footer
$admin->print_footer();
