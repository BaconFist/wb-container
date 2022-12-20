<?php
/*
 * Copyright (C) 2020 Manuela v.d.Decken <manuela@isteam.de>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * @category     Core
 * @package      Initialize
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      1.0.1
 * @revision     $Id: initialize.php 5 2022-04-21 19:42:26Z manuela $
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
// $aPhpFunctions = get_defined_functions();
/**
 * sanitize $_SERVER['HTTP_REFERER']
 * @param string $sWbUrl qualified startup URL of current application
 */

declare(strict_types=1);

use src\Security\{CsfrTokens, Randomizer, Password};
use bin\Requester\HttpRequester;

function SanitizeHttpReferer($sWbUrl = WB_URL)
{
    $sTmpReferer = '';

    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
        \define('ORG_REFERER', ($_SERVER['HTTP_REFERER'] ?: ''));
        $aRefUrl = \parse_url($_SERVER['HTTP_REFERER']);
        if ($aRefUrl !== false) {
            $aRefUrl['host'] = isset($aRefUrl['host']) ? $aRefUrl['host'] : '';
            $aRefUrl['path'] = isset($aRefUrl['path']) ? $aRefUrl['path'] : '';
            $aRefUrl['fragment'] = isset($aRefUrl['fragment']) ? '#'.$aRefUrl['fragment'] : '';
            $aWbUrl = \parse_url(WB_URL);
            if ($aWbUrl !== false) {
                $aWbUrl['host'] = isset($aWbUrl['host']) ? $aWbUrl['host'] : '';
                $aWbUrl['path'] = isset($aWbUrl['path']) ? $aWbUrl['path'] : '';
                if (\strpos($aRefUrl['host'].$aRefUrl['path'], $aWbUrl['host'].$aWbUrl['path']) !== false) {
                    $aRefUrl['path'] = \preg_replace('#^'.$aWbUrl['path'].'#i', '', $aRefUrl['path']);
                    $sTmpReferer = WB_URL.$aRefUrl['path'].$aRefUrl['fragment'];
                }
                unset($aWbUrl);
            }
            unset($aRefUrl);
        }
    }
    $_SERVER['HTTP_REFERER'] = $sTmpReferer;
}

/**
 * Read DB settings from configuration file
 * @return array
 * @throws RuntimeException
 *
 */
function initReadSetupFile()
{
// check for valid file request. Becomes more stronger in next version
//    initCheckValidCaller(array('save.php','index.php','config.php','upgrade-script.php'));
    $aCfg = ['Constants'=>[]];
    $sSetupFile = (\dirname(__DIR__)).'/setup.ini.php';
    if (\is_readable($sSetupFile) && !\defined('WB_URL')) {
        $aCfg = \parse_ini_file($sSetupFile, true);
        if (!isset($aCfg['Constants']) || !isset($aCfg['DataBase'])) {
            throw new \InvalidArgumentException('configuration missmatch in setup.ini.php');
        }
        foreach($aCfg['Constants'] as $key=>$value) {
            switch($key):
                case 'DEBUG':
                    $value = \filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    if(!\defined('DEBUG')) { \define('DEBUG', $value); }
                    break;
                case 'WB_URL': // << case is set deprecated
                case 'AppUrl':
                    $value = \trim(\str_replace('\\', '/', $value), '/');
                    if (!\defined('WB_URL')) {\define('WB_URL', $value); }
                    break;
                case 'ADMIN_DIRECTORY': // << case is set deprecated
                case 'AcpDir':
                    $value = \trim(\str_replace('\\', '/', $value), '/');
                    if (!\defined('ADMIN_DIRECTORY')) {\define('ADMIN_DIRECTORY', $value); }
                    break;
                default:
                    if (!\defined($key)) {\define($key, $value); }
                    break;
            endswitch;
        }
    }
    return $aCfg;
//      throw new RuntimeException('unable to read setup.ini.php');
}
/**
 * Set constants for system/install values
 * @throws RuntimeException
 */
