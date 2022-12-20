<?php

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use bin\requester\HttpRequester;

/*--------------------------------------------------------------------------------------------------*/
/**
 *
 *
 */
/*--------------------------------------------------------------------------------------------------*/

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */
 try {

    $sAppPath = str_replace(DIRECTORY_SEPARATOR,'/',\dirname(\dirname(__DIR__)));
//    if (!\function_exists('rm_full_dir')) {require ($sAppPath.'/framework/functions.php');}
    $sAddonPath = str_replace(DIRECTORY_SEPARATOR,'/',__DIR__);

//    \trigger_error(\sprintf('[%d] Database Error:: %s',__LINE__, $database->get_error()), E_USER_NOTICE);

    $DEFAULT_TEMPLATE = (DEFAULT_TEMPLATE ?: 'DefaultTemplate');
    if (DEFAULT_THEME !== $DEFAULT_THEME) {
      db_update_key_value('settings', 'default_theme', $DEFAULT_THEME);
    //  exit();
    }

/*--------------------------------------------------------------------------------------------------*/
/**
 * There are entries which are updating settings table.
 *
 */
/*--------------------------------------------------------------------------------------------------*/
    $cfg = array(
        'media_width'         => (\defined('MEDIA_WIDTH') ? MEDIA_WIDTH : '0'),
        'media_height'        => (\defined('MEDIA_HEIGHT') ? MEDIA_HEIGHT : '0'),
        'media_compress'      => (\defined('MEDIA_COMPRESS') ? MEDIA_COMPRESS : '75'),
        'mediasettings'       => (\defined('MEDIASETTINGS') ? MEDIASETTINGS : ''),
    );
    foreach($cfg as $key=>$value) {
        db_update_key_value('settings', $key, $value);
    }
/*--------------------------------------------------------------------------------------------------*/
/**
 * There are files which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*--------------------------------------------------------------------------------------------------*/
    $aFilesToDelete = [
        '/inc/'
        ];

    foreach ($aFilesToDelete as $sFilename){
        if (\is_writeable($sAddonPath.$sFilename)) {
            if (\substr($sFilename, -1) == '/'){
              rm_full_dir($sAddonPath.$sFilename);
            } else {
            \unlink($sAddonPath.$sFilename);
            }
        }
    } // end foreach

} catch (\Exception $ex) {

//   if (!function_exists ('xnl2br')) {require(WB_PATH . '/framework/functions.php');}
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    echo $sErrMsg;
}