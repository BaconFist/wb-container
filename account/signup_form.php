<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 7.4 and higher
 * @version         $Id: signup_form.php 267 2019-03-21 16:44:22Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/account/signup_form.php $
 * @lastmodified    $Date: 2019-03-21 17:44:22 +0100 (Do, 21. Mrz 2019) $
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;


use bin\{WbAdaptor,Login,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,Parentlist};
use vendor\phplib\Template;

/*--------------------------------------------------------------------------------*/
// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/*--------------------------------------------------------------------------------*/
//  Create new frontend object
    //$sCallingScript = WB_URL;
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $database = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();

    $sAddonFile    = $oApp->getCallingScript();
    $sAddonPath    = $oReg->DocumentRoot.ltrim(dirname($sAddonFile),'/').'/';
    $sAddonName    = \basename($sAddonPath);

    $iSteps = 0;// steps to /modules/ root
    switch ($iSteps) {
        case 2:
          $sAddonPath = \dirname($sAddonPath);
        case 1:
          $sAddonPath = \dirname($sAddonPath);
        case 0:
          $sModulesPath = \basename($sAddonPath);
          break;
    }
    $sPattern      = "/^(.*?\/)".$sModulesPath."\/.*$/";
    $sAppPath      = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    // Only for development for pretty mysql dump
    $bLocalDebug  =  is_readable($sAddonPath.'.setDebug');
    // Only for development prevent secure token check,
    $bSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');

    if (!isset($page_id)){
        $page_id = $oRequest->getParam('page_id',FILTER_VALIDATE_INT);
        $page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID'] > 0) ? $_SESSION['PAGE_ID'] : $page_id);
    }
    $redirect_url = ((isset($_SESSION['HTTP_REFERER']) && $_SESSION['HTTP_REFERER'] != '') ? $_SESSION['HTTP_REFERER'] : $sCallingScript );
    $action = $oRequest->getParam('action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $redirect_url = (isset($_SESSION['HTTP_REFERER']) && ($_SESSION['HTTP_REFERER'] != '') ? $_SESSION['HTTP_REFERER'] : $sCallingScript );
    $redirect_url = (isset($redirect) && ($redirect!='') ? $redirect : $redirect_url);
/*--------------------------------------------------------------------------------*/
    $error    = [];
    $aSuccess = [];
    $aMessage = [];
/*--------------------------------------------------------------------------------*/
//  load gdpr/dsgvo settings from db or ini file (found in your frontend template root)
    if (!$sSettings = \bin\helpers\ParentList::gdprSettings()){
        $sInifile     = '/templates/'.TEMPLATE.'/DataProtection.ini.php';
        $sIniUserfile = '/templates/'.TEMPLATE.'/DataUserProtection.ini.php';
        if (is_readable(WB_PATH .$sIniUserfile)){
            $sInifile = $sIniUserfile;
        }
        if (is_readable(WB_PATH .$sInifile)){
            $aTmp = \parse_ini_file(WB_PATH .$sInifile, true, INI_SCANNER_TYPED);
            $aSettings = $aTmp['dsgvo'];
         }
    } else {
      $aSettings = \bin\helpers\ParentList::unserialize($sSettings);
    }
/*--------------------------------------------------------------------------------*/
    $action =($action ?? 'show');
    if ($action=='send') {
        require(__DIR__.'/signup2.php');
    } else {
        $_SESSION['display_form'] = true;
    }
/*--------------------------------------------------------------------------------*/
    $sTemplate  = 'signup_form.htt';
/*--------------------------------------------------------------------------------*/
// looking for template in frontend templates  otherwise set from account templates folder
    $sTemplatePath = WB_PATH.'/account/templates/';
    $sTemplateURL  = str_replace(['\\','//'],'/',WB_REL.'/account/templates');
    if (is_readable(WB_PATH .'/templates/'.TEMPLATE.'/templates/account/'.$sTemplate)) {
       $sTemplatePath = WB_PATH .'/templates/'.TEMPLATE.'/templates/account/';
       $sTemplateURL  = str_replace(['\\','//'],'/',WB_REL.'/templates/'.TEMPLATE.'/templates/account');
    }

/*--------------------------------------------------------------------------------*/

    $template = new Template($sTemplatePath);
    $template->set_file('page', $sTemplate);
    $template->set_block('page', 'main_block', 'main');
//  load module default language file (EN)
    $sAddonName = basename(__DIR__);
    $oTrans = Translate::getInstance();
    $oTrans->enableAddon('\\account');
/*--------------------------------------------------------------------------------*/
    $template->set_var($oTrans->getLangArray());
    $aTwigData = [
          "FORM_TYPE" => 'signup',
          "MESSAGE_TITLE" => $oTrans->TEXT_SIGNUP,
          "PAGE_ID" => $page_id,
          "WB_URL" => $oReg->AppUrl,
          "ADMIN_URL" => $oReg->AcpUrl,
          "THEME_URL" => $oReg->ThemeUrl,
          "TEMPLATE_URL" => $oReg->TemplateUrl,
          "DATA_TEMPLATE" => $sTemplateURL,
          "URL" => $redirect_url,
          "REDIRECT" => $redirect_url,
          "REDIRECT_URL" => $redirect_url,
    ];
/*
    $template->set_var('SIGNUP_URL', WB_URL.'/account/signup.php');
    $template->set_var('MESSAGE_TITLE', $oTrans->TEXT_SIGNUP);
    $template->set_var('REDIRECT', $redirect_url);
    $template->set_var('REDIRECT_URL', $redirect_url);
    $template->set_var('DATA_TEMPLATE', $sTemplateURL);
    $template->set_var([
    'FORM_TYPE' => 'signup',
    'WB_URL'    => WB_URL,
    ]);
*/

    $template->set_var($aTwigData);
    $template->set_ftan(SecureTokens::getFTAN());
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
    $template->set_block('success_block', 'success_list_block', 'success_list');
    if (count($aSuccess)>0){
        foreach($aSuccess as $value){
            $template->set_var('SUCCESS_MESSAGE', PreCheck::xnl2br($value));
            $template->parse('success_list', 'success_list_block', true);
        }
        $template->parse('success', 'success_block', true);
    } else {
        $template->set_block('success_block', '');
    }
/*--------------------------------------------------------------------------------*/
    $template->set_block('main_block', 'display_form_block', 'display_form');
    if ($_SESSION['display_form']){
        $template->set_block('display_form_block', 'error_block', 'error');
        $template->set_block('error_block', 'error_list_block', 'error_list');
        if (count($error) > 0){
            foreach($error as $value) {
                $template->set_var('ERROR_MESSAGE', $value);
                $template->parse('error_list', 'error_list_block', true);
            }
            $template->parse('error', 'error_block', true);
        } else {
            $template->set_block('error_block', '');
        }
        $template->set_block('display_form_block', 'heading_block', 'heading');
        $template->set_block('heading_block', '');
/*--------------------------------------------------------------------------------*/
    //  add some honeypot-fields
        $iNow = time(); $_SESSION['submitted_when']=$iNow;
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
        if ($wb->bit_isset(ENABLED_CAPTCHA,1)){
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
        if ($wb->bit_isset($aSettings['use_data_protection'],1)) {
        //  $target_section_id = $aSettings[LANGUAGE];
        //  $target_section_id = $aSettings['data_protection_link'];
            $sDataLink = ParentList::getDsgvoDefaultLink();
            $template->set_var('CALL_DSGVO_LINK',sprintf($oTrans->MESSAGE_DSGVO, $sDataLink));
            $template->parse('use_data_protection', 'use_data_protection_block', false);
        } else{
            $template->set_block('use_data_protection_block', '');
        }
        $template->parse('display_form', 'display_form_block', true);
    } else {
        $template->set_block('display_form_block', '');
    }
/*--------------------------------------------------------------------------------*/
//  Parse template for preferences form
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
/*--------------------------------------------------------------------------------*/
