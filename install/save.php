<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of install/save.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: save.php 338 2019-04-24 15:08:47Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;



    $sAddonFile   = str_replace('\\','/',__FILE__);
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = $sAddonPath;
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

    $bLocalDebug  = is_readable($sAddonPath.'.setDebug');
    // Only for development prevent secure token check,
    $bSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
    if (!\class_exists('Sanitize')){require ($sAppPath.'framework/Sanitize.php');}
    if (!\class_exists('\src\Interfaces\Requester')) {require ($sAppPath.'framework/Interfaces/Requester.php');}
    if (!\class_exists('\bin\Requester\HttpRequester')) {require ($sAppPath.'framework/HttpRequester.php');}
// activate requester --------------------------------------------------------------------
    $oRequest = \bin\Requester\HttpRequester::getInstance();
 // get request method
    $requestMethod = \strtoupper($oRequest->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oRequest->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oRequest->getParam($sName);
    }

// Function to workout what the default permissions are for files created by the webserver
    function default_file_mode($temp_dir) {
        if (\version_compare(\PHP_VERSION, '7.2.0', '>=') && \is_writable($temp_dir)) {
            $filename = $temp_dir.'/test_permissions.txt';
            $handle = \fopen($filename, 'w');
            \fwrite($handle, 'This file gets the default file permissions');
            \fclose($handle);
            $default_file_mode = '0'.\substr(\sprintf('%o', \fileperms($filename)), -3);
            \unlink($filename);
        } else {
            $default_file_mode = '0777';
        }
        return $default_file_mode;
    }

// Function to workout what the default permissions are for directories created by the webserver
    function default_dir_mode($temp_dir) {
        if (\version_compare(\PHP_VERSION, '7.4.0', '>=') && \is_writable($temp_dir)) {
            $dirname = $temp_dir.'/test_permissions/';
            \mkdir($dirname);
            $default_dir_mode = '0'.\substr(\sprintf('%o', \fileperms($dirname)), -3);
            \rmdir($dirname);
        } else {
            $default_dir_mode = '0777';
        }
        return $default_dir_mode;
    }

    function add_slashes($sInput) {
        return $sInput;
    }

    function StripFromText($sInput) {
        return Sanitize::StripFromText($sInput);
    }
//
// ************************************************************************************ //
//

    $debug = false;

    if (true === $debug) {
        \ini_set('display_errors', 1);
        \error_reporting(E_ALL);
    }
