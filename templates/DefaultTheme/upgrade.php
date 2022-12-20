<?php
/**
 *
 * @category        templates
 * @package         themes
 * @subpackage      DefaultTheme
 * @subpackage      install
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3 SP7
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: upgrade.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/templates/DefaultTheme/upgrade.php $
 * @lastmodified    $Date: 2019-03-17 07:05:56 +0100 (So, 17. Mrz 2019) $
 * @created
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
//if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

    $aErrorMsg    = [];
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', (__DIR__));
    $sAddonName = basename($sAddonPath);
    $sAddonTable  = 'mod_'.strtolower($sAddonName);
//    $sActionFile  = strtolower(str_replace('', '', $sCommand).'.php');

    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
/*
    if (\is_writable(WB_PATH.'/temp/cache')) {
        \Translate::getInstance()->clearCache();
    }
*/
    \Translate::getInstance()->enableAddon('templates\\'.basename(__DIR__));
// files to be remove
    $aFilesToDelete  = ['/css/customAlert.css',
                        '/css/dialogBox.css',
                        '/images/thumbs-up.psd',
                        '/images.ico/',
                        '/images.org/',
                        '/lib/min/',
                        '/lib/select2/',
                        '/lib/clearTranslateCache.php',
                        '/lib/rebuildAccessFiles.php',
                        '/lib/w3data.php',
                        '/LoadErrorlog.php',
                        '/delete_errorlog.php',
                        '/templates/addon_tpl.htt'
                        ];
           PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);

    if (\file_exists(__DIR__.'/images/flags')){rm_full_dir(__DIR__.'/images/flags');}
