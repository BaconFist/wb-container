<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 7.2 and higher
 * @version         $Id: signup2.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/signup2.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,Parentlist};
use vendor\phplib\Template;

// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
//  Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $database = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
//  load module default language file (EN)
    $sAddonName = basename(__DIR__);
    $oTrans->enableAddon('account');

    if (!SecureTokens::checkFTAN ()) {
        $msg = $oTrans->MESSAGE_GENERIC_NOT_FOUND_ACCESS;
        throw new \Exception (sprintf($msg, ''));
    }
    if (!function_exists('ObfuscateIp')) {
        function ObfuscateIp() {
            $sClientIp = (isset($_SERVER['REMOTE_ADDR']))
                                 ? $_SERVER['REMOTE_ADDR'] : '000.000.000.000';
            $iClientIp = ip2long($sClientIp);
            $sClientIp = long2ip(($iClientIp & ~65535));
            return $sClientIp;
        }
    }
    if (!function_exists('emailAdmin')) {
        function emailAdmin() {
            $database = \database::getInstance();
            $retval = false;
            $sql  = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` ';
            $sql .= 'WHERE `user_id`=\'1\' ';
            if(!($retval = $database->get_one($sql))){
                $retval = false;
            }
            return $retval;
        }
    }
    if (!function_exists("replace_all")) {
        function replace_all (&$aArray, $aStr = "") {
            foreach($aArray as $k=>$v) {$aStr = str_replace("{{".$k."}}", $v, $aStr);}
            return $aStr;
        }
    }
//  Get details entered
    $groups_id = FRONTEND_SIGNUP;
    $active = 1;
    $error = [];
    $aSuccess = [];
/*--------------------------------------------------------------------------------*/
//    $username = strtolower(strip_tags($wb->get_post('username')));
//    $display_name = strip_tags($wb->get_post('display_name'));
//    $email = $wb->get_post('email');
    $username     = strtolower($oRequest->getParam('username',FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    //$bDisplay_name = ($oRequest->issetParam('display_name'));
    //$display_name = $oRequest->getParam('display_name',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $display_name = Sanitize::StripFromText($oRequest->getParam('display_name'), Sanitize::REMOVE_DEFAULT);
    $email        = $oRequest->getParam('email',FILTER_SANITIZE_EMAIL);
//  test the messages
/*
    $_SESSION['display_form']=false;
    $aSuccess[] = $oTrans->MESSAGE['FORGOT_PASS_PASSWORD_RESET'];
    $error[]   = $oTrans->MESSAGE['USERS_NAME_INVALID_CHARS']."\n";
    $error[]   = $oTrans->MESSAGE['SIGNUP_NO_EMAIL']."\n";
*/
/*--------------------------------------------------------------------------------*/
//  Check if username already exists
    $sql = 'SELECT `user_id` FROM `'.TABLE_PREFIX.'users` '
         . 'WHERE `username` = \''.$database->escapeString($username).'\'';
    if ($database->get_one($sql)) {
        $error[] = $oTrans->MESSAGE_USERS_USERNAME_TAKEN."\n";
    }
//    if (!preg_match('/^[a-z]{1}[a-z0-9_-]{2,}$/i', $username)) {
// min 2 chars because backward compbility
    if (!\preg_match('/^[a-z0-9&\-.=@_]{2,}$/i', $admin->StripCodeFromText($username), $match)) {
        $error[] =  $oTrans->MESSAGE_USERS_NAME_INVALID_CHARS."\n";
    }
/*
    if (preg_match('/^[\w\d\x{0020}\x{002E}\x{0040}-\x{007E}\x{86c3}-\x{86c3}]+$/siu', $admin->StripCodeFromText($display_name))) {
        $error[] =  sprintf("%s [%s]\n",$oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS,$display_name);
    }
    $sql = '
    SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users`
    WHERE  `display_name` LIKE \''.$database->escapeString($display_name, '_%').'\'
    ';

    if ($database->get_one($sql) > 0) {
        $error[] = $oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN.'';
    }
*/

    $display_name = \filter_var(
        $display_name,
        \FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[\w\d\x{0020}\x{002E}\x{0040}-\x{007E}\x{86c3}-\x{86c3}]+$/siu', 'default' => '']]
    );

    if (! empty($display_name))
    {
        $sql  = '
        SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users`
        WHERE `display_name` LIKE \''.$database->escapeString($display_name).'\'
        ';
        if ((bool)$database->get_one($sql)) {
            $error[] =  sprintf("%s [%s]\n",$oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN,$display_name);
        }
    } else { // display_name must be present
            $error[] =  sprintf("%s [%s]\n",$oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS,$display_name);
    }

    if (!empty($email)) {
        if ($wb->validate_email($email) == false) {
            $error[] = $oTrans->MESSAGE_USERS_INVALID_EMAIL."\n";
        }
    } else {
        $error[] = $oTrans->MESSAGE_SIGNUP_NO_EMAIL."\n";
    }
    $search  = ['{SERVER_EMAIL}'];
    $replace = [SERVER_EMAIL];
/*--------------------------------------------------------------------------------*/
//  Captcha
    if ($wb->bit_isset(ENABLED_CAPTCHA,1)){
        $aSuR = [
            '{SERVER_EMAIL}' => SERVER_EMAIL,
        ];
        $sOut = str_replace(array_keys($aSuR), $aSuR,  $oTrans->MESSAGE_MOD_FORM_INCORRECT_CAPTCHA);
//        $oTrans->MESSAGE_MOD_FORM_INCORRECT_CAPTCHA = $sOut;
        $sCaptcha = $oRequest->getParam('captcha',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (isset($sCaptcha) && $sCaptcha != ''){
            // Check for a mismatch
            if (!isset($sCaptcha) || !isset($_SESSION['captcha']) || ($sCaptcha != $_SESSION['captcha'])) {
                $error[] = $sOut."\n";
            }
        } else {
                $error[] = $sOut."\n";
        }
    }
    if (isset($_SESSION['captcha'])) { unset($_SESSION['captcha']); }
/*--------------------------------------------------------------------------------*/
    $data_protection = $oRequest->getParam('data_protection',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($wb->bit_isset($aSettings['use_data_protection'],1)) {
        if (isset($data_protection) && ($data_protection != '')){
        } else {
           $error[] = $oTrans->MESSAGE_DSGVO_ERROR;
        }
    }
/*--------------------------------------------------------------------------------*/
//    if (!\class_exists('PasswordHash')){require(WB_PATH.'/framework/PasswordHash.php');}
    $pwh = new \App\PasswordHash(0, true);
// Generate a random password then update the database with it
    $new_pass = $pwh->NewPassword();
// Check if the email already exists
    $sql = 'SELECT `user_id` FROM `'.TABLE_PREFIX.'users` '
         . 'WHERE `email` = \''.$database->escapeString($email).'\'';
    if ($database->get_one($sql)) {
        if (isset($oTrans->MESSAGE_USERS_EMAIL_TAKEN)) {
            $error[] = $oTrans->MESSAGE_USERS_EMAIL_TAKEN."\n";
        } else {
            $error[] = $oTrans->MESSAGE_USERS_INVALID_EMAIL."\n";
        }
    }
/*--------------------------------------------------------------------------------*/
    if (sizeof($error)==0){
        $get_ip = ObfuscateIp();
        $get_ts = time();
    //  MD5 supplied password
        $md5_password = $pwh->HashPassword($new_pass, true);
    //  Insert the user into the database
        $sql  = 'INSERT INTO `'.TABLE_PREFIX.'users` SET '
              . '`group_id` = '.$database->escapeString($groups_id).', '
              . '`groups_id` = \''.$database->escapeString($groups_id).'\', '
              . '`active` = '.$database->escapeString($active).', '
              . '`username` = \''.$database->escapeString($username).'\', '
              . '`password` = \''.$database->escapeString($md5_password).'\', '
              . '`display_name` = \''.$database->escapeString($display_name).'\', '
              . '`home_folder` = \'\', '
              . '`email` = \''.$database->escapeString($email).'\', '
              . '`timezone` = \''.$database->escapeString(DEFAULT_TIMEZONE).'\', '
               .'`date_format`=\''.DEFAULT_DATE_FORMAT.'\', '
              . '`time_format`=\''.DEFAULT_TIME_FORMAT.'\', '
              . '`language` = \''.$database->escapeString(DEFAULT_LANGUAGE).'\', '
              . '`login_when` = \''.$get_ts.'\', '
              . '`login_ip` = \''.$get_ip.'\' '
              .'';
        if (!$bLocalDebug && ($database->query($sql))){}
        if (($database->is_error())) {
    //  Error updating database
            $error[] = $database->get_error();
        }
       if (sizeof($error)==0){
        //  get user_id for admin mail
            $user_id = $database->getLastInsertId();
//  WB_MAILER settings
            $sServerEmail = (defined('SERVER_EMAIL') && SERVER_EMAIL != '' ? SERVER_EMAIL : emailAdmin());
//  Setup email to send
            $email_to = $mail_to = $email;
            $sDomain = parse_url(WB_URL, PHP_URL_HOST);
            $aSuR = [
                '{SERVER_EMAIL}' => $sServerEmail,
                '{LOGIN_DISPLAY_NAME}' => $display_name,
                '{LOGIN_WEBSITE_TITLE}' => WEBSITE_TITLE,
                '{LOGIN_NAME}' => 'xxxxxxxxx',
                '{LOGIN_PASSWORD}' => $new_pass,
                '{{WB_URL}}' => $sDomain,
            ];
            $mail_subject = str_replace(array_keys($aSuR), $aSuR, $oTrans->MESSAGE_SIGNUP2_SUBJECT_LOGIN_INFO);
            $mail_message = str_replace(array_keys($aSuR), $aSuR, $oTrans->MESSAGE_SIGNUP2_BODY_LOGIN_INFO);
        //  Try sending the email
            $bSendMailToUser = true;
            if (!$bLocalDebug){
                $bSendMailToUser = ($wb->mail(SERVER_EMAIL, $mail_to, $mail_subject, $mail_message));
            }
            if ($bSendMailToUser) {
                $aSuccess[] = $oTrans->MESSAGE_SIGNUP2_SUBJECT_NEW_USER;
                $aSuccess[] = $oTrans->MESSAGE_FORGOT_PASS_PASSWORD_RESET;
                $sWebMailer   = (defined('WBMAILER_DEFAULT_SENDERNAME') && WBMAILER_DEFAULT_SENDERNAME != '' ? WBMAILER_DEFAULT_SENDERNAME : 'WebsiteBaker Mailer');
            //  first send to admin
                $bSendRegistrationMailtoAdmin = false;
                $mail_replyto   = $email_to;
                $email_fromname = $mail_replyName = $display_name;
                $aSuR = [
                    '{SERVER_EMAIL}' => $sServerEmail,
                    '{LOGIN_EMAIL}' => $email_to,
                    '{DISPLAY_NAME}' => $oTrans->TEXT_DISPLAY_NAME,
                    '{LOGIN_ID}' => $email_fromname.' ('.$user_id.')',
                    '{SIGNUP_DATE}' => date(DATE_FORMAT.' '.TIME_FORMAT,$get_ts ),
                    '{LOGIN_NAME}' => $username,
                    '{WB_URL}' => $sDomain,
                ];
                $mail_message  = str_replace(array_keys($aSuR), $aSuR, $oTrans->MESSAGE_SIGNUP2_ADMIN_INFO);
                $email_subject = str_replace(array_keys($aSuR), $aSuR, $oTrans->MESSAGE_SIGNUP2_NEW_USER);
                $mail_message  = str_replace($search, $replace, $mail_message);
                $email_body    = preg_replace( '/(content-type:|bcc:|cc:|to:|from:)/im', '', $mail_message );
                $success_email_to = ((defined('OWNER_EMAIL') && OWNER_EMAIL != '') ? OWNER_EMAIL : emailAdmin());
                if (!$bLocalDebug){
                    $bSendRegistrationMailtoAdmin = $wb->mail($sServerEmail, $success_email_to, $email_subject, $email_body, $mail_replyName, $mail_replyto);
                }
                $display_form = $bSendRegistrationMailtoAdmin;
            } else {
                $sql = 'DELETE FROM `'.TABLE_PREFIX.'users` '
                     . 'WHERE `username` = \''.$database->escapeString($username).'\'';
                $database->query($sql);
                $error[] = $oTrans->MESSAGE_FORGOT_PASS_CANNOT_EMAIL."\n";
            }
        }
    }
    $_SESSION['display_form'] = ((sizeof($aSuccess) == 0) ? true : false);