// Start a session
    if (!\defined('SESSION_STARTED')) {
        $is_secure = ((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off' ) ? false : true);
        \session_name('wb-installer');
//        **PREVENTING SESSION HIJACKING**
//        Prevents javascript XSS attacks aimed to steal the session ID
        \ini_set('session.cookie_httponly', true);
//        **PREVENTING SESSION FIXATION**
        \ini_set('session.use_trans_sid', false);
//        Session ID cannot be passed through URLs
        \ini_set('session.use_only_cookies', true);
//        Uses a secure connection (HTTPS) if possible
        \ini_set('session.cookie_samesite', 'Strict');
        \ini_set('session.cookie_secure', $is_secure);
        \session_start();
        \define('SESSION_STARTED', true);
    } else {
        //\session_regenerate_id(true); // avoids session fixation attacks
    }

try {
// create log file -----------------------------------------------------------
    $sDirSep = DIRECTORY_SEPARATOR;

    $sAddonsLogFile = \dirname(__DIR__).$sDirSep.'var'.$sDirSep.'log'.$sDirSep.'install.log.php';
    if (!\file_exists($sAddonsLogFile)) {
    }
        $sTmp = '<?php header($_SERVER[\'SERVER_PROTOCOL\'].\' 404 Not Found\');echo \'404 Not Found\'; flush(); exit; ?>'
              . 'created: ['.date('r').']'.PHP_EOL;
        $iFile = \file_put_contents($sAddonsLogFile, $sTmp);
// check if request is allowed -----------------------------------------------------------
    $bTokenOk = false;
    if (isset($_SESSION['token'])) {
        $sTokenName   = (string) $_SESSION['token']['name'];
        $sTokenValue  = (string) $_SESSION['token']['value'];
        $iTokenExpire = (int) $_SESSION['token']['expire'];
        $sArgValue    = isset($_POST[$sTokenName]) ? (string) $_POST[$sTokenName] : 'xxxx';
        $bTokenOk    = (($sTokenValue === $sArgValue) && ($iTokenExpire > time()));
        $aTokenVars  = [$sTokenName,$sTokenValue,$iTokenExpire,$sArgValue];
        unset($_SESSION['token'], $sTokenName, $sTokenValue ,$iTokenExpire, $sArgValue);
    }
    if (!$bTokenOk && $bSecureToken) {
        $sErrMsg = sprintf("Installer Security warning! Illegal file access detected!!");
        $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
        throw new \Exception ($sErrMsg);
    }
// --------------------------------------------------------------------------------------
// Begin check to see if form was even submitted
// --------------------------------------------------------------------------------------

    $aInputs = $aRequestVars;
    foreach ($aRequestVars as $key=>$sValue){
        $sValue = Sanitize::StripFromText($sValue,31);
        switch ($key):
            case 'default_timezone':
              $aInputs[$key] = \filter_var($sValue, FILTER_VALIDATE_INT);
              break;
            case 'remember':
            case 'install_tables':
              $aInputs[$key] = \filter_var($sValue, FILTER_VALIDATE_BOOLEAN);
              break;
            case 'wb_url':
            case 'website_title':
            case 'admin_email':
              $aInputs[$key] = $sValue;
              break;
            case 'admin_username':
              $aInputs[$key] =  \preg_replace('/[^a-z0-9&\-.=@_]/i', '', $sValue);
              break;
            case 'admin_password':
            case 'admin_repassword':
              $aInputs[$key] =  \preg_replace('/[^\x20-\x7E^<>]+$/', '', $sValue);
              break;
            case 'database_password':
              $aInputs[$key] =  \preg_replace('/[^\x20-\x7E\x80-\xFE^<>]+$/', '', $sValue);
              break;
            case 'database_host':
              $aInputs[$key] = \preg_replace('/[^a-z0-9_\-\.]/i', '', $sValue);
              break;
            case 'table_prefix':
              $sValue = \preg_replace('/[^a-z0-9_]/i', '', $sValue);
              $sValue = (!empty($sValue) && (substr($sValue,-1)!='_') ? $sValue.'_' : $sValue );
              $sValue = (empty($sValue) ? 'wb_' : $sValue);
              $aInputs[$key] = \filter_var($sValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS, ['options'=>['default'=>'wb_']]);
              break;
            case 'database_name':
            case 'database_username':
              $aInputs[$key] = \preg_replace('/[^a-z0-9_-]/iu', '', $sValue);
              break;
            default:
              $aInputs[$key] = \filter_var($sValue, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        endswitch;
    }
// End check to see if form was even submitted

// Check if user has entered the installation url
    if (!isset($aInputs['wb_url']) || (isset($aInputs['wb_url']) && empty($aInputs['wb_url']))) {

        throw new \Exception (\sprintf('Please enter an absolute URL'));
    } else {
        $wb_url = ($aInputs['wb_url']);
    }
// Remove any slashes at the end of the URL
    $wb_url = \rtrim($wb_url, '\\/');

// Get the default time zone
    if (!isset($aInputs['default_timezone']) && !\is_numeric($aInputs['default_timezone'])) {
        throw new \Exception (\sprintf('Please select a valid default timezone'));
    } else {
        $default_timezone = (int)$aInputs['default_timezone']; //*60*60
    }
// End path and timezone details code

// Get the default language
    $sLangDir = \str_replace('\\', '/', \dirname(\dirname(__FILE__)).'/languages/');
    $allowed_languages = \preg_replace('/^.*\/([A-Z]{2})\.php$/iU', '\1', \glob($sLangDir.'??.php'));
    if (!isset($aInputs['default_language']) || !in_array($aInputs['default_language'], $allowed_languages)) {
        $aInput['ERROR_FIELD'] = 'default_language';
        throw new \Exception (\sprintf('Please select a valid default backend language'));
    } else {
        $default_language = $aInputs['default_language'];
        // make sure the selected language file exists in the language folder
        if (!\file_exists('../languages/' .$default_language .'.php')) {
            $aInput['ERROR_FIELD'] = 'default_language';
            throw new \Exception (\sprintf(
                'The language file: \'' .$default_language .'.php\' is missing. '.
                'Upload file to language folder or choose another language',
                'default_language'
            ));
        }
    }
// End default language details code

// Begin operating system specific code
    if (!isset($aInputs['operating_system']) && ($aInputs['operating_system'] != 'linux' || $aInputs['operating_system'] != 'windows')) {
//        $aInput['ERROR_FIELD'] = 'operating_system';
        $sFieldname = 'operating_system';
        throw new \Exception (\sprintf('Please select a valid operating system'));
    } else {
        $operating_system = ($aInputs['operating_system']);
    }
// Work-out file permissions
    if ($operating_system == 'windows') {
        $file_mode = '0666';
        $dir_mode = '0777';
    } elseif (isset($aInputs['world_writeable']) && $aInputs['world_writeable'] == 'true') {
        $file_mode = '0666';
        $dir_mode  = '0777';
    } else {
        $file_mode = default_file_mode('../temp');
        $dir_mode  = default_dir_mode('../temp');
    }
// End operating system specific code

// Begin database details code
// Check if user has entered a database host
    if (!isset($aInputs['database_host']) || (isset($aInputs['database_host']) && empty($aInputs['database_host']))) {
//        $aInput['ERROR_FIELD'] = 'database_host';
        $sFieldname = 'database_host';
        throw new \Exception (\sprintf('Please enter a valide host name'));
    } else {
        $database_host = \trim(($aInputs['database_host']));
    }
// extract port if available
    if (isset($database_port)) { unset($database_port); }
    $aMatches = \preg_split('/:/s', $database_host, -1, PREG_SPLIT_NO_EMPTY);
    $database_host = $aMatches[0];
    $database_port = (isset($aMatches[1]) ? (int)$aMatches[1] : \ini_get('mysqli.default_port'));

// Check if user has entered a database name
    if (!isset($aInputs['database_name']) || (isset($aInputs['database_name']) && empty($aInputs['database_name']))) {
//        $aInput['ERROR_FIELD'] = 'database_name';
        $sFieldname = 'database_name';
        throw new \Exception (\sprintf('Please enter a database name'));
    } else {
        // make sure only allowed characters are specified
        if (\preg_match('/[^a-z0-9_-]+/iu', $aInputs['database_name'])) {
            // contains invalid characters (only a-z, A-Z, 0-9 and _ allowed to avoid problems with table/field names)
//            $aInput['ERROR_FIELD'] = 'database_name';
            $sFieldname = 'database_name';
            throw new \Exception (\sprintf('Contains invalid characters! Only characters a-z, A-Z, 0-9 and _ allowed in database name.'));
        }
        $database_name = ($aInputs['database_name']);
    }

// Get table prefix
    if (\preg_match('/[^a-z0-9_]+/', $aInputs['table_prefix'])) {
        // contains invalid characters (only a-z, A-Z, 0-9 and _ allowed to avoid problems with table/field names)
//        $aInput['ERROR_FIELD'] = 'table_prefix';
        $sFieldname = 'table_prefix';
        throw new \Exception (\sprintf('Only lowercase characters a-z, 0-9 and _ allowed in table_prefix.'));
    } else {
        $table_prefix = ($aInputs['table_prefix']);
    }
    $database_charset = 'utf8mb4_unicode_ci'; //
    if (isset($aInputs['db_collation'])) {
        $database_charset = (($aInputs['db_collation']=='utf8') ? 'utf8_unicode_ci' : $database_charset);
    }

// Check if user has entered a database username
    if (!isset($aInputs['database_username']) || (isset($aInputs['database_username']) && empty($aInputs['database_username']))) {
//        $aInput['ERROR_FIELD'] = 'database_username';
        $sFieldname = 'database_username';
        throw new \Exception (\sprintf('Please enter a database username'));
    } else {
        $database_username = ($aInputs['database_username']);
    }
// Check if user has entered a database password
    if (!isset($aInputs['database_password']) || (isset($aInputs['database_password']) && empty($aInputs['database_password']))) {
//        $aInput['ERROR_FIELD'] = 'database_password';
        $sFieldname = 'database_password';
        throw new \Exception (\sprintf('Please enter a database password'));
    } else {
        $database_password = ($aInputs['database_password']);
    }

    $install_tables = true;
// Begin website title code
// Get website title
    if (!isset($aInputs['website_title']) || (isset($aInputs['website_title']) && $aInputs['website_title'] == '')) {
//        $aInput['ERROR_FIELD'] = 'website_title';
        $sFieldname = 'website_title';
        throw new \Exception (\sprintf('Please enter a website title'));
    } else {
        $website_title = ($aInputs['website_title']);
    }
// End website title code

// Begin admin user details code
    $sClientIp = '';
// Get admin username
    if (!isset($aInputs['admin_username']) || (isset($aInputs['admin_username']) && $aInputs['admin_username'] == '')) {
//        $aInput['ERROR_FIELD'] = 'admin_username';
        $sFieldname = 'admin_username';
        throw new \Exception (\sprintf('Please enter a username for the Administrator account'));
    } else {
        $admin_username = ($aInputs['admin_username']);
        $sClientIp = (isset($_SERVER['REMOTE_ADDR']))
                             ? $_SERVER['REMOTE_ADDR'] : '000.000.000.000';
        $iClientIp = \ip2long($sClientIp);
        $sClientIp = \long2ip(($iClientIp & ~65535));
    }

// Get admin email and validate it
    if (!isset($aInputs['admin_email']) || (isset($aInputs['admin_email']) && $aInputs['admin_email'] == '')) {
//        $aInput['ERROR_FIELD'] = 'admin_email';
        $sFieldname = 'admin_email';
        throw new \Exception (\sprintf('Please enter an email for the Administrator account'));
    } else {
        if (\preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', $aInputs['admin_email'])) {
            $admin_email = $aInputs['admin_email'];
        } else {
//            $aInput['ERROR_FIELD'] = 'admin_email';
            $sFieldname = 'admin_email';
            throw new \Exception (\sprintf('Please enter a valid email address for the Administrator account'));
        }
    }
// Get the two admin passwords entered, and check that they match
    if (!isset($aInputs['admin_password']) || (isset($aInputs['admin_password']) && $aInputs['admin_password'] == '')) {
//        $aInput['ERROR_FIELD'] = 'admin_password';
        $sFieldname = 'admin_password';
        throw new \Exception (\sprintf('Please enter a password for the Administrator account','admin_password'));
    } else {
        $admin_password = ($aInputs['admin_password']);
    }
    if (!isset($aInputs['admin_repassword']) || (isset($aInputs['admin_password']) && $aInputs['admin_repassword'] == '')) {
//        $aInput['ERROR_FIELD'] = 'admin_repassword';
        $sFieldname = 'admin_repassword';
        throw new \Exception (\sprintf('Please make sure you re-enter the password for the Administrator account'));
    } else {
        $admin_repassword = ($aInputs['admin_repassword']);
    }
    if ($admin_password != $admin_repassword) {
//        $aInput['ERROR_FIELD'] = 'admin_repassword';
        $sFieldname = 'admin_repassword';
        throw new \Exception (\sprintf('Sorry, the two Administrator account passwords you entered do not match'));
    }

// End admin user details code

// proof db connection

    $getNewVersion = function () {
        $sVersionContent = \file_get_contents(\dirname(__DIR__).'/admin/interface/version.php');
        $sPattern = '=*(VERSION).*\=.*\"(.*)\"';
        \preg_match('/'.$sPattern.'/', $sVersionContent, $aMatch);
        $sRetval = isset($aMatch['2']) ? $aMatch['2'] : '???';
        return $sRetval;
    };
    $sErrMsg = \sprintf('[%03d] Create config.php',__LINE__);
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
// build name and content of the config file
    $sFileMarker = '*** auto generated config file for '.$getNewVersion();
    $config_filename = (\dirname(__DIR__)).'/config.php';
    if (\is_readable($config_filename) && \filesize($config_filename) > 64) {
        $sErrMsg = \sprintf('[%03d] config.php already fill in',__LINE__);
        $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
        throw new \Exception ($sErrMsg);
    }
    $config_content
        = '<?php'."\n"
        . '/*'."\n"
        . ' '.$sFileMarker."\n"
        . ' *** WebsiteBaker '.$getNewVersion()."\n"
        . ' *** created at '.\date('Y-m-d h:i:s e')."\n"
        . ' */'."\n"
        . '// define(\'DEBUG\', false);'."\n"
        . 'define(\'DB_TYPE\',         \'mysqli\');'."\n"
        . 'define(\'DB_HOST\',         \''.$database_host.'\');'."\n"
        . 'define(\'DB_PORT\',         \''.\sprintf('%04d', $database_port).'\');'."\n"
        . 'define(\'DB_NAME\',         \''.$database_name.'\');'."\n"
        . 'define(\'DB_USERNAME\',     \''.$database_username.'\');'."\n"
        . 'define(\'DB_PASSWORD\',     \''.$database_password.'\');'."\n"
        . 'define(\'DB_CHARSET\',      \''.$database_charset.'\');'."\n"
        . 'define(\'TABLE_PREFIX\',    \''.$table_prefix.'\');'."\n"
       . "\n"
        . 'define(\'WB_URL\',          "'.$wb_url.'"); '
        . '// no trailing slash or backslash!!'."\n"
        . 'define(\'ADMIN_DIRECTORY\', \'admin\'); '
        . '// no leading/trailing slash or backslash!! A simple directory name only!!'."\n"
        . "\n".'require __DIR__.\'/framework/initialize.php\';'."\n"
        . '// --- end of file ----------------------------------'."\n";
    unset($getNewVersion);

// Define additional configuration constants
    \define('DEBUG', false);
    \define('DB_TYPE', 'mysqli');
    \define('DB_HOST', $database_host);
    \define('DB_PORT', \sprintf('%04d', $database_port));
    \define('DB_NAME', $database_name);
    \define('DB_USERNAME', $database_username);
    \define('DB_PASSWORD', $database_password);
    \define('DB_CHARSET', $database_charset);
    \define('TABLE_PREFIX', $table_prefix);

    \define('ADMIN_DIRECTORY', 'admin');
    \define('WB_PATH', \dirname(__DIR__));
    \define('WB_URL', $wb_url);
    \define('ADMIN_PATH', WB_PATH.'/'.ADMIN_DIRECTORY);
    \define('ADMIN_URL', WB_URL.'/'.ADMIN_DIRECTORY);
    if (!\defined('SYSTEM_RUN')) { \define('SYSTEM_RUN', true); }
    require(ADMIN_PATH.'/interface/version.php');
// activate Autoloader -------------------------------------------------------------------
/* */
    if (!\class_exists('\bin\CoreAutoloader',false)) {
        include WB_PATH.'/framework/CoreAutoloader.php';
    }
    \bin\CoreAutoloader::doRegister(WB_PATH);
    \bin\CoreAutoloader::addNamespace([ // add several needed namespaces->folder translations
    //  Namespace               Directory
    // aliases needed until the new folder structure is etablished
        'bin\\Requester'         => 'framework', // deprecated
//        'bin\\Security'          => 'framework',
    // regular namespace translations
        'App'                    => 'framework',
        'bin'                    => 'framework', // deprecated
        'src'                    => 'framework', // deprecated
        'Acp'                    => ADMIN_DIRECTORY,
        'addon'                  => 'modules',
        'Mod'                    => 'modules',
        'vendor'                 => 'include',
        'api'                    => 'framework/api',
        'Moment'                 => 'include/fightbulc/moment/src'.\PHP_MAJOR_VERSION,
    ]);
// register content of /vendor/autoload_classmap.php ---------------------------------
    $sAutoloadingMapFile = str_replace('\\','/',dirname(__DIR__)).'/vendor/autoload_classmap.php';
    if (is_readable($sAutoloadingMapFile)){
        $aNamespaces = require $sAutoloadingMapFile;
        \bin\CoreAutoloader::addNamespace($aNamespaces['default']);
    }
// *** initialize Exception handling -----------------------------------------------------
    \set_exception_handler(['\bin\Exceptions\ExceptionHandler', 'handler']);

    if (!file_exists(WB_PATH.'/framework/class.database.php')) {
        throw new \Exception (\sprintf('It appears the absolute path that you entered is incorrect or file \'class.database.php\' is missing!'));
    }

// Try connecting to database
    if (!\class_exists('\database')) {require (WB_PATH.'/framework/class.database.php');}
    $sErrMsg = \sprintf('[%03d] check class database and include',__LINE__);
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);

    if (!($database = \database::getInstance())) {
        $sMsg = \sprintf('MYSQLI Error: check database host name, username and/or password.');
        throw new \Exception ($sMsg);
    }

    if (!\defined('WB_INSTALL_PROCESS')) {\define('WB_INSTALL_PROCESS', true);}
    $sErrMsg = (\sprintf('[%03d] %s %s ',__LINE__, \basename(__FILE__),'WB_INSTALL_PROCESS'));
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);

if (!\class_exists('\src\Security\Randomizer')) {require (WB_PATH.'/framework/Security/Randomizer.php');}
//$code = (new Randomizer())->getHexString($iDigits);
if (!\class_exists('\bin\Requester\HttpRequester')) {require (WB_PATH.'/framework/HttpRequester.php');}
    $oRequest = \bin\Requester\HttpRequester::getInstance();

/*****************************
Begin Create Database Tables
*****************************/
    $sInstallDir = \dirname(__FILE__);
    $sErrMsg = (\sprintf('[%03d] %s %s ',__LINE__, \basename(__FILE__),'install-struct.sql'));
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
    if (\is_readable($sInstallDir.'/install-struct.sql.php')) {
        if (!$database->SqlImport($sInstallDir.'/install-struct.sql.php', TABLE_PREFIX, false)) {
            throw new \Exception (\sprintf($database->get_error().PHP_EOL.'Error: unable to import \'install/install-struct.sql\''));
        }
    } else {
        throw new \Exception (\sprintf('unable to read file \'install/install-struct.sql\''));
    }
    if (\is_readable($sInstallDir.'/install-data.sql.php')) {
        if (!$database->SqlImport($sInstallDir.'/install-data.sql.php', TABLE_PREFIX, false )) {
            throw new \Exception (\sprintf($database->get_error().PHP_EOL.'Error: unable to import \'install/install-data.sql\''));
        }
    } else {
        throw new \Exception (\sprintf('unable to read file \'install/install-data.sql\''));
    }
    $sUpdatePatchFile = '/interface/update';
    $admin_timestamp = time();
    $admin_revision  = REVISION;
/* load update script, to prevent starting upgrade-script after install */
    if (is_file(ADMIN_PATH.$sUpdatePatchFile)){
        include ADMIN_PATH.$sUpdatePatchFile;
    }

    $sql = // add settings from install input
    'INSERT INTO `'.TABLE_PREFIX.'settings` (`name`, `value`) VALUES '
        .'(\'wb_version\', \''.$database->escapeString(VERSION).'\'),'
        .'(\'wb_revision\', \''.$database->escapeString(REVISION).'\'),'
        .'(\'wb_sp\', \''.$database->escapeString(SP).'\'),'
        .'(\'patch_revision\', \''.$database->escapeString($admin_revision).'\'),'
        .'(\'patch_update\', \''.$database->escapeString($admin_timestamp).'\'),'
        .'(\'website_title\', \''.$database->escapeString($website_title).'\'),'
        .'(\'default_language\', \''.$database->escapeString($default_language).'\'),'
        .'(\'app_name\', \'PHPSESSID-WB-'.$database->escapeString((new Randomizer())->getHexString(6)).'\'),'
        .'(\'default_timezone\', \''.$database->escapeString($default_timezone).'\'),'
        .'(\'finalize_setup\', \''.$database->escapeString('true').'\'),'
        .'(\'operating_system\', \''.$database->escapeString($operating_system).'\'),'
        .'(\'server_email\', \''.$database->escapeString($admin_email).'\')';
    if (!($database->query($sql))) {
        $msg = $database->get_error();
        throw new \Exception (\sprintf("unable to write 'install presets' into table 'settings'\n%s",$msg));
    }
    $sErrMsg = (\sprintf('[%03d] %s %s ',__LINE__, \basename(__FILE__),TABLE_PREFIX.'settings'));
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);

    $aDateFormat = [
    'DATE' => (($default_language==='DE') ? 'd-m-Y' : 'm-d-Y'),
    'TIME' => (($default_language==='DE') ? 'H:i' : 'g:i|A'),
    ];

    $sql = // add the Admin user
         'INSERT INTO `'.TABLE_PREFIX.'users` SET '
        .    '`group_id`=1, '
        .    '`groups_id`=\'1\', '
        .    '`active`=\'1\', '
        .    '`username`=\''.$database->escapeString($admin_username).'\', '
        .    '`password`=\''.$database->escapeString(\md5($admin_password)).'\', '
        .    '`remember_key`=\'\', '
        .    '`last_reset`=0, '
        .    '`display_name`=\'Administrator\', '
        .    '`email`=\''.$database->escapeString($admin_email).'\', '
        .    '`timezone`=\''.$database->escapeString($default_timezone).'\', '
        .    '`date_format`= \''.$aDateFormat['DATE'].'\', '
        .    '`time_format`= \''.$aDateFormat['TIME'].'\', '
        .    '`language`=\''.$database->escapeString($default_language).'\', '
        .    '`home_folder`=\'\', '
        .    '`login_when`=\''.\time().'\', '
        .    '`login_ip`=\''.$database->escapeString($sClientIp).'\' '
        .    '';
    if (!($database->query($sql))) {
        throw new \Exception (\sprintf('unable to write Administrator account into table \'users\''));
    }
    $sErrMsg = (\sprintf('[%03d] %s %s ',__LINE__, \basename(__FILE__),TABLE_PREFIX.'users'));
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);

