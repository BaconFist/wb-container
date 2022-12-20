<?php
/**
 *
 * @category        admin
 * @package         media
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: rename2.php 159 2018-10-14 05:34:52Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/rename2.php $
 * @lastmodified    $Date: 2018-10-14 07:34:52 +0200 (So, 14. Okt 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue,ParentList,StopWatch};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

    $sAddonFile   = str_replace(['\\','//','\\\\'],'/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {require($sAppPath.'config.php');}

try {
    $admin = new \admin('Media', 'media_rename', false);
    $admin->print_header('',false);

    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }

    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();

// Create admin object
//    if ($admin->get_permission('Media', 'media_rename', false)){
//    }
//    if (!defined('STYLE')){define('STYLE',strtolower($admin->getSection()));}
// Include the WB functions file
//    if (!\function_exists('make_dir')) {require (WB_PATH.'/framework/functions.php');}
    if (!\function_exists('mediaScanDir')){require('MediaScanDir.inc');}
//  Pattern check for potentially malicious files extensions
    $forbidden_file_types  = (''.\preg_replace( '/\s*[,;\|#]\s*/','|',RENAME_FILES_ON_UPLOAD));

// Get the current dir
//    $requestMethod = '_'.\strtoupper($_SERVER['REQUEST_METHOD']); //
//    $directory   = (isset(${$requestMethod}['dir'])) ? ${$requestMethod}['dir'] : '';
    $directory = (($oRequest->getParam('dir')));
    $directory = (($directory == '/') ?  '' : $directory);
    $sMediaPath = str_replace(['\\','//','\\\\'],'/',$oReg->AppPath.$oReg->MediaDir.$directory);

    //$sBacklinkUrl = ADMIN_URL.'/media/index.php';
    $sBacklinkUrl = $oReg->AcpUrl.'media/index.php?dir='.$directory;
    $dirlink  = 'browse.php?dir='.$directory;
    $rootlink = 'browse.php?dir=';

