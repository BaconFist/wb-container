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
 * Description of admin/settings/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 286 2019-03-26 14:44:25Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
use vendor\phplib\Template;
use src\Security\{CsfrTokens, Randomizer, Password};

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFilter;


    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/*-------------------------------------------------------------------------------------------*/

try {

    PreCheck::increaseMemory();
    $bAdvanced    = $oRequest->getParam('advanced',\FILTER_VALIDATE_BOOLEAN);
    $aRequestVars = $oRequest->getParamNames();
    $sSelect    = ' selected="selected"';
    $sAddonBackUrl = ADMIN_URL;
    $admin =  ($bAdvanced ? new \admin('Settings', 'settings_advanced') : new \admin('Settings', 'settings_basic'));
    if (!in_array($bAdvanced, [false,true,'0','1'])){
            throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }
/*-------------------------------------------------------------------------------------------*/
// Include the WB functions file
    if (!\function_exists('functions-utf8')){require(WB_PATH.'/framework/functions-utf8.php');}
/* dprecated call
    $cfg = [];
*/
    $bPageNewsytle = (\defined('PAGE_NEWSTYLE') ? ((PAGE_NEWSTYLE == '1') ? 'true' : 'false'): 'false');
    $bPageOldsytle = (\defined('PAGE_OLDSTYLE') ? ((PAGE_OLDSTYLE=='1') ? 'true' : 'false') : 'false');
    $bPageOldsytle = (($bPageNewsytle=='false') ? 'true' : 'false');
    $cfg = [
        'page_newstyle'         => (\defined('PAGE_NEWSTYLE') ? PAGE_NEWSTYLE : 'true'),
        'website_signature'     => (\defined('WEBSITE_SIGNATURE') ? WEBSITE_SIGNATURE : ''),
        'twig_version'          => (\defined('TWIG_VERSION') ? TWIG_VERSION : '3'),
        'jquery_version'        => (\defined('JQUERY_VERSION') ? JQUERY_VERSION : '1.12.4'),
        'media_width'           => (\defined('MEDIA_WIDTH') ? MEDIA_WIDTH : '0'),
        'media_height'          => (\defined('MEDIA_HEIGHT') ? MEDIA_HEIGHT : '0'),
        'media_compress'        => (\defined('MEDIA_COMPRESS') ? MEDIA_COMPRESS : '75'),
        'mediasettings'         => (\defined('MEDIASETTINGS') ? MEDIASETTINGS : ''),
        'dsgvo_settings'        => (\defined('DSGVO_SETTINGS') && !empty(DSGVO_SETTINGS) ? DSGVO_SETTINGS : 'a:3:{s:19:"use_data_protection";b:1;s:2:"DE";i:0;s:2:"EN";i:0;}'),
        'user_login'            => (\defined('USER_LOGIN') ? ((USER_LOGIN=='1') ? 'true' : 'false') : 'false'),
        'system_locked'         => (\defined('SYSTEM_LOCKED') ? ((SYSTEM_LOCKED=='1') ? 'true' : 'false') : 'false'),
        'page_oldstyle'         => $bPageOldsytle,
        'page_newstyle'         => $bPageNewsytle,
        'dev_infos'             => (\defined('DEV_INFOS') ? ((DEV_INFOS=='1') ? 'true' : 'false') : 'false'),
        'sgc_execute'           => (\defined('SGC_EXECUTE') ? ((SGC_EXECUTE=='1') ? 'true' : 'false') : 'false'),
        'wbmailer_smtp_debug'   => (\defined('WBMAILER_SMTP_DEBUG') ? WBMAILER_SMTP_DEBUG : 'false'),
        'wbmailer_smtp_auth'    => (\defined('WBMAILER_SMTP_AUTH')  ? ((WBMAILER_SMTP_AUTH=='1') ? 'true' : (empty(WBMAILER_SMTP_AUTH) ? 'false' : 'true')) : 'false'),
        'wbmailer_low_security' => (\defined('WBMAILER_LOW_SECURITY') ? ((WBMAILER_LOW_SECURITY=='1') ? 'true' : 'false') : 'false'),
        'sec_token_life_time'   => (\defined('SEC_TOKEN_LIFE_TIME') ? SEC_TOKEN_LIFE_TIME : '1800'),
        'show_start_datetime'   => (\defined('SHOW_START_DATETIME')  ? ((SHOW_START_DATETIME=='1') ? 'true' : (empty(SHOW_START_DATETIME) ? 'false' : 'true')) : 'false'),
        'patch_revision'        => (\defined('PATCH_REVISION')  ? PATCH_REVISION : WB_REVISION),
        'patch_update'          => (\defined('PATCH_UPDATE')  ? PATCH_UPDATE : '0'),
    ];

/*
    $aKeys = array_keys($cfg);
    $aValues = array_values($cfg);
    $oDb->replace('settings', $aKeys, $aValues);
*/
    foreach($cfg as $key=>$value) {
        db_update_key_value('settings', $key, $value);
    }

    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oReg = WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source('settings.htt')), 'remove');

    $aTmp = ['header_block','page','infoExented','maintenance_block','button_locking_block','button_info','button_error_block'];
   /**
    * Determines how much debugging output Template will produce.
    * This is a bitwise mask of available debug levels:
    * 0 = no debugging
    * 1 = debug variable assignments
    * 2 = debug calls to get variable
    * 4 = debug internals (outputs all function calls with parameters).
    * 8 = debug (outputs all set_block variables calls with parameters).
    *
    * Note: setting $this->debug = true will enable debugging of variable
    *
    */
    $template->setDebug(0);

   /**
    * A hash of strings forming a translation table which translates variable names
    * hidden names of block files containing the variable content.
    * $aHideVarkeys[varname] = "varname";
    *
    * @var       array
    * @access    private
    * @see
    */
    $template->setHideVarArray([]);

    $template->set_file('page',  'settings.htt');
    $template->set_block('page', 'main_block', 'main');