/************************
END OF CORE TABLES IMPORT
************************/
} catch (InvalidTokenException $ex) {
    echo $ex->getMessage();
    exit();
} catch (\Exception $ex) {
// Include WB functions file
// Start a session
    if (!\defined('SESSION_STARTED')) {
        \bin\Sessions\SameSiteSessionStarter::setName('wb-installer');
        \bin\Sessions\SameSiteSessionStarter::$samesite    = 'Lax';
        \bin\Sessions\SameSiteSessionStarter::$is_secure   = $oRequest->isSecure();
        \bin\Sessions\SameSiteSessionStarter::$is_httponly = true;
        \bin\Sessions\SameSiteSessionStarter::session_start();
        \define('SESSION_STARTED', true);
    } else {
        //\session_regenerate_id(); // avoids session fixation attacks
    }

//     else {
//        if (!headers_sent()) {
//          \session_regenerate_id(true); // avoids session fixation attacks
//        }
//    }
    if (!\defined('SYSTEM_RUN')) { \define('SYSTEM_RUN', true); }
    \clearstatcache();
    $sErrMsg = \nl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
    \clearstatcache();
/* deprecated  create empty $config_filename
    $config_filename = $sAppPath.'config.php';
    if (!\is_writeable($config_filename) || (\is_writeable($config_filename) && \filesize($config_filename) < 64)) {
        $empty_content  = '<?php'."\n";
        if ($iSize = \file_put_contents($config_filename, $empty_content)) {
            $sLogMsg = \sprintf('[%03d] '.'Empty config.php successfully created',__LINE__);
            $iFile = \file_put_contents($sAddonsLogFile, $sLogMsg.\PHP_EOL, \FILE_APPEND);
        } else {
            $sLogMsg = \sprintf('[%03d] '.'Can\'t empty %s ',__LINE__,\basename($config_filename));
            $iFile = \file_put_contents($sAddonsLogFile, $sLogMsg.\PHP_EOL, \FILE_APPEND);
        }
        unset($empty_content);
    } // create empty $config_filename
*/
    if (isset($sErrMsg) && $sErrMsg != '') {
        // first clean session before fill up with values to remember
        if (isset($aInputs['database_password'])){unset ($aInputs['database_password']);}
        if (isset($aInputs['admin_password'])){unset ($aInputs['admin_password']);}
        if (isset($aInputs['admin_repassword'])){unset ($aInputs['admin_repassword']);}
        // Copy values entered into session so user doesn't have to re-enter everything
        $_SESSION = $aInputs;
        // Set the message
        $_SESSION['message'] = $sErrMsg;
        // Set the element(s) to highlight
        $_SESSION['ERROR_FIELD'] = ($sFieldname ?? 'unknown fieldname');
        // Specify that session support is enabled
        $_SESSION['session_support'] = '<font class="good">Enabled</font>';
    }
    // Redirect to first page again and exit
    if (!headers_sent()) {
      \header('Location: index.php?sessions_checked=true');
      exit( 0);
    } else {
        $msg = '<div style="text-align:center;"><h2>An error has occurred</h2><p>The <strong>Redirect</strong> could not be start automatically.'."\n"
             . 'Please click <a style="font-weight:bold;" '.'href="index.php?sessions_checked=true">on this link</a> to restart wizard!</p></div>'."\n";
        throw new \Exception( $msg);
    }
    exit();
} // end catch

