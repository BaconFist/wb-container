<?php
/**
 *
 * @category        admin
 * @package         preferences
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 7.2.0 and higher
 * @version         $Id: save.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/preferences/save.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

function save_preferences()
{
    $oReg     = WbAdaptor::getInstance();
    $oDB      = $oReg->getDatabase();
    $database = $oDB;
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $admin    = $oReg->getApplication();
    $oTrans->enableAddon($oReg->AcpDir.'/users');

    $err_msg  = [];
    $iMinPassLength  = 6;
    $bPassRequest    = false;
    $bMailHasChanged = false;
// first check form-tan
    if (!$admin->checkFTAN()){ $err_msg[] = $oTrans->MESSAGE_GENERIC_SECURITY_ACCESS; }
    $sLanguagesAddonDefaultFile = WB_PATH.'/account/languages/EN.php';
    if (\is_readable($sLanguagesAddonDefaultFile)){include $sLanguagesAddonDefaultFile;}
    $sLanguagesAddonFile = WB_PATH.'/account/languages/'.LANGUAGE.'.php';
    if (\is_readable($sLanguagesAddonFile)){include $sLanguagesAddonFile;}
// Get entered values and validate all
// remove any dangerouse chars from display_name
//    $display_name = strip_tags( $admin->StripCodeFromText($admin->get_post('display_name')));
//    $display_name     = ( $display_name == '' ? $admin->get_display_name() : $display_name );
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
        // check that display_name is unique in whoole system (prevents from User-faking)
        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` ';
        $sql .= 'WHERE `user_id` <> '.(int)$admin->getUserId().' AND `display_name` LIKE "'.$database->escapeString($display_name).'"';
        if ($database->get_one($sql) > 0 ){
            $err_msg[] = ( isset($oTrans->MESSAGE_USERS_DISPLAYNAME_TAKEN) ? $oTrans->MESSAGEUSERS_DISPLAYNAME_TAKEN : $oTrans->MESSAGE_MEDIA_BLANK_NAME.' ('.$oTrans->TEXT_DISPLAY_NAME.')');
        }
    }
    else
    {
        $err_msg[] = ( isset($oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS) ? $oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS : $oTrans->MESSAGE_USERS_DISPLAYNAME_INVALID_CHARS.' ('.$oTrans->TEXT_DISPLAY_NAME.')');
    }
// language must be 2 upercase letters only
//    $language         = \strtoupper($admin->get_post('language'));
//    $language         = (\preg_match('/^[A-Z]{2}$/', $language) ? $language : DEFAULT_LANGUAGE);
    $language = strtoupper($oRequest->getParam(
        'language',
        \FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]{2}$/si', 'default' => 'EN']]
    ));
// timezone must be between -12 and +13  or -20 as system_default
//    $user_time = true;
//    $timezone         = \filter_var($admin->get_post('timezone'),FILTER_VALIDATE_INT);
//    $timezone         = (\is_numeric($timezone) ? $timezone : DEFAULT_TIMEZONE/3600);
//    $timezone         = (($timezone >= -12 && $timezone <= 13) ? $timezone : DEFAULT_TIMEZONE/3600) * 3600;
    $user_time = true;
    $timezone = $oRequest->getParam(
        'timezone',
        \FILTER_VALIDATE_INT,
        ['options' => ['min_range' => -12, 'max_range' => 13, 'default' => (DEFAULT_TIMEZONE/3600)]]
    )* 3600;//

// date_format must be a key from /interface/date_formats
    include( ADMIN_PATH.'/interface/date_formats.php' );
    $date_format = $oRequest->getParam('date_format');
    $date_format = ($date_format ?? DEFAULT_DATE_FORMAT);
    $date_format = (array_key_exists(str_replace(' ', '|', $date_format), $DATE_FORMATS)) ? $date_format : DEFAULT_DATE_FORMAT;
    $date_format = (($date_format !== 'system_default') ? $date_format : DEFAULT_DATE_FORMAT);
    unset($DATE_FORMATS);

// time_format must be a key from /interface/time_formats
    include( ADMIN_PATH.'/interface/time_formats.php' );
    $time_format = $oRequest->getParam('time_format');
    $time_format = ($time_format ?? DEFAULT_TIME_FORMAT);
    $time_format = (array_key_exists(str_replace(' ', '|', $time_format), $TIME_FORMATS)) ? $time_format : DEFAULT_TIME_FORMAT;
    $time_format = (($time_format !== 'system_default') ? $time_format : DEFAULT_TIME_FORMAT);
    unset($TIME_FORMATS);


// email should be validatet by core
    $email = \trim( $admin->get_post('email') == null ? '' : $admin->get_post('email') );
    if ((!$admin->validate_email($email)) )
    {
        $email = '';
        $err_msg[] = $MESSAGE['USERS_INVALID_EMAIL'];
    }elseif ($email != '') {
            $sql = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                 . 'WHERE `user_id` = '.(int)$admin->getUserId().' AND `email` LIKE \''.$email.'\'';
            $IsOldMail = $database->get_one($sql);
        // check that email is unique in whoole system
            $email = $admin->add_slashes($email);
            $sql = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                 . 'WHERE `user_id` <> '.(int)$admin->getUserId().' AND `email` LIKE \''.$email.'\'';
            $checkMail = $database->get_one($sql);
            if( $checkMail == $email ){ $err_msg[] = $oTrans->MESSAGE_USERS_EMAIL_TAKEN; }
            $bMailHasChanged = ($email != $IsOldMail);
        }

// receive password vars and calculate needed action
    $sCurrentPassword = \preg_replace('/[^\x20-\x7E]+$]/', '',$admin->StripCodeFromText($admin->get_post('current_password')));
    $sCurrentPassword = (\is_null($sCurrentPassword) ? '' : $sCurrentPassword);
    $sNewPassword = \preg_replace('/[^\x20-\x7E]+$]/', '',$admin->StripCodeFromText($admin->get_post('new_password_1')));
    $sNewPassword = (\is_null($sNewPassword) ? '' : $sNewPassword);
    $sNewPasswordRetyped = \preg_replace('/[^\x20-\x7E]+$]/', '',$admin->StripCodeFromText($admin->get_post('new_password_2')));
    $sNewPasswordRetyped= (\is_null($sNewPasswordRetyped) ? '' : $sNewPasswordRetyped);

    if($bMailHasChanged == true)
    {
        $bPassRequest = $bMailHasChanged;
    } else {
        $bPassRequest = ( ( $sCurrentPassword != '') || ($sNewPassword != '') || ($sNewPasswordRetyped != '') ) ? true : false;
    }

// Check existing password
    $sql  = 'SELECT `password` ';
    $sql .= 'FROM `'.TABLE_PREFIX.'users` ';
    $sql .= 'WHERE `user_id` = '.$admin->getUserId();
    if ( $bPassRequest && \md5($sCurrentPassword) != $database->get_one($sql)) {
// access denied
        $err_msg[] = $oTrans->MESSAGE_PREFERENCES_CURRENT_PASSWORD_INCORRECT;
    }else {
// validate new password
        $sPwHashNew = false;
        if($sNewPassword != '') {
            if (\strlen($sNewPassword) < $iMinPassLength) {
                $err_msg[] = $oTrans->MESSAGE_USERS_PASSWORD_TOO_SHORT;
            }else {
                if ($sNewPassword != $sNewPasswordRetyped) {
                    $err_msg[] = $oTrans->MESSAGE_USERS_PASSWORD_MISMATCH;
                }else {
                    $pattern = '/[^'.$admin->password_chars.']/';
                    if (\preg_match($pattern, $sNewPassword)) {
                        $err_msg[] = $oTrans->MESSAGE_PREFERENCES_INVALID_CHARS;
                    }else {
                        $sPwHashNew = \md5($sNewPassword);
                    }
                }
            }
        }
// if no validation errors, try to update the database, otherwise return errormessages
        if (\sizeof($err_msg) == 0)
        {
            $sql  = 'UPDATE `'.TABLE_PREFIX.'users` ';
            $sql .= 'SET `display_name`=\''.$database->escapeString($display_name).'\', ';
            if($sPwHashNew) {
                $sql .=     '`password`=\''.$database->escapeString($sPwHashNew).'\', ';
            }
            if($email != '') {
                $sql .=     '`email`=\''.$database->escapeString($email).'\', ';
            }
            $sql .= '`language`=\''.$database->escapeString($language).'\', '
                  . '`timezone`='.(int)$timezone.', '
                  . '`date_format`=\''.$database->escapeString($date_format).'\', '
                  . '`time_format`=\''.$database->escapeString($time_format).'\' '
                  . 'WHERE `user_id`='.(int)$admin->getUserId();
            if( $database->query($sql) )
            {

                // update successfull, takeover values into the session
                $_SESSION['DISPLAY_NAME'] = $display_name;
                $_SESSION['LANGUAGE'] = ($language);
                $_SESSION['TIMEZONE'] = (empty($timezone) ? $oReg->defaultTimeZone : $timezone);
                $_SESSION['DATE_FORMAT'] = (empty($date_format) ? $oReg->DefaultDateFormat : str_replace('|', ' ', $date_format));
                $_SESSION['TIME_FORMAT'] = (empty($time_format) ? $oReg->DefaultTimeFormat : str_replace('|', ' ', $time_format));
                $_SESSION['EMAIL'] = $email;
/*
                // Update date format
                if($date_format != '') {
                    $_SESSION['DATE_FORMAT'] = $date_format;
                    if (isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) { unset($_SESSION['USE_DEFAULT_DATE_FORMAT']); }
                } else {
                    $_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
                    $_SESSION['DATE_FORMAT'] = $oReg->DefaultDateFormat;
//                    if (isset($_SESSION['DATE_FORMAT'])) { unset($_SESSION['DATE_FORMAT']); }
                }
                // Update time format
                if($time_format != '') {
                    $_SESSION['TIME_FORMAT'] = $time_format;
                    if (isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) { unset($_SESSION['USE_DEFAULT_TIME_FORMAT']); }
                } else {
                    $_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
//                    if (isset($_SESSION['TIME_FORMAT'])) { unset($_SESSION['TIME_FORMAT']); }
                }
*/
            }else {
                $err_msg[] = 'invalid database UPDATE call in '.__FILE__.'::'.__FUNCTION__.'before line '.__LINE__;
            }
        }
    }
    return ((\sizeof($err_msg) > 0) ? \implode('<br />', $err_msg) : '' );
}
/* ------------------------------------------------------------------------------------- */
if (!\defined('SYSTEM_RUN')) {require(\dirname(\dirname((__DIR__))).'/config.php');}
// suppress to print the header, so no new FTAN will be set obselete in newer version
    $admin = new \admin('Preferences','start', false);
    $oReg   = WbAdaptor::getInstance();
    $retval = save_preferences();
    if ($retval == '')
    {
        // print the header
        $admin->print_header();
        $admin->print_success($oTrans->MESSAGE_PREFERENCES_DETAILS_SAVED);
        $admin->print_footer();
    }else {
        // print the header
        $admin->print_header();
        $admin->print_error($retval);
    }