/*-------------------------------------------------------------------------------------------*/
// global blocks
/*-------------------------------------------------------------------------------------------*/
    $template->set_block('main_block',    'show_checkbox_1_block',       'show_checkbox_1');
    $template->set_block('main_block',    'show_checkbox_2_block',       'show_checkbox_2');
    $template->set_block('main_block',    'show_checkbox_3_block',       'show_checkbox_3');

    $template->set_block('main_block',    'show_page_level_limit_block', 'show_page_level_limit');
    $template->set_block('main_block',    'show_redirect_timer_block',   'show_redirect_timer');
    $template->set_block('main_block',    'show_php_error_level_block',  'show_php_error_level');
    $template->set_block('main_block',    'show_wysiwyg_block',          'show_wysiwyg');
    $template->set_block('main_block',    'show_charset_block',          'show_charset');
    $template->set_block('main_block',    'show_media_setting_block',    'show_media_setting');
    $template->set_block('main_block',    'show_search_block',           'show_search');
    $template->set_block('main_block',    'show_access_block',           'show_access');
    $template->set_block('main_block',    'show_chmod_js_block',         'show_chmod_js');
    $template->set_block('main_block',    'show_setting_js_block',       'show_setting_js');
    $template->set_block('main_block',    'show_frontend_block',         'show_frontend');
/*-------------------------------------------------------------------------------------------*/

    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\'.\basename(__DIR__));
//    $aLang = $oTrans->getLangArray();

    $template->set_var($oTrans->getLangArray());

// Query current settings in the db, then loop through them and print them
    $query = "SELECT * FROM `".TABLE_PREFIX."settings`";
    if ($results = $database->query($query)) {
        $aSetting = [];
        $settings = [];
        while($aSetting = $results->fetchRow(MYSQLI_ASSOC))
        {
            $setting_name  = $aSetting['name'];
            $setting_value = $aSetting['value'];
            $settings[$setting_name] = $setting_value;
            switch ($setting_name) :
                case 'wbmailer_smtp_debug':
                case 'wbmailer_low_security':
                case 'wbmailer_smtp_password':
                    break;
                case 'pages_directory':
                    $setting_name  = \strtoupper($aSetting['name']);
                    \defined($setting_name) ? '' : \define($setting_name, \preg_quote($setting_value));
                    break;
                default :
                    $setting_value = OutputFilterApi('ReplaceSysvar', $setting_value);
                    $setting_value = \htmlspecialchars($setting_value);
                    break;
            endswitch;
//        $setting_value = ( $setting_name != 'wbmailer_smtp_password' ) ? htmlspecialchars($aSetting['value']) : $aSetting['value'];
            $setting_name  = \strtoupper($aSetting['name']);
            $template->set_var($setting_name,$setting_value);
            \defined($setting_name) ? '' : \define($setting_name, $setting_value);
        }
    }

    $SecureTokenLifeTime = $admin->getTokenLifeTime();
    \array_walk(
        $SecureTokenLifeTime,
        function (& $aItem) {
            $aItem /= 60;
        }
    );

    $template->set_var( $SecureTokenLifeTime );

/*-------------------------------------------------------------------------------------------*/
// Do the same for settings stored in config file as with ones in db
    $database_type = '';
    $is_advanced = (bool)$bAdvanced;
// Tell the browser whether or not to show advanced options
    if ($bAdvanced){
        $template->set_var('DISPLAY_ADVANCED', '');
        $template->set_var('ADVANCED_FILE_PERMS_ID', 'file_perms_box');
        $template->set_var('BASIC_FILE_PERMS_ID', 'hide');
        $template->set_var('ADVANCED_VALUE', 1);
        $template->set_var('ADVANCED_BUTTON', '&laquo; '.$TEXT['HIDE_ADVANCED']);
        $template->set_var('ADVANCED_LINK', 0);
    } else {
        $template->set_var('DISPLAY_ADVANCED', ' style="display: none;"');
        $template->set_var('BASIC_FILE_PERMS_ID', 'file_perms_box');
        $template->set_var('ADVANCED_FILE_PERMS_ID', 'hide');
        $template->set_var('ADVANCED_VALUE', 0);
        $template->set_var('ADVANCED_BUTTON', $TEXT['SHOW_ADVANCED'].' &raquo;');
        $template->set_var('ADVANCED_LINK', 1);
    }
