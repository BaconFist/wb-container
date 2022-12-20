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
 * Description of admin/login/forgot/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 193 2019-01-29 17:31:29Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use vendor\phplib\Template;

// Include the configuration file
if (!\defined('SYSTEM_RUN') ){ require(\dirname(\dirname(\dirname(__DIR__)))."/config.php"); }

// Include the database class file and initiate an object
$admin = new \admin('Start', 'start', false, false);

// Check if the user has already submitted the form, otherwise show it
if (isset($_POST['email']) && $_POST['email'] != "") {
    if (!\bin\SecureTokens::checkFTAN ()) {
      #
    }
    $email = \htmlspecialchars($_POST['email'],ENT_QUOTES);
    // Check if the email exists in the database
    $query = 'SELECT `user_id`, `username`, `display_name`, `email`, `last_reset`, `password` FROM `'.TABLE_PREFIX.'users` '
    . 'WHERE `email` = \''.$database->escapeString($_POST['email']).'\'';
    $oRes = $database->query($query);
    if($oRes->numRows() > 0) {
        // Get the id, username, email, and last_reset from the above db query
        $results_array = $oRes->fetchRow(MYSQLI_ASSOC);
        // Check if the password has been reset in the last 2 hours
        $last_reset = $results_array['last_reset'];
        $time_diff = \time()-$last_reset; // Time since last reset in seconds
        $time_diff = $time_diff/60/60; // Time since last reset in hours
        if($time_diff < 2) {
            // Tell the user that their password cannot be reset more than once per hour
            $message = $MESSAGE['FORGOT_PASS_ALREADY_RESET'];
        } else {
            $pwh = new \PasswordHash(0, true);
            $old_pass = $results_array['password'];
            // Generate a random password then update the database with it
                $new_pass = $pwh->NewPassword();
/*  */
                $sql  = 'UPDATE `'.TABLE_PREFIX.'users` SET '
                      . '`password`=\''.$database->escapeString($pwh->HashPassword($new_pass, true)).'\', '
                      . '`last_reset`='.\time().' '
                      . 'WHERE `user_id`='.(int)$results_array['user_id'];

            unset($pwh); // destroy $pwh-Object
            $database->query($sql);
            if($database->is_error()) {
                // Error updating database
                $message = $database->get_error();
            } else {
                // Setup email to send
                $mail_to = $email;
                $mail_subject = $MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'];
                // Replace placeholders from language variable with values
                $search = array('{LOGIN_DISPLAY_NAME}', '{LOGIN_WEBSITE_TITLE}', '{LOGIN_NAME}', '{LOGIN_PASSWORD}');
                $replace = array($results_array['display_name'], WEBSITE_TITLE, 'xxxxxxxxxx', $new_pass);
                $mail_message = \str_replace($search, $replace, $MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT']);
                // Try sending the email
                if($admin->mail( SERVER_EMAIL, $mail_to, $mail_subject, $mail_message )) {
                    $message = $MESSAGE['FORGOT_PASS_PASSWORD_RESET'];
                    $display_form = false;
                } else {
                    $sql = 'UPDATE `'.TABLE_PREFIX.'users` SET '
                    . '`password` = \''.$database->escapeString($old_pass).'\' '
                    . 'WHERE `user_id` = '.$results_array['user_id'].'';
//                    $database->query("UPDATE ".TABLE_PREFIX."users SET password = '".$old_pass."' WHERE user_id = '".$results_array['user_id']."'");
                    $database->query($sql);
                    $message = $MESSAGE['FORGOT_PASS_CANNOT_EMAIL'];
                }
            }
        }
    } else {
        // Email doesn't exist, so tell the user
        $message = $MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'];
        // and delete the wrong Email
        $email = '';
    }
} else {
    $email = '';
}

if (!isset($message)) {
    $message = $MESSAGE['FORGOT_PASS_NO_DATA'];
    $message_color = '000000';
} else {
    $message_color = 'FF0000';
}
    $aSettings = ['website_title' => 'none','jquery_version'=> ''];
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'settings` '
         . 'WHERE `name` IN (\'website_title\',\'jquery_version\') ';
    if ($oSetting = $database->query($sql)) {
        while ( $aSetting = $oSetting->fetchRow(MYSQLI_ASSOC)){
          $aSettings[$aSetting['name']] = $aSetting['value'];
        }
    }
    if ($database->is_error()){
        throw new \DatabaseException($database->get_error());
    }
    $bShowOldstyle = true;
    $jquery_version = (isset($aSettings['jquery_version']) && !empty(\trim($aSettings['jquery_version'])) ? $aSettings['jquery_version'] : '1.12.4').'/';
    $sTemplateFile = $admin->correct_theme_source('login_forgot.htt');
    $template = new Template(\dirname($sTemplateFile));
// Setup template object, parse vars to it, then parse it
    $template->set_file('page', 'login_forgot.htt');
    $template->set_block('page', 'main_block', 'main');

    $template->set_block('main_block', 'ForgotBlockNoscript', 'ForgotNoscript');
    $template->set_block('main_block', 'ForgotBlockPanel', 'ForgotPanel');
    $template->set_block('main_block', 'ForgotBlockScript', 'ForgotScript');

    if (\defined('FRONTEND')) {
        $template->set_var('ACTION_URL', 'forgot.php');
    } else {
        $template->set_var('ACTION_URL', 'index.php');
    }
    $template->set_var('EMAIL', $email);

    if(isset($display_form)) {
        $template->set_var('DISPLAY_FORM', 'display:none;');
    }
/*------------------------------------------------------------------------------------*/
        $sTemplateFunc = 'resolveTemplateImagesPath';
        $sImages       = $sTemplateFunc($oReg->Theme);
/*------------------------------------------------------------------------------------*/

    $aHeaderData = [
        'SECTION_FORGOT'     => $MENU['FORGOT'],
        'WEBSITE_TITLE'      => $aSettings['website_title'],
        'MESSAGE_COLOR'      => $message_color,
        'MESSAGE'            => $message,
        'WB_URL'             => WB_URL,
        'ADMIN_URL'          => ADMIN_URL,
        'THEME_URL'          => THEME_URL,
        'WB_URL'             => WB_URL,
        'ADMIN_URL'          => ADMIN_URL,
        'THEME_URL'          => THEME_URL,
        'IMAGES'             => $sImages,
        'TEXT_HOME'          => $TEXT['HOME'],
        'TEXT_SAVE'          => $TEXT['SAVE'],
        'TEXT_RESET'         => $TEXT['RESET'],
        'HELPER_URL'         =>  WB_URL.'/framework/helpers',
        'JQUERY_VERSION'     => $jquery_version,
        'LANGUAGE'           => \strtolower(LANGUAGE),
        'TEXT_EMAIL'         => $TEXT['EMAIL'],
        'TEXT_SEND_DETAILS'  => $TEXT['SEND_DETAILS'],
        'TEXT_NEED_TO_LOGIN' => $TEXT['NEED_TO_LOGIN'],
    ];
    $template->set_var($aHeaderData);

    if (\defined('FRONTEND')) {
        $template->set_var('LOGIN_URL', WB_URL.'/account/login.php');
    } else {
        $template->set_var('LOGIN_URL', ADMIN_URL.'/login/index.php');
    }
    $template->set_var('INTERFACE_URL', ADMIN_URL.'/interface');

    if (\defined('DEFAULT_CHARSET')) {
        $charset=DEFAULT_CHARSET;
    } else {
        $charset='utf-8';
    }
    $template->set_var('CHARSET', $charset);

    if ($bShowOldstyle){
        $template->parse('ForgotNoscript', 'ForgotBlockNoscript', true);
        $template->set_block('ForgotBlockPanel', '');
        $template->set_block('ForgotBlockScript', '');
    } else {
        $template->parse('ForgotPanel', 'ForgotBlockPanel', true);
        $template->parse('ForgotScript', 'ForgotBlockScript', true);
        $template->set_block('ForgotBlockNoscript', '');
    }

    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
