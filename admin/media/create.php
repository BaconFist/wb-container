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
 * @version         $Id: create.php 155 2018-10-12 08:56:04Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/media/create.php $
 * @lastmodified    $Date: 2018-10-12 10:56:04 +0200 (Fr, 12. Okt 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;


// Print admin header
if (!\defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}

try {

// check if theme language file exists for the language set by the user (e.g. DE, EN)
    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

// Include the WB functions file
//    if (!defined('check_media_path') ){ require(WB_PATH.'/framework/functions.php');  }

// suppress to print the header, so no new FTAN will be set
    $admin = new \admin('Media', 'media_create', false);
    $sMediaDir = MEDIA_DIRECTORY;
    $sMedia    = \basename($sMediaDir); //

// Get dir name and target location
    $name = $oReg->Request->getParam('name');
// Remove bad characters
    $name = trim(media_filename($name),'.');

// Target location
    $target = str_replace($sMediaDir, '',$oReg->Request->getParam('create_target'));
    $sBacklinkUrl = ADMIN_URL.'/media/index.php?dir='.$target;

// Check to see if name or target contains ../
    if (strstr($name, '..')) {
//        $admin->print_error(sprintf('[%03d] %s',__LINE__,$MESSAGE['MEDIA_NAME_DOT_DOT_SLASH']),$sBacklinkUrl);
        throw new \Exception ($MESSAGE['MEDIA_NAME_DOT_DOT_SLASH']);
    }

    if (!$admin->checkFTAN())
    {
        throw new \Exception ($MESSAGE['GENERIC_SECURITY_ACCESS']);
    }
//   $aTarget   = \preg_split('/[\s,=+\;\:\/\.\|]+/', $target, -1, \PREG_SPLIT_NO_EMPTY);
// Create relative path of the new dir name  WB_PATH. .'/'.$name
    $directory = $target;

    $bIncludeMedia = (\strstr($directory,$target.$name)>=0);

    if (!check_media_path($directory, $bIncludeMedia)) {
        throw new \Exception ($MESSAGE['MEDIA_NAME_DOT_DOT_SLASH']);
    }
// Check to see if the folder already exists
    if (file_exists(WB_PATH.$sMediaDir.$directory.'/'.$name)) {
        throw new \Exception ($MESSAGE['MEDIA_DIR_EXISTS']);
    }

//if ( sizeof(createFolderProtectFile( $directory )) )
    if (!make_dir( WB_PATH.$sMediaDir.$directory.'/'.$name) )
    {
        throw new \Exception ($MESSAGE['MEDIA_DIR_NOT_MADE']);
    } else {
    //    createFolderProtectFile($directory);
        $usedFiles = [];
        // feature freeze
        // require_once(ADMIN_PATH.'/media/dse.php');
        $admin->print_header();
        $admin->print_success($MESSAGE['MEDIA_DIR_MADE'],$sBacklinkUrl);
    }

} catch (\Exception $ex) {

//    if (!function_exists ('xnl2br')) {require(WB_PATH . '/framework/functions.php');}
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_header();
    $admin->print_error ($sErrMsg, $sBacklinkUrl);
    exit;
}
// Print admin
$admin->print_footer();
