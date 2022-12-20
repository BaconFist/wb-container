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
 * Description of settings/save.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: save.php 252 2019-03-17 17:58:36Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

// prevent this file from being accessed directly in the browser (would set all entries in DB settings table to '')
//if(!isset($_POST['default_language']) || $_POST['default_language'] == '') die(header('Location: index.php'));
/*
print '<pre  class="mod-pre rounded">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> <br />';
print_r( $aTmp ); print '</pre>'; flush (); //  ob_flush();;sleep(10); die();
*/

// Print admin header
    if (!defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}
//    if (!function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

try {

    $sErrorMsg    = '';
    $bAdvanced    = 0;
    $aRequestVars = [];
    $aDsgvo       = [];

    $bAdvanced    = $oRequest->getParam('advanced',FILTER_VALIDATE_INT,['options'=>['default'=>0]]);
    $aRequestVars = $oRequest->getParamNames();

    function getDsgvoSection($sValue){
        $oDb = \database::getInstance();
        $aRetval = [];
        $sLanguage = DEFAULT_LANGUAGE;
        $table_pages = TABLE_PREFIX."pages";
        $table_sections = TABLE_PREFIX."sections";
        $sqlWhere = 'WHERE'.(($sValue != 0) ? '`s`.`section_id` = '.(int)$sValue.''
                  : '`p`.`parent` = '.(int)$sValue.'').' ';
        $sql  = 'SELECT `s`.*, `p`.`link`, `p`.`parent`, `p`.`language` '
              . 'FROM `'.$table_sections.'` s '
              . 'JOIN `'.$table_pages.'` `p` ON (`s`.`page_id` = `p`.`page_id`) '
              . $sqlWhere;
        if (($oInstances = $oDb->query($sql))) {
            if (($aRecord = $oInstances->fetchRow(MYSQLI_ASSOC))) {
                $sLanguage = $aRecord['language'];
            }
        }
        $aRetval[$sLanguage] = $sValue;
        return $aRetval;
    }
// Find out if the user was view advanced options or not

// Create a  back link
    $sAddonBackUrl = ADMIN_URL.'/settings/index.php?advanced='.($bAdvanced);
    $oReg = WbAdaptor::getInstance();
    if (!$bAdvanced){
        $admin = new \admin('Settings', 'settings_basic');
    } else {
        $admin = new \admin('Settings', 'settings_advanced');
    }

    if (!SecureTokens::checkFTAN ()) {
        $sErrorMsg = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($sErrorMsg);
    }

//    $TOKENS = \unserialize($_SESSION['TOKENS']);
//    $array  = $_POST;
//    \ksort($array);

// array for allowed submits
    $aSubmits  = ['submit_general','submit_dsgvo','submit_default','submit_search','submit_system','submit_mailer','submit_settings'];
    $bSubmit   = false;
    $sSubmit   = '';
    $aOutputs  = [];
    $aDsgvo    = [];
    $sDsgvoSet = '';
    $sql = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'settings` '
         . 'ORDER BY `name`';
    if ($oSettings = $database->query($sql)) {
        while($aSetting = $oSettings->fetchRow( MYSQLI_ASSOC )) {
          $aOutputs['_POST'][$aSetting['name']] = $aSetting['value'];
        }
    }
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }
// sanitize valide submit
    foreach ($aRequestVars as $sSubmit){
        $bSubmit = ((!($bSubmit)) ? in_array($sSubmit, $aSubmits) : $bSubmit);
        $sSubmit = ((empty($sSubmit) && !$bSubmit) ? $sSubmit : $sSubmit);
        if ($bSubmit){break;}
    }

//  set flags for $use_data_protection
    $enabled_signup = $oRequest->getParam('enabled_signup',FILTER_VALIDATE_INT);
    $enabled_loginform = $oRequest->getParam('enabled_loginform',FILTER_VALIDATE_INT);
    $enabled_lostpassword = $oRequest->getParam('enabled_lostpassword',FILTER_VALIDATE_INT);

    $aDsgvo['use_data_protection'] = ($enabled_signup |= $enabled_loginform |= $enabled_lostpassword);
//  get gdpr urls TODO change if in_array to requester isset
    if (\array_key_exists('dsgvo',$aRequestVars))
