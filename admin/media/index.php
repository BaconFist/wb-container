<?php


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;
use vendor\phplib\Template;

/**
 *
 * @category        admin
 * @package         admintools
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.4.0 and higher
 * @version         $Id: index.php 155 2018-10-12 08:56:04Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/index.php $
 * @lastmodified    $Date: 2018-10-12 10:56:04 +0200 (Fr, 12. Okt 2018) $
 *
 */


    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
// Print admin header
//if (!defined( 'SYSTEM_RUN')){ require( dirname(dirname(__DIR__)).'/config.php' ); }
/* -------------------------------------------------------- */
    $admin = new \admin('Media', 'media');
    $oReg  = WbAdaptor::getInstance();
/* -------------------------------------------------------- */
    $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
    $ModuleUrl      = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl      = $oReg->AppUrl.$sAddonRel;
/* -------------------------------------------------------- */
    $oApp     = $oReg->getApplication();
    $oDb      = $oReg->getDatabase();
    $sDomain  = $oApp->getDirNamespace(__DIR__);
    $oTrans   = $oReg->getTranslate();
    $oTrans->enableAddon($sDomain);
    $aLang    = $oTrans->getLangArray();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */

    PreCheck::increaseMemory();

// Include the WB functions file
//    if (!function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}
    if (!function_exists('__unserialize')){include(__DIR__.'/parameters.php');}

    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

try {
//    $sPathRel = str_replace(MEDIA_DIRECTORY, '', $_REQUEST['dir']);
//    $sPathRel = filter_var('dir', INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
//    $sPathRel = filter_var('dir', INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $sPathRel = ($oReg->Request->getParam('dir'));
    $sBacklinkUrl = ADMIN_URL.'/media/index.php?dir='.$sPathRel;
    $aFtan = \bin\SecureTokens::getFTAN();
    $width  = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_width\' ');
    $height = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'media_height\' ');
    $sMessageMediaSizeInfo = PreCheck::xnl2br(sprintf($MESSAGE['MEDIA_SIZE_INFO'], $width, $height));

// Insert language text and messages
    $LangVars = [
                    'HEADING_MEDIA_MANAGEMENT' => $HEADING['HEADING_MEDIA_MANAGEMENT'],
                    'HEADING_BROWSE_MEDIA' => $HEADING['BROWSE_MEDIA'],
                    'HEADING_CREATE_FOLDER' => $HEADING['CREATE_FOLDER'],
                    'HEADING_UPLOAD_FILES' => $HEADING['UPLOAD_FILES'],
                    'MESSAGE_MEDIA_SIZE_INFO' => (($width!=0 || $height!=0) ? $sMessageMediaSizeInfo : $MESSAGE['MEDIA_NO SIZE_INFO']),
                    'TEXT_NAME' => $TEXT['TITLE'],
                    'TEXT_RELOAD' => $TEXT['RELOAD'],
                    'TEXT_TARGET_FOLDER' => $TEXT['TARGET_FOLDER'],
                    'TEXT_OVERWRITE_EXISTING' => $TEXT['OVERWRITE_EXISTING'],
                    'TEXT_FILES' => $TEXT['FILES'],
                    'TEXT_CREATE_FOLDER' => $TEXT['CREATE_FOLDER'],
                    'TEXT_UPLOAD_FILES' => $TEXT['UPLOAD_FILES'],
                    'FILE_SIZE' => 68,
                    'CHANGE_SETTINGS' => $TEXT['MODIFY_SETTINGS'],
                    'OPTIONS' => $TEXT['OPTION'],
                    'TEXT_UNZIP_FILE' => $TEXT['UNZIP_FILE'],
                    'TEXT_DELETE_ZIP' => $TEXT['DELETE_ZIP'],
                    'TEXT_DELETE_ARCHIVE' => $TEXT['DELETE_ARCHIVE'],
                    'MAX_FILE_SIZE' => PRECHECK::convertToByte('upload_max_filesize')*128,
//                    'FTAN' => $admin->getFTAN()
                ];
    $currentHome = $admin->get_home_folder();
    $currentHome = '';
    if ($currentHome){
        $dirs = directory_list(WB_PATH.MEDIA_DIRECTORY.$currentHome);
    }
    else
    {
        $dirs = directory_list(WB_PATH.MEDIA_DIRECTORY);
    }
//$sPathRel
    $sKey = '';
    $sMediaSelected = ' selected="selected"';
//    $template->set_var('MEDIA_SELECTED', $sMediaSelected);
//    $template->parse('dir_list', 'dir_list_block', true);

    $array_lowercase = array_map('strtolower', $dirs);
    array_multisort($array_lowercase, SORT_ASC, SORT_STRING, $dirs);
    $aSelected = [];
    $sMedia = '/'.trim($currentHome,'/'); // MEDIA_DIRECTORY.