function initSetInstallWbConstants($aCfg)
{
    if (isset($aCfg['Constants']) && \sizeof($aCfg['Constants'])) {
        foreach($aCfg['Constants'] as $key=>$value) {
            switch($key):
                case 'DEBUG':
                    $value = \filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    if(!\defined('DEBUG')) { define('DEBUG', $value); }
                    break;
                case 'WB_URL': // << case is set deprecated
                case 'AppUrl':
                    $value = \trim(\str_replace('\\', '/', $value), '/');
                    if(!defined('WB_URL')) {\define('WB_URL', $value); }
                    break;
                case 'ADMIN_DIRECTORY': // << case is set deprecated
                case 'AcpDir':
                    $value = \trim(\str_replace('\\', '/', $value), '/');
                    if (!\defined('ADMIN_DIRECTORY')) {\define('ADMIN_DIRECTORY', $value); }
                    if (!\preg_match('/xx[a-z0-9_][a-z0-9_\-\.]+/i', 'xx'.ADMIN_DIRECTORY)) {
                        throw new \RuntimeException('Invalid admin-directory: ' . ADMIN_DIRECTORY);
                    }
                    break;
                default:
                    if (!\defined($key)) {\define($key, $value); }
                    break;
            endswitch;
        }
    }
    if (\defined('ADMIN_URL') && !\defined('ADMIN_DIRECTORY')){\define('ADMIN_DIRECTORY', \str_replace(WB_URL.'/','',ADMIN_URL));}
    if (!\defined('WB_PATH')){\define('WB_PATH', \dirname(__DIR__)); }
    if (!\defined('ADMIN_URL')){\define('ADMIN_URL', \rtrim(WB_URL, '/\\').'/'.ADMIN_DIRECTORY); }
    if (!\defined('ADMIN_PATH')){\define('ADMIN_PATH', WB_PATH.'/'.ADMIN_DIRECTORY); }
    if (!\defined('VERSION')) {require (ADMIN_PATH.'/interface/version.php');}
    if (!\defined('WB_REL')){
        $x1 = \parse_url(WB_URL, PHP_URL_PATH);
        \define('WB_REL',($x1 ?? ''));
    }
    if (!\defined('ADMIN_REL')){\define('ADMIN_REL', WB_REL.'/'.ADMIN_DIRECTORY); }
    if (!\defined('DOCUMENT_ROOT')) {
        \define('DOCUMENT_ROOT', \preg_replace('/'.\preg_quote(\str_replace('\\', '/', WB_REL), '/').'$/', '', str_replace('\\', '/', WB_PATH)));
        $_SERVER['DOCUMENT_ROOT'] = DOCUMENT_ROOT;
    }
    if (!\defined('TMP_PATH')){\define('TMP_PATH', WB_PATH.'/temp'); }

    if (\defined('DB_TYPE'))
    {
    // import constants for compatibility reasons
        $db = [];
        if (\defined('DB_TYPE'))      { $db['type']         = DB_TYPE; }
        if (\defined('DB_USERNAME'))  { $db['user']         = DB_USERNAME; }
        if (\defined('DB_PASSWORD'))  { $db['pass']         = DB_PASSWORD; }
        if (\defined('DB_HOST'))      { $db['host']         = DB_HOST; }
        if (\defined('DB_PORT'))      { $db['port']         = DB_PORT; }
        if (\defined('DB_NAME'))      { $db['name']         = DB_NAME; }
        if (\defined('DB_CHARSET'))   { $db['charset']      = DB_CHARSET; }
        if (\defined('TABLE_PREFIX')) { $db['table_prefix'] = TABLE_PREFIX; }
    } else {
        foreach($aCfg['DataBase'] as $key=>$value) {
            switch($key):
                case 'type':
                    if (!\defined('DB_TYPE')) {\define('DB_TYPE', $value); }
                    break;
                case 'user':
                    if (!\defined('DB_USERNAME')) {\define('DB_USERNAME', $value); }
                    break;
                case 'pass':
                    if (!\defined('DB_PASSWORD')) {\define('DB_PASSWORD', $value); }
                    break;
                case 'host':
                    if (!\defined('DB_HOST')) {\define('DB_HOST', $value); }
                    break;
                case 'port':
                    if (!\defined('DB_PORT')) {\define('DB_PORT', $value); }
                    break;
                case 'name':
                    if (!\defined('DB_NAME')) {\define('DB_NAME', $value); }
                    break;
                case 'charset':
                    if (!\defined('DB_CHARSET')) {\define('DB_CHARSET', $value); }
                    break;
                default:
                    $key = \strtoupper($key);
                    if (!\defined($key)) {\define($key, $value); }
                    break;
            endswitch;
        }
    }
}