//    if (isset($aRequestVars['dsgvo']))
    {
        $aOptions  = ['options' => 'getDsgvoSection'];
        $aDsgvoTmp = $oRequest->getParam('dsgvo',\FILTER_CALLBACK,$aOptions);
        foreach ($aDsgvoTmp as $key=>$aValue){
            foreach ($aValue as $sLang=>$iSectionId){
                $aDsgvo[$sLang] = (int)$iSectionId;
            }
        }
        $sDsgvoSet = \serialize($aDsgvo);  // dsgvo_settings
    } else {
// restore old settings if no request
        $sDsgvoSet = $aOutputs['_POST']['dsgvo_settings'];
    }

// Work-out file mode
    $file_mode = $aOutputs['_POST']['string_file_mode'];
    $dir_mode  = $aOutputs['_POST']['string_dir_mode'];
    if (!$bAdvanced){
        // Check if should be set to 777 or left alone
        if (isset($aRequestVars['world_writeable']) && $aRequestVars['world_writeable'] == 'true'){
            $file_mode = '0777';
            $dir_mode  = '0777';
        } else {
        }
    } else {
        if($admin->getUserId()=='1'){
            // Work-out the octal value for file mode
            $u = 0;
            if (isset($aRequestVars['file_u_r']) && $aRequestVars['file_u_r'] == 'true') {
                $u = $u+4;
            }
            if (isset($aRequestVars['file_u_w']) && $aRequestVars['file_u_w'] == 'true') {
                $u = $u+2;
            }
            if (isset($aRequestVars['file_u_e']) && $aRequestVars['file_u_e'] == 'true') {
                $u = $u+1;
            }
            $g = 0;
            if (isset($aRequestVars['file_g_r']) && $aRequestVars['file_g_r'] == 'true') {
                $g = $g+4;
            }
            if(isset($aRequestVars['file_g_w']) && $aRequestVars['file_g_w'] == 'true') {
                $g = $g+2;
            }
            if (isset($aRequestVars['file_g_e']) && $aRequestVars['file_g_e'] == 'true') {
                $g = $g+1;
            }
            $o = 0;
            if (isset($aRequestVars['file_o_r']) && $aRequestVars['file_o_r'] == 'true') {
                $o = $o+4;
            }
            if (isset($aRequestVars['file_o_w']) && $aRequestVars['file_o_w'] == 'true') {
                $o = $o+2;
            }
            if(isset($aRequestVars['file_o_e']) && $aRequestVars['file_o_e'] == 'true') {
                $o = $o+1;
            }
            $file_mode = "0".$u.$g.$o;
            // Work-out the octal value for dir mode
            $u = 0;
            if (isset($aRequestVars['dir_u_r']) && $aRequestVars['dir_u_r'] == 'true') {
                $u = $u+4;
            }
            if (isset($aRequestVars['dir_u_w']) && $aRequestVars['dir_u_w'] == 'true') {
                $u = $u+2;
            }
            if (isset($aRequestVars['dir_u_e']) && $aRequestVars['dir_u_e'] == 'true') {
                $u = $u+1;
            }
            $g = 0;
            if (isset($aRequestVars['dir_g_r']) && $aRequestVars['dir_g_r'] == 'true') {
                $g = $g+4;
            }
            if(isset($aRequestVars['dir_g_w']) && $aRequestVars['dir_g_w'] == 'true') {
                $g = $g+2;
            }
            if (isset($aRequestVars['dir_g_e']) && $aRequestVars['dir_g_e'] == 'true') {
                $g = $g+1;
            }
            $o = 0;
            if(isset($aRequestVars['dir_o_r']) && $aRequestVars['dir_o_r'] == 'true') {
                $o = $o+4;
            }
            if (isset($aRequestVars['dir_o_w']) && $aRequestVars['dir_o_w'] == 'true') {
                $o = $o+2;
            }
            if (isset($aRequestVars['dir_o_e']) && $aRequestVars['dir_o_e'] == 'true') {
                $o = $o+1;
            }
            $dir_mode = "0".$u.$g.$o;
        }
// Ensure that the specified default email is formally valid
        if (isset($aRequestVars['server_email'])){
            if (!$admin->validate_email($aRequestVars['server_email'])){
                $sErrorMsg = sprintf($oTrans->MESSAGE_USERS_INVALID_EMAIL."\n".'Email: %s',htmlentities($aRequestVars['server_email']));
                throw new \Exception ($sErrorMsg);
            }
        }

        if (isset($aRequestVars['wbmailer_routine']) && ($aRequestVars['wbmailer_routine']=='smtp')) {
            $checkSmtpHost = (isset($aRequestVars['wbmailer_smtp_host']) && ($aRequestVars['wbmailer_smtp_host']=='') ? false : true);
            $checkSmtpUser = (isset($aRequestVars['wbmailer_smtp_username']) && ($aRequestVars['wbmailer_smtp_username']=='') ? false : true);
            $checkSmtpPassword = (isset($aRequestVars['wbmailer_smtp_password']) && ($aRequestVars['wbmailer_smtp_password']=='') ? false : true);
            if (!$checkSmtpHost || !$checkSmtpUser || !$checkSmtpPassword) {
                $sErrorMsg = sprintf('%s %s'."\n".' `%s`',$oTrans->TEXT_REQUIRED,$oTrans->TEXT_WBMAILER_SMTP_AUTH,$oTrans->MESSAGE_GENERIC_FILL_IN_ALL);
                throw new \Exception ($sErrorMsg);
            }
        }
    }

    $allow_tags_in_fields = ['website_header', 'website_footer','website_signature'];
    $allow_empty_values   = ['website_header','website_footer','website_signature','sec_anchor','pages_directory','page_spacer','wbmailer_smtp_secure'];
    $disallow_in_fields   = ['pages_directory', 'media_directory','wb_version'];