// Workout if the up arrow should be shown
    if((empty($dirs)) || ($dirs==$currentHome) || (!$oReg->Request->issetParam('dir'))) {
        $display_up_arrow = 'hide';
    } else {
        $display_up_arrow = '';
    }
    $iIndex = 0;
    $aDirs = [];
    foreach($dirs as $name) {
        $sKey = str_replace(WB_PATH, '', $name);

        $aSelected[$sKey]   = $bSelected = (($sKey == $sPathRel) ? true : false);
        $aSelected[$sMedia] = $bMediaSelected = (($bSelected) ? true : false);
        if (!isset($home_folders[$sKey])) {
            $sDirname = $sKey;
            $iSteps = count(explode('/',$sDirname))-1;
            $aDirs[$iIndex]['path'] = $name;
            $aDirs[$iIndex]['value'] = $sDirname;
            $aDirs[$iIndex]['name'] = str_repeat(' --- ',$iSteps).basename($sDirname);
            $aDirs[$iIndex]['level'] = $iSteps;
            $aDirs[$iIndex]['selected'] = $aSelected;
            $iIndex++;
        }
    }// end foreach

// insert urls
    $aUrlLinks = [
                    'HOME_DIRECTORY' => $currentHome,
                    'DISPLAY_UP_ARROW' => $display_up_arrow, // **!
                    'MEDIA_DIRECTORY' => MEDIA_DIRECTORY,
                    'MEDIA_DIR' => \trim(MEDIA_DIRECTORY, '\\/'),
                    'CURRENT_DIR' => $sPathRel,
//                    'CURRENT_DIR' => $directory,
                    'ADMIN_URL' => ADMIN_URL,
                    'WB_URL' => WB_URL,
                    'THEME_URL' => THEME_URL
                ];

// force to set media_directory an exisiting or default value
    $sMediaDir = (defined('MEDIA_DIRECTORY') && !empty(MEDIA_DIRECTORY) ? MEDIA_DIRECTORY  : '/media');
    if (defined('MEDIA_DIRECTORY') && empty(MEDIA_DIRECTORY) || !defined('MEDIA_DIRECTORY')) {
        db_update_key_value('settings','media_directory', $sMediaDir);
        throw new \Exception ($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    }

// MEDIA_DIRECTORY is not set, create dir
    if (!make_dir(WB_PATH.MEDIA_DIRECTORY)){
        throw new \Exception ($oTrans->MESSAGE_MEDIA_DIR_ACCESS_DENIED);
    }

// Create new template object
    $template = new Template(dirname($admin->correct_theme_source('media.htt')));
    $template->set_file('page', 'media.htt');
    $template->set_block('page', 'main_block', 'main');

// Get home folder not to show
    $home_folders = get_home_folders();

// Insert values
    $template->set_block('main_block', 'dir_list_block', 'dir_list');
    foreach($aDirs as $name) {
        $sKey = $name['value'];
        $aSelected[$sKey]   = $sSelected = (($sKey == $sPathRel) ? ' selected="selected"' : '');
        $aSelected[$sMedia] = $sMediaSelected = (($sSelected) ? ' selected="selected"' : '');
        if (!isset($home_folders[$sKey])) {
            $sDirname = $sKey;
            $iSteps = count(explode('/',$sDirname))-1;
            $template->set_var('VALUE', $sDirname);
            $template->set_var('LEVEL', $iSteps);
            $template->set_var('SELECTED', $sSelected );
            $template->set_var('MEDIA_SELECTED', $sMediaSelected);
            $template->set_var('NAME',str_repeat(' -- ',$iSteps).basename($sDirname));
            $template->parse('dir_list', 'dir_list_block', true);
        }
    } // end foreach

// Insert permissions values
    if($admin->get_permission('media_create') != true) {
        $template->set_var('DISPLAY_CREATE', 'hide');
    }

    if($admin->get_permission('media_upload') != true) {
        $template->set_var('DISPLAY_UPLOAD', 'hide');
    }

    if (isset($pathsettings['global'])&&($_SESSION['GROUP_ID'] != 1 && $pathsettings['global']['admin_only'])) { // Only show admin the settings link
        $template->set_var('DISPLAY_SETTINGS', 'hide');
    }
    $template->set_var($LangVars);
    $template->set_ftan($aFtan);
    $template->set_var($aUrlLinks);

// Parse template object
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');

}catch (\Exception $ex) {

    $sErrMsg = PreCheck::xnl2br(sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBacklinkUrl);
    exit;
}

$admin->print_footer();
