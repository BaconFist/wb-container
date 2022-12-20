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

/*--------------------------------------------------------------------------------------------------*/
/**
 * There are files which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*--------------------------------------------------------------------------------------------------*/
    $aFilesToDelete = [
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
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    echo $sErrMsg;
}