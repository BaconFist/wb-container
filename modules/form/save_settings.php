<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        addons
 * @package         form
 * @subpackage      modify_settings
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */

use bin\helpers\ParentList;
use bin\{WbAdaptor,SecureTokens,Sanitize};

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/* -------------------------------------------------------- */
    $bLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
    $bSecureToken = (!is_readable($sAddonPath.'.setToken'));
    $bFrontendCss = (!is_readable($sAddonPath.'.setFrontend'));
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
    $sqlEOL       = ($bLocalDebug ? "\n" : "");
/* ------------------------------------------------------------------ */
// print with or without header
    $admin_header = false;
// Workout if the developer wants to show the info banner
    $print_info_banner = false; // true/false
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require($sModulesPath.'admin.php');
/* -------------------------------------------------------- */
// settings needs a admin object
    $oReg     = WbAdaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();

/* ----------set to deprecated----------------------------- */
// load module language file
    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
/* -------------------------------------------------------- */
    $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
    $ModuleUrl    = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl    = $oReg->AppUrl.$sAddonRel;
    $sTargetPath  = $sAddonPath.'/data/layouts/';
    $sDomain      = $oApp->getDirNamespace($sCallingScript);
/* -------------------------------------------------------- */
    $sBacklink = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id;
    $sGetOldSecureToken = (SecureTokens::checkFTAN());
    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];
/* -------------------------------------------------------- */
   if ($bSecureToken && !$sGetOldSecureToken){
        $admin->print_header();
        $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $sBacklink);
    }

    $admin->print_header();

    if (!function_exists('emailAdmin')) {
        function emailAdmin() {
            global $database,$admin;
            $retval = $admin->get_email();
            if ($admin->getUserId()!='1') {
                $sql  = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                      . 'WHERE `user_id`=\'1\' ';
                $retval = $database->get_one($sql);
            }
            return $retval;
        }
    }

// This code removes any <?php tags and adds slashes
    $friendly = array('&lt;', '&gt;', '?php');
    $raw = array('<', '>', '');

    $iAdminRights = (($admin->ami_group_member('1')||$admin->get_permission('settings_view')) ? 29 : 26);
//$header     = CleanInput('header');
    $header = ($admin->StripCodeFromText($admin->get_post('header'),$iAdminRights) ?? '');
//$field_loop = CleanInput('field_loop');
    $field_loop = ($admin->StripCodeFromText($admin->get_post('field_loop'),$iAdminRights) ?? '');
    $extra = ($admin->StripCodeFromText($admin->get_post('extra'),$iAdminRights) ?? '');
    $footer = $admin->StripCodeFromText($admin->get_post('footer'),$iAdminRights);
//$email_to   = CleanInput('email_to');
    $subject    = ($admin->StripCodeFromText($admin->get_post('subject_email')) ?? '');
    $email_to   = ($admin->StripCodeFromText($admin->get_post('email_to')) ?? '');
    $email_to   = $admin->StripCodeFromText($email_to != '' ? $email_to :(SERVER_EMAIL ?? emailAdmin()));
    $email_from = SERVER_EMAIL;
//$use_captcha =CleanInput('use_captcha');
    $use_captcha = intval($admin->get_post('use_captcha'));
    $use_captcha_auth = intval($admin->get_post('use_captcha_auth'));
    $prevent_user_confirmation = intval($admin->get_post('prevent_user_confirmation'));
    $info_dsgvo_in_mail = intval($admin->get_post('info_dsgvo_in_mail'));
    $captcha_action = $admin->StripCodeFromText($admin->get_post('captcha_action'));
    $captcha_style  = $admin->StripCodeFromText($admin->get_post('captcha_style'));
    $layout  = \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $admin->StripCodeFromText($admin->get_post('file')));
    $description  = $admin->StripCodeFromText($admin->get_post('description'));
    $use_data_protection = intval($admin->get_post('use_data_protection'));
    $data_protection_link = intval($admin->get_post('data_protection_link'));
    $title_placeholder = intval($admin->get_post('title_placeholder'));
    $form_required = intval($admin->get_post('form_required'));
    $delete_submissions = intval($admin->get_post('delete_submissions'));
    $spam_submissions = intval($admin->get_post('spam_submissions'));
    // spam_submissions

    $frontend_css = intval($admin->get_post('frontend_css'));
    $sFrontendCssFile = $sAddonPath.".setFrontend";
    $sFrontendAspFile = $sAddonPath."aspSupported.css";
    if ($bFrontendCss && ((bool)$frontend_css == true)){
        if (!is_readable($sFrontendCssFile)) {
            $sTmp = 'created: ['.date('r').']'."\n";
            $iFile = \file_put_contents($sFrontendCssFile, $sTmp);
        }
        if (!is_readable($sFrontendAspFile)) {
            $sContent = "/* created: ['.date('r').'] */\n.hide,\n.w3-hide,\n.nixhier {display:none;}\n";
            $iFile = \file_put_contents($sFrontendAspFile, ($sContent));
        }
    } else if (((bool)$frontend_css == false) && is_readable($sFrontendCssFile)) {
        unlink($sFrontendCssFile);
    }

    if( isset($_POST['email_fromname_field']) && ($_POST['email_fromname_field'] != '')) {
        $email_fromname = $admin->StripCodeFromText($admin->get_post('email_fromname_field'));
    } else {
        $email_fromname = $admin->StripCodeFromText($admin->get_post('email_fromname'));
    }

    $email_fromname = ($email_fromname != '' ? $email_fromname : WBMAILER_DEFAULT_SENDERNAME);
    $email_subject = ($admin->StripCodeFromText($admin->get_post('email_subject')) ?? '');
    $success_page = (int)($admin->StripCodeFromText($admin->get_post('success_page')));
    $success_email_to = ($admin->StripCodeFromText($admin->get_post('success_email_to')) ?? '');
    $success_email_from = (SERVER_EMAIL);
    $success_email_fromname = ($admin->StripCodeFromText($admin->get_post('success_email_fromname')) ?? '');
    $success_email_fromname = ($success_email_fromname != '' ? $success_email_fromname : $email_fromname);
    $success_email_text = ($admin->StripCodeFromText($admin->get_post('success_email_text')) ?? '');
    $success_email_text = (($success_email_text != '') ? $success_email_text : '');
    $success_email_subject = ($admin->StripCodeFromText($admin->get_post('success_email_subject')) ?? '');
    $success_email_subject = (($success_email_subject  != '') ? $success_email_subject : '');
    $divider = htmlspecialchars($admin->StripCodeFromText($admin->get_post('divider')) ?? '');
    if (isset($_POST['max_submissions']) && !is_numeric($_POST['max_submissions'])) {
        $max_submissions = 50;
    } else {
//    $max_submissions = intval($_POST['max_submissions']);
        $max_submissions = filter_var($_POST['max_submissions'],FILTER_VALIDATE_INT);
    }

    if (isset($_POST['stored_submissions']) && !is_numeric($_POST['stored_submissions'])) {
        $stored_submissions = 100;
    } else {
//    $stored_submissions = intval($_POST['stored_submissions']);
        $stored_submissions = filter_var($_POST['stored_submissions'],FILTER_VALIDATE_INT);
    }

    if (isset($_POST['perpage_submissions']) && !is_numeric($_POST['perpage_submissions'])) {
        $perpage_submissions = 10;
    } else {
//    $perpage_submissions = intval($_POST['perpage_submissions']);
        $perpage_submissions = filter_var($_POST['perpage_submissions'],FILTER_VALIDATE_INT);
    }