/*-------------------------------------------------------------------------------------------*/
    $query = 'SELECT * FROM `'.TABLE_PREFIX.'search` WHERE `extra` = \'\'';
    if ($results = $database->query($query)){
    // Query current settings in the db, then loop through them and print them
        while($aSearch = $results->fetchRow(MYSQLI_ASSOC))
        {
            $search_name = $aSearch['name'];
            $search_value = \htmlspecialchars(($aSearch['value']));
            switch($search_name) {
                // Search header
                case 'header':
                    $template->set_var('SEARCH_HEADER', $search_value);
                break;
                // Search results header
                case 'results_header':
                    $template->set_var('SEARCH_RESULTS_HEADER', $search_value);
                break;
                // Search results loop
                case 'results_loop':
                    $template->set_var('SEARCH_RESULTS_LOOP', $search_value);
                break;
                // Search results footer
                case 'results_footer':
                    $template->set_var('SEARCH_RESULTS_FOOTER', $search_value);
                break;
                // Search no results
                case 'no_results':
                    $template->set_var('SEARCH_NO_RESULTS', $search_value);
                break;
                // Search footer
                case 'footer':
                    $template->set_var('SEARCH_FOOTER', $search_value);
                break;
                // Search module-order
                case 'module_order':
                    $template->set_var('SEARCH_MODULE_ORDER', $search_value);
                    break;
                // Search max lines of excerpt
                case 'max_excerpt':
                    $template->set_var('SEARCH_MAX_EXCERPT', $search_value);
                    break;
                // time-limit
                case 'time_limit':
                    $template->set_var('SEARCH_TIME_LIMIT', $search_value);
                    break;
                // Search template
                case 'template':
                    $search_template = $search_value;
                    break;
            }
        }
    }
/*-------------------------------------------------------------------------------------------*/
    $template->set_var([
                        'WB_URL' => WB_URL,
                        'THEME_URL' => THEME_URL,
                        'ADMIN_URL' => ADMIN_URL,
                        'SETTINGS_REL' => ADMIN_REL.'/settings/',
                     ]);
    $template->set_var('FTAN', $admin->getFTAN());
/*-------------------------------------------------------------------------------------------*/
    $template->set_block('show_page_level_limit_block', 'page_level_limit_list_block', 'page_level_limit_list');
    // Insert page level limits
    $template->set_var('PAGE_LEVEL_LIMIT', $settings['page_level_limit']);
    // if select list
    for($i = 1; $i <= 10; $i++)
    {
        $template->set_var('NUMBER', $i);
        $template->set_var('SELECTED', ((PAGE_LEVEL_LIMIT == $i) ? ' selected="selected"' : '') );
        $template->parse('page_level_limit_list', 'page_level_limit_list_block', true);
    }
/*-------------------------------------------------------------------------------------------*/
    $template->set_block('show_frontend_block', 'show_smart_login_block',      'show_smart_login');
    // Work-out if smart login feature is enabled  show_1_login_block
        if (\defined('SMART_LOGIN') && SMART_LOGIN == true){
            $template->set_var('SMART_LOGIN_ENABLED', ' checked="checked"');
        } else {
            $template->set_var('SMART_LOGIN_DISABLED', ' checked="checked"');
        }