/**
 * will show behind a load balancer/proxy to detect HTTPS usage
 * @param  void
 * @return bool  true if request is ssl secured
 */
function isHttps()
{
    return \bin\Requester\HttpRequester::getInstance()->isSecure();
}
/**
 * create / recreate a admin object
 * @param string $section_name (default: '##skip##')
 * @param string $section_permission (default: 'start')
 * @param bool $auto_header (default: true)
 * @param bool $auto_auth (default: true)
 * @return \admin
 */
function newAdmin($section_name= '##skip##', $section_permission = 'start', $auto_header = true, $auto_auth = true)
{
    if (isset($GLOBALS['admin']) && $GLOBALS['admin'] instanceof \admin) {
        unset($GLOBALS['admin']);
        \usleep(10000);
    }
    return new \admin($section_name, $section_permission, $auto_header, $auto_auth);
}

/** Send HTTP headers
* @return null
*/
function SendPageHeaders(array $aCspSettings=[]) {
    if (\headers_list() !== false) {
        \header("Server: Apache");
        \header("Content-Type: text/html; charset=utf-8");
        \header("X-Powered-By: ".VERSION);
        if ($aCspSettings) {
            \header("cache-control: public,max-age=31536000,immutable");
        //    \header("Cache-Control: no-cache");
            \header('Strict-Transport-Security: max-age=63072000; includeSubdomains; preload');
            \header("X-Frame-Options: SAMEORIGIN"); //deny ClickJacking protection in IE8, Safari 4, Chrome 2, Firefox 3.6.9
            \header("X-XSS-Protection: 1; mode=block"); // prevents introducing XSS in IE8 by removing safe parts of the page
            \header("X-Content-Type-Options: nosniff");
            \header("Referrer-Policy: no-referrer"); // origin-when-cross-origin
            \header("Feature-Policy: vibrate 'self'; geolocation 'none'; sync-xhr 'self' ".WB_URL.";  fullscreen ");
        //    return;
            $aSCP = cspSettings();
            $header = [];
            foreach ($aSCP as $val) {
                $header[] = $val;
            }
            \header("Content-Security-Policy: ".\implode("; ", $header).";");
            \header("X-WebKit-CSP: ".\implode("; ", $header).";");
            \header("X-Content-Security-Policy: ".\implode("; ", $header).";");
        }
    }
} // end of function SendPageHeaders

/** Get Content Security Policy headers
* @return array of arrays with directive name in key, allowed sources in value
*/
function cspSettings() {
    return [
//        "default-src 'self' ".WB_URL,  //  'unsafe-inline' 'unsafe-eval'
//        "script-src  'self' ".WB_URL,   // 'unsafe-inline'  'unsafe-eval'
//        "style-src 'self' ".WB_URL,                  //  'unsafe-inline'
//        "child-src ".WB_URL,    // deprecate
        'img-src \'self\' data: '.WB_URL,
        'font-src \'self\' data: '.WB_URL,
        'connect-src \'self\' '.WB_URL,
        'media-src \'self\' '.WB_URL,
        'object-src \'self\' '.WB_URL,
        'frame-src * \'self\' '.WB_URL,
        'worker-src \'self\' '.WB_URL,
        'form-action \'self\' '.WB_URL,
//        "frame-ancestors ".WB_URL,
//        "sandbox allow-forms allow-same-origin allow-scripts allow-top-navigation allow-popups allow-pointer-lock",
//        "reflected-xss filter" //Deprecate and remove
    ];
}

/** Get a CSP nonce
* @return string Base64 value
*/
function getNonce() {
    static $nonce;
    if (!$nonce) {
        $nonce = \base64_encode(rand_string());
    }
    return $nonce;
}

/** Get a random string
* @return string 32 hexadecimal characters
*/
function randString() {
    return \md5(\uniqid(\mt_rand(), true));
}

