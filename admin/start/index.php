<?php
/**
 *
 * @category        admin
 * @package         start
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2.6 and higher
 * @version         $Id: index.php 141 2018-10-03 19:01:52Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/start/index.php $
 * @lastmodified    $Date: 2018-10-03 21:01:52 +0200 (Mi, 03. Okt 2018) $
 *
*/
use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,SysInfo};

use vendor\phplib\Template;

    $ds           = DIRECTORY_SEPARATOR;
    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleDir.'/'.$sAddonName;
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {require($sAppPath.'config.php');}

    if (!defined('TABLE_PREFIX')){
        if (!function_exists('callInstaller')){
            $sRequestFromInitialize = true;
            require $sAppPath.'framework/functions.php';
        }
        callInstaller();
    }

//$admin = new \admin('##skip##');
    $admin = new \admin('Start','start');
// ---------------------------------------
    if (\defined('FINALIZE_SETUP')) {
        $sql = 'DELETE FROM `'.TABLE_PREFIX.'settings` WHERE `name`=\'finalize_setup\'';
        if ($database->query($sql)) {unset($sql);}
    }
// ---------------------------------------
    $msg  = '<br />';
    callUpgrade(WbAdaptor::getInstance());
/**
 * delete stored ip adresses default after 30 days
 */
    $iSecsPerDay = 86400;
    $iTotalDays  = 30;
    $sql = 'UPDATE `'.TABLE_PREFIX.'users` SET `login_ip` = \'\' WHERE `login_when` < '.(time()-($iSecsPerDay*$iTotalDays));
    if ($database->query($sql)) { /* do nothing */}

// Setup template object, parse vars to it, then parse it
    $oLang = Translate::getInstance();
    $oLang->enableAddon('templates/'.DEFAULT_THEME);

// Create new template object
    $tpl = new Template(dirname($admin->correct_theme_source('start.htt')));
    $tpl->set_file('page', 'start.htt');
    $tpl->set_block('page', 'main_block', 'main');
    $tpl->set_block('main_block','show_date_block','show_date');
// Insert values into the template object
    $iNow = time()+$oReg->Timezone;
    $aDefaultData = [
              'WELCOME_MESSAGE' => $oLang->MESSAGE_START_WELCOME_MESSAGE,
              'CURRENT_USER' => $oLang->MESSAGE_START_CURRENT_USER,
              'DISPLAY_NAME' => $admin->get_display_name(),
              'ADMIN_URL' => ADMIN_URL,
              'WB_URL' => WB_URL,
              'THEME_URL' => THEME_URL,
              'WB_VERSION' => WB_VERSION,
              'START_LIST' => ' ',
          ];

    $tpl->set_var($aDefaultData);

    $iNow   = time()+$oReg->Timezone;
    $ShowDate = sprintf('%s',\bin\helpers\PreCheck::getStrftime($oReg->DateFormat,$iNow,$oReg->Language)); //
    $ShowTime = sprintf('%s',\bin\helpers\PreCheck::getStrftime($oReg->TimeFormat,$iNow,$oReg->Language)); //
    $aMatches = [];
    $bPeriod  = preg_match('/[^0-9:\-\/_].*(am|pm|AM|PM)/i',$ShowTime,$aMatches);
    $sPeriod  = ($bPeriod ? trim($aMatches[0]) : "");
    $aDatesData = [
            'SHOW_DATE' => $ShowDate, //
            'SHOW_TIME' => $ShowTime, //
            'TIMEZONE'  => $oReg->Timezone,
            'PERIOD'    => $sPeriod,
            'LANGUAGE'  => strtolower($oReg->Language),
    ];

    $sDateBlock = ($oReg->ShowStartDatetime ?? false);
    if ((($sDateBlock===false)))
    {
        $tpl->set_block('show_date_block','');
    }
    else
    {
        $tpl->set_var($aDatesData);
        $tpl->parse( 'show_date', 'show_date_block', true);
    }



// Insert permission values into the template object
    $get_permission = (function($type='preferences', $ParentBlock='main_block') use ($admin, $tpl){
        $bRetVal = false;
        $sBlock  = '';
        $tpl->set_block($ParentBlock, 'show_'.$type.'_block', 'show_'.$type);
        if (($admin->get_permission($type) != true) && ($type!='preferences')) {
            $sBlock = 'show_'.$type;
            $tpl->set_block($sBlock, '');
        } else {
            $sBlock = "show_$type"."_block";
            $tpl->parse('show_'.$type, 'show_'.$type.'_block', true);
            $bRetVal = true;
        }
        return $bRetVal;
    });
