<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 7.4 and higher
 * @version         $Id: forgot_form.php 346 2019-05-07 13:42:36Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/forgot_form.php $
 * @lastmodified    $Date: 2019-05-07 15:42:36 +0200 (Di, 07. Mai 2019) $
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;

/*--------------------------------------------------------------------------------*/
// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/*--------------------------------------------------------------------------------*/
//  Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();

    $aRequestVars  = $oApp->getRequestVars();
    //$oRequest = \bin\requester\HttpRequester::getInstance();
    if (!isset($page_id)){
        $page_id = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
        $page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID'] > 0) ? $_SESSION['PAGE_ID'] : $page_id);
    }
    $sCallingScript = ($_SESSION['HTTP_REFERER'] ?? $oReg->AppUrl);
    $redirect_url = (isset($redirect) && (!empty($redirect)) ? $redirect : $sCallingScript);
/*
    $redirect_url = ((isset($_SESSION['HTTP_REFERER']) && !empty($_SESSION['HTTP_REFERER'])) ? $_SESSION['HTTP_REFERER'] : $sCallingScript );
    $redirect_url = (isset($redirect) && ($redirect!='') ? $redirect : $redirect_url);
*/
    $action = $oRequest->getParam('action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email  = $oRequest->getParam('email',FILTER_SANITIZE_EMAIL);
//    if (!function_exists('xnl2br')){include(WB_PATH .'/framework/functions.php');}

/*--------------------------------------------------------------------------------*/
    $errMsg   = [];
    $aSuccess  = [];
    $aMessage = [];
//  load module default language file (EN)
    $sAddonName = basename(__DIR__);
    $oTrans = Translate::getInstance();
    $oTrans->enableAddon('\\account');
/*--------------------------------------------------------------------------------*/
//  load gdpr/dsgvo settings from db or ini file (found in your frontend template root)
    if (!$sSettings = \bin\helpers\ParentList::gdprSettings()){
        $sInifile     = 'templates/'.TEMPLATE.'/DataProtection.ini.php';
        $sIniUserfile = 'templates/'.TEMPLATE.'/DataUserProtection.ini.php';
        if (is_readable($oReg->AppPath .$sIniUserfile)){
            $sInifile = $sIniUserfile;
        }
        if (is_readable($oReg->AppPath .$sInifile)){
            $aTmp = \parse_ini_file($oReg->AppPath .$sInifile, true, INI_SCANNER_TYPED);
            $aSettings = $aTmp['dsgvo'];
         }
    } else {
      $aSettings = \bin\helpers\ParentList::unserialize($sSettings);
    }
/*--------------------------------------------------------------------------------*/
    $display_form = true;
    if ($action=='send') {
        $search  = ['{SERVER_EMAIL}'];
        $replace = [$oReg->ServerEmail];
/*--------------------------------------------------------------------------------*/
//  Captcha
        if ($wb->bit_isset($oReg->EnabledCaptcha,4)){
            $aSuR = [
                '{SERVER_EMAIL}' => $oReg->ServerEmail,
            ];
            $sOut = str_replace(array_keys($aSuR), $aSuR,  $oTrans->MESSAGE_MOD_FORM_INCORRECT_CAPTCHA);
//          $oTrans->MESSAGE_MOD_FORM_INCORRECT_CAPTCHA = $sOut;
            $sCaptcha = $oRequest->getParam('captcha',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (isset($sCaptcha) && !empty($sCaptcha)){
                // Check for a mismatch
                if (!isset($sCaptcha) || !isset($_SESSION['captcha']) || ($sCaptcha != $_SESSION['captcha'])) {
                    $errMsg[] = $sOut."\n";
                }
            } else {
                    $errMsg[] = $sOut."\n";
            }
        }
        if (isset($_SESSION['captcha'])) {unset($_SESSION['captcha']);}
/*--------------------------------------------------------------------------------*/
//    if ($aSettings['use_data_protection']) {
        $data_protection = $oRequest->getParam('data_protection',FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($wb->bit_isset($aSettings['use_data_protection'],4)) {
            if (isset($data_protection) && ($data_protection != '')){
            } else {
               $errMsg[] = $oTrans->MESSAGE_DSGVO_ERROR;
            }
        }
/*--------------------------------------------------------------------------------*/
        if (isset($email)){
            if (!\bin\SecureTokens::checkFTAN ()) {
                throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
            }
        }
/*--------------------------------------------------------------------------------*/
        $display_form = true;
        $aMessage[] = $MESSAGE['FORGOT_PASS_NO_DATA'];
/*--------------------------------------------------------------------------------*/
// testing the messages
/*
    $errMsg[] = $MESSAGE['FORGOT_PASS_ALREADY_RESET'];
    $errMsg[] = $MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'];
    $aSuccess[] = $MESSAGE['FORGOT_PASS_PASSWORD_RESET'];
    $display_form = false;
*/
        if ((isset($email) && $email != "")){
        //  $email = strip_tags($oRequest->email);
            if($admin->validate_email($email) == false)
            {
                $errMsg[] = $MESSAGE['USERS_INVALID_EMAIL'];
                $email = '';
            } else {
            //  Check if the email exists in the database
                $sql  = '
                SELECT
                `user_id`,`username`,`display_name`,`email`,`last_reset`,`password`
                FROM `'.$oReg->TablePrefix.'users`
                WHERE `email`=\''.$database->escapeString($email).'\'
                ';
                if (($results = $database->query($sql)))
                {
                    if(($results_array = $results->fetchRow(\MYSQLI_ASSOC)))
                    { // Get the id, username, email, and last_reset from the above db query
            // Check if the password has been reset in the last 2 hours
                        if ((time() - (int)$results_array['last_reset']) < (2 * 3600)) {
                        // Tell the user that their password cannot be reset more than once per hour
                            $errMsg[] = $MESSAGE['FORGOT_PASS_ALREADY_RESET'];
                        } else {
                            // if (!\class_exists('PasswordHash')){require(WB_PATH.'/framework/PasswordHash.php');}
                            $pwh = new \App\PasswordHash(0, true);
                            $old_pass = $results_array['password'];
                        // Generate a random password then update the database with it
                            $new_pass = $pwh->NewPassword();
                            $sql  = 'UPDATE `'.TABLE_PREFIX.'users` SET '
                                  . '`password`=\''.$database->escapeString($pwh->HashPassword($new_pass, true)).'\', '
                                  . '`last_reset`='.time().' '
                                  . 'WHERE `user_id`='.(int)$results_array['user_id'];
                            unset($pwh); // destroy $pwh-Object
                            if ($database->query($sql))
                            { // Setup email to send
                                $mail_to = $email;
                                $sDomain = parse_url($oReg->AppUrl, \PHP_URL_HOST);
                                $mail_subject = str_replace('WB', $sDomain,$MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO']);
                            // Replace placeholders from language variable with values
                                $search  = array('{LOGIN_DISPLAY_NAME}', '{LOGIN_WEBSITE_TITLE}', '{LOGIN_NAME}', '{LOGIN_PASSWORD}');
                                $replace = array($results_array['display_name'], WEBSITE_TITLE, $results_array['username'], $new_pass);
                                $mail_message = str_replace($search, $replace, $MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT']);
                            // Try sending the email
                                if ($wb->mail(SERVER_EMAIL,$mail_to,$mail_subject,$mail_message)) {
                                    $aSuccess[] = $MESSAGE['FORGOT_PASS_PASSWORD_RESET'];
                                    $display_form = false;
                                } else { // snd mail failed, rollback
                                    $sql = 'UPDATE `'.TABLE_PREFIX.'users` '.
                                           'SET `password`=\''.$database->escapeString($old_pass).'\' '.
                                           'WHERE `user_id`='.(int)$results_array['user_id'];
                                    $database->query($sql);
                                    $errMsg[] = $MESSAGE['FORGOT_PASS_CANNOT_EMAIL'];
                                }
                            }else { // Error updating database
                                $errMsg[] = $MESSAGE['RECORD_MODIFIED_FAILED'];
                            }
                        }
                    } else { // no record found - Email doesn't exist, so tell the user
                        $errMsg[] = $MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'];
                    }
                } else { // Query failed
                    $errMsg[] = 'SystemError:: Database query failed!';
                }
            }
        } else { // end $email
            $email = '';
            $errMsg[] = $MESSAGE['SIGNUP_NO_EMAIL'];
        }
    } // end $action

/*--------------------------------------------------------------------------------*/
    $sTemplate  = 'forgot_form.htt';
/*--------------------------------------------------------------------------------*/
    \header("X-Robots-Tag: noindex", true);
    $sTemplatePath = WB_PATH.'/account/templates/';

    if (is_readable(WB_PATH .'/templates/'.TEMPLATE.'/templates/account/'.$sTemplate)) {
       $sTemplatePath = WB_PATH .'/templates/'.TEMPLATE.'/templates/account/';
       $sTemplateURL  = str_replace(['\\','//'],'/',WB_REL.'/templates/'.TEMPLATE.'/templates/account');
    }

/*--------------------------------------------------------------------------------*/
    $template = new Template($sTemplatePath);
    $template->set_file('page', $sTemplate);
    $template->set_block('page', 'main_block', 'main');
    $template->set_block('main_block', 'display_form_block', 'display_form');
    $template->set_var($oTrans->getLangArray());
/*--------------------------------------------------------------------------------*/

    $aTwigData = [
    "MESSAGE_TITLE" => $oTrans->MENU_FORGOT,
    "FORM_TYPE" => 'forgot',
    "WB_URL" => $oReg->AppUrl,
    "PAGE_ID" => $page_id,
    "URL" => $redirect_url,
    "REDIRECT" => $redirect_url, //$thisApp->redirect_url
    "HTTP_REFERER" => $redirect_url, //$thisApp->redirect_url
    "ADMIN_URL" => $oReg->AcpUrl,
    ];

    $template->set_var($aTwigData);
    $template->set_ftan(\bin\SecureTokens::getFTAN());
/*--------------------------------------------------------------------------------*/
    $template->set_block('main_block', 'back_block', 'back');
//    $template->set_block('back_block', '');
/* */
    if (!empty($redirect_url) && (count($aSuccess)>0)){
        $template->set_var('REDIRECT', $redirect_url);
        $template->set_var('REDIRECT_URL', $redirect_url);
        $template->set_var('TEXT_BACK', $oTrans->TEXT_BACK);
        $template->parse('back', 'back_block', true);
    } else {
        $template->set_block('back_block', '');
    }

/*--------------------------------------------------------------------------------*/
    $template->set_block('main_block', 'success_block', 'success');
    if (count($aSuccess)>0)
    {
        $template->set_block('success_block', 'success_list_block', 'success_list');
        foreach($aSuccess as $value){
            $template->set_var('SUCCESS_MESSAGE', PreCheck::xnl2br($value));
            $template->parse('success_list', 'success_list_block', true);
        }
        $template->parse('success', 'success_block', true);
    } else {

        $template->set_block('success_block', '');
    }

/*--------------------------------------------------------------------------------*/
    $template->set_block('display_form_block', 'error_block', 'error');
    if ($display_form){
        if (count($errMsg) > 0){
        $template->set_block('error_block', 'error_list_block', 'error_list');
            foreach($errMsg as $value) {
                $template->set_var('ERROR_MESSAGE', $value);
                $template->parse('error_list', 'error_list_block', true);
            }
            $template->parse('error', 'error_block', true);
        } else {
            $template->set_block('error_block', '');
        }
/*--------------------------------------------------------------------------------*/
        $template->set_block('display_form_block', 'heading_block', 'heading');
        if (count($aSuccess)>0)
        {
            $template->set_block('heading_block', 'heading_list_block', 'heading_list');
            foreach($aSuccess as $value)
            {
                $template->set_var('MESSAGE', PreCheck::xnl2br($value));
                $template->parse('heading_list', 'heading_list_block', true);
            }
            $template->parse('heading', 'heading_block', true);
        } else {
            $template->set_block('heading_block', '');
        }
/*--------------------------------------------------------------------------------*/
    //  add some honeypot-fields
        $iNow = time();
        $_SESSION['submitted_when'] = $iNow;
        $template->set_block('display_form_block', 'honeypot_block', 'honeypot');
        if (ENABLED_ASP) {
            $template->set_var('SESSION_SUBMITTED_WHEN', $iNow);
            $template->parse('honeypot', 'honeypot_block', true);
        } else {
            $template->set_block('honeypot_block', '');
        }
/*--------------------------------------------------------------------------------*/
//  Captcha
        $template->set_block('display_form_block', 'display_captcha_block', 'display_captcha');
        if ($wb->bit_isset(ENABLED_CAPTCHA,4)){
        //  load captcha script first if captcha is enabled
            if (!function_exists('captcha_header')) {require(WB_PATH.'/include/captcha/captcha.php');}
        //  declared some default settings
            $aCaptachs['ct_color'] = 1;
            if ($oCaptcha = $database->query('SELECT * FROM `'.TABLE_PREFIX.'mod_captcha_control` ')){
                $aCaptachs = $oCaptcha->fetchRow(MYSQLI_ASSOC);
            }
            $template->set_var('CALL_CAPTCHA', call_captcha('all','','',false,$aCaptachs['ct_color']));
            $template->parse('display_captcha', 'display_captcha_block', false);
        } else{
            $template->set_block('display_captcha_block', '');
        }
/*--------------------------------------------------------------------------------*/
        $template->set_block('display_form_block', 'use_data_protection_block', 'use_data_protection');
        if ($wb->bit_isset($aSettings['use_data_protection'],4)) {
        //  $target_section_id = $aSettings[LANGUAGE];
        //  $target_section_id = $aSettings['data_protection_link'];
            $sDataLink = \bin\helpers\ParentList::getDsgvoDefaultLink();
            $template->set_var('CALL_DSGVO_LINK',sprintf($oTrans->MESSAGE_DSGVO, $sDataLink));
            $template->parse('use_data_protection', 'use_data_protection_block', false);
        } else{
            $template->set_block('use_data_protection_block', '');
        }
/*--------------------------------------------------------------------------------*/
        $template->set_block('display_form_block', 'display_extra_link_block', 'display_extra_link');
        $template->parse('display_extra_link', 'display_extra_link_block', true);
//        $template->set_block('display_extra_link_block', '');
/*--------------------------------------------------------------------------------*/
        $template->parse('display_form', 'display_form_block', true);
    } else {
        $template->set_block('display_form_block', '');
    }
/*--------------------------------------------------------------------------------*/
//  Parse template for preferences form
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
/*--------------------------------------------------------------------------------*/