// Make sure max submissions is not greater than stored submissions if stored_submissions <> 0
    if (($stored_submissions > 0) && ($max_submissions > $stored_submissions)) {
        $max_submissions = $stored_submissions;
    }
    // if stored_submissions equal 0 than $delete_submissions can not be greater than 0
    if (((int)$stored_submissions === 0)){$delete_submissions = 0;}

//    $FTAN = $admin->getFTAN('GET');
    $sBackUrl = $sAddonUrl.'modify_settings.php?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery;
    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;

    $sBacklink = (isset($_REQUEST['save_pagetree'])? $sBacklink : $sBackUrl );
// Update settings
$sql  = '
    UPDATE `'.TABLE_PREFIX.'mod_form_settings` SET
    `layout` = \''.$database->escapeString($layout).'\',
    `description` = \''.$database->escapeString($description).'\',
    `header` = \''.$database->escapeString($header).'\',
    `field_loop` = \''.$database->escapeString($field_loop).'\',
    `extra` = \''.$database->escapeString($extra).'\',
    `footer` = \''.$database->escapeString($footer).'\',
    `email_to` = \''.$database->escapeString($email_to).'\',
    `email_from` = \''.$database->escapeString($email_from).'\',
    `email_fromname` = \''.$database->escapeString($email_fromname).'\',
    `email_subject` = \''.$database->escapeString($email_subject).'\',
    `success_page` = '.(int)$success_page.',
    `success_email_to` = \''.$database->escapeString($success_email_to).'\',
    `success_email_from` = \''.$database->escapeString($success_email_from).'\',
    `success_email_fromname` = \''.$database->escapeString($success_email_fromname).'\',
    `success_email_text` = \''.$database->escapeString($success_email_text).'\',
    `success_email_subject` = \''.$database->escapeString($success_email_subject).'\',
    `max_submissions` =  '.(int)($max_submissions).',
    `stored_submissions` = '.(int)($stored_submissions).',
    `delete_submissions` = '.(int)($delete_submissions).',
    `spam_submissions` = '.(int)$spam_submissions.',
    `perpage_submissions` = '.(int)($perpage_submissions).',
    `use_captcha` = '.(int)($use_captcha).',
    `subject_email` = \''.$database->escapeString($subject).'\',
    `divider` = \''.$database->escapeString($divider).'\',
    `use_captcha_auth` = '.(int)($use_captcha_auth).',
    `prevent_user_confirmation` = '.(int)($prevent_user_confirmation).',
    `captcha_action` = \''.$database->escapeString($captcha_action).'\',
    `captcha_style` = \''.$database->escapeString($captcha_style).'\',
    `use_data_protection`= '.(int)($use_data_protection).',
    `info_dsgvo_in_mail` = '.(int)($info_dsgvo_in_mail).',
    `title_placeholder` = '.(int)($title_placeholder).',
    `form_required` = '.(int)($form_required).',
    `frontend_css` = '.(int)($frontend_css).',
    `data_protection_link`='.(int)($data_protection_link).'
    WHERE `section_id` = '.(int)$section_id.'
';

    if ($database->query($sql)) {
        $admin->print_success($MESSAGE['SETTINGS_SAVED'], $sBacklink);
    }
// Check if there is a db error, otherwise say successful
    if ($database->is_error()) {
        $admin->print_error($database->get_error(), $sBacklink);
    }
// Print admin footer
    $admin->print_footer();