// Query current settings in the db, then loop through them and update the db with the new value
    $settings      = [];
    $old_settings  = [];
    $sErrorMessage = '';
// Query current settings in the db, then loop through them to get old values
    $sql  = '
    SELECT `name`, `value` FROM `'.TABLE_PREFIX.'settings`
    ORDER BY `name`
    ';
    if ($res_settings = $database->query($sql)) {
        $passed = false;

        while(($setting = $res_settings->fetchRow(MYSQLI_ASSOC))) :
            $old_settings[$setting['name']] = $setting['value'];
            $setting_name = $setting['name'];
            if ($setting_name=='wb_version') { continue; }
            $value = ($aRequestVars[$setting_name] ?? '');
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d]%s = %s</div>\n",__LINE__,$setting_name,$value ));
            $value = (\is_null($value) ? '' : $value);
            $value = ($aRequestVars[$setting_name] ?? $old_settings[$setting_name]) ;
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d]%s = %s</div>\n",__LINE__,$setting_name,$value));
//             $value = ($oRequest->getParam('wbmailer_smtp_debug', FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['options'=>['default'=>'0']]) ?? '0');
            switch ($setting_name) :
                case 'groups_updated':
                case 'confirmed_registration':
                case 'user_login':
                case 'system_locked':
                case 'sgc_execute':
                case 'smart_login':
                case 'section_blocks':
                case 'show_start_datetime':
                case 'manage_sections':
                case 'multiple_menus':
                case 'page_languages':
                case 'warn_page_leave':
                case 'sec_token_fingerprint':
                case 'home_folders':
                case 'homepage_redirection':
                    break;
                case 'dev_infos':
                    //$value = ($aRequestVars['dev_infos'] ?? 'false');
                    //$value = str_replace(['1','0',''],['true','false','false'],$value);
//echo \nl2br(\sprintf("---- [%04d] %s => %s\n",__LINE__,$setting_name,$value));
                case 'wbmailer_smtp_auth':
                    $value = ($aRequestVars[$setting_name] ?? 'false');
                    $value = ($admin->StripCodeFromText($value));
                    $value = str_replace(['1','0',''],['true','false','false'],$value);