/* ***************************************************************************************
 * Start initialization                                                                  *
 ****************************************************************************************/
// load configuration --------------------------------------------------------------------
    \defined('SYSTEM_RUN') or define('SYSTEM_RUN', true);
    \defined('ADMIN_DIRECTORY') or define('ADMIN_DIRECTORY', 'admin');

// check existence of ADMIN_DIRECTORY ----------------------------------------------------
    if (
        !\preg_match('/xx[a-z_][a-z0-9_\-\.]+/i', 'xx'.ADMIN_DIRECTORY) || // check syntax
        !\is_dir(\dirname(__DIR__).'/'.ADMIN_DIRECTORY)                    // check existence
    ) {
        throw new RuntimeException('Invalid admin-directory set: ' . ADMIN_DIRECTORY);
    }
// ----------------------------------------------------------------------------------------
    $aPhpClasses = \get_declared_classes();
    $aCfg = initReadSetupFile();
    initSetInstallWbConstants($aCfg);
// values have to change for running php8
    $sMinPhpVersion  = (\version_compare(VERSION,'7.4.0','<' ) ? '7.4.0' : '8');
    $sBestPhpVersion = '8.1.0';
    if (\version_compare(PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION, $sMinPhpVersion, '<')) {
        $sMsg = '<p style="color: #ff0000;">WebsiteBaker is not able to run with PHP-Version less then '.$sMinPhpVersion.'!!<br />'
              . 'Please change your PHP-Version to any kind from '.$sMinPhpVersion.' and up!<br />'
              . 'If you have problems to solve that, ask your hosting provider for it.<br  />'
              . 'The very best solution is the use of PHP-'.$sBestPhpVersion.' and up</p>';
        die($sMsg);
    }
    \error_reporting(\E_ALL);
    $iStartTime = \microtime(true);

    $sRequestFromInitialize = true;
    require __DIR__.'/functions.php';

// initialize debug evaluation values ---
    $iPhpDeclaredClasses = \sizeof(\get_declared_classes());
    \date_default_timezone_set('UTC');

//    $sRequestFromInitialize = true;
//    require __DIR__.'/functions.php';
    require __DIR__.'/helpers/mb_extension.php';

    if (!\defined('MAX_DATETIME')) {\define('MAX_DATETIME', ((2**31)-1)); }
    /* -------------------------------------------------------- */
    if (!\defined('WB_PATH')) {\define('WB_PATH', \dirname(__DIR__)); }

// activate Autoloaders ------------------------------------------------------------------
    if (!\class_exists('\bin\CoreAutoloader', false)) {
        if (!\is_readable(__DIR__.'/CoreAutoloader.php')) {
            throw new \RuntimeException('[/framework/CoreAutoloader.php] missing or not readable!');
        }
        include __DIR__.'/CoreAutoloader.php';
    }
    \bin\CoreAutoloader::doRegister(\dirname(__DIR__));
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

    // register composer autoloader ------------------------------------------------------
    $sComposerAutoloader = \dirname(__DIR__).'/vendor/autoload.php';
    if (\is_readable($sComposerAutoloader)) {
        require $sComposerAutoloader;
    }
// end of Autoloaders part ---------------------------------------------------------------

// *** initialize WB-Adaptor -------------------------------------------------------------
    $oReg = \bin\WbAdaptor::getInstance();

// *** initialize Exception handling -----------------------------------------------------
    \set_exception_handler(['\bin\Exceptions\ExceptionHandler', 'handler']);

// *** initialize Error handling ---------------------------------------------------------
    \ini_set('log_errors', "On");
    if (!class_exists('\bin\Exceptions\ErrorHandler')) {
        throw new \RuntimeException('Missing mandatory ErrorHandler! ');
    }
    try {
        \bin\Exceptions\ErrorHandler::setAppPath(WB_PATH);
        $sErrorLogFile = \bin\Exceptions\ErrorHandler::getLogFile();
        \ini_set ('error_log', $sErrorLogFile);
    } catch (Exception $ex) {
        if (!\ini_get('error_log')) {
            \ini_set ('error_log', str_replace('\\', '/', WB_PATH).'/temp/php_error.log.php');
        }
    }
    \set_error_handler(['\bin\Exceptions\ErrorHandler', 'handler'], -1);