// Check if the file exists and is writable first.
    $sMessage = '';
    $sErrMsg = \sprintf('[%03d] config.php successfully written %s',__LINE__,$sFileMarker).PHP_EOL;
    if (\is_writable($config_filename) && \filesize($config_filename) > 64) {
        $sMessage = \sprintf('No permission to overwrite the configuration file!!');
    } else {
        if (\is_writable($config_filename)) {
        // try to write file
            if (\file_put_contents($config_filename, $config_content) === false) {
                $sMessage = \sprintf('[%03d] Cannot write to the configuration file <%s>',_LINE__,basename($config_filename));
            }
        } else {
            $sMessage = \sprintf('[%03d] The configuration file <%s> is missing or not writable.<br />'
                  . 'Change its permissions so it is, then re-run step 4.',__LINE__,basename($config_filename));
        }
    }
    // if something gone wrong, break with message
    if (\trim($sMessage)!='') {
      $sErrMsg = $sMessage;
      $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg."\n", \FILE_APPEND);
      throw new \Exception (\sprintf($sErrMsg));
    }
    $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg."\n", \FILE_APPEND);

// delete session cookie if set
    if (isset($_COOKIE[\session_name()])) {
        \setcookie(\session_name(), '', \time() - 42000, '/');
    }
    $_SESSION = [];