/**/
    $get_permission ('pages');
    $get_permission ('media');
    $get_permission ('addons');
    $get_permission ('preferences');
    $get_permission ('settings');
    $get_permission ('admintools');
    $get_permission ('access');

//$msg .= (file_exists(WB_PATH.'/install/')) ?  $MESSAGE['START_INSTALL_DIR_EXISTS'] : $msg;
    $tpl->set_var('DISPLAY_WARNING', 'display:none;');
// Check if installation directory still exists
    if (\is_readable(WB_PATH.'/install/upgrade-script.php') ) {
// Check if user is part of Adminstrators group / better be a Systemadministrator
//      if ($admin->ami_group_member(1)){
        if ($admin->getUserId() == 1) {
            $tpl->set_var('WARNING', $msg );
        } else {
            $tpl->set_var('DISPLAY_WARNING', 'display:none;');
        }
    } else {
        $tpl->set_var('DISPLAY_WARNING', 'display:none;');
    }

// Insert "Add-ons" section overview (pretty complex compared to normal)
    $addons_overview = $oLang->TEXT_MANAGE.' ';
    $addons_count = 0;
    if($admin->get_permission('modules') == true)
    {
        $addons_overview .= '<a class="wb-bold" href="'.ADMIN_URL.'/modules/index.php">'.$oLang->MENU_MODULES.'</a>';
        $addons_count = 1;
    }
    if($admin->get_permission('templates') == true)
    {
        if($addons_count == 1) { $addons_overview .= ', '; }
        $addons_overview .= '<a class="wb-bold" href="'.ADMIN_URL.'/templates/index.php">'.$oLang->MENU_TEMPLATES.'</a>';
        $addons_count = 1;
    }
    if($admin->get_permission('languages') == true)
    {
        if($addons_count == 1) { $addons_overview .= ', '; }
        $addons_overview .= '<a class="wb-bold" href="'.ADMIN_URL.'/languages/index.php">'.$oLang->MENU_LANGUAGES.'</a>';
    }

// Insert "Access" section overview (pretty complex compared to normal)
    $access_overview = $oLang->TEXT_MANAGE.' ';
    $access_count = 0;
    if($admin->get_permission('users') == true) {
        $access_overview .= '<a class="wb-bold" href="'.ADMIN_URL.'/users/index.php">'.$oLang->MENU_USERS.'</a>';
        $access_count = 1;
    }
    if($admin->get_permission('groups') == true) {
        if($access_count == 1) { $access_overview .= ', '; }
        $access_overview .= '<a class="wb-bold" href="'.ADMIN_URL.'/groups/index.php">'.$oLang->MENU_GROUPS.'</a>';
        $access_count = 1;
    }

// Insert section names and descriptions
    $aLangData = [
          'PAGES' => $oLang->MENU_PAGES,
          'MEDIA' => $oLang->MENU_MEDIA,
          'ADDONS' => $oLang->MENU_ADDONS,
          'ACCESS' => $oLang->MENU_ACCESS,
          'PREFERENCES' => $oLang->MENU_PREFERENCES,
          'SETTINGS' => $oLang->MENU_SETTINGS,
          'ADMINTOOLS' => $oLang->MENU_ADMINTOOLS,
          'HOME_OVERVIEW' => $oLang->OVERVIEW_START,
          'PAGES_OVERVIEW' => $oLang->OVERVIEW_PAGES,
          'MEDIA_OVERVIEW' => $oLang->OVERVIEW_MEDIA,
          'ADDONS_OVERVIEW' => $addons_overview,
          'ACCESS_OVERVIEW' => $access_overview,
          'PREFERENCES_OVERVIEW' => $oLang->OVERVIEW_PREFERENCES,
          'SETTINGS_OVERVIEW' => $oLang->OVERVIEW_SETTINGS,
          'ADMINTOOLS_OVERVIEW' => $oLang->OVERVIEW_ADMINTOOLS,
    ];
    $tpl->set_var($aLangData);

// Parse template object
    $tpl->parse('main', 'main_block', false);
    $tpl->pparse('output', 'page');

// Print admin footer
$admin->print_footer();