//echo \nl2br(\sprintf("---- [%04d] %s->%s \n",__LINE__,$setting_name,$value));
                    $passed = true;
                    break;
                case 'website_header':
                case 'website_footer':
                    $value = $admin->ReplaceAbsoluteMediaUrl($value);
                    $value = $admin->StripCodeFromText($value, 29);
                    $passed = true;
                    break;
                case 'website_title':
                case 'website_keywords':
                case 'website_description':
                case 'website_signature':
                    $value = $admin->StripCodeFromText($value);
                    $passed = true;
                    break;
                case 'dsgvo_settings':
                    $value = $sDsgvoSet;
                    $passed = true;
                    break;
                case 'default_charset':
                    $value='utf-8';
                    $passed = true;
                    break;
                case 'er_level':
                    require(ADMIN_PATH.'/interface/er_levels.php');
                    if (!\array_key_exists($value, $ER_LEVELS)){
                      $value='0';
                      $passed = true;
                    }
                    break;
                case 'default_date_format':
                case 'default_time_format':
                    $value = str_replace(' ', '|',$value);
                    $passed = true;
                    break;
                case 'default_timezone':
                    $value=$value*60*60;
                    $passed = true;
                    break;
                case 'string_dir_mode':
                    $value=$dir_mode;
                    $passed = true;
                    break;
                case 'string_file_mode':
                    $value=$file_mode;
                    $passed = true;
                    break;
                case 'page_newstyle':
                    $value = ($admin->StripCodeFromText($value));
                    $sOldStyle = ($aRequestVars['page_oldstyle'] ?? 'false');
                    $value = (($sOldStyle == 'true') ? 'false' : 'true');
                    $passed = true;
                    break;
                case 'page_oldstyle':
                    $value = ($admin->StripCodeFromText($value));
                    $value = str_replace(['1','0',''],['true','false','false'],$value);
                    $passed = true;
                    break;
                 case 'media_directory':
                    $value = $admin->StripCodeFromText($value);
                    $value = '/'.\trim($value, '/');
                    $passed = false;
                    if ($admin->isAllowedRootFolder($value) || \trim($value, '/') === ''){
                        $value = '/media';
                        $sErrorMessage .= 'Change media_directory to '.$value.'<br />';
                    }
                    $passed = make_dir(WB_PATH.$value);
                    break;
                case 'pages_directory':
                    $value = $admin->StripCodeFromText($value);
                    $value = '/'.\trim($value, '/');
                    $passed = false;
                    if ($admin->isAllowedRootFolder($value)){
                        $value = '/pages';
                        $sErrorMessage .= 'Change pages_directory to '.$value.'<br />';
                    }
                    $passed = make_dir(WB_PATH.$value);
                    break;
                case 'rename_files_on_upload':
                    $value = $admin->StripCodeFromText($value);
                    $aForbiddenFiletypes = ['ph.*?','cgi','pl','pm','exe','com','bat','pif','cmd','src','asp','aspx','js'];
                    if (!\is_array($value)){
                        $aRenameFilesOnUpload = \preg_split('/[\s,=+\;\:\/\|]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
                    }
                    $aValue = \array_merge($aForbiddenFiletypes, $aRenameFilesOnUpload);
                    $aValue = \array_unique($aValue);
                    $value  = \trim(\implode(',', $aValue),',');
                    $passed = true;
                    break;
                case 'media_width':
                case 'media_height':
                    $value = $admin->StripCodeFromText($value);
                    $value = \preg_replace("/[^0-9]/i", "", $value);
                    $value = ((\trim($value) == '') ? '0' : $value);
                    $passed = true;
                    break;
                case 'jquery_version':
                    $value = $admin->StripCodeFromText($value);
                    $passed = \preg_match("/[0-9\.]/i", $value);
                    break;
                case 'twig_version':
                    $value = $admin->StripCodeFromText($value);
                    $value = (\in_array($value, ['1','2','3']) ? $value : '3');
                    $passed = true;
                    break;
                case 'app_name' :
                    $value = $admin->StripCodeFromText($value);
                    $value = (empty($value) ? 'PHPSESSID-wb-'.$database->escapeString(\bin\SecureTokens::getUniqueFreeToken(6)) : $value );
                    $passed = true;
                    break;
                case 'sec_anchor':
                    $value = $admin->StripCodeFromText($value);
                    $value = (($value == '') ? 'Sec' : $value);
                    $passed = ($value != '');
                    break;
                case 'wbmailer_smtp_auth':
                    $value = ($aRequestVars['wbmailer_smtp_auth'] ?? 'false');
                    $value = ($admin->StripCodeFromText($value));
                    $value = str_replace(['1','0',''],['true','false','false'],$value);
                    $value='true';
                    $passed = true;
                    break;
                case 'wbmailer_smtp_debug':
//                    $value = ($oRequest->getParam('wbmailer_smtp_debug', FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['options'=>['default'=>'0']]) ?? '0');
                    $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['options'=>['default'=>'false']]);
                    $passed = true;
                    break;
                case 'wbmailer_low_security':
                    $checkLowSecurity  = (isset($aRequestVars['wbmailer_low_security']) ? 'true' : 'false' );
                    $value = $checkLowSecurity;
                    $value = str_replace(['1','0',''],['true','false','false'],$value);
                    $passed = true;
                    break;
                case 'sec_token_netmask4':
                    $iValue = \intval( $value );
                    $value  = (($iValue > 32) || ( $iValue < 0 ) ? '24' : $value);
                    $passed = true;
                    break;
                case 'sec_token_netmask6':
                    $iValue = \intval( $value );
                    $value  = (($iValue > 128) || ( $iValue < 0 ) ? '64' : $value);
                    $passed = true;
                    break;
                case 'sec_token_life_time':
                    $value = $admin->sanitizeLifeTime(\intval( $value ) * 60 );
                    $passed = true;
                    break;
                case 'wb_version':
                    continue 2;
                    break;
                case 'frontend_signup':
                    $value = \intval( $value );
                    $passed = true;
                    break;
                default :
                    $value = $admin->StripCodeFromText($value);
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d]%s = %s</div>\n",__LINE__,$setting_name,$value ));
                    $passed = \in_array($setting_name, $allow_empty_values);
                    break;
            endswitch;
            if (\is_array($value)){ $value = $value['0']; }

            if (!\in_array($setting_name, $allow_tags_in_fields)) {
              $value = \strip_tags($value);
            }
            if ((!\in_array($value, $disallow_in_fields) && $oRequest->issetParam($setting_name)) || ($passed == true))
            {
                $sIdentifier = \trim($database->escapeString($setting_name));
                $value = \trim($database->escapeString($value));
/*
                if (in_array($sIdentifier, ['wbmailer_smtp_auth','sgc_execute','dev_infos','page_oldstyle','page_newstyle','wbmailer_low_security'])) {
                    //echo \nl2br(\sprintf("---- [%04d] %s\n",__LINE__,$sql));
echo \nl2br(\sprintf("---- [%04d] %s->%s \n",__LINE__,$sIdentifier,$value));
                }
*/
                if (!db_update_key_value('settings',$sIdentifier,$value)) {
                    $sErrorMsg = \sprintf("%s\n %s",$oTrans->MESSAGE_GENERIC_NOT_UPGRADED,$database->get_error());
                    throw new \Exception ($sErrorMsg);
                }
            }
        endwhile;
    }

