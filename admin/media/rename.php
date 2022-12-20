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
 * @version         $Id: rename.php 159 2018-10-14 05:34:52Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/rename.php $
 * @lastmodified    $Date: 2018-10-14 07:34:52 +0200 (So, 14. Okt 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue,ParentList,StopWatch};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;
use vendor\phplib\Template;

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

    $admin = new \admin('Media', 'media_rename', false,false );
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


// Include the WB functions file
//    if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}
    if (!\function_exists('mediaScanDir')){require('MediaScanDir.inc');}

/* -------------------------------------------------------------------- */
/*
// Get the current dir
    $directory = (($oRequest->getParam('dir')));
    $directory = (($directory == '/') ?  '' : $directory);
    $sBacklinkUrl = $oReg->AcpPath.'media/index.php?dir='.$directory;
    $dirlink      = 'browse.php?dir='.$directory;
    $rootlink     = 'browse.php?dir=';
//    $file_id = ($admin->get_get('id'));
*/
// Get the current dir
//    $directory = $admin->get_get('dir');
    $directory = ($oReg->Request->getParam('dir'));
    $directory = ($directory == '/') ?  '' : $directory;
    $sMediaPath = str_replace(['\\','//'],'/',$oReg->AppPath.$oReg->MediaDir.$directory);
    $dirlink = 'browse.php?dir='.$directory;
    $rootlink = 'browse.php?dir=';
//    $sBacklinkUrl = ADMIN_URL.'/media/index.php?dir='.$directory;
    $sBacklinkUrl = 'browse.php?dir='.$directory;

/* -------------------------------------------------------------------- */
// Check permission ..
    if (($admin->get_permission('media_rename')==false)){
        $admin->print_error($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES'],$sBacklinkUrl,false );
    }

// first Check to see if it contains ..
    if (!check_media_path($directory)) {
//        $admin->print_error('['.__LINE__.'] '.$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'],$rootlink, false);
        $sBacklinkUrl = $rootlink;
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_DIR_DOT_DOT_SLASH']);
        throw new \Exception ($sMessage);
    }

    $aParentPaths = $sMediaPath; //.'/'.$directory
// Get the file id
    $file_id = $admin->getIdFromRequest('id');
    if (($file_id ===0)) {
//        $admin->print_error(sprintf('[%d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],$file_id),$sBacklinkUrl, false);
        $sMessage = sprintf("%s",$MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($sMessage);
    }

    // scan given dir
    $aListDir = mediaScanDir($directory);
    $rename_file = '';
//    $file_id--;
    if (!empty($aListDir)) {
//        sort($aListDir, SORT_REGULAR|SORT_FLAG_CASE);
          $rename_file = ($aListDir[$file_id] ?? '');
          $type = \is_dir($sMediaPath.'/'.$rename_file) ? 'folder' : 'file';
    }

    if (!isset($rename_file)) {
//        $admin->print_error($MESSAGE['MEDIA_FILE_NOT_FOUND'], $dirlink, false);
        $sBacklinkUrl = $dirlink;
        $sMessage = sprintf("%s",$MESSAGE['MEDIA_FILE_NOT_FOUND']);
        throw new \Exception ($sMessage);
    }

    $sExtension = '';
    $sBasename = $rename_file;
    \preg_match (
        '/^(?:.*?[\/])?([^\/]*?)\.([^\.]*)$/iU',
        str_replace('\\', '/', $rename_file),
        $aMatches
    );
    if (\sizeof($aMatches) == 3) {
        $sBasename  = $aMatches[1];
        $sExtension = $aMatches[2];
    }

// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source('media_rename.htt')));
    $template->set_file('page', 'media_rename.htt');
    $template->set_block('page', 'main_block', 'main');
//echo WB_PATH.'/media/'.$directory.'/'.$rename_file;
    if($type == 'folder') {
        $template->set_var('DISPlAY_EXTENSION', 'hide');
        $extension = '';
    } else {
        $template->set_var('DISPlAY_EXTENSION', '');
        $extension = \strstr($rename_file, '.');
    }

    if($type == 'folder') {
        $type = $TEXT['FOLDER'];
    } else {
        $type = $TEXT['FILE'];
    }

    $file_id_key = \bin\SecureTokens::getIDKEY($file_id);
    $template->set_var(array(
                    'THEME_URL' => THEME_URL,
                    'FILENAME' => $rename_file,
                    'BASENAME' => $sBasename,
                    'DIR' => $directory,
                    'FILE_ID' => $file_id_key,
                    'FILE_ID_NUM' => $file_id,
                    'TYPE' => $type,
                    'EXTENSION' => $sExtension,
                    'TEXT_TYPE' => (\is_dir( $aParentPaths.'/'.$rename_file) ? $TEXT['FOLDER'] : $TEXT['FILE']),
                    'FTAN' => $admin->getFTAN()
                )
            );

// Insert language text and messages
    $template->set_var(array(
                    'TEXT_RENAME' => $TEXT['RENAME'],
                    'TEXT_CANCEL' => $TEXT['CANCEL'],
                    'TEXT_UP' => $TEXT['UP'],
                    'TEXT_OVERWRITE_EXISTING' => $TEXT['OVERWRITE_EXISTING']
                )
            );

// Parse template object
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');

}catch (\Exception $ex) {
//    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBacklinkUrl);
    exit;
}