// load configuration --------------------------------------------------------------------
/*
    \defined('SYSTEM_RUN') ? '' : define('SYSTEM_RUN', true);
    \defined('ADMIN_DIRECTORY') ? '' : define('ADMIN_DIRECTORY', 'admin');
    $aCfg = initReadSetupFile();
    initSetInstallWbConstants($aCfg);
*/
// ----------------------------------------------------------------------------------------
    \defined('ADMIN_URL')  or \define('ADMIN_URL', WB_URL.'/'.ADMIN_DIRECTORY);
    \defined('ADMIN_PATH') or \define('ADMIN_PATH', WB_PATH.'/'.ADMIN_DIRECTORY);
    if ( !\defined('WB_REL')){
        $x1 = \parse_url(WB_URL);
        \define('WB_REL', ($x1['path'] ?? ''));
    }
    if ( !\defined('DOCUMENT_ROOT')) {
        \define('DOCUMENT_ROOT', \preg_replace('/'.\preg_quote(str_replace('\\', '/', WB_REL), '/').'$/', '', str_replace('\\', '/', WB_PATH)));
        $_SERVER['DOCUMENT_ROOT'] = DOCUMENT_ROOT;
    }
// activate requester and add it to WB-Adaptor -------------------------------------------
    $oRequest = HttpRequester::getInstance();
    $oReg->setRequester($oRequest);

// sanitize $_SERVER['HTTP_REFERER'] -----------------------------------------------------
    SanitizeHttpReferer(WB_URL);
    \date_default_timezone_set('UTC');

// Activate database instance and add it to WB-Adaptor -----------------------------------
    $database = $oDb = \database::getInstance();
    $oReg->setDatabase($oDb);

// initialize password class -------------------------------------------------------------
    Password::init($oDb, 'users', 'user_id', 'username', 'password');

// recreate table 'settings' if missing --------------------------------------------------
    $oRes = $oDb->query('SHOW TABLES LIKE \''.TABLE_PREFIX.'settings\'');
    if ($oRes->numRows() == 0) {
        $sInstallDir = WB_PATH.'/install';
        if (!$oDb->SqlImport($sInstallDir.'/install-settings.sql.php', TABLE_PREFIX, false)){
            throw new \RuntimeException('couldn\'t fix table settings!');
        }
    }

// activate frontend OutputFilterApi -----------------------------------------------------
    if (\is_readable(WB_PATH .'/modules/output_filter/OutputFilterApi.php')) {
        if (!\is_callable('OutputFilterApi')) {
            include WB_PATH .'/modules/output_filter/OutputFilterApi.php';
        }
    } else {
        throw new \RuntimeException('missing mandatory global OutputFilterApi!');
    }

    $sql = 'SELECT `value` FROM `'.TABLE_PREFIX.'settings` '
         . 'WHERE `name` = \'user_login\' ';
    $bTmp = $oDb->get_one($sql);
//    $bTmp = (empty($bTmp) ? '1' : $bTmp);
    $bUserLogin = \filter_var($bTmp, \FILTER_VALIDATE_BOOLEAN);