// Query current search settings in the db, then loop through them and update the db with the new value
    $sql = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'search` '
         . 'WHERE `extra`=\'\'';
    if (!($res_search = $database->query($sql))) {
        $sErrorMsg = \sprintf("%s \n%s",$oTrans->MESSAGE_GENERIC_NOT_UPGRADED,$database->get_error);
        throw new \Exception ($sErrorMsg);
    }
    while($search_setting = $res_search->fetchRow()) {
        $old_value = $search_setting['value'];
        $setting_name = $search_setting['name'];
        $post_name = 'search_'.$search_setting['name'];
        // hold old value if post is empty
        // check search template
        $value = (($admin->get_post($post_name) == '') && ($setting_name != 'template'))
                 ? $old_value
                 : $admin->get_post($post_name);
        if (isset($value)) {
            $value = $database->escapeString($value);
            $sql = 'UPDATE `'.TABLE_PREFIX.'search` '
                 . 'SET `value`=\''.$value.'\' '
                 . 'WHERE `name`=\''.$setting_name.'\' AND `extra`=\'\'';
            if (!($database->query($sql))) {
                $sErrorMsg = \sprintf("%s \n%s",$oTrans->MESSAGE_GENERIC_NOT_UPGRADED,$database->get_error);
                throw new \Exception ($sErrorMsg);
            }
            // $sql_info = mysql_info($database->db_handle); //->> nicht mehr erforderlich
        }
    }
    $sErrorMessage = \sprintf($oTrans->MESSAGE_SETTINGS_SAVED);
    $admin->print_success($sErrorMessage, $sAddonBackUrl );

} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
    $admin->print_footer();