/*-------------------------------------------------------------------------------------------*/
     $template->set_block('show_frontend_block', 'show_login_block', 'show_login');
    // Work-out if frontend login feature is enabled
    if (FRONTEND_LOGIN){
        $template->set_var('PRIVATE_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('PRIVATE_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert groups into signup list
        $template->set_block('show_login_block', 'group_list_block', 'group_list');
//    if ($admin->ami_group_member('1')||$admin->get_permission('settings_advanced')){
        $sqlGroup = 'SELECT `group_id`, `name` FROM `'.TABLE_PREFIX.'groups` '
                  . 'WHERE `group_id` != 1'
                  . '';
        if (($results = $database->query($sqlGroup))){
            while($group = $results->fetchRow(MYSQLI_ASSOC)){
                $template->set_var('ID', $group['group_id']);
                $template->set_var('NAME', $group['name']);
                $template->set_var('SELECTED', ((FRONTEND_SIGNUP == $group['group_id']) ? ' selected="selected"' : '') );
                $template->parse('group_list', 'group_list_block', true);
            }
        } else {
            $template->set_var('ID', 'disabled');
            $template->set_var('NAME', $MESSAGE['GROUPS_NO_GROUPS_FOUND']);
            $template->parse('group_list', 'group_list_block', true);
        }
/*-------------------------------------------------------------------------------------------*/
/*
/*-------------------------------------------------------------------------------------------*/
        $template->set_block('show_frontend_block', 'show_dsgvo_login_block', 'show_dsgvo_login');
        $template->set_block('show_dsgvo_login_block', 'dsgvo_list_block', 'dsgvo_list');
    // Insert dsgvo into signup list
        $aDsgvo = [];
        $aSectionList = [];
        if (is_string($sValue = ParentList::dsgvoSettings())){
            $aDsgvo = ParentList::unserialize($sValue);
            $aSectionList = ParentList::build_sectionlist(0,0,$aSectionList);
        }

        $iUseDataProtection = \array_shift($aDsgvo);
        $sChecked = (($iUseDataProtection > 0) ? ' checked="checked" ' : '');
//        $template->set_var('USE_DATA_PROTECTION', $iUseDataProtection);
        $template->set_var('CHECKED', $sChecked);

        $bSignupChecked = $admin->bit_isset($iUseDataProtection,1);
        $bLoginChecked = $admin->bit_isset($iUseDataProtection,2);
        $bLostPWChecked = $admin->bit_isset($iUseDataProtection,4);

        $template->set_var('SIGNUPCHECKED', (($bSignupChecked > 0) ? ' checked="checked" ' : ''));
        $template->set_var('LOGINCHECKED', (($bLoginChecked > 0) ? ' checked="checked" ' : ''));
        $template->set_var('LOSTPWCHECKED', (($bLostPWChecked > 0) ? ' checked="checked" ' : ''));
        $template->set_var('ADDON_LANG_URL', WB_URL.'/modules/WBLingual/');
        $sSelectReset = $sSelect;
        foreach($aSectionList as $aRes) {
                $aOptionLink = \explode('||',$aRes['descr']);
                $sSelected = '';
                $sOption      = $aOptionLink['1'];
                $sSelected    = (\in_array((int)$aOptionLink[0],$aDsgvo) ? $sSelect : '');
                $sSelectReset = ($sSelected ? '' : $sSelectReset);
                $template->set_var('DSGVO_ID', $aRes['section_id']);
                $template->set_var('DSGVO_NAME', $aOptionLink[1]);
                $template->set_var('PAGE_LANG', strtolower($aRes['language']));
                $template->set_var('SELECTED', $sSelected);
                $template->set_var('SELECT_RESET', $sSelectReset);
                $template->parse('dsgvo_list', 'dsgvo_list_block', true);
        }
/*-------------------------------------------------------------------------------------------*/
    // Insert default error reporting values
    $template->set_block('show_php_error_level_block', 'error_reporting_list_block',  'error_reporting_list');
    require(ADMIN_PATH.'/interface/er_levels.php');
    foreach($ER_LEVELS AS $value => $title)
    {
        $template->set_var('VALUE', $value);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', ((ER_LEVEL == $value) ? ' selected="selected"' : '') );
        $template->parse('error_reporting_list', 'error_reporting_list_block', true);
    }
/*-------------------------------------------------------------------------------------*/
    // Insert default twig_version
    $template->set_block('show_php_error_level_block', 'twig_version_list_block',  'twig_version_list');
    require(ADMIN_PATH.'/interface/er_levels.php');
    foreach($TWIG_VERSIONS AS $value => $title)
    {
        $template->set_var('VALUE', $value);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', ((TWIG_VERSION == $value) ? ' selected="selected"' : '') );
        $template->parse('twig_version_list', 'twig_version_list_block', true);
    }
/*-------------------------------------------------------------------------------------*/
    // Insert default twig_version
    $template->set_block('show_php_error_level_block', 'jquery_version_list_block',  'jquery_version_list');
    $sAbsjQueryPath = WB_PATH.'/include/jquery/dist/';
    $sPattern = '*';
    $aJqueryDirs = \glob($sAbsjQueryPath.$sPattern, \GLOB_NOSORT|\GLOB_ONLYDIR);
    \natsort($aJqueryDirs);
    foreach ($aJqueryDirs as $sPathname) {
        $key = \basename($sPathname);
//    foreach($TWIG_VERSIONS AS $value => $title) {
        $template->set_var('VALUE', $key);
        $template->set_var('NAME', $key);
        $template->set_var('SELECTED', ((JQUERY_VERSION == $key) ? ' selected="selected"' : '') );
        $template->parse('jquery_version_list', 'jquery_version_list_block', true);
    }
/*-------------------------------------------------------------------------------------*/
    // Insert default twig_version
    $template->set_block('show_php_error_level_block', 'twig_version_list_block',  'twig_version_list');
    require(ADMIN_PATH.'/interface/er_levels.php');
    foreach($TWIG_VERSIONS AS $value => $title)
    {
        $template->set_var('VALUE', $value);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', ((TWIG_VERSION == $value) ? ' selected="selected"' : '') );
        $template->parse('twig_version_list', 'twig_version_list_block', true);
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert WYSIWYG modules
    $template->set_block('show_wysiwyg_block', 'wysiwyg_list_block', 'wysiwyg_list');
    $file='none';
    $module_name=$TEXT['NONE'];
    $template->set_var('FILE', $file);
    $template->set_var('NAME', $module_name);
    $template->set_var('SELECTED', ((!\defined('WYSIWYG_EDITOR') || $file == WYSIWYG_EDITOR) ? ' selected="selected"' : '') );
    $template->parse('wysiwyg_list', 'wysiwyg_list_block', true);
    $sqlEditor  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
          . 'WHERE `type` = \'module\' '
          .   'AND `function` = \'wysiwyg\' '
          . 'ORDER BY `name`';
    if ($result = $database->query($sqlEditor)){
        while($aWysiwyg = $result->fetchRow(MYSQLI_ASSOC))
        {
            $template->set_var('FILE', $aWysiwyg['directory']);
            $template->set_var('NAME', $aWysiwyg['name']);
            $template->set_var('SELECTED', ((!\defined('WYSIWYG_EDITOR') || $aWysiwyg['directory'] == WYSIWYG_EDITOR) ? ' selected="selected"' : '') );
            $template->parse('wysiwyg_list', 'wysiwyg_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert language values
    $template->set_block('main_block', 'language_list_block', 'language_list');
    $sqlLang  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
              . 'WHERE `type` = \'language\' '
              . 'ORDER BY `directory`';
    if ($result = $database->query($sqlLang)){
        $template->set_var('ADDON_LANG_URL', WB_URL.'/modules/WBLingual/');
        while($aLang = $result->fetchRow(MYSQLI_ASSOC)) {
            $langIcons = (empty($aLang['directory']) ? 'none' : \strtolower($aLang['directory']));
            $template->set_var('CODE',        $aLang['directory']);
            $template->set_var('NAME',        $aLang['name']);
            $template->set_var('PAGE_LANG',   $langIcons);
            $template->set_var('FLAG',        $langIcons);
            $template->set_var('SELECTED',    (DEFAULT_LANGUAGE == $aLang['directory'] ? ' selected="selected"' : '') );
            $template->parse('language_list', 'language_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert default timezone values
    $template->set_block('main_block', 'timezone_list_block', 'timezone_list');
    require(ADMIN_PATH.'/interface/timezones.php');
    foreach($TIMEZONES AS $hour_offset => $title){
        // Make sure we dont list "System Default" as we are setting this value!
        if ($hour_offset != '-20') {
            $template->set_var('VALUE', $hour_offset);
            $template->set_var('NAME', $title);
            $template->set_var('SELECTED', ( (DEFAULT_TIMEZONE == $hour_offset*60*60) ? ' selected="selected"' : '' ) );
            $template->parse('timezone_list', 'timezone_list_block', true);
        }
    }
/*--------------------------------------------------------------------------------------
    // Insert default charset values
    $template->set_block('show_charset_block', 'charset_list_block', 'charset_list');
    require(ADMIN_PATH.'/interface/charsets.php');
    foreach($CHARSETS AS $code => $title) {
        $template->set_var('VALUE', $code);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', ( (DEFAULT_CHARSET == $code) ? ' selected="selected"':'' ) );
        $template->parse('charset_list', 'charset_list_block', true);
    }
-----*/
/*-------------------------------------------------------------------------------------------*/
    // Insert date format list
    $template->set_block('main_block', 'date_format_list_block', 'date_format_list');
    require(ADMIN_PATH.'/interface/date_formats.php');

    foreach($DATE_FORMATS as $format => $title) {
//        $format = str_replace('|', ' ', $format);
        $template->set_var('VALUE', $format);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', (($oReg->DefaultDateFormat == $format) ? ' selected="selected"' : '' ) );
        $template->parse('date_format_list', 'date_format_list_block', true);
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert time format list
    $template->set_block('main_block', 'time_format_list_block', 'time_format_list');
    require(ADMIN_PATH.'/interface/time_formats.php');

    foreach($TIME_FORMATS as $format => $title) {
//        $format = \str_replace('|', ' ', $format);
            $template->set_var('VALUE', $format);
        $template->set_var('NAME', $title);
        $template->set_var('SELECTED', ( ($oReg->DefaultTimeFormat == $format)?' selected="selected"' : '' ) );
        $template->parse('time_format_list', 'time_format_list_block', true);
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert templates
    $template->set_block('main_block', 'template_list_block', 'template_list');
    $sqlTheme = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
              . 'WHERE `type` = \'template\' '
              .   'AND `function` != \'theme\' '
              . 'ORDER BY `name`';
    if($result = $database->query($sqlTheme)) {
        while($addon = $result->fetchRow( \MYSQLI_ASSOC )) {
            $template->set_var('FILE', $addon['directory']);
            $template->set_var('NAME', $addon['name']);
            $template->set_var('SELECTED', (($addon['directory'] == DEFAULT_TEMPLATE) ? ' selected="selected"' : '') );
            $template->parse('template_list', 'template_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert backend theme
    $template->set_block('main_block', 'theme_list_block', 'theme_list');
    $sqlTheme = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
              . 'WHERE `type` = \'template\' '
              .   'AND `function` = \'theme\' '
              . 'ORDER BY `name`';
    if($result = $database->query($sqlTheme)) {
        while($addon = $result->fetchRow( \MYSQLI_ASSOC )) {
            $template->set_var('FILE', $addon['directory']);
            $template->set_var('NAME', $addon['name']);
            $template->set_var('SELECTED', (($addon['directory'] == DEFAULT_THEME) ? ' selected="selected"' : '') );
            $template->parse('theme_list', 'theme_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------*/
// Insert templates for search settings
    $template->set_block('main_block', 'search_template_list_block', 'search_template_list');
    $search_template = ( ($search_template == DEFAULT_TEMPLATE) || ($search_template == '')  ? '' : $search_template);
    $selected = ( ($search_template != DEFAULT_TEMPLATE) ) ?  ' selected="selected"' : '';
    $template->set_var(array(
            'FILE' => '',
            'NAME' => $TEXT['SYSTEM_DEFAULT'],
            'SELECTED' => $selected
        ));

    $template->parse('search_template_list', 'search_template_list_block', true);
    $sqlSearch = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
              . ' WHERE `type` = \'template\' '
              .    'AND `function` =\'template\' '
              . 'ORDER BY `name`';
    if ($result = $database->query($sqlSearch)){
        while($addon = $result->fetchRow(\MYSQLI_ASSOC)){
            $template->set_var('FILE', $addon['directory']);
            $template->set_var('NAME', $addon['name']);
            $template->set_var('SELECTED', (($addon['directory'] == $search_template) ? ' selected="selected"' :  '') );
            $template->parse('search_template_list', 'search_template_list_block', true);
        }
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert permissions values
    if($admin->get_permission('settings_advanced') != 'true'){
        $template->set_var('DISPLAY_ADVANCED_BUTTON', 'hide');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if multiple menus feature is enabled
    if(\defined('MULTIPLE_MENUS') && MULTIPLE_MENUS == 'true'){
        $template->set_var('MULTIPLE_MENUS_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('MULTIPLE_MENUS_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if page languages feature is enabled
    if(\defined('PAGE_LANGUAGES') && PAGE_LANGUAGES == 'true'){
            $template->set_var('PAGE_LANGUAGES_ENABLED', ' checked="checked"');
    } else {
            $template->set_var('PAGE_LANGUAGES_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if warn_page_leave feature is enabled
    if (\defined('WARN_PAGE_LEAVE') && WARN_PAGE_LEAVE == 'true'){
        $template->set_var('WARN_PAGE_LEAVE_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('WARN_PAGE_LEAVE_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    /* Make's sure GD library is installed */
    if (\extension_loaded('gd') && \function_exists('imageCreateFromJpeg')){
        $template->set_var('GD_EXTENSION_ENABLED', '');
    } else {
        $template->set_var('GD_EXTENSION_ENABLED', ' style="display: none;"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if section blocks feature is enabled
    if (\defined('SECTION_BLOCKS') && SECTION_BLOCKS == 'true'){
        $template->set_var('SECTION_BLOCKS_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('SECTION_BLOCKS_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if homepage redirection feature is enabled
    if (\defined('HOMEPAGE_REDIRECTION') && HOMEPAGE_REDIRECTION == 'true'){
        $template->set_var('HOMEPAGE_REDIRECTION_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('HOMEPAGE_REDIRECTION_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if show_start_datetime feature is enabled
    if (\defined('SHOW_START_DATETIME') && SHOW_START_DATETIME == 'true'){
        $template->set_var('SHOW_START_DATETIME_ENABLED', ' checked="checked"');
        $template->set_var('SHOW_START_DATETIME_DISABLED', '');
    } else {
        $template->set_var('SHOW_START_DATETIME_DISABLED', ' checked="checked"');
        $template->set_var('SHOW_START_DATETIME_ENABLED', '');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if debug mode feature is enabled
    if (\defined('DEBUG') && \DEBUG == true){
        $template->set_var('DEBUG_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('DEBUG_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
 // Work-out if developer infos feature is enabled
    if (\defined('DEV_INFOS') && \DEV_INFOS == true){
     $template->set_var(array(
       'DEV_INFOS_ENABLED' => ' checked="checked"',
//       'DEV_INFOS_DISABLED' => '',
       ));
    } else {
     $template->set_var(array(
       'DEV_INFOS_DISABLED' => ' checked="checked"',
//       'DEV_INFOS_ENABLED' => '',
       ));
    }
 // Work-out if developer infos feature is enabled
    if (\defined('SGC_EXECUTE') && \SGC_EXECUTE == true){
     $template->set_var(array(
       'SGC_EXECUTE_ENABLED' => ' checked="checked"',
//       'SGC_EXECUTE_DISABLED' => '',
       ));
    } else {
     $template->set_var(array(
       'SGC_EXECUTE_DISABLED' => ' checked="checked"',
//       'SGC_EXECUTE_ENABLED' => '',
       ));
    }

/*-------------------------------------------------------------------------------------------*/
    // Work-out setting of page_oldstyle enabled in page settings
    if (defined('PAGE_OLDSTYLE') && PAGE_OLDSTYLE == true){
        $template->set_var('PAGES_OLDSTYLE_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('PAGES_OLDSTYLE_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if token_fingerprint feature is enabled
    if (defined('SEC_TOKEN_FINGERPRINT') && SEC_TOKEN_FINGERPRINT == true){
        $template->set_var('FINGERPRINT_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('FINGERPRINT_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out which server os should be checked   {DISPLAY_CHMOD}
    if (OPERATING_SYSTEM == 'linux'){
        $template->set_var('LINUX_SELECTED', ' checked="checked"');
        $template->set_var('DISPLAY_CHMOD', ' style="display: block;"');
    } elseif(OPERATING_SYSTEM == 'windows') {
        $template->set_var('WINDOWS_SELECTED', ' checked="checked"');
        $template->set_var('DISPLAY_CHMOD', ' style="display: none;"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if manage sections feature is enabled
    if (MANAGE_SECTIONS){
        $template->set_var('MANAGE_SECTIONS_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('MANAGE_SECTIONS_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    $template->set_var('SMTP_VISIBILITY_AUTH', '');

//    if (WBMAILER_ROUTINE == 'phpmail'){
    if (in_array(WBMAILER_ROUTINE, ['phpmail','sendmail'])){
        $template->set_var('PHPMAIL_SELECTED', ' checked="checked"');
        $template->set_var('SMTP_VISIBILITY', ' style="display: none;"');
        $template->set_var('SMTP_USER_VISIBILITY', ' style="display: none;"');
        $template->set_var('SMTPMAIL_SELECTED', '');
        // $template->set_var('SMTP_AUTH_SELECTED', '');
    } elseif(WBMAILER_ROUTINE == 'smtp'){
        $template->set_var('PHPMAIL_SELECTED', '');
        $template->set_var('SMTPMAIL_SELECTED', ' checked="checked"');
        $template->set_var('SMTP_VISIBILITY', '');
        if (defined('WBMAILER_SMTP_AUTH') && WBMAILER_SMTP_AUTH=='true'){
            $template->set_var('SMTP_USER_VISIBILITY', '');
        } else {
            $template->set_var('SMTP_USER_VISIBILITY', ' style="display: none;"');
        }

        //$template->set_var('SMTP_VISIBILITY_AUTH', '');
    }
//    elseif(WBMAILER_ROUTINE == 'sendmail'){
//        $template->set_var('SENDMAIL_SELECTED', ' checked="checked"');
//        $template->set_var('SMTP_VISIBILITY', ' style="display: none;"');
//        $template->set_var('SMTP_VISIBILITY_AUTH', '');
//    }

    $template->set_var('SMTP_AUTH_CHECKED',( (WBMAILER_SMTP_AUTH == 'true') ? ' checked="checked"' : '') );
    $template->set_var('SMTP_DEBUG_ENABLED', ((WBMAILER_SMTP_DEBUG == 'true') ? ' checked="checked"' : '++'));
    $template->set_var('SMTP_DEBUG_DISABLED',((WBMAILER_SMTP_DEBUG == 'false') ? ' checked="checked"' : '--'));
    $template->set_block('show_access_block', 'smtp_debug_list_block', 'smtp_debug_list');
    $aSmtpDebug = ['DEBUG_OFF'=>'0','DEBUG_CLIENT'=>'1','DEBUG_SERVER'=>'2','DEBUG_CONNECTION'=>'3','DEBUG_LOWLEVEL'=>'4'];
    foreach($aSmtpDebug as $key => $item){
        $template->set_var('DEBUG_VALUE', $item);
        $template->set_var('DEBUG_NAME', $key);
        $template->set_var('DEBUG_SELECTED', ((WBMAILER_SMTP_DEBUG == $item) ? ' selected="selected"' : '') );
        $template->parse('smtp_debug_list', 'smtp_debug_list_block', true);
    }

    $template->set_block('show_access_block', 'smtp_port_list_block', 'smtp_port_list');
    $aSmtpPorts = ['25', '465', '587', '2525'];
    foreach($aSmtpPorts as $sPort){
        $template->set_var('VALUE', $sPort);
        $template->set_var('PNAME', $sPort);
        $template->set_var('SELECTED', ((WBMAILER_SMTP_PORT == $sPort) ? ' selected="selected"' : '') );
        $template->parse('smtp_port_list', 'smtp_port_list_block', true);
    }
/*-------------------------------------------------------------------------------------------*/
    $template->set_block('show_access_block', 'smtp_secure_list_block', 'smtp_secure_list');
    $aSmtpSecure = ['TLS', 'SSL'];
    foreach($aSmtpSecure as $sSecure){
        $template->set_var('VALUE', $sSecure);
        $template->set_var('SNAME', $sSecure);
        $template->set_var('SELECTED', ((WBMAILER_SMTP_SECURE == $sSecure) ? ' selected="selected"' : '') );
        $template->parse('smtp_secure_list', 'smtp_secure_list_block', true);
    }

    $template->set_block('show_access_block', 'smtp_secure_low_block', 'smtp_secure_low');
        $wbmailer_low_security =(defined('WBMAILER_LOW_SECURITY') ? WBMAILER_LOW_SECURITY : 'false');
        $template->set_var('SCHECKED', (($wbmailer_low_security) ? ' checked="checked"' : '') );
        $template->set_var('Check_SSL_SECURE', (($wbmailer_low_security) ? $oTrans->TEXT_WBMAILER_ENABLE_SSL_SECURE : $oTrans->TEXT_WBMAILER_DISABLE_SSL_SECURE ) );
    $template->parse('smtp_secure_low', 'smtp_secure_low_block', true);
/*-------------------------------------------------------------------------------------------*/
    // Work-out if intro feature is enabled
    if (INTRO_PAGE){
        $template->set_var('INTRO_PAGE_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('INTRO_PAGE_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if page trash feature is disabled, in-line, or separate
    if(PAGE_TRASH == 'disabled')
    {
        $template->set_var('PAGE_TRASH_DISABLED', ' checked="checked"');
        $template->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: none;');
    } elseif(PAGE_TRASH == 'inline')
    {
        $template->set_var('PAGE_TRASH_INLINE', ' checked="checked"');
        $template->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: none;');
    } elseif(PAGE_TRASH == 'separate')
    {
        $template->set_var('PAGE_TRASH_SEPARATE', ' checked="checked"');
        $template->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: inline;');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if media home folde feature is enabled
    if(HOME_FOLDERS)
    {
        $template->set_var('HOME_FOLDERS_ENABLED', ' checked="checked"');
    } else {
        $template->set_var('HOME_FOLDERS_DISABLED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert search select
    if(SEARCH == 'private'){
        $template->set_var('PRIVATE_SEARCH', ' selected="selected"');
    } elseif(SEARCH == 'registered') {
        $template->set_var('REGISTERED_SEARCH', ' selected="selected"');
    } elseif(SEARCH == 'none') {
        $template->set_var('NONE_SEARCH', ' selected="selected"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out if 777 permissions are set
    if((STRING_FILE_MODE == '0777') && (STRING_DIR_MODE == '0777')){
        $template->set_var('WORLD_WRITEABLE_SELECTED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out which file mode boxes are checked
    if(extract_permission(STRING_FILE_MODE, 'u', 'r')){
        $template->set_var('FILE_U_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'u', 'w')){
        $template->set_var('FILE_U_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'u', 'e'))
    {
        $template->set_var('FILE_U_E_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'g', 'r')){
        $template->set_var('FILE_G_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'g', 'w')){
        $template->set_var('FILE_G_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'g', 'e')){
        $template->set_var('FILE_G_E_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'o', 'r')){
        $template->set_var('FILE_O_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'o', 'w')){
        $template->set_var('FILE_O_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_FILE_MODE, 'o', 'e')){
        $template->set_var('FILE_O_E_CHECKED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Work-out which dir mode boxes are checked
    if(extract_permission(STRING_DIR_MODE, 'u', 'r')){
        $template->set_var('DIR_U_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'u', 'w')){
        $template->set_var('DIR_U_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'u', 'e')){
        $template->set_var('DIR_U_E_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'g', 'r')){
        $template->set_var('DIR_G_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'g', 'w')){
        $template->set_var('DIR_G_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'g', 'e')){
        $template->set_var('DIR_G_E_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'o', 'r')){
        $template->set_var('DIR_O_R_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'o', 'w')){
        $template->set_var('DIR_O_W_CHECKED', ' checked="checked"');
    }
    if(extract_permission(STRING_DIR_MODE, 'o', 'e')){
        $template->set_var('DIR_O_E_CHECKED', ' checked="checked"');
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert Server Email value into template
    $template->set_var('SERVER_EMAIL', SERVER_EMAIL);
/*-------------------------------------------------------------------------------------------*/
    $template->set_block('show_access_block', 'input_pages_directory_block', 'input_pages_directory');
    $template->set_block('show_access_block', 'show_pages_directory_block',  'show_pages_directory');
    $sql = 'SELECT COUNT(`page_id`) `numRows` FROM `'.TABLE_PREFIX.'pages` ';
    $iNumRow = $database->get_one($sql);

    $sPagesDir = '/'.\trim(PAGES_DIRECTORY, '/');
    $iPages  = sizeof(glob(WB_PATH.$sPagesDir.'/*'));
    if ((($sPagesDir !== '') && !$iPages)){
        $template->parse('input_pages_directory', 'input_pages_directory_block', true);
        $template->set_block('show_pages_directory', '');
    } else {
        $template->parse('show_pages_directory', 'show_pages_directory_block', true);
        $template->set_block('input_pages_directory', '');
    }

/*-------------------------------------------------------------------------------------------*/
    $template->set_block('show_access_block', 'input_media_directory_block', 'input_media_directory');
    $template->set_block('show_access_block', 'show_media_directory_block',  'show_media_directory');
    $sMediaDir = '/'.\trim(MEDIA_DIRECTORY, '/');
    $iMedia = sizeof(glob(WB_PATH.$sMediaDir.'/*'));
    if ((($sMediaDir !== '') && !$iMedia))  {
        $template->parse('input_media_directory', 'input_media_directory_block', true);
        $template->set_block('show_media_directory', '');
    } else {
        $template->parse('show_media_directory', 'show_media_directory_block', true);
        $template->set_block('input_media_directory', '');
    }
/*-------------------------------------------------------------------------------------------*/
    // Insert language text and messages
    $template->set_var([
                    'TEXT_FILES' => \strtoupper(\substr($TEXT['FILES'], 0, 1)).\substr($TEXT['FILES'], 1),
                    'TEXT_WEBSITE_SIGNATURE' => 'Signature',
                    ]);
/*-------------------------------------------------------------------------------------------*/

    if (!$bAdvanced && ($admin->ami_group_member('1') || $admin->get_permission('settings')))
    {
        $template->set_block('show_media_setting', '');
        $template->set_block('show_frontend', '');
        $template->set_block('show_login', '');
        $template->set_block('show_dsgvo_login', '');
    }else {
        $template->parse('show_media_setting',  'show_media_setting_block', true);
        $template->parse('show_frontend',       'show_frontend_block', true);
        $template->parse('show_login',          'show_login_block', true);
        $template->parse('show_dsgvo_login',    'show_dsgvo_login_block', true);
    }

    if (($bAdvanced && ($admin->ami_group_member('1') || $admin->get_permission('settings_advanced'))))
    {
        $template->parse('show_page_level_limit', 'show_page_level_limit_block', true);
        $template->parse('show_smart_login',      'show_smart_login_block', true);
    } else {
        $template->set_block('show_page_level_limit', '');
        $template->set_block('show_smart_login','');
    }

    if ($bAdvanced){
        $template->parse('show_checkbox_1',       'show_checkbox_1_block', true);
        $template->parse('show_checkbox_2',       'show_checkbox_2_block', true);
        $template->parse('show_checkbox_3',       'show_checkbox_3_block', true);
        $template->parse('show_php_error_level',  'show_php_error_level_block', true);
// no more needed, default is utf-8
        $template->set_block('show_charset', '');
        $template->parse('show_wysiwyg',          'show_wysiwyg_block', true);
        $template->parse('show_search',           'show_search_block', true);
        $template->parse('show_redirect_timer',   'show_redirect_timer_block', true);
    } else {
        $template->set_block('show_checkbox_1', '');
        $template->set_block('show_checkbox_2', '');
        $template->set_block('show_checkbox_3', '');
        $template->set_block('show_php_error_level', '');
        $template->set_block('show_charset', '');
        $template->set_block('show_wysiwyg', '');
        $template->set_block('show_search', '');
        $template->set_block('show_redirect_timer', '');
    }
    if ($bAdvanced && $admin->getUserId()=='1'){
        $template->parse('show_access', 'show_access_block', true);
        $template->parse('show_chmod_js', 'show_chmod_js_block', true);
        $template->parse('show_setting_js', 'show_setting_js_block', true);
    }else {
        $template->set_block('show_access', '');
        $template->set_block('show_chmod_js', '');
        $template->set_block('show_setting_js', '');
    }

/*-------------------------------------------------------------------------------------------*/
    $template->set_var(\Translate::getInstance()->getLangArray());
/*-------------------------------------------------------------------------------------------*/
// Parse template objects output
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
finally {
        PreCheck::resetMaxExecutionTime();
}

$admin->print_footer();

