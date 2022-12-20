<?php
/**
 *
 * @category        admin
 * @package         admintools
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: delete.php 159 2018-10-14 05:34:52Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/delete.php $
 * @lastmodified    $Date: 2018-10-14 07:34:52 +0200 (So, 14. Okt 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue,ParentList,StopWatch};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;
use vendor\phplib\Template;

    $sAddonFile   = str_replace(['\\','//'],'/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {require($sAppPath.'config.php');}
/* -------------------------------------------------------------------- */
// Include the WB functions file
//    if (!\function_exists('check_media_path')){require(WB_PATH.'/framework/functions.php'); }
    if (!\function_exists('mediaScanDir')){require('MediaScanDir.inc');}
/* -------------------------------------------------------------------- */
// Get the current dir
//    $directory = $admin->get_get('dir');
    $directory = ($oReg->Request->getParam('dir'));
    $directory = ($directory == '/') ?  '' : $directory;
    $dirlink = 'browse.php?dir='.$directory;
    $rootlink = 'browse.php?dir=';
//    $sBacklinkUrl = ADMIN_URL.'/media/index.php?dir='.$directory;
    $sBacklinkUrl = 'browse.php?dir='.$directory;
/* -------------------------------------------------------------------- */
// Create admin object
    $admin = new \admin('Media', 'media_delete',false,false);
    $admin->print_header('',false);
// Check permission ..
    if (($admin->get_permission('media_delete')==false)){
        $admin->print_error($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES'],$sBacklinkUrl,false );
    }

// Check to see if it contains ..
    if (!check_media_path($directory)) {
        $admin->print_error($MESSAGE['MEDIA_DIR_DOT_DOT_SLASH'],$sBacklinkUrl,false );
    }

// Get the file id
    $file_id = ($admin->checkIDKEY('id', false, $_SERVER['REQUEST_METHOD']));
    if (!$file_id) {
        $admin->print_error(\sprintf('[%d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],$iFileId),$sBacklinkUrl, false);
    }
/*
    $file_id = \bin\SecureTokens::checkIDKEY('id');
    if (isset($file_id)) {
        $admin->print_error(sprintf('[%d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],$iFileId),$dirlink, false);
    }
*/
// Get home folder not to show
    $home_folders = get_home_folders();
    $usedFiles = [];
// feature freeze
// require_once(ADMIN_PATH.'/media/dse.php');
/*

if(!empty($currentdir)) {
    $usedFiles = $Dse->getMatchesFromDir( $directory, DseTwo::RETURN_USED);
}
*/

//    $file_id--;
    // scan given dir
    $aListDir = mediaScanDir($directory);
    $delete_file = '';
    if (isset($aListDir)) {
        foreach($aListDir as $temp_id => $name) {
            if (($file_id == $temp_id)) {
                $delete_file = $name;
                $type = \is_dir(WB_PATH.MEDIA_DIRECTORY.$directory.'/'.$delete_file) ? 'folder' : 'file';
            }
//            $temp_id++;
        }
    }

// Check to see if we could find an id to match
    if (!isset($delete_file)) {
        $admin->print_error(\sprintf('[%d] %s '.$MESSAGE['MEDIA_FILE_NOT_FOUND'],__LINE__,$delete_file), $sBacklinkUrl, false);
    }

    $relative_path = WB_PATH.MEDIA_DIRECTORY.'/'.$directory.'/'.$delete_file;
    // Check if the file/folder exists
    if (!\is_writable($relative_path)) {
        $admin->print_error(\sprintf('[%d] %s '.$MESSAGE['MEDIA_FILE_NOT_FOUND'],__LINE__,$delete_file), $sBacklinkUrl, false);
    }

// Find out whether its a file or folder
/**/
    if ($type == 'folder') {
        // Try and delete the directory
        if (rm_full_dir($relative_path)) {
/*
            echo '
              <script>
              // Set the value of the location object
                parent.document.location.href="index.php";
              </script>
            ';
*/
            $admin->print_success($MESSAGE['MEDIA_DELETED_DIR'], $sBacklinkUrl);
        } else {
            $admin->print_error($MESSAGE['MEDIA_CANNOT_DELETE_DIR'], $sBacklinkUrl, false);
        }
    } else {
        // Try and delete the file
        if (\unlink($relative_path)) {
            $admin->print_success($MESSAGE['MEDIA_DELETED_FILE'], $sBacklinkUrl);
        } else {
            $admin->print_error($MESSAGE['MEDIA_CANNOT_DELETE_FILE'], $sBacklinkUrl, false);
        }
    }

// Print admin
$admin->print_footer();