// delete the session itself
    \session_destroy();

// initialize the system
    include(WB_PATH.'/framework/initialize.php');

/***********************
// Dummy class to allow modules' install scripts to call $admin->print_error
***********************/
if (!\class_exists('\admin')) {require (WB_PATH.'/framework/class.admin.php');}
class admin_dummy extends \admin{
    public $error='';
    // overwrite method from parent
    public function print_error($message, $link = 'index.php', $auto_footer = true)
    {
        $this->error=$message;
    }
}

// Include WB functions file
//if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

// Include the PclZip class file (thanks to
//if (!\class_exists('\src\Interfaces\Requester')) {require (WB_PATH.'/framework/Interfaces/Requester.php');}

$admin = new admin_dummy('Start','',false,false);

// Load addons into DB
    foreach (\glob(WB_PATH.'/languages/??.php') as $sLanguage) {
        load_language($sLanguage);
    }

    $sOldWorkingDir = \getcwd();
    if (is_readable(WB_PATH.'/install/ModuleWhiteList')){
        $aModuleWhiteList = file(WB_PATH.'/install/ModuleWhiteList', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $sAddonCompareFlag = (in_array('ForceUpgrade',$aModuleWhiteList) ? '<=' : '<');
    }// Load addons into DB
    $sOldWorkingDir = \getcwd();
    foreach (\glob(WB_PATH.'/modules/*', \GLOB_ONLYDIR) as $sModule) {
        $sModuleName = \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', \basename($sModule));
        if (in_array($sModuleName, $aModuleWhiteList) && \is_readable($sModule.'/info.php'))
        {
            load_module($sModule, true);
            $sErrMsg = (\sprintf('[%03d] install %s ',__LINE__, \basename($sModule)));
            $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
            if ($admin->error!='') {
                throw new \RuntimeException (\sprintf($admin->error));
            }
        }
    }

    foreach (\glob(WB_PATH.'/templates/*', \GLOB_ONLYDIR) as $sTemplate) {
        load_template($sTemplate);
        $sErrMsg = (\sprintf('[%03d] install %s ',__LINE__, \basename($sTemplate)));
        $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
    }

// Check if there was a database error
    if ($database->is_error()) {
        throw new \RuntimeException (\sprintf($database->get_error()));
    }
    $filesRemove = [
            '[MEDIA]PLACEHOLDER',
            '[PAGES]PLACEHOLDER',
            '[TEMP]PLACEHOLDER',
            '[VAR]log/PLACEHOLDER',
        ];
    if(\sizeof($filesRemove)) {
        $searches = array(
            '[ROOT]',
            '[ACCOUNT]',
            '[ADMIN]',
            '[INCLUDE]',
            '[INSTALL]',
            '[FRAMEWORK]',
            '[LANGUAGES]',
            '[MEDIA]',
            '[MODULES]',
            '[PAGES]',
            '[TEMP]',
            '[TEMPLATE]',
            '[DOCU]',
            '[VAR]',
        );
        $replacements = array(
            '/',
            '/account/',
            '/'.\substr(ADMIN_PATH, \strlen(WB_PATH)+1).'/',
            '/include/',
            '/install/',
            '/framework/',
            '/languages/',
            MEDIA_DIRECTORY.'/',
            '/modules/',
            PAGES_DIRECTORY.'/',
            '/temp/',
            '/templates/',
            '/DOCU/',
            '/var/',
        );

        $aMsg = [];
        \array_walk(
            $filesRemove,
            function (& $sFile) use($searches, $replacements) {
                $sFile = \str_replace( '\\', '/', WB_PATH.\str_replace($searches, $replacements, $sFile) );
            }
        );
        $sWbPath = \str_replace('\\', '/', WB_PATH );
        foreach ( $filesRemove as $sFileToDelete ) {
            if (false !== ($aExistingFiles = glob(dirname($sFileToDelete).'/*', GLOB_MARK)) ) {
                if (\in_array($sFileToDelete, $aExistingFiles)) {
                    if (\is_writable($sFileToDelete) && \unlink($sFileToDelete)) {
                        $sErrMsg = $aMsg[] = \sprintf ('[%03d] Remove %s successfully',__LINE__,\str_replace($sWbPath, '',$sFileToDelete));
                        $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
                    } else {
                        $sErrMsg = $aMsg[] = \sprintf ('[%03d] Remove %s failed',__LINE__,\str_replace($sWbPath, '',$sFileToDelete));
                        $iFile = \file_put_contents($sAddonsLogFile, $sErrMsg.\PHP_EOL, \FILE_APPEND);
                    }
                }
            }
        }
        unset($aExistingFiles);
    }
//
    $sConfigNewFile = dirname(__DIR__).'/config.php.new';
    if (\is_writeable($sConfigNewFile) && \unlink($sConfigNewFile)){
        $sLogMsg = \sprintf('[%03d] '.'Remove %s ',__LINE__,\basename($sConfigNewFile));
    } else {
        $sLogMsg = \sprintf('[%03d] '.'Couldn\'t remove %s ',__LINE__,\basename($sConfigNewFile));
    }
    $iFile = \file_put_contents($sAddonsLogFile, $sLogMsg.\PHP_EOL, \FILE_APPEND);

    $sUpdateFile = ADMIN_PATH.'/interface/update';
    if (\is_readable($sUpdateFile)){
        if (!\rename($sUpdateFile, $sUpdateFile.'.fixed')){
            $sLogMsg = \sprintf('renaming of %s %s <br />',\basename($sUpdateFile), 'failed');
            $iFile = \file_put_contents($sAddonsLogFile, $sLogMsg.\PHP_EOL, \FILE_APPEND);
       }
    }

// remove session cookie  'wb-installer'
    // delete the session itself
    if (session_status() === \PHP_SESSION_ACTIVE ) {
        session_unset();
        session_destroy();
    }

    $sLogMsg = \sprintf('[%03d] '.'Installation succesfully at %s ',__LINE__,\date('r'));
    $iFile = \file_put_contents($sAddonsLogFile, $sLogMsg.\PHP_EOL, \FILE_APPEND);

    $ThemeUrl = WB_URL.$admin->correct_theme_source('warning.html.php');
// Setup template object, parse vars to it, then parse it
    $ThemePath = \realpath(WB_PATH.$admin->correct_theme_source('login.htt'));

    $aConfig = [
            "MAX_ATTEMPS" => "3",
            "WARNING_URL" => $ThemeUrl,
            "USERNAME_FIELDNAME" => 'admin_username',
            "PASSWORD_FIELDNAME" => 'admin_password',
            "REMEMBER_ME_OPTION" => false,
            "MIN_USERNAME_LEN" => "2",
            "MIN_PASSWORD_LEN" => "3",
            "MAX_USERNAME_LEN" => "30",
            "MAX_PASSWORD_LEN" => "30",
            'LOGIN_URL' => ADMIN_URL."/login/index.php",
            'DEFAULT_URL' => ADMIN_URL."/start/index.php",
            'TEMPLATE_DIR' => $ThemePath,
            'TEMPLATE_FILE' => 'login.htt',
            'FRONTEND' => false,
            'FORGOTTEN_DETAILS_APP' => ADMIN_URL."/login/forgot/index.php",
            'USERS_TABLE' => TABLE_PREFIX."users",
            'GROUPS_TABLE' => TABLE_PREFIX."groups",
    ];
// Log the user in and go to Website Baker Administration
    if (!\class_exists('\bin\Login')) {require (WB_PATH.'/framework/Login.php');}
    $thisApp = new \bin\Login($aConfig);
print '<pre  class="mod-pre" style="margin-left:30px;">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> '."\n";
print_r( $aConfig ); print '</pre>'; \flush (); //  sleep(10); die();

    class InvalidTokenException extends exception {};