// Get website settings (title, keywords, description, header, and footer)
    $sql = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'settings`';
    if (($oSettings = $oDb->query($sql))) {
        $x = 0;
        while ($aSetting = $oSettings->fetchAssoc()) {
//            $setting_name  = $aSetting['name'];
            $setting_value = $aSetting['value'];
/*
                if (in_array($aSetting['name'], ['dev_infos','page_oldstyle','page_newstyle','wbmailer_low_security'])) {
                    echo \nl2br(\sprintf("---- [%04d] %s => %s\n",__LINE__,$aSetting['name'],$aSetting['value']));
                }
*/
/*  */
            if (in_array($aSetting['value'],['false'])) {
                $setting_value = false;
            }
            if (in_array($aSetting['value'],['true'])) {
                $setting_value = true;
            }

            switch ($aSetting['name']) :
                case 'frontend_login':
                case 'frontend_signup':
                    $setting_value = ($bUserLogin ? $setting_value : false);
                    break;
                case 'pages_directory':
                    $setting_value = (($aSetting['value']== '/') ? '' : $aSetting['value']);
                default :
                    break;
            endswitch;
            $setting_name  = \strtoupper($aSetting['name']);
            \defined($setting_name) or \define($setting_name, $setting_value);
            $x++;
        }
        \defined('DEFAULT_THEME')        or \define('DEFAULT_THEME','DefaultTheme');
        \defined('DIR_MODE')             or \define('DIR_MODE','493');
        \defined('FILE_MODE')            or \define('FILE_MODE','420');
        \defined('PASSWORD_CRYPT_LOOPS') or \define('PASSWORD_CRYPT_LOOPS','12');
        \defined('PASSWORD_HASH_TYPE')   or \define('PASSWORD_HASH_TYPE',false);
        \defined('PASSWORD_LENGTH')      or \define('PASSWORD_LENGTH',10);
        \defined('SYSTEM_LOCKED')        or \define('SYSTEM_LOCKED',false);

        unset($oSettings);
    } else {
        die($oDb->get_error());
    }
    if (!$x) {
        throw new \RuntimeException('no settings found');
    }

    \defined('DO_NOT_TRACK') or \define('DO_NOT_TRACK', ($oRequest->issetHeader('DNT')));
    \ini_set('display_errors', ((\defined('DEBUG') && DEBUG) ? '1' : '0'));
    \ini_set('display_startup_errors', 'On');
    \ini_set('max_execution_time', '300');
    \ini_set('memory_limit', '256M');
// set error-reporting from loaded settings ---
    $iErrorLevel = \intval(ER_LEVEL);
    if ($iErrorLevel >= 0 && $iErrorLevel <= E_ALL) {
        \error_reporting($iErrorLevel);
    } else {
// on invalid value in ER_LEVEL activate E_ALL
        \error_reporting(E_ALL);
    }
//  HTTP Security-Header
    SendPageHeaders([]);

/*
// < ", 1" together with gc_divisor ensures that expired sessions are "cleaned up". (explanation here https://www.php.net/manual/de/session.configuration.php#ini.session.gc-divisor)
    \ini_set('session.gc_probability', 0);
    \ini_set('session.gc_divisor', 1);
*/
    \defined('DEBUG') ? : \define('DEBUG', false);
    $string_file_mode = \defined('STRING_FILE_MODE') ? STRING_FILE_MODE : '0644';
    \defined('OCTAL_FILE_MODE') ? : \define('OCTAL_FILE_MODE', (int) \octdec($string_file_mode));
    $string_dir_mode = defined('STRING_DIR_MODE') ? STRING_DIR_MODE : '0755';
    \defined('OCTAL_DIR_MODE')  ? : \define('OCTAL_DIR_MODE',  (int) \octdec($string_dir_mode));

    $isSecure = $oRequest->isSecure(); // if you only want to receive the cookie over HTTPS
    $httponly = true; // prevent JavaScript access to session cookie
    $Samesite = 'Strict'; // provides some protection against cross-site request forgery attacks

/* this settings has security risk (SESSION HIJACKING), only comment out if needed for debug
// need const OCTAL_DIR_MODE for make_dir
    if (!is_dir(WB_PATH.'/var/phpsessions')){
        make_dir(WB_PATH.'/var/phpsessions', (int)\octdec('700'));
    }
    session_save_path(WB_PATH.'/var/phpsessions/');
 */

// **PREVENTING SESSION HIJACKING**
// Prevents javascript XSS attacks aimed to steal the session ID
    \ini_set('session.cookie_httponly', 'On');
// **PREVENTING SESSION FIXATION**
    \ini_set('session.use_trans_sid', 'Off');

// Session ID cannot be passed through URLs
    \ini_set('session.use_only_cookies', 'On');
// Uses a secure connection (HTTPS) if possible
    \ini_set('session.cookie_secure', ($isSecure ? 'On' : 'Off'));