// first Check to see if it contains ..
    if (!check_media_path($directory)) {
//        $admin->print_error('['.__LINE__.'] '.$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'],$rootlink, false);
        $sBacklinkUrl = $rootlink;
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH']);
        throw new \Exception ($sMessage);
    }


    $file_id = $admin->getIdFromRequest('id');
    if (($file_id ===0)) {
//        $admin->print_error(sprintf('[%d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],$file_id),$sBacklinkUrl, false);
        $sMessage = sprintf("%s",$MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($sMessage);
    }
// Get home folder not to show
    $home_folders = get_home_folders();

    // scan given dir
    $aListDir = mediaScanDir($directory);
    $rename_file = '';
// Get the temp id
    if (!empty($aListDir)) {
//        sort($aListDir, SORT_REGULAR|SORT_FLAG_CASE);
          $rename_file = ($aListDir[$file_id] ?? '');
          $type = \is_dir($sMediaPath.'/'.$rename_file) ? 'folder' : 'file';

/*
        foreach($aListDir as $temp_id => $name)
        {
            if (($file_id == $temp_id)) {
                $rename_file = $name;
            }
//            $temp_id++;
        }
*/
    }

    if (!isset($rename_file)) {
//        $admin->print_error($MESSAGE['MEDIA_FILE_NOT_FOUND'], $sBacklinkUrl, false);
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_FILE_NOT_FOUND']);
        throw new \Exception ($sMessage);
    }

    $file_id_key = \bin\SecureTokens::getIDKEY($file_id);
    $old_name = $admin->StripCodeFromText($oReg->Request->getParam('old_name'));
    $new_name = $admin->StripCodeFromText(media_filename($oReg->Request->getParam('name')));
// Check if they entered a new name
    if (($new_name=='')) {
//        $admin->print_error($MESSAGE['MEDIA_BLANK_NAME'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_BLANK_NAME']);
        throw new \Exception ($sMessage);
    } else {
    }
// Check if they entered an extension   $oReg->Request->getParam('dir')
    if ($type == 'file') {
        if (\strstr($new_name,'.')){
            $new_name = \str_replace('.', '_', $new_name);
        }
        if (media_filename($oReg->Request->getParam('extension')) == "") {
            $name = $new_name;
        } else {
            $extension = $admin->StripCodeFromText(media_filename($oReg->Request->getParam('extension')));
            $name = $new_name.'.'.\trim($extension,'.');
        }
    } elseif ($type == 'folder') {
        $extension = '';
        $name = $new_name;
    }

// Join new name and extension
    $sPathname = str_replace(['\\','//','\\\\'],'/',$sMediaPath.'/'.$name);
    $info = \pathinfo($sPathname);
    $ext  = isset($info['extension']) ? $info['extension'] : '';
    $dots = (\substr($info['basename'], 0, 1) == '.') || (\substr($info['basename'], -1, 1) == '.');

    if (\preg_match('/'.$forbidden_file_types.'$/i', $ext) || $dots == '.' ) {
//        $admin->print_error($MESSAGE['MEDIA_CANNOT_RENAME'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("Forbidden Filetypes, %s",$MESSAGE['MEDIA_CANNOT_RENAME']);
        throw new \Exception ($sMessage);
    }

// Check if the name contains ..
    if (\strstr($name, '..')) {
//        $admin->print_error(sprintf('[%03d] %s',__LINE__,$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH']), "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH']);
        throw new \Exception ($sMessage);
    }

// Check if the name is index
    if ($name == 'index') {
//        $admin->print_error($MESSAGE['MEDIA_NAME_INDEX_PHP'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_NAME_INDEX_PHP']);
        throw new \Exception ($sMessage);
    }

// Check that the name still has a value
    if ($name == '') {
//        $admin->print_error($MESSAGE['MEDIA_BLANK_NAME'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_BLANK_NAME']);
        throw new \Exception ($sMessage);
    }
/*
    $sPathname = str_replace(['\\','//','\\\\'],'/',$sMediaPath.'/'.$name);
    $info = \pathinfo($sPathname);
    $ext  = isset($info['extension']) ? $info['extension'] : '';
    $dots = (\substr($info['basename'], 0, 1) == '.') || (\substr($info['basename'], -1, 1) == '.');
*/
    if (\preg_match('/'.$forbidden_file_types.'$/i', $ext) || $dots == '.' ) {
//        $admin->print_error($MESSAGE['MEDIA_CANNOT_RENAME'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_CANNOT_RENAME']);
        throw new \Exception ($sMessage);
    }

// Check if we should overwrite or not
    if ($admin->get_post('overwrite') != 'yes' && \is_readable($sPathname) == true) {
        if ($type == 'folder') {
//            $admin->print_error($MESSAGE['MEDIA_DIR_EXISTS'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_DIR_EXISTS']);
        throw new \Exception ($sMessage);
        } else {
//            $admin->print_error($MESSAGE['MEDIA_FILE_EXISTS'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_FILE_EXISTS']);
        throw new \Exception ($sMessage);
        }
    }

// Try and rename the file/folder
    $sOldname = str_replace(['\\','//','\\\\'],'/',$sMediaPath.'/'.$rename_file);
    $sNewname = str_replace(['\\','//','\\\\'],'/',$sMediaPath.'/'.$name);
    sleep(1);    // this does the trick
    if (\rename($sOldname, $sNewname)===true) {
        $usedFiles = [];
        // feature freeze
        // require_once(ADMIN_PATH.'/media/dse.php'); $sBacklinkUrl  $rootlink  $dirlink
        $admin->print_success($MESSAGE['MEDIA_RENAMED'], $dirlink);
    } else {
//        $admin->print_error($MESSAGE['MEDIA_CANNOT_RENAME'], "rename.php?dir=$directory&id=$file_id_key", false);
        $sBacklinkUrl = "rename.php?dir=$directory&id=$file_id_key";
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_CANNOT_RENAME']);
        throw new \Exception ($sMessage);
    }
}catch (\Exception $ex) {
//    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBacklinkUrl);
    exit;
}
