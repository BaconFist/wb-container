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
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: index.php 70 2018-09-17 16:48:30Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/preferences/index.php $
 * @lastmodified    $Date: 2018-09-17 18:48:30 +0200 (Mo, 17. Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;

// put all inside a function to prevent global vars
function build_page()//  &$admin, &$database
{
    global $HEADING, $TEXT;
    include_once(WB_PATH.'/framework/functions-utf8.php');

    $oReg = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();

    $oTrans->enableAddon($oReg->AcpDir.'/users');

    // Setup template object, parse vars to it, then parse it
    // Setup template object, parse vars to it, then parse it
    // Create new template object
    $template = new Template(dirname($oApp->correct_theme_source('preferences.htt')));
    $template->set_file( 'page', 'preferences.htt' );
    $template->set_block( 'page', 'main_block', 'main' );
// read user-info from table users and assign it to template
    $sql  = 'SELECT `display_name`,`username`,`email`,`timezone`,`date_format`,`time_format` FROM `'.TABLE_PREFIX.'users` '
          . 'WHERE `user_id` = '.(int)$oApp->getUserId();
    if ($oUsers = $oDb->query($sql) )
    {
        if ($aUser = $oUsers->fetchRow(MYSQLI_ASSOC) )
        {
            $template->set_var('DISPLAY_NAME', $aUser['display_name']);
            $template->set_var('USERNAME',     $aUser['username']);
            $template->set_var('EMAIL',        $aUser['email']);
            $template->set_var('ADMIN_URL',    ADMIN_URL);
            $template->set_var('THEME_URL',    THEME_URL);
        }
    }
// read available languages from table addons and assign it to the template
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
    $sql .= 'WHERE `type` = \'language\' ORDER BY `directory`';
    if ($res_lang = $oDb->query($sql) )
    {
        $template->set_block('main_block', 'language_list_block', 'language_list');
        $template->set_var('ADDON_LANG_URL', WB_URL.'/modules/WBLingual/');
        while( $rec_lang = $res_lang->fetchRow(MYSQLI_ASSOC) )
        {
            $langIcons = (empty($rec_lang['directory'])) ? 'none' : strtolower($rec_lang['directory']);
            $template->set_var('CODE',        $rec_lang['directory']);
            $template->set_var('NAME',        $rec_lang['name']);
            $template->set_var('PAGE_LANG',   $langIcons);
            $template->set_var('ADDON_LANG_URL', WB_URL.'/modules/WBLingual/');

            $template->set_var('SELECTED',    (LANGUAGE == $rec_lang['directory'] ? ' selected="selected"' : '') );
            $template->parse('language_list', 'language_list_block', true);
        }
    }
/* ------------------------------------------------------------------------ */
    $user_time = true;
    $sSelected = ' selected="selected"';
/* ------------------------------------------------------------------------ */
// Insert default timezone values
    $template->set_block('main_block', 'timezone_list_block', 'timezone_list');
    $userTimezone = $aUser['timezone'];
    if (!isset($TIMEZONES)){require(ADMIN_PATH.'/interface/timezones.php');}
    $mActualTimezone = (($oReg->DefaultTimezone === $aUser['timezone']) ? 'system_default' : (int)$oApp->get_timezone());
    foreach( $TIMEZONES as $hour_offset => $title )
    {
        $mTmpOffset = (is_numeric($hour_offset) ? $hour_offset*3600 : $hour_offset);
        $isSelected = (($mTmpOffset === $mActualTimezone) ? $sSelected : '');
//        echo nl2br(sprintf("%s===%s ist %s %s\n",$mTmpOffset,$mActualTimezone,(($mTmpOffset === $mActualTimezone) ? 'wahr' : 'falsch'),$isSelected));
        $template->set_var('NAME',     $title);
        $template->set_var('SELECTED', $isSelected);
        $template->set_var('VALUE',    $hour_offset);
        $template->parse('timezone_list', 'timezone_list_block', true);
    }

// Insert date format list
    $template->set_block('main_block', 'date_format_list_block', 'date_format_list');
    $userTimezone = $aUser['timezone'];
    if (!isset($DATE_FORMATS)){require ADMIN_PATH.'/interface/date_formats.php';}
    $sDateFormat = str_replace(' ', '|', $aUser['date_format']);
    $sDateFormat = (($oReg->DefaultDateFormat === $sDateFormat) ? 'system_default' : $sDateFormat);

    foreach( $DATE_FORMATS as $format => $title )
    {
        $template->set_var('NAME', str_replace('|', ' ', $title));
        $template->set_var('SELECTED', (($sDateFormat === $format) ? ' selected="selected"' : ''));
        $template->set_var('VALUE', $format);
        $template->parse('date_format_list', 'date_format_list_block', true);
    }
// Insert time format list
    $template->set_block('main_block', 'time_format_list_block', 'time_format_list');
    $userTimezone = $aUser['timezone'];
    include_once( ADMIN_PATH.'/interface/time_formats.php' );
    $sTimeFormat = str_replace(' ', '|', $aUser['time_format']);
    $sTimeFormat = (($oReg->DefaultTimeFormat === $sTimeFormat) ? 'system_default' : $sTimeFormat);

    foreach( $TIME_FORMATS as $format => $title )
    {
        $template->set_var('NAME',  str_replace('|', ' ', $title));
        $template->set_var('SELECTED', (($sTimeFormat === $format) ? ' selected="selected"' : ''));
        $template->set_var('VALUE', $format);
        $template->parse('time_format_list', 'time_format_list_block', true);
    }
/* ------------------------------------------------------------------------ */

// assign systemvars to template
    $aSystemVars = ['ADMIN_URL'  => ADMIN_URL,
                    'WB_URL'     => WB_URL,
                    'THEME_URL'  => THEME_URL,
                    'ACTION_URL' => ADMIN_URL.'/preferences/save.php',
                    'FTAN' => $oApp->getFTAN(),
                    'FORM_NAME' => 'preferences_save',
                  ];
    $template->set_var($aSystemVars);

// assign language vars
   $aLang = [ 'HEADING_MY_SETTINGS'      => $HEADING['MY_SETTINGS'],
              'HEADING_MY_EMAIL'         => $HEADING['MY_EMAIL'],
              'HEADING_MY_PASSWORD'      => $HEADING['MY_PASSWORD'],
              'TEXT_SAVE'                => $TEXT['SAVE'],
              'TEXT_RESET'               => $TEXT['RESET'],
              'TEXT_CLOSE'               => $TEXT['CLOSE'],
              'TEXT_DISPLAY_NAME'        => $TEXT['DISPLAY_NAME'],
              'TEXT_USERNAME'            => $TEXT['USERNAME'],
              'TEXT_EMAIL'               => $TEXT['EMAIL'],
              'TEXT_LANGUAGE'            => $TEXT['LANGUAGE'],
              'TEXT_TIMEZONE'            => $TEXT['TIMEZONE'],
              'TEXT_DATE_FORMAT'         => $TEXT['DATE_FORMAT'],
              'TEXT_TIME_FORMAT'         => $TEXT['TIME_FORMAT'],
              'TEXT_CURRENT_PASSWORD'    => $TEXT['CURRENT_PASSWORD'],
              'TEXT_NEW_PASSWORD'        => $TEXT['NEW_PASSWORD'],
              'TEXT_RETYPE_NEW_PASSWORD' => $TEXT['RETYPE_NEW_PASSWORD'],
              'TEXT_NEW_PASSWORD'        => $TEXT['NEW_PASSWORD'],
              'TEXT_RETYPE_NEW_PASSWORD' => $TEXT['RETYPE_NEW_PASSWORD'],
              'TEXT_NEED_CURRENT_PASSWORD' => $TEXT['NEED_CURRENT_PASSWORD'],
              'EMPTY_STRING'             => ''
            ];
//    $template->set_var($aLang);
    $template->set_var($oTrans->getLangArray());


// Parse template for preferences form
    $template->parse('main', 'main_block', false);
    $output = $template->finish($template->parse('output', 'page'));
    return $output;
} // end function

// test if valid $admin-object already exists (bit complicated about PHP4 Compatibility)
if( !(isset($admin) && is_object($admin) && (get_class($admin) == 'admin')) )
{

    require( '../../config.php' );
    $admin = new admin('Preferences');
}
echo build_page(); //$admin, $database
$admin->print_footer();