//    ini_set('session.cookie_path', WB_REL);
    $maxlifetime = (defined('SEC_TOKEN_LIFE_TIME') ? SEC_TOKEN_LIFE_TIME : '1440');
    \ini_set('session.gc_maxlifetime', $maxlifetime);
    \ini_set('session.use_strict_mode', 'On');
    \ini_set('session.cookie_samesite', $Samesite);
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,sys_get_temp_dir()));
    if (!\defined('WB_INSTALL_PROCESS') && !\defined('WB_UPGRADE_PROCESS')) {
        $hasCaptcha = false;
        $sUpgradeFile = \dirname(__DIR__).'/modules/captcha_control/upgrade.php';
        $sTable = TABLE_PREFIX.'mod_captcha_control';
        if (($oDb->get_one('SELECT COUNT(*) FROM `'.$sTable.'`')==0)){
            if (\is_readable($sUpgradeFile)){
                $bDebugModus = true;
                require $sUpgradeFile;
            }
        }
    // get CAPTCHA and ASP settings
          $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_captcha_control`';
          if (($oCaptcha = $oDb->query($sql)) && ! is_null($aCaptcha = $oCaptcha->fetchRow(MYSQLI_ASSOC))
          ) {
            \defined('ENABLED_CAPTCHA')     ? : \define('ENABLED_CAPTCHA',     (int)($aCaptcha['enabled_captcha']));
            \defined('ENABLED_ASP')         ? : \define('ENABLED_ASP',         (bool) ($aCaptcha['enabled_asp'] == '1'));
            \defined('CAPTCHA_TYPE')        ? : \define('CAPTCHA_TYPE',        $aCaptcha['captcha_type']);
            \defined('ASP_SESSION_MIN_AGE') ? : \define('ASP_SESSION_MIN_AGE', (int) $aCaptcha['asp_session_min_age']);
            \defined('ASP_VIEW_MIN_AGE')    ? : \define('ASP_VIEW_MIN_AGE',    (int) $aCaptcha['asp_view_min_age']);
            \defined('ASP_INPUT_MIN_AGE')   ? : \define('ASP_INPUT_MIN_AGE',   (int) $aCaptcha['asp_input_min_age']);
        } else {
          throw new \RuntimeException('CAPTCHA-Settings not found');
        }
    } // end upgrade process

// import all already defined constants into WB-Adaptor -----------------------------------------
    $oReg->getWbConstants();

// Start a session
/* */
    if (!\defined('SESSION_STARTED')) {
        \bin\Sessions\SameSiteSessionStarter::setName(($oReg->AppSid ?? ($oReg->AppName ?? 'PHPSESSID-123')));
        \bin\Sessions\SameSiteSessionStarter::$samesite    = 'Lax';
        \bin\Sessions\SameSiteSessionStarter::$is_secure   = ($oRequest->isSecure() ?? false);
        \bin\Sessions\SameSiteSessionStarter::$is_httponly = true;
        \bin\Sessions\SameSiteSessionStarter::session_start();
        \define('SESSION_STARTED', true);
    } else {
        //\session_regenerate_id(); // avoids session fixation attacks
    }


// start session Garbage Collection  --------------------------------------------------------------------
    if ((defined('SGC_EXECUTE') && (SGC_EXECUTE==='true')) && is_readable(__DIR__.'/Sessions/SessionGarbage.php')){
        \bin\Sessions\SessionGarbage::execute();
    }

    if (\defined('ENABLED_ASP') && ENABLED_ASP && !isset($_SESSION['session_started'])) {
        $_SESSION['session_started'] = \time();
    }

// Get users language --------------------------------------------------------------------
    $sLang = $oRequest->getParam('lang');
    $aMatches = [];
    $sPattern = '/^\s*([a-z]{2})(?:[\-_]([a-z]{2})(?:[\-_]([a-z\-_]{2,8}))?)?[\s_\-]*$/i';
    /* pattern result:
     * $aMatches[1] => language code
     * $aMatches[2] => country code if available
     * $aMatches[3] => region code if available
     */
    if ($sLang && \preg_match($sPattern, $sLang, $aMatches)) {
        $sLang = \strtoupper($aMatches[1]);
        \define('LANGUAGE', $sLang);
        $_SESSION['LANGUAGE'] = $sLang;
    } else {
        if (isset($_SESSION['LANGUAGE']) && \preg_match($sPattern, $_SESSION['LANGUAGE'], $aMatches)) {
            \define('LANGUAGE', \strtoupper($aMatches[1]));
        } else {
            \define('LANGUAGE', DEFAULT_LANGUAGE);
        }
    }
//        \trigger_error(\sprintf('[%03d] page[language] %s != language  ',__LINE__, $sLang,$aMatches[2]),E_USER_NOTICE);
    unset($sLang, $sPattern, $aMatches);

// Load Language file(s) -----------------------------------------------------------------
    $sCurrLanguage = '';
    $slangFile = WB_PATH.'/languages/EN.php';
    if (\is_readable($slangFile)) {
        require $slangFile;
        $sCurrLanguage ='EN';
    }
    if ($sCurrLanguage != DEFAULT_LANGUAGE) {
        $slangFile = WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php';
        if (\is_readable($slangFile)) {
            require $slangFile;
            $sCurrLanguage = DEFAULT_LANGUAGE;
        }
    }
    if ($sCurrLanguage != LANGUAGE) {
        $slangFile = WB_PATH.'/languages/'.LANGUAGE.'.php';
        if (\is_readable($slangFile)) {
            require $slangFile;
        }
    }
// activate Translate and add it to WB-Adaptor--------------------------------------------
    $sCachePath = \dirname(__DIR__).'/temp/cache/Translate';
    if (!\file_exists($sCachePath)) {
        if (!\mkdir($sCachePath, 0777, true)) { $sCachePath = \dirname(__DIR__).'/temp/cache/Translate'; }
    }
    $oTrans = Translate::getInstance();
    $oTrans->initialize(['EN', DEFAULT_LANGUAGE, LANGUAGE], $sCachePath); // 'none'
    $oReg->setTranslate($oTrans);

// activate CsfrTokens ------------------------------------------------------------------
//    CsfrTokens::getInstance(\bin\WbAdaptor::getInstance(), (new Randomizer(Randomizer::ALNUM_64)));

/*
// activate SecureTokens -----------------------------------------------------------------
    $oApp = (object) [
        'oRequester' => $oRequest,
        'oRegistry'  => (object) [
            'SecTokenFingerprint' => (bool) (defined('SEC_TOKEN_FINGERPRINT') ? SEC_TOKEN_FINGERPRINT : true),
            'SecTokenNetmask4'    => (defined('SEC_TOKEN_NETMASK4') ? SEC_TOKEN_NETMASK4 : 24),
            'SecTokenNetmask6'    => (defined('SEC_TOKEN_NETMASK6') ? SEC_TOKEN_NETMASK6 : 64),
            'SecTokenLifeTime'    => (defined('SEC_TOKEN_LIFE_TIME') ? SEC_TOKEN_LIFE_TIME : 7200)
        ],
        'oTrans' => $oTrans
    ];
    \bin\SecureTokens::getInstance($oApp);
    \bin\SecureTokens::checkFTAN();
*/
// ---------------------------------------------------------------------------------------
// Get users timezone, date format, time format

    \define('TIMEZONE',    ($_SESSION['TIMEZONE']    ?? DEFAULT_TIMEZONE));
    \define('DATE_FORMAT', ($_SESSION['DATE_FORMAT'] ?? DEFAULT_DATE_FORMAT));
    \define('TIME_FORMAT', ($_SESSION['TIME_FORMAT'] ?? DEFAULT_TIME_FORMAT));
// Set Theme dir
    \define('THEME_URL', WB_URL.'/templates/'.DEFAULT_THEME);
    \define('THEME_PATH', WB_PATH.'/templates/'.DEFAULT_THEME);
// extended wb_settings
    \define('EDIT_ONE_SECTION', false);
    \define('EDITOR_WIDTH', 0);

// instantiate and initialize adaptor for temporary registry replacement ---

// import all already defined constants into WB-Adaptor -----------------------------------------
    $oReg->getWbConstants();

// activate CsfrTokens -----------------------------------------------------------------
    CsfrTokens::getInstance($oReg, (new Randomizer(Randomizer::ALNUM_64)));
