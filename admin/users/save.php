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
 * @platform        WebsiteBaker 2.13.0
 * @requirements    PHP 7.4 and higher
 * @version         $Id: save.php 250 2019-03-17 16:24:20Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/users/save.php $
 * @lastmodified    $Date: 2019-03-17 17:24:20 +0100 (So, 17. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
//use bin\requester\HttpRequester;

//  Print admin header
if (!\defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}
//  suppress to print the header, so no new FTAN will be set
    $admin  = new \admin('Access', 'users_modify', false);
    $oReg   = WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $sDomain = basename(dirname(__DIR__)).'\\'.basename(__DIR__);
    $oTrans->enableAddon($sDomain);

    $aInputs = [];
    $sErrorMessage = 'unknown error';
    $aErrorMessage = [];
// Create a back link
    $sBackLink = ADMIN_URL.'/users/index.php';
//    $sAddonBackUrl = null;

try {

    if (! SecureTokens::checkFTAN())
    {
        $admin->print_header();
        $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION).'::');
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// After check print the header
    $admin->print_header();
// get request method
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }
    $aInputs = $aRequestVars;
// Check if user id is a valid number and doesnt equal 1
    $user_id = ($admin->getIdFromRequest('user_id'));
    if (is_numeric($user_id)){
        $sUserIdKey = SecureTokens::getIDKEY($user_id);
        $sFtan = SecureTokens::getFTAN();
        $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];
        $sAddonBackUrl = ADMIN_URL.'/users/users.php?user_id='.$sUserIdKey.'&userstatus=1'.'&status=1'.'&modify=modify'.'&'.$sFtanQuery;
    }

// Gather details entered
    $groups_id = (isset($aInputs['groups']) ? \implode(",", $aInputs['groups']) : '');
    $active = $oReg->Request->getParam('active');

    $password     = \preg_replace('/[^\x20-\x7E]+$]/', '',$admin->StripCodeFromText($admin->get_post('password')));
    $password2    = \preg_replace('/[^\x20-\x7E]+$]/', '',$admin->StripCodeFromText($admin->get_post('password2')));
//TODO change if media mangement ist recoded
//    $home_folder  = ($admin->StripCodeFromText($admin->get_post('home_folder')) ?? '');
// in moment disable home folder
    $home_folder  = '';

    $display_name = ($oRequest->issetParam('display_name'))
                  ? Sanitize::StripFromText($oRequest->getParam('display_name'), Sanitize::REMOVE_DEFAULT)
                  : $admin->get_display_name();
    $display_name = \filter_var(
        $display_name,
        \FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[\w\d\x{0020}\x{002E}\x{0040}-\x{007E}\x{86c3}-\x{86c3}]+$/sui', 'default' => '']]
    );

    if (! empty($display_name))
    {
        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` '
              . 'WHERE `display_name` LIKE \''.$database->escapeString($display_name).'\' '
              .   'AND `user_id` <>'.(int)$user_id;
        if ((bool)$database->get_one($sql)) {
            $sErrorMessage = $aErrorMessage[] = ($oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN ? : $oTrans->MESSAGE_MEDIA_BLANK_NAME.' ('.$oTrans->TEXT_DISPLAY_NAME.')');
            throw new \Exception ($sErrorMessage);
        }
    } else { // display_name must be present
        $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS;
        throw new \Exception ($sErrorMessage);
    }

// Check values
    if (empty($groups_id)) {
        $aErrorMessage[] = ($oTrans->MESSAGE_USERS_NO_GROUP);
        throw new \Exception ($oTrans->MESSAGE_USERS_NO_GROUP);
    }

    if ($password != "") {
        if (strlen($password) < 6) {
            $aErrorMessage[] = ($oTrans->MESSAGE_USERS_PASSWORD_TOO_SHORT);
            throw new \Exception ($oTrans->MESSAGE_USERS_PASSWORD_TOO_SHORT);
        }
        if ($password != $password2) {
            $aErrorMessage[] = ($oTrans->MESSAGE_USERS_PASSWORD_MISMATCH);
            throw new \Exception ($oTrans->MESSAGE_USERS_PASSWORD_MISMATCH);
        }
    }
    $md5_password =  md5($password);

    $email = $admin->StripCodeFromText($admin->get_post('email'));
    if (! empty($email))
    {
        // Check if the email already exists
        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` '
              . 'WHERE `email` LIKE \''.$database->escapeString($email).'\' '
              .   'AND `user_id` <>'.(int)$user_id;
        if ((bool)$database->get_one($sql))
        {
            $sErrorMessage = $aErrorMessage[] = $oTrans->MESSAGE_USERS_EMAIL_TAKEN;
            throw new \Exception ($sErrorMessage);
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

    if (!sizeof($aErrorMessage)) {
    // Update the database
        $sql  = 'UPDATE `'.TABLE_PREFIX.'users` SET '
              . '`groups_id` = \''.$database->escapeString($groups_id).'\', '
              . '`active` = '.(int)$active.', '
              . '`display_name` = \''.$database->escapeString($display_name).'\', '
              . '`home_folder` = \''.$database->escapeString($home_folder).'\', '
              . '`email` = \''.$database->escapeString($email).'\' '
              . ((empty($password)) ? ' ': ', `password` = \''.$database->escapeString($md5_password).'\' ' )
              . 'WHERE `user_id` = '.(int)$user_id;
        if ($database->query($sql)) {
        }
        if ($database->is_error()) {
            $sErrorMessage = $aErrorMessage[] = $database->get_error();
            throw new \Exception ($sErrorMessage);
        }
    }

    if (isset($_SESSION['users'])) {unset($_SESSION['users']);}
    $admin->print_success($oTrans->MESSAGE_USERS_SAVED, $sBackLink);

} catch (\Exception $ex) {
    $sAddonBackUrl = ($sAddonBackUrl ?? $sBackLink);
    $sErrMsg = Precheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
    $oTrans->disableAddon();

// Print admin footer
$admin->print_footer();
