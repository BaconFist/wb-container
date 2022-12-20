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
 * Description of upgrade-script.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: upgrade-script.php 363 2019-05-30 17:54:47Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use \bin\{WbAdaptor,SecureTokens};
use bin\helpers\{PreCheck,csvHelper,StopWatch};

// Stop execution if PHP version is too old
// PHP less then 7.2.0 is prohibited ---
if (\version_compare(PHP_VERSION, '7.4.0', '<')) {
    $sMsg = '<p style="color: #ff0000;">WebsiteBaker is not able to run with PHP-Versions less than 7.2.0!!<br />'
          . 'Please change your PHP to any Version from 7.4.x and up!<br />'
          . 'If you have problems solving this issue, ask your hosting provider for assistance.<br  />'
          . 'The very best solution is the use of PHP-8.0.x and up</p>';
    die($sMsg);
}

    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = $sAddonPath.'';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sAddonFile, 1 );
    $sAddonRel    = '/'.$sAddonName.'/';
    // comment out if you have to load config.php
//    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'/config.php')) {require($sAppPath.'/config.php');}

// get POST or GET requests, never both at once
    if (!empty($oReg)){
        $aRequestVars = [];
        $aVars = $oReg->Request->getParamNames();
        foreach ($aVars as $sName) {
            $aRequestVars[$sName] = $oReg->Request->getParam($sName);
        }
        if ($oReg->Request->issetParam('backend') || $oReg->Request->issetParam('frontend')){
        }
    }

/* -------------------------------------------------------------------------------- */
function sanitizeConfigFile($sConfigFile)
{
    global $sAppPath;
    if (!is_readable(dirname($sConfigFile).'/setup.ini.php'))
    {
        $sFileMarker = '*** auto generated config file for '.getNewVersionString();
        $sCfgContent = \file_get_contents($sConfigFile);
        $sPattern = '=define\s*\(\'DB_CHARSET\'\,\s*\'([^\']*)\'=is';
        $sDbCharset = ((\preg_match($sPattern, $sCfgContent, $aMatches)) ? $aMatches[1] : 'utf8_unicode_ci');
        $aNeedles =['utf8_unicode_ci', 'utf8mb4_unicode_ci'];
        $sDbCharset = (\in_array($sDbCharset, $aNeedles)  ? $sDbCharset : 'utf8_unicode_ci');
// check if config is created by WB
        $bUpgradeConfig = \preg_match('/'.\preg_quote($sFileMarker, '/').'/siU', $sCfgContent);
        $bCharsetConfig = \preg_match('/'.\preg_quote($sDbCharset, '/').'/siU', $sCfgContent);
        if (!$bUpgradeConfig || !$bCharsetConfig) {
            if (!\is_writeable($sConfigFile)) {
                $sMsg = 'The file ['.\basename($sConfigFile).'] is not writable and can not be corrected!'."\n"
                      . 'Please grant the necessary rights to the file and restart this program!';
                throw new \RuntimeException($sMsg);
            }
            // clean from includes
            $sPattern = '/\n[^;]*(require|include).*framework\/initialize\.php.*$/siU';
            $sCfgContent = \preg_replace($sPattern, "\n",$sCfgContent);
            // create temporary file
            $sTmpFilename = \tempnam($sAppPath.'temp', '~config');
            // fill it with old content
            \file_put_contents($sTmpFilename, $sCfgContent);
            // include this file
            include $sTmpFilename;
            // it can be deleted now
            \unlink($sTmpFilename);
            // collect and check available data
            $aValues = ['ADMIN_DIRECTORY' => 'admin'];
            if (!\defined('ADMIN_DIRECTORY')) {
                if (\defined('ADMIN_URL')) {
                  $sValue = \trim(\str_replace(\str_replace('\\', '/', WB_URL), '', str_replace('\\', '/', ADMIN_URL)), '/');
                    $aValues = ['ADMIN_DIRECTORY'=>$sValue];
                }
            } else {
                $sValue = (\defined('ADMIN_DIRECTORY') ? ADMIN_DIRECTORY : 'admin');
                $aValues = ['ADMIN_DIRECTORY' => $sValue];
            }
            unset($sValue);
            $aValues['WB_URL']       = \defined('WB_URL')       ? WB_URL       : '';
            $aValues['DB_TYPE']      = \defined('DB_TYPE')      ? DB_TYPE      : 'mysqli';
            $aValues['DB_HOST']      = \defined('DB_HOST')      ? DB_HOST      : 'localhost';
            $aValues['DB_PORT']      = \defined('DB_PORT')      ? DB_PORT      : '3306';
            $aValues['DB_NAME']      = \defined('DB_NAME')      ? DB_NAME      : '';
            $aValues['DB_USERNAME']  = \defined('DB_USERNAME')  ? DB_USERNAME  : '';
            $aValues['DB_PASSWORD']  = \defined('DB_PASSWORD')  ? DB_PASSWORD  : '';
            $aValues['DB_CHARSET']   = ((\defined('DB_CHARSET') && (\trim(DB_CHARSET) != '') && trim(DB_CHARSET) == $sDbCharset) ? DB_CHARSET : 'utf8_unicode_ci');
            $aValues['TABLE_PREFIX'] = \defined('TABLE_PREFIX') ? TABLE_PREFIX : 'wb_';
            // build the new config content
            $sConfigContent
                = '<?php'."\n"
                . '/*'."\n"
                . ' '.$sFileMarker."\n"
                . ' ****[WebsiteBaker]****'."\n"
                . ' *** created at '.\date('Y-m-d h:i:s e')."\n"
                . ' */'."\n"
                . '// define(\'DEBUG\', false);'."\n"
                . 'define(\'DB_TYPE\',         \''.$aValues['DB_TYPE'].'\');'."\n"
                . 'define(\'DB_HOST\',         \''.$aValues['DB_HOST'].'\');'."\n"
                . 'define(\'DB_PORT\',         \''.$aValues['DB_PORT'].'\');'."\n"
                . 'define(\'DB_NAME\',         \''.$aValues['DB_NAME'].'\');'."\n"
                . 'define(\'DB_USERNAME\',     \''.$aValues['DB_USERNAME'].'\');'."\n"
                . 'define(\'DB_PASSWORD\',     \''.$aValues['DB_PASSWORD'].'\');'."\n"
                . 'define(\'DB_CHARSET\',      \''.$aValues['DB_CHARSET'].'\');'."\n"
                . 'define(\'TABLE_PREFIX\',    \''.$aValues['TABLE_PREFIX'].'\');'."\n"
                . "\n"
                . 'define(\'WB_URL\',          "'.$aValues['WB_URL'].'"); '
                . '// no trailing slash or backslash!!!'."\n"
                . 'define(\'ADMIN_DIRECTORY\', \''.$aValues['ADMIN_DIRECTORY'].'\'); '
                . '// no leading/trailing slash or backslash!! A simple directory name only!!'."\n"
                . "\n"
                . 'require __DIR__.\'/framework/initialize.php\';'."\n"
                . '// --- end of file ----------------------------------'."\n";
            if (false === \file_put_contents($sConfigFile, $sConfigContent)) {
                $sMsg = 'Write file ['.\basename($sConfigFile).'] failed!'."\n"
                      . 'Please create the file manually. You can find an example at '
                      . '<a href="https://wiki.websitebaker.org/" title="WB-wiki">WebsiteBaker Wiki</a>';
                throw new \RuntimeException($sMsg);
            }
            $sMsg = 'Update file ['.\basename($sConfigFile).'] successfully completed!';
            throw new \RuntimeException($sMsg);
        }
    } // don't modify for WB 2.8.4

}
/* ************************************************************************** */
function getOldVersionString()
{
    global $sAppPath;
    $sRetval = '';
    $aMatches = [];
    $sConfigFile = \file_get_contents($sAppPath.'config.php');
    $sPattern = '=(\ \*\*\*[^\*]*?WebsiteBaker.*? )(?:[0-9][^ \n]*?)$=ism';
    if (\preg_match($sPattern, $sConfigFile, $aMatches)) {
        $sRetval = $aMatches[0];
    }
return $sRetval;
}
/* ************************************************************************** */
function getNewVersionString()
{
    global $sAppPath;
    $sAdminDirectory = searchAdminDir();
    $sVersionFile = $sAppPath.$sAdminDirectory.'/interface/version.php';
    $sVersionContent = \file_get_contents($sAppPath.''.$sAdminDirectory.'/interface/version.php');
    $sPattern = '=*(VERSION).*\=.*\"(.*)\"';
    \preg_match('/'.$sPattern.'/', $sVersionContent, $aMatch);
    $sRetval = isset($aMatch['2']) ? $aMatch['2'] : '???';
    return $sRetval;
}
/* ************************************************************************** */
function getNewRevisionString()
{
    global $sAppPath;
    $sAdminDirectory = searchAdminDir();
    $sVersionFile = $sAppPath.$sAdminDirectory.'/interface/version.php';
    $sVersionContent = \file_get_contents($sAppPath.''.$sAdminDirectory.'/interface/version.php');
    $sPattern = '=*(REVISION).*\=.*\"(.*)\"';
    \preg_match('/'.$sPattern.'/', $sVersionContent, $aMatch);
    $sRetval = isset($aMatch['2']) ? $aMatch['2'] : '???';
    return $sRetval;
}

/* ************************************************************************** */
function updateConfigPhP($sConfigFile, $sOldVersionString)
{
    $oReg = WbAdaptor::getInstance();
    $oDb  = $oReg->getDatabase();
    StopWatch::start();
    $sNewVersion = getNewVersionString();
    $sql = 'SELECT `value` FROM `'.$oReg->TablePrefix.'settings` '
         . 'WHERE `name`=\'wb_version\'';
    $sOldVersion = $oDb->get_one($sql);
    if ($sNewVersion != $sOldVersion) {
    // new upgrade detected
        $sVersionString = ' *** WebsiteBaker upgrade from '.$sOldVersion.' to '.$sNewVersion;
    } else {
    // modify old string if needed
        $sVersionString = ($sOldVersionString ?: ' *** WebsiteBaker '.$sNewVersion);
    }
    $sCfgContent = \file_get_contents($sConfigFile);

    \file_put_contents($sConfigFile, \str_replace(' ****[WebsiteBaker]****', $sVersionString, $sCfgContent));
    $sVersionString = \sprintf($sVersionString, $sNewVersion);
}
/* ************************************************************************** */
function searchAdminDir()
{
    global $sAppPath;
    $sBaseDir = $sAppPath.'*';
    $sConfigFile = 'config.php';
    $sAdminFolder = false;
    foreach (\glob($sBaseDir, \GLOB_ONLYDIR) as $sFolder) { //\GLOB_MARK|
        $sFolder = \str_replace('\\', '/', $sFolder);
        if (
            \file_exists(($sFolder).'/access/') &&
            \file_exists(($sFolder).'/interface/') &&
            \file_exists(($sFolder).'/groups/')
        ) {
            $sAdminFolder = \trim(\basename($sFolder), '/');
            break;
        }
    }
    if (!$sAdminFolder) {
        throw new \RuntimeException('Sorry, '.\basename($sConfigFile).' is not readable or does not exist!');
    }
    return $sAdminFolder;
}
/* ************************************************************************** */
/* *** start script ********************************************************* */
/* ************************************************************************** */

$sOldVersionString = getOldVersionString();
// exception handling
try {
    sanitizeConfigFile($sAppPath.'config.php');
} catch(\Exception $e) {
    $aServerDefaultPorts = ['80','443'];
    $aServerVariables = $_SERVER;
    $sProtokol  = ((!isset($aServerVariables['HTTPS']) || $aServerVariables['HTTPS'] == 'off' ) ? 'http' : 'https') . '://';

    $sSriptname = \trim(isset($aServerVariables['SCRIPT_URI'])
                   ? $aServerVariables['SCRIPT_URI'].'?'.$aServerVariables['QUERY_STRING']
                   : (isset($aServerVariables['REQUEST_URI'])
                     ? $aServerVariables['REQUEST_URI']
                     : $aServerVariables['SCRIPT_NAME']),'/');
    $sReloadLink = $sProtokol.$aServerVariables['HTTP_HOST'].(\in_array($aServerVariables['SERVER_PORT'],$aServerDefaultPorts) ? '' : $aServerVariables['SERVER_PORT'].':').'/'.$sSriptname;
    $aTmp = \explode('?', $sReloadLink, 2);
    $sReloadLink = $aTmp[0].'?ts='.\dechex(\time());
    $sReloadLink = WB_URL.'/install/upgrade-script.php?ts='.\dechex(time());

    $sOutput
        = '<!DOCTYPE html><html lang="en-US"><head>'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<meta charset="UTF-8"><meta name="robots" content="noindex,nofollow">'
        . '<meta http-equiv="expires" content="0">'
        . '<title>System Message</title></head>'
        . '<body><h1>WebsiteBaker - System Message</h1><hr>'
        . '<p>'.\nl2br($e->getMessage(), false).'</p>'
        . '<form><button style="margin: 5px 50px;" type="submit" formmethod="get" formaction="'.$sReloadLink.'">'
        . 'Restart Program<br>(or press F5)</button></form>'
        . '<hr></body></html>';
    echo $sOutput;
    \flush();
    die;
}
/* ************************************************************************** */
// include the new config and initialize
/* ************************************************************************** */
    if (!\defined('WB_URL')) { require($sAppPath.'config.php'); }
    updateConfigPhP($sAppPath.'config.php', $sOldVersionString);
//    if (!\function_exists('make_dir'))  {require($sAppPath.'framework/functions.php');}
    if (!\class_exists('\admin')) {require ($sAppPath.'framework/class.admin.php');}
    $admin = new \admin('Addons', 'modules', false, false);
/*--------------------------------------------------------------------------------------*/
    $WbMinVersion = '2.7.0';
    $sUpgradeMinVersion = '2.12.1';
    $sUpgradeMaxVersion = VERSION;
/*--------------------------------------------------------------------------------------*/

/* display a status message on the screen **************************************
 * @param string $message: the message to show
 * @param string $class:   kind of message as a css-class
 * @param string $element: witch HTML-tag use to cover the message
 * @return void
 */
    function status_msg($message, $class='check', $element='p')
    {
        // returns a status message
        $msg  = '<'.$element.' class="'.$class.'" style="padding: 0.825em;">';
//        $msg .= '<h4>'.strtoupper(strtok($class, ' ')).'</h4>';
        $msg .= $message.'</'.$element.'>';
        echo '<div class="message">'.$msg.'</div>';
    }

    function bodyScript(){
?>
    <script>
    </script>
<?php
    }
    function bodyFormLogin() {
      $oReg = WbAdaptor::getInstance();
?>
                <form action="<?php echo $oReg->AcpUrl;?>login/index.php" method="post">
                   <span style="padding: 0.825em 0.525em;"><input name="backend_send" type="submit" value="Kick me to the Login" /></span>
                </form>
                <br /><br />
            </div>
        </body>
    </html>
<?php
    }

    function bodyTag() {
      $oReg = WbAdaptor::getInstance();
?>
            </div>
            <br />
            <script>
                function redirect() {
                    document.location = '<?php echo $oReg->AcpUrl."start//index.php";?>';
                }
                var btnBack = document.getElementById("btnBack");
//                btnBack.onclick = redirect();
                btnBack.addEventListener('click',
                    function() {
                      confirm = document.getElementById("confirmed");
                      confirm.removeAttribute('required');
                      redirect();
                      },
                     false);
            </script>
        </body>
    </html>
<?php
    }

    function bodyTagDownload() {
?>
            </div>
        </body>
    </html>
<?php
    }
    $oReg = WbAdaptor::getInstance();
    $sAddonCompareFlag = '<';
    if (\is_readable($oReg->AppPath.'install/ModuleWhiteList')){
        $aModuleWhiteList = \file($oReg->AppPath.'install/ModuleWhiteList', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    } else {
// default $aModuleWhiteList
        $aModuleWhiteList =
              [
                    'captcha_control',
                    'ckeditor',
                    'code',
                    'droplets',
                    'form',
                    'jsadmin',
                    'menu_link',
                    'news',
                    'output_filter',
                    'show_menu2',
                    'WBLingual',
                    'wrapper',
                    'wysiwyg'
            ];
    }

    $aDefaultSettings = [
        'app_name' => 'wb-2121',
        'confirmed_registration' => '0',
        'debug' => 'false',
        'dev_infos' => 'false',
        'sgc_execute' => 'false',
        'default_charset' => 'utf-8',
        'default_date_format' => 'm-d-Y',
        'default_language' => 'en',
        'default_template' => 'DefaultTemplate',
        'default_theme' => 'DefaultTheme',
        'default_time_format' => 'g:i|A',
        'default_timezone' => '',
        'er_level' => '32767',
        'frontend_login' => 'false',
        'frontend_signup' => 'false',
        'dsgvo_settings' => 'a:3:{s:19:"use_data_protection";b:1;s:2:"DE";i:0;s:2:"EN";i:0;}',
        'home_folders' => 'false',
        'homepage_redirection' => 'false',
        'intro_page' => 'false',
        'manage_sections' => 'true',
        'media_directory' => '/media',
        'mediasettings' => '',
        'media_height' => '0',
        'media_width' => '0',
        'media_compress' => '75',
        'media_version' => '1.0.0',
        'multiple_menus' => 'true',
        'operating_system' => 'linux',
        'page_extension' => '.php',
        'page_icon_dir' => '/templates/*/title_images',
        'page_languages' => 'true',
        'page_level_limit' => '4',
        'page_spacer' => '-',
        'page_trash' => 'inline',
        'pages_directory' => '/pages',
        'redirect_timer' => '1000',
        'rename_files_on_upload' => 'ph.*?,cgi,pl,pm,exe,com,bat,pif,cmd,src,asp,aspx,js,inc',
        'search' => 'public',
        'sec_anchor' => 'Sec',
        'page_oldstyle' => 'false',
        'page_newstyle' => 'true',
        'sec_token_fingerprint' => 'true',
        'sec_token_netmask4' => '24',
        'sec_token_netmask6' => '64',
        'sec_token_life_time' => '1800',
        'section_blocks' => 'true',
        'server_email' => 'info@example.com',
        'show_start_datetime' => 'false',
        'smart_login' => 'true',
        'string_dir_mode' => '0755',
        'string_file_mode' => '0644',
        'system_locked' => '0',
        'user_login' => '1',
        'twig_version' => '3',
        'jquery_version' => '1.9.1',
        'jquery_cdn_link' => '',
        'warn_page_leave' => '1',
        'wb_revision' => '',
        'wb_sp' => '',
        'wb_version' => '',
        'patch_revision' => '',
        'patch_update' => '0',
        'wbmailer_default_sendername' => 'WB Mailer',
        'wbmailer_routine' => 'phpmail',
        'wbmailer_smtp_debug' => '0',
        'wbmailer_smtp_auth' => '',
        'wbmailer_smtp_host' => 'localhost',
        'wbmailer_smtp_password' => '',
        'wbmailer_smtp_port' => '25',
        'wbmailer_smtp_secure' => 'TLS',
        'wbmailer_smtp_username' => '',
        'website_description' => '',
        'website_footer' => '',
        'website_header' => '',
        'website_keywords' => '',
        'website_signature' => '',
        'website_title' => '',
        'wysiwyg_editor' => 'ckeditor',
        'wysiwyg_style' => 'font-family: Verdana => Arial => Helvetica => sans-serif; font-size: 12px;',
    ];

// database tables including in WB package
    $table_list = ['settings','groups','addons','pages','sections','search','users'];

    $OK               = ' <span class="ok">OK</span> ';
    $FAIL             = ' <span class="error">FAILED</span> ';
    $OK               = ($OK ?? '<span style="color:green;">&#10004;</span>');
    $FAIL             = ($FAIL ?? '<span style="color:red;">&#10007;</span>');
    $DEFAULT_THEME    = 'DefaultTheme';
    $bShowDetails     = false;
    $stepID = 0;
/*
    $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
*/
    $sInstallStruct = 'install/settings-struct.sql.php';
    if (! $oReg->Db->SqlImport($oReg->AppPath.$sInstallStruct, $oReg->TablePrefix, 'upgrade' )){
        $sMsg = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
        status_msg($sMsg, 'error warning', 'h4');
    }

    $DEFAULT_TEMPLATE = (DEFAULT_TEMPLATE ?: 'DefaultTemplate');
    if (DEFAULT_THEME !== $DEFAULT_THEME) {
      db_update_key_value('settings', 'default_theme', $DEFAULT_THEME);
    //  exit();
    }
    $sScriptUrl = $oReg->AppUrl.ltrim($oRequest->getServerVar('SCRIPT_NAME'),'/');
    $sScriptUrl = ($oRequest->getServerVar('SCRIPT_NAME'));
    $sScriptUrl = $oReg->AppRel.'install/upgrade-script.php';
//    $sThemeUrl = WB_URL.'/templates/'.(\is_readable(WB_URL.'/templates/'.$DEFAULT_THEME) ? $DEFAULT_THEME:'DefaultTheme');
    $sThemeUrl = $oReg->ThemeUrl;
// ------------------------------------------------------------------------------
//
// ------------------------------------------------------------------------------
// analyze/check database tables
    function mysqlCheckTables( $dbName ){
        global $table_list,$FAIL;
        $oReg = WbAdaptor::getInstance();
        $oDb  = $oReg->getDatabase();
        $table_prefix = $oReg->TablePrefix;

        $sql = 'SHOW TABLES FROM `'.$dbName.'`';
        $result = $oDb->query($sql);

        $data = [];
        $retVal = [];
        $x = 0;

        while (( $row = $result->fetchRow(MYSQLI_NUM)) == true)
        {
            $sql = "CHECK TABLE `" . $row[0].'`';
            $analyze = $oDb->query($sql);
            if( $analyze ) {
                $rowFetch = $analyze->fetchRow(MYSQLI_ASSOC);
                $data[$x]['Op'] = $rowFetch["Op"];
                $data[$x]['Msg_type'] = $rowFetch["Msg_type"];
                $msgColor = '<span class="error">';
                $data[$x]['Table'] = $row[0];
                $retVal[] = $row[0];
               // print  " ";
                $msgColor = ($rowFetch["Msg_text"] == 'OK') ? '<span class="ok">' : '<span class="error">';
                $data[$x]['Msg_text'] = $msgColor.$rowFetch["Msg_text"].'</span>';
               // print  "";
                $x++;
             } else {
                echo '<br /><b>'.$sql.'</b>'.$FAIL.'<br />';
            }
       }
        return $retVal; //$data;
    }

// check existings tables for upgrade or install
    function check_wb_tables(){
        global $table_list;
        $oReg = WbAdaptor::getInstance();
        $oDb  = $oReg->getDatabase();
     // if prefix inludes '_' or '%' $oRes = $oDb->query('SHOW TABLES LIKE \'%'.TABLE_PREFIX.'settings%\'');
         $search_for = 'SHOW TABLES LIKE \'%'.$oReg->TablePrefix.'%\'';
         $get_result = $oDb->query($search_for);
            // $get_result = $database->query( "SHOW TABLES FROM ".DB_NAME);
            $all_tables = [];
            if ($get_result->numRows() > 0)
            {
                while ($data = $get_result->fetchRow(MYSQLI_BOTH))
                {
                    $tmp = \str_replace($oReg->TablePrefix, '', $data[0]);
                    if (\in_array($tmp,$table_list))
                    {
                        $all_tables[] = $tmp;
                    }
                }
            }
         return $all_tables;
    }

    $oReg = WbAdaptor::getInstance();
// check existing tables
    $all_tables = check_wb_tables();
/* */

?><!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Upgrade script</title>
    <meta name="author" content="WebsiteBaker Org e.V." />
<!-- Mobile viewport optimisation -->
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=2" />

    <link rel="stylesheet" href="<?= $oReg->ThemeUrl;?>css/4/w3.css" media="screen" />
    <link rel="stylesheet" href="<?= $oReg->ThemeUrl;?>css/fontawesome.min.css" media="screen" />

    <style>
        html{overflow:-moz-scrollbars-vertical;}
        body{margin:0;padding:0;border:0;background:#EBF7FC;color:#4A4A4A;font-family: 'Trebuchet MS ',Verdana,Arial,Helvetica,Sans-Serif;font-size:14px;height:101%;}
        #container{width:85%;color:#4A4A4A;background:#A8BCCB url("<?= $sThemeUrl; ?>images/background.png") repeat-x;border:1px solid #000;margin:2em auto;padding:0 0.925em;min-height:19.225em;text-align:left;margin: 2.225em auto;}
        form{display:inline-block;line-height:20px;vertical-align:baseline;}
        a:link{text-decoration: none;color: #60DB6A;}
        a:active{color: #60DB6A;}
        a:focus{color: #60DB6A;}
        a:hover{color: #F31D0E;}
        h1,h2,h3,h4,h5,h6{font-family:Verdana,Arial,Helvetica,sans-serif;color:#527AA2;margin-top:1.0em;margin-bottom:0.1em;}
        h1{font-size:150%;}
        h2{font-size:110%;border-bottom: none;}
        h3{font-size:120%;}
        h4{font-size:110%;font-weight: normal;}
        h5{font-size:100%;font-weight: normal;}
        h6{font-size:100%;font-weight: normal;margin: 0.2em;color:#6B6B6B;}
        input[type= "submit " ].restart{background-color:#FFDBDB;font-weight:bold;}
        input#btnBack{margin-left: 8px;}
        p{line-height:1.5em;}
        .ok,.error{font-weight:bold;}
        .ok{color:green;}
        .error{color:red;}
        .header{color:#515050;}
        .check{color:#4A4A4A;}
        .result{color:#C3E3C3;}
        .content{margin-left:1.925em;}
        .warning{width:98%;background:#FCDADA;padding:0.2em;margin-top:0.5em;border:1px solid black;}
        .error p{color:#369;}
        .info{width:98%;background:#C3E3C3;padding:0.2em;margin-top:0.5em;border:1px solid black;}
        .message{padding:0.525em;margin-bottom: 0.825em;}
        input,label{cursor:pointer;}
        ul{list-style: none;line-height: 2.1;}
        .w3-padding-4{padding-top:4px!important;padding-bottom:4px!important}
        .w3-button{color:#000;background-color:#f1f1f1;padding:8px 16px}.w3-button:hover{color:#000!important;background-color:#ccc!important}
        .w3-btn{border:none;display:inline-block;outline:0;padding:8px 16px;vertical-align:middle;overflow:hidden;text-decoration:none!important;color:#03369b;background:#A8BCCB url("<?= $sThemeUrl; ?>images/background.png") repeat-x ;text-align:center;cursor:pointer;white-space:nowrap;}
        .w3-btn:hover,.w3-btn-block:hover,.w3-btn-floating:hover,.w3-btn-floating-large:hover{box-shadow:0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)}
        .w3-blue-wb,.w3-hover-blue-wb:hover{color:#fff!important;background-color:#1A75AA!important}
        input.switch {
        -webkit-appearance: none;-moz-appearance: none;-o-appearance: none;
        width:40px;height:20px;
        background-color:#fff;border:0px solid #D9DADC;border-radius:50px;
        -webkit-box-shadow: inset -20px 0px 0px 0px #D9DADC;
        box-shadow: inset -20px 0px 0px 0px #D9DADC;
        -webkit-transition-duration: 200ms;
        transition-duration: 400ms;
        vertical-align: top;
        }
        input.switch:checked {-webkit-box-shadow: inset 20px 0px 0px 1px #4ed164;box-shadow: inset 20px 0px 0px 1px #4ed164;}
    </style>
    <link rel="shortcut icon" href="<?= $sThemeUrl; ?>images/favicon.ico" type="image/x-icon"/>

</head>
<body>
    <div id="container" class=" w3-container">
        <img src="<?= $sThemeUrl; ?>images/logo.png" alt="WebsiteBaker Project" />
        <h2>WebsiteBaker Upgrade</h2>
<?php
    if (!defined('WB_REVISION')){define('WB_REVISION', '999');}
    $oldVersionOutput  = \trim(''.WB_VERSION.'+'.(\defined('WB_SP') ? WB_SP : ''), '+').' (r'.WB_REVISION.')';
    $newVersionOutput  = \trim(''.VERSION.'+'.(\defined('SP') ? SP : ''), '+').' (r'.REVISION.')';
    $oldVersion  = \trim(''.WB_VERSION.'+'.WB_REVISION.'+'.(\defined('WB_SP') ? WB_SP : ''), '+');
    $newVersion  = \trim(''.VERSION.'+'.REVISION.'+'.(\defined('SP') ? SP : ''), '+');
    $sUpdateFile = ADMIN_PATH.'/interface/update';
    $admin_revision = '';
    if (\is_readable($sUpdateFile)){
        include $sUpdateFile;
    } elseif(\is_readable($sUpdateFile.'.fixed')){
        include $sUpdateFile.'.fixed';
    }

    if (\version_compare( WB_VERSION, $WbMinVersion, '<' )) {
        status_msg('It is not possible to upgrade from WebsiteBaker Versions before '.$WbMinVersion.'. To upgrade to version '.VERSION.' you must first upgrade to v'.$WbMinVersion.' at least!', 'warning','h2');
        status_msg('First <a href="https://addon.websitebaker.org/pages/en/browse-add-ons.php?download=0EA85F12">[downloading]</a> and upgrading to WebsiteBaker 2.8.3 before upgrading to the latest stable version. Find it on our <a href="https://wiki.websitebaker.org/doku.php/en/downloads">wiki download area</a>', 'info','h2');
        $sMsg  = 'You can overwrite the existing WebsiteBaker Version '.WB_VERSION.'! Please close this Browser Tab.<br />';
        $sMsg .= 'After uploading the '.$WbMinVersion.' package to your Webspace via FTP, log in to the backend and confirm upgrade to WebsiteBaker '.VERSION;
        status_msg($sMsg, 'info', 'h4');
        bodyTagDownload();
        exit();
    }

if ((int)$admin->getUserId()!==1){
    status_msg('WebsiteBaker upgrading is not possible!<br />Before upgrading '
              .'to Version '.VERSION.' you have to login as System-Administrator!',
              'warning', 'h4');
  // delete remember key cookie if set
    if (isset($_COOKIE['REMEMBER_KEY']) && !headers_sent() ) {
      \setcookie('REMEMBER_KEY', '', time() - 3600, '/');
    }
    // delete most critical session variables manually
    $_SESSION['USER_ID'] = null;
    $_SESSION['GROUP_ID'] = null;
    $_SESSION['GROUPS_ID'] = null;
    $_SESSION['USERNAME'] = null;
    $_SESSION['PAGE_PERMISSIONS'] = null;
    $_SESSION['SYSTEM_PERMISSIONS'] = null;
    // overwrite session array
    $_SESSION = [];
    // delete session cookie if set
    if (isset($_COOKIE[session_name()]) && !headers_sent()) {
        \setcookie(session_name(), '', \time() - 42000, '/');
    }
    // delete the session itself
    \session_destroy();
    status_msg('Log in as System-Administrator, then start '
              .'upgrade-script.php again!', 'info', 'h4');
    if (\defined('ADMIN_URL')) {
        bodyFormLogin();
    }
    exit();
}

?>
<p>This script upgrades an existing WebsiteBaker <strong> <?php echo $oldVersionOutput; ?></strong> installation to <strong> <?php echo $newVersionOutput ?> </strong> Patch <?= $admin_revision; ?>.<br />The upgrade script changes the existing WB database to reflect the changes introduced with newer versions.</p>

<?php
/**
 * Check if disclaimer was accepted
 */
if (!(isset($_POST['backup_confirmed']) && $_POST['backup_confirmed'] == 'confirmed')) { ?>
<h2>Step 1: Backup your files</h2>
<p>It is highly recommended to <strong> backed up </strong>of all files and folders in the entire <strong>WebsiteBaker installation</strong> and <strong>MySQL database</strong> before proceeding.
<br /><strong class="error">Note: </strong>The upgrade script alters some settings of your existing database!!! You need to confirm the disclaimer before proceeding.</p>

    <form name="send" action="<?php echo $sScriptUrl;?>" method="post">
        <ul>
            <li>
                <textarea cols="80" rows="5">DISCLAIMER: The WebsiteBaker upgrade script is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. One needs to confirm that a manual backup of the /pages folder (including all files and subfolders contained in it) and backup of the entire WebsiteBaker MySQL database was created before you can proceed.</textarea>
            </li>
            <li>
                <input class="switch" name="show_details" type="checkbox" value="1" id="show_details" />&nbsp;<label for="show_details">Show Upgrade Details (optional)</label>
            </li>
            <li>
                <input class="switch" name="force_addon_upgrade" checked="checked" type="checkbox" value="1" id="force_addon_upgrade" />&nbsp;<label for="force_addon_upgrade">Force Addons Upgrade (if not checked, upgrade only newer ones)</label>
            </li>
            <li>
                <input class="switch" name="backup_confirmed" type="checkbox" value="confirmed" id="confirmed" required="required" />&nbsp;<label for="confirmed">I confirm that i backed up the entire WebsiteBaker installation and MySQL database.</label>
            </li>
            <li>
                <input id="send" class="w3-btn w3-blue-hover-wb" name="send" type="submit" value="Start upgrade script" />
                <input id="btnBack" class="w3-btn w3-blue-hover-wb" name="close" value="Close and Back" />
            </li>
        </ul>
    </form>
<?php
    status_msg('You confirm that you have backed up  all files and folders of your WebsiteBaker installation and MySQL database before proceeding.', 'warning', 'h3');
    bodyTag();
    exit();
}
    $bShowDetails = \filter_input(\INPUT_POST,'show_details', \FILTER_VALIDATE_BOOLEAN);
    $bForceAddonUpgrade = \filter_input(\INPUT_POST,'force_addon_upgrade', \FILTER_VALIDATE_BOOLEAN);
// force to upgrade addons listed in ModuleWhiteList
    $sAddonCompareFlag = ($bForceAddonUpgrade ? '<=' : '<');
// function to add a var/value-pair into settings-table
    function db_add_key_value($key, $sValue) {
        global $database, $OK, $FAIL;
        global $bShowDetails;
        $bRetval = false;
        $table = TABLE_PREFIX.'settings';
        if (!\defined($key)){\define($key, $sValue);}
        $query = $database->query("SELECT `value` FROM `$table` WHERE `name` = '$key' ");
        if($query->numRows() > 0) {
            $sql = "UPDATE $table SET `name` = '$key',`value` = '$sValue' WHERE `name` = '$key' ";
            if (!$database->query($sql)) {
              if ($bShowDetails){echo \sprintf('%s %s <br />', $database->get_error(), $FAIL);}
            } else {
            if ($bShowDetails){echo "$key: already exists. $OK.<br />";}
            $bRetval = true;
            }
        } else {
            $database->query("INSERT INTO `$table` (`name`,`value`) VALUES ('$key', '$sValue')");
            if ($bShowDetails){echo ($database->is_error() ? $database->get_error().'<br />' : '');}
            $query = $database->query("SELECT `value` FROM `$table` WHERE `name` = '$key' ");
            if($query->numRows() > 0) {
                if ($bShowDetails){echo "insert $key: $OK.<br />";}
                $bRetval = true;
            } else {
                if ($bShowDetails){echo "insert $key: $FAIL!<br />";}
                $bRetval = false;
            }
        }
        return $bRetval;
    }

// function to add a new field into a table
     function db_add_field($sTable, $sField, $desc) {
        global $database, $OK, $FAIL;
        global $bShowDetails;
        $sTable = TABLE_PREFIX.$sTable;
        if (($database->field_exists($sTable,$sField)==false)) {
            if ($database->field_add($sTable,$sField,$desc)){
                if ($bShowDetails){echo "'$sField' added. $OK.<br />";}
            }else {
                if ($bShowDetails){echo "adding '$sField' $FAIL!<br />";}
            }
        }
        else
        {
            if ($bShowDetails){echo "'$sField' already exists. $OK.<br />";}
        }
     }
/**
 *
 * @param object $oDb  current database object
 * @param string $sTablePrefix the valid TABLE_PREFIX
 * @return an error message or emty string on ok
 */
    function MigrateSettingsTable($oDb, $sTablePrefix, $aDefaults)
    {
        global $bShowDetails,$sAppPath;
        $sRetval        = '';
        $aSettings      = [];
        $aOldSettings   = [];
        $sCfgContent = \file_get_contents($sAppPath.'config.php');
        $sPattern = '=define\s*\(\'DB_CHARSET\'\,\s*\'([^\']*)\'=is';
        $sTblCollation = ((\preg_match($sPattern, $sCfgContent, $aMatches)) ? $aMatches[1] : 'utf8mb4_unicode_ci');
        $sTblCollation = 'utf8mb4_unicode_ci';
        $aTmp = \preg_split('/_/', $sTblCollation, 0, PREG_SPLIT_NO_EMPTY);
        $sCharset = $aTmp[0];
        $sql = 'SELECT * FROM `'.$sTablePrefix.'settings`';
        if (($oSettings = $oDb->query($sql))) {
            // backup all entries and remove duplicate entries
            while (($aEntry = $oSettings->fetchAssoc())) {
                $aOldSettings[$aEntry ['name']] = $aEntry ['value'];
                (\define($aEntry ['name'], $aEntry ['value']));
            }
            $aSettings = \array_merge($aDefaults, $aOldSettings);
            // drop the old table
            $sql = 'DROP TABLE IF EXISTS `'.$sTablePrefix.'settings`';
            if (!($oDb->query($sql))) { if ($bShowDetails){$sRetval = 'unable to delete old table `settings`';}goto end;}
            // recreate the table with corrected structure
            $sql = 'CREATE TABLE IF NOT EXISTS `'.$sTablePrefix.'settings` ('
                 .     '`name` VARCHAR(160) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\', '
                 .     '`value` LONGTEXT COLLATE '.$sTblCollation.' NOT NULL, '
                 .     'PRIMARY KEY (`name`)'
                 . ')ENGINE=InnoDB DEFAULT CHARSET='.$sCharset.' COLLATE='.$sTblCollation.'';
            if (!($oDb->query($sql))) { if ($bShowDetails){$sRetval = 'unable to recreate table `settings`';} goto end; }
            // insert backed up entries into the new table
            foreach ($aSettings as $sName => $sValue) {
/*
                db_add_key_value($sName,$sValue);
*/
                $sql = 'INSERT INTO  `'.$sTablePrefix.'settings`'
                     . 'SET `name`=\''.$oDb->escapeString($sName).'\', '
                     .    '`value`=\''.$oDb->escapeString($sValue).'\'';
                if (!($oDb->query($sql))) { $sRetval = 'unable to insert values into new table `settings`'; goto end;}
                $sRetval = '';
            }
        } else {
            if ($bShowDetails){$sRetval = 'unable to read old table `settings`';}
        }
end:
        return $sRetval;
    }

// check again all tables, to get a new array
    if ((\sizeof($all_tables) != \sizeof($table_list))) {
      $all_tables = check_wb_tables();
    }

/**********************************************************
 *  - check tables comin with WebsiteBaker
 */
    $check_text = 'total ';
    // $check_tables = mysqlCheckTables( DB_NAME ) ;
    if ((\sizeof($all_tables) === \sizeof($table_list)))
    {
        echo ('<h2>Step '.(++$stepID).' Your database '.DB_NAME.' has '.\sizeof($all_tables).' '.$check_text.' tables from '.sizeof($table_list).' included in package '.'</h2>');
    }
    else
    {
        status_msg('can\'t run Upgrade, missing tables', 'warning', 'h3');
        echo '<h4>Missing required tables. Contact your administrator. Then run upgrade-script.php again</h4>';
        $aResult = \array_diff ( $table_list, $all_tables );
        echo '<h4 class="warning"><br />';
        foreach ($aResult as $key => $val) {
            echo TABLE_PREFIX.$val.' '.$FAIL.'<br>';
        }
?>
        <br /></h4>
        <div class="w3-twothird w3-container" style="margin: 10px 0;" >
            <form action="<?= $sScriptUrl;?>">
                <input type="submit" value="kick me back" />
            </form>
<?php
        if (\defined('ADMIN_URL')) {
?>
            <form action="<?= ADMIN_URL;?>" target="_self">
                <input type="submit" value="kick me to the Backend" />
            </form>
<?php
        }
?>
        </div>
        </div><!-- #container zn805 -->
        </body>
        </html>
<?php
        exit();
    }
/* */
echo '<h2>Step '.(++$stepID).' : clear Translate cache if exists</h2>';
//**********************************************************
if (\is_writable(WB_PATH.'/temp/cache')) {
    \Translate::getInstance()->clearCache();
}

if (\defined('DEBUG') && DEBUG){
    echo '<h2>Step '.(++$stepID).' : Adding/Updating settings table</h2>';
    echo "<br />Set DEBUG Mode to false in settings table<br />";
    db_update_key_value('settings', 'debug', 'false');
    $msg = '<p> To run upgrade-script  properly,  Debug property was set to the value==false and will be corrected automatically.</p><p>Please restart the upgrade-script!</p>';
    status_msg($msg, 'error warning', 'h3');
    echo '<p style="font-size:120%;"><strong>WARNING: The upgrade script failed ...</strong></p>';
    echo '<form action="'.$sScriptUrl.'">';
    echo '&nbsp;<input name="send" type="submit" value="Restart upgrade script" />';
    echo '</form>';
    echo '<br /><br /></div></body></html>';
    exit;

}

/**********************************************************/
echo '<h2>Step '.(++$stepID).' : Adding/Updating database core tables</h2>';
/***********************************  - Upgrade Core Tables **************/
// try to upgrade table if not exists
    $sInstallStruct = WB_PATH.'/install/install-struct.sql.php';
    if (\is_readable($sInstallStruct)){
    // first some index drop if exists not needed anymore
        $aIndex   = [];
/*
        //$aIndex[] = ['table' => TABLE_PREFIX.'settings', 'field'=>'ident'];
        $aIndex[] = ['table' => TABLE_PREFIX.'addons', 'field'=>'ident_addons'];
        $aIndex[] = ['table' => TABLE_PREFIX.'groups', 'field'=>'ident_groups'];
        echo '<div class="content">';
// ------------------------------------------------------
        for ($i=0; $i < \sizeof($aIndex); $i++){
            if ($database->index_remove($aIndex[$i]['table'],$aIndex[$i]['field'])){
                if ($bShowDetails){echo 'DROP INDEX '.$aIndex[$i]['table'],' ',$aIndex[$i]['field'].' '.$database->get_error(). $OK.'<br />';}
            }
        }
        echo '</div>';
*/
        if (! $database->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
            echo '<div class="content">';
            echo $database->get_error(). $FAIL.'(Database Error '.$database->get_errno().')<br />';
            echo '</div>';
        } else {
            echo '<div class="content">';
            echo 'Upgrade Core Tables '. $OK.'<br />';
            echo '</div>';
            echo '<h2>Step '.(++$stepID).' : Adding/Updating publ_date sections table</h2>';
            echo '<div class="content">';
            $sDescription = 'UPDATE `'.TABLE_PREFIX.'sections` SET `publ_end`='.MAX_DATETIME.' WHERE `publ_end` NOT BETWEEN 1 AND '.(MAX_DATETIME-1);
            if (!$database->query($sDescription)){
              if ($bShowDetails){echo 'Upgrading sections Table (publ_end field) '. $FAIL.'<br />';}
            } else {
              if ($bShowDetails){echo 'Upgrade sections Table (publ_end field) '. $OK.'<br />';}
            }
            $sDescription = 'UPDATE `'.TABLE_PREFIX.'sections` SET `title` = REPLACE(`title`,\'Section-ID 0\',\'\') WHERE `title` LIKE \'%Section-ID%\'';
            if ($bShowDetails && !$database->query($sDescription)){
              echo 'Upgrading sections Table (empty title field) '. $FAIL.'<br />';
            } else {
              if ($bShowDetails){echo 'Upgrade sections Table (empty title field) '. $OK.'<br />';}
            }
    /* - TODO has to be moved later --
            $sDescription = 'UPDATE `'.TABLE_PREFIX.'users` SET `home_folder` = \'\' WHERE `home_folder` != \'\'';
            if ($bShowDetails && !$database->query($sDescription)){
              echo 'Upgrading sections Table (empty home_folder field) '. $FAIL.'<br />';
            } else {
              if ($bShowDetails){echo 'Upgrade users Table (empty home_folder field) '. $OK.'<br />';}
            }
-- */
            echo '</div>';
        }
    } else {
    if (!\is_readable(WB_PATH.'/install')) {
        $msg = '<p>\'Missing or not readable install folder\' '.$FAIL.'</p>';
    } else {
        $msg = '<p>\'Missing or not readable file [install-struct.sql.php]\'</p> '.$FAIL.'';
    }

        $msg = $msg.'<p>Check if the install folder exists.<br />Please upload install folder
                using FTP and restart upgrade-script!</p>';
        status_msg($msg, 'error warning', 'h3');
        echo '<p style="font-size:120%;">WARNING: The upgrade script failed ...</p>';
        echo '<form action="'.$sScriptUrl.'">';
        echo '&nbsp;<input name="send" type="submit" value="Restart upgrade script" />';
        echo '</form>';
        echo '<br /><br /></div></body></html>';
        exit;
    }
\flush();
echo '<h2>Step '.(++$stepID).' : Updating Administators system permission</h2>';
/***********************************  - Upgrade group Table **************/
// update-group.sql.php
    $sInstallData = WB_PATH.'/install/update-group.sql.php';
    if (\is_readable($sInstallData)){
        if (!$database->SqlImport($sInstallData, TABLE_PREFIX, 'upgrade' )){
            echo '<div class="content">';
            echo sprintf('%s updating Administators system permission %s <br />',$database->get_error(), $FAIL);
            echo '</div>';
        } elseif ($bShowDetails) {
            echo '<div class="content">';
            echo sprintf('Updating Administators system permission %s <br />', $OK);
            echo '</div>';
        }
// Be sure to upgrade the running User SESSION
        if (isset($_SESSION['SYSTEM_PERMISSIONS'])){
            $aTmp1 = $_SESSION['SYSTEM_PERMISSIONS'];
            $sqlAdmin = 'SELECT `system_permissions` FROM `'.TABLE_PREFIX.'groups` '
                      . 'WHERE `group_id`=\'1\' ';
            $sPermissions = $database->get_one($sqlAdmin);
            $aPermissions = explode(',',$sPermissions);
            $_SESSION['SYSTEM_PERMISSIONS'] = $aPermissions;
            $aTmp2 = $_SESSION['SYSTEM_PERMISSIONS'];
            $aTmp3 = array_diff($aTmp1,$aTmp2);
            if (count($aTmp3)){
                echo '<div class="content">';
                echo sprintf('%d Updating Administators system permission %s <br />',count($aTmp3), $FAIL);
                echo '</div>';
            }
        }
    }else {
        echo '<div class="content">';
        echo sprintf('Can\'t read update-group.sql.php %s <br />', $FAIL);
        echo '</div>';
    }

// update-sections.sql.php
$sInstallData = WB_PATH.'/install/update-sections.sql.php';
if (\is_readable($sInstallData)){
    echo '<h2>Step '.(++$stepID).' : Updating Enable/Disable Sections</h2>';
/***********************************  - Upgrade sections Table **************/
   if (!$database->SqlImport($sInstallData, TABLE_PREFIX, 'upgrade' )){
        echo '<div class="content">';
        echo \sprintf('%s updating toggle sections to active %s <br />',$database->get_error(), $FAIL);
        echo '</div>';
    } else {
        if ($bShowDetails) {
            echo '<div class="content">';
            echo \sprintf('Updating toggle sections to active %s <br />', $OK);
            echo '</div>';
        }
    }
\flush();

}
// --- modify table `settings` -----------------------------------------------------------
    echo '<h2>Step '.(++$stepID).' : Modify PRIMARY KEY in settings table and add missing entries</h2>';
    echo '<div class="content">';
    $msg = MigrateSettingsTable($database, TABLE_PREFIX, $aDefaultSettings);
    if ($bShowDetails){echo ($msg!='' ? $msg.' '.$FAIL : 'Upgrading table `settings` succesfully done '.$OK).'<br />';}
    echo '</div>';
\flush();

    echo '<h2>Step '.(++$stepID).' : Updating default_theme/default_template/default editor in settings table</h2>';
/**********************************************************
 *  - Adding field default_theme to settings table
 */
    echo '<div class="content">';
    $aCfg = [
    'default_theme' => $DEFAULT_THEME,
    'default_template' => (\defined('DEFAULT_TEMPLATE') && (DEFAULT_TEMPLATE!='') ? DEFAULT_TEMPLATE : $DEFAULT_TEMPLATE),
    'wysiwyg_editor'   => 'ckeditor',
    ];

    foreach($aCfg as $key=>$value) {
        db_add_key_value($key, $value);
    }
    echo '</div>';

    $check_tables = mysqlCheckTables( DB_NAME ) ;
\flush();

/**********************************************************
 *  - Adding field sec_anchor to settings table
 */
    echo '<h2>Step '.(++$stepID).' : Adding/Updating settings table</h2>';
    echo '<div class="content">';

    $cfg280 = array(
        'redirect_timer' => (\defined('REDIRECT_TIMER') ? REDIRECT_TIMER:'1000'),
    );
    $cfg = \array_merge($cfg280);

    $cfg283 = array(
        'sec_anchor' => (\defined('SEC_ANCHOR') ? SEC_ANCHOR : 'none'),
        'er_level' => (\defined('ER_LEVEL')&& empty(ER_LEVEL) ? '32767' : ER_LEVEL),
        'website_signature' => (\defined('WEBSITE_SIGNATURE') ? WEBSITE_SIGNATURE : ''),
    );
    $cfg = \array_merge($cfg,$cfg283);

    $cfg212 = array(
        'dsgvo_settings' => (\defined('DSGVO_SETTINGS') && !empty(DSGVO_SETTINGS) ? DSGVO_SETTINGS : 'a:3:{s:19:"use_data_protection";b:1;s:2:"DE";i:0;s:2:"EN";i:0;}'),
        'twig_version'  => (\defined('TWIG_VERSION') ? TWIG_VERSION : '1'),
        'groups_updated' => (\defined('GROUPS_UPDATED') ? GROUPS_UPDATED:''),
        'page_icon_dir' => (\defined('PAGE_ICON_DIR') ? PAGE_ICON_DIR : '/templates/*/title_images'),
        'system_locked' => (\defined('SYSTEM_LOCKED') ? SYSTEM_LOCKED : '0'),
        'string_dir_mode' => (\defined('STRING_DIR_MODE') ? STRING_DIR_MODE : '0755'),
        'string_file_mode' => (\defined('STRING_FILE_MODE')  ?STRING_FILE_MODE : '0644'),
        'user_login'    => (\defined('USER_LOGIN') ? USER_LOGIN : '1'),
        'confirmed_registration' => (\defined('CONFIRMED_REGISTRATION') ? CONFIRMED_REGISTRATION:'0'),
        'page_newstyle'       => (\defined('PAGE_NEWSTYLE') ? PAGE_NEWSTYLE : '1'),
        'page_oldstyle'       => (\defined('PAGE_OLDSTYLE') ? PAGE_OLDSTYLE : '0'),
        'website_signature'   => (\defined('WEBSITE_SIGNATURE') ? WEBSITE_SIGNATURE : ''),
        'jquery_version'      => (\defined('JQUERY_VERSION') ? JQUERY_VERSION : '1.12.4'),
        'show_start_datetime' => (\defined('SHOW_START_DATETIME') ? SHOW_START_DATETIME : 'false'),
    );
    $cfg = \array_merge($cfg,$cfg212);

    foreach($cfg as $key=>$value) {
        db_add_key_value($key, $value);
    }

/**********************************************************
 *  - Adding redirect timer to settings table
echo "<br />Adding redirect timer to settings table<br />";
 */

/**********************************************************
 *  - Adding rename_files_on_upload to settings table
echo "<br />Updating rename_files_on_upload to settings table<br />";
 */
    $cfg = array(
        'rename_files_on_upload' => (\defined(RENAME_FILES_ON_UPLOAD) ? RENAME_FILES_ON_UPLOAD : 'ph.*?,cgi,pl,pm,exe,com,bat,pif,cmd,src,asp,aspx,js')
    );
    db_add_key_value( 'rename_files_on_upload', $cfg['rename_files_on_upload']);

/**********************************************************
 *  - Adding mediasettings to settings table
echo "<br />Adding mediasettings, media_version and debug to settings table<br />";
 */
\flush();

$cfg = array(
    'debug' => (\defined('DEBUG')?DEBUG:'false'),
    'media_width'   => (\defined('MEDIA_WIDTH') ? MEDIA_WIDTH : '0'),
    'media_height'  => (\defined('MEDIA_HEIGHT') ? MEDIA_HEIGHT : '0'),
    'mediasettings' => (\defined('MEDIASETTINGS') ? MEDIASETTINGS : ''),
    'media_version' => (\defined('MEDIA_VERSION') ? MEDIA_VERSION : '1.0.0'),
);
foreach($cfg as $key=>$value) {
    db_add_key_value($key, $value);
}
if (!\defined('MEDIA_VERSION')){\define('MEDIA_VERSION', '1.0.0');}
/**********************************************************
 *  - Adding mediasettings to settings table   checkMediaVersion   version_compare(MEDIA_VERSION, VERSION, '<')
echo '<span class="header">Updating mediasettings to new format</span><br />';
 */
if ($bShowDetails){
}
    if (\version_compare(MEDIA_VERSION, VERSION, '=')){
        if ($bShowDetails){echo '<span> Mediasettings already updated '.$OK.'</span><br />'.PHP_EOL;}
    } else
    if (!\function_exists('updateMediaSettings') && \file_exists(ADMIN_PATH.'/media/updateMediaSettings.php')){
        require(ADMIN_PATH.'/media/updateMediaSettings.php');
        if (\defined('MEDIASETTINGS') && \trim(MEDIASETTINGS) != '') {
            echo '<span> Mediasettings updated '.(updateMediaSettings($database) ? $OK : $FAIL).'</span><br />'.PHP_EOL;
        } else {
            if ($bShowDetails){echo '<span>Mediasettings no updrade needed '.$OK .'</span><br />'.PHP_EOL;}
        }
    }
/**********************************************************
 *  - Set wysiwyg_editor to settings table
 */
//    db_update_key_value('settings', 'wysiwyg_editor', 'ckeditor');
//    'wysiwyg_editor' => (defined('WYSIWYG_EDITOR') && (WYSIWYG_EDITOR!='none') ? WYSIWYG_EDITOR : 'ckeditor'),

/**********************************************************
 *  - Adding fingerprint_with_ip_octets to settings table
echo "<br />Adding fingerprint_with_ip_octets to settings table<br />";
 */
$cfg = array(
    'sec_token_fingerprint' => (\defined('SEC_TOKEN_FINGERPRINT') ? SEC_TOKEN_FINGERPRINT : 'true'),
    'sec_token_netmask4'    => (\defined('SEC_TOKEN_NETMASK4') ? SEC_TOKEN_NETMASK4 : '24'),
    'sec_token_netmask6'    => (\defined('SEC_TOKEN_NETMASK6') ? SEC_TOKEN_NETMASK6 : '64'),
    'sec_token_life_time'   => (\defined('SEC_TOKEN_LIFE_TIME') ? SEC_TOKEN_LIFE_TIME : '180'),
    'wbmailer_smtp_port'    => (\defined('WBMAILER_SMTP_PORT') ? WBMAILER_SMTP_PORT : '25'),
    'wbmailer_smtp_secure'  => (\defined('WBMAILER_SMTP_SECURE') ? WBMAILER_SMTP_SECURE : 'TLS')
);
foreach($cfg as $key=>$value) {
    db_add_key_value($key, $value);
}
echo '</div>';
\flush();

/**********************************************************
 *  - Add field "redirect_type" to table "mod_menu_link" */
    echo '<h2>Step '.(++$stepID).' : Upgrading menu_link</h2>';
    echo '<div class="content">';
    if ($bShowDetails){echo "Adding field redirect_type to mod_menu_link table<br />";}
    db_add_field('mod_menu_link', 'redirect_type', "INT NOT NULL DEFAULT '301' AFTER `target_page_id`");
    echo '</div>';
\flush();

/**********************************************************
 *  - Update search no results database filed to create
 *  valid XHTML if search is empty
if (version_compare(WB_VERSION, '2.8', '<'))
{
    echo "<br />Updating database field `no_results` of search table: ";
    $search_no_results = addslashes('<tr><td><p>[TEXT_NO_RESULTS]</p></td></tr>');
    $sql  = 'UPDATE `'.TABLE_PREFIX.'search` ';
    $sql .= 'SET `value`=\''.$search_no_results.'\' ';
    $sql .= 'WHERE `name`=\'no_results\'';
    echo ($database->query($sql)) ? ' $OK<br />' : ' $FAIL<br />';
}
 */
/* *****************************************************************************
 * - check for deprecated / never needed files
 */
    if (is_readable('getRemoveList.php')){require 'getRemoveList.php';}
    if (\sizeof($aRemoveList['filesRemove'])) {
        echo '<h2>Step '.(++$stepID).': Remove deprecated and old files</h2>';
        echo '<div class="content">';
        $searches = [
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
            '[VENDOR]',
        ];
        $replacements = [
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
            '/vendor/',
        ];

        $aMsg = [];
        \array_walk(
            $aRemoveList['filesRemove'],
            function (& $sFile) use($searches, $replacements) {
                $sFile = \str_replace( '\\', '/', WB_PATH.\str_replace($searches, $replacements, $sFile) );
            }
        );
        $sWbPath = \str_replace('\\', '/', WB_PATH );
        $sCachePath = $sWbPath.'/temp/cache/';
        $sMask = $sCachePath.'*';
        $aFiles = \glob($sMask, GLOB_NOSORT);
        foreach ($aFiles as $sFile) {
            if (\is_writable($sFile) && is_file($sFile)) {
              \unlink($sFile);
            }
        }
        foreach ( $aRemoveList['filesRemove'] as $sFileToDelete ) {
            if (false !== ($aExistingFiles = \glob(\dirname($sFileToDelete).'/*')) ) {
                if (\in_array($sFileToDelete, $aExistingFiles)) {
                    if (\is_writable($sFileToDelete) && \unlink($sFileToDelete)) {
//                        $aMsg[] = sprintf('%s',str_replace($sWbPath, '',$sFileToDelete));
                        echo ''.\sprintf("<h6>Remove %s %s</h6>",\str_replace($sWbPath, '',$sFileToDelete),$OK).'';
                    } else {
                        $aMsg[] = \sprintf('%s',\str_replace($sWbPath, '',$sFileToDelete));
                    }
                }
            }
        }
        echo '</div>';
        unset($aExistingFiles);
        if (\sizeof($aMsg) )
        {
            $sFiles = \implode('<br />', $aMsg).'<br />';
            $msg = '<br /><br />The following files are deprecated, outdated or a security risk and
                    can not be removed automatically.<br /><br />Please delete them
                    using FTP and restart upgrade-script!<br /><br />'.$sFiles.'<br />';
            status_msg($msg, 'error warning', 'h3');
            echo '<p style="font-size:110%;"><strong>WARNING: The upgrade script failed ...</strong></p>';
            echo '<form action="'.$sScriptUrl.'">';
            echo '&nbsp;<input name="send" type="submit" value="Restart upgrade script" />';
            echo '</form>';
            echo '<br /><br /></div></body></html>';
\flush();
            exit;
        }
    }

/**********************************************************
 * - check for deprecated / never needed folder
 */
    if (\sizeof($aRemoveList['dirRemove'])) {
        echo '<h2>Step  '.(++$stepID).': Remove deprecated and old folders</h2>';
        echo '<div class="content">';
        $searches = [
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
            '[VENDOR]',
        ];
        $replacements = [
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
            '/vendor/',
        ];
        $aMsg = [];
        \array_walk(
            $aRemoveList['dirRemove'],
            function (& $sFile) use($searches, $replacements,$sWbPath) {
                $sFile = \str_replace( '\\', '/', WB_PATH.\str_replace($searches, $replacements, $sFile) );
            }
        );
        $sWbPath = \str_replace('\\', '/', WB_PATH );
        foreach( $aRemoveList['dirRemove'] as $item) {
            if (false !== ($aExistingFiles = \glob(\dirname($item).'/*', \GLOB_MARK|\GLOB_ONLYDIR)) ) {
                $item = \rtrim($item, '/').DIRECTORY_SEPARATOR;
                if (\in_array($item, $aExistingFiles)) {
                    if (\is_writable($item) && rm_full_dir($item)) {  //
                    // try to delete dir
                        echo \sprintf("<h6>Remove %s %s</h6>",\str_replace($sWbPath, '',$item),$OK).'';
                    } else {
                        // save in err-list, if failed
                        $aMsg[] = \sprintf('%s',\str_replace($sWbPath, '',$item));
                    }
                }
            }
        }
        echo '</div>';
\flush();
        if (\sizeof($aMsg)){
            $sFiles = implode('<br />', $aMsg).'<br />';
            $msg = '<br /><br />The following folder are deprecated or outdated and
                    can not be removed automatically.<br /><br />Please delete them
                    using FTP and restart upgrade-script!<br /><br />'.$sFiles.'<br />';
            status_msg($msg, 'error warning', 'h3');
            echo '<p style="font-size:110%;"><strong>WARNING: The upgrade script failed ...</strong></p>';
            echo '<form action="'.$sScriptUrl.'">';
            echo '&nbsp;<input name="send" type="submit" value="Restart upgrade script" />';
            echo '</form>';
            echo '<br /><br /></div></body></html>';
            exit;
        }
    }
    unset($aRemoveList);
/**********************************************************
 * upgrade admin addons if newer version is available
   find upgrade.php in all core admin folders and run;
 */

    echo '<h2>Step '.(++$stepID).' : Checking all core addons with a newer version (upgrade)</h2>';
    echo '<div class="content">';
    $aCoreDirPattern = [
        WB_PATH.'/admin/*',
        WB_PATH.'/framework/*',
        WB_PATH.'/include/*',
        WB_PATH.'/languages/*',
    ];
    foreach ($aCoreDirPattern as $sDirList){
        $aDirList = \glob($sDirList.'', \GLOB_ONLYDIR );
        $i = $upgradeID = 0;
        foreach($aDirList as $sCoreAbsPath)
        {
            $sCoreAddonName = \basename($sCoreAbsPath);
            $i++;
            if (\is_readable($sCoreAbsPath.'/upgrade.php')){
                include $sCoreAbsPath.'/upgrade.php';
                echo ''.\sprintf('<h6>[%02d] : Upgrade Core Addons %s  </h6>', (++$upgradeID), basename(dirname($sCoreAbsPath)).'/'.$sCoreAddonName).'';
            }
        }
        $aDirList = [];
    }
    unset($aCoreDirPattern);
    echo '</div>';
\flush();



/**********************************************************
 * upgrade modules if newer version is available
    $aModuleList = array_intersect($aModuleDirList, $aModuleWhiteList);
 */

    echo '<h2>Step '.(++$stepID).' : Checking all addons with a newer version (upgrade)</h2>';
    echo '<div class="content">';
    $aModuleDirList = \glob(WB_PATH.'/modules/*', \GLOB_ONLYDIR );
    $i = $upgradeID = 0;
#    $aModuleWhiteList = array_flip($aModuleWhiteList);
    foreach($aModuleDirList as $sModuleAbsPath)
    {
        $sModulName = \basename($sModuleAbsPath);
        $i++;
        if (\in_array($sModulName, $aModuleWhiteList) && \is_readable($sModuleAbsPath.'/upgrade.php'))
        {
            $currModulVersion = get_modul_version ($sModulName, false);
            $newModulVersion =  get_modul_version ($sModulName, true);
            if ((\version_compare($currModulVersion, $newModulVersion, $sAddonCompareFlag ))) {
                load_module($sModuleAbsPath, true);
                echo ''.\sprintf('<h6 style="color: #0382E3">[%02d] : Upgrade module %s from version %s to version %s %s</h6>', (++$upgradeID),$sModulName,$currModulVersion,$newModulVersion,$OK).'';
            } else {
                echo ''.\sprintf('<h6 style="color: #7B7B7B">[%02d] : Module %s  - Your current version is %s %s</h6>', (++$upgradeID),$sModulName,$currModulVersion,$OK).'';
            }
        } else {
            if ($bShowDetails && \is_readable($sModuleAbsPath.'/info.php')){
                echo ''.\sprintf('<h6>[%02d] : Unproofed Modules %s are not be upgrading automatically </h6>', (++$upgradeID),$sModulName).'';
            }
        }
    }
    unset($aModuleDirList);
    echo '</div>';
/**********************************************************
 *  - Reload all addons
 */
\flush();

    echo '<h2>Step '.(++$stepID).' : Checking all templates with a newer version (upgrade)</h2>';
    echo '<div class="content">';
    $aAddonDirList = \glob(WB_PATH.'/templates/*', \GLOB_ONLYDIR );
    $i = $upgradeID = 0;
//    $aModuleWhiteList = array_flip($aModuleWhiteList);
    foreach($aAddonDirList as $sAddonAbsPath)
    {
        $sAddonName = \basename($sAddonAbsPath);
        $i++;
        if (\is_readable($sAddonAbsPath.'/upgrade.php') && \is_readable($sAddonAbsPath.'/info.php'))
        {
            $currAddonVersion = get_modul_version ($sAddonName, false ,'template');
            $newAddonVersion =  get_modul_version ($sAddonName, true,'template');
            if ((\version_compare($currAddonVersion, $newAddonVersion, $sAddonCompareFlag ))) {
                load_template($sAddonAbsPath, true);
                echo ''.\sprintf('<h6 style="color: #0382E3">[%02d] : Upgrade template %s from version %s to version %s %s</h6>', (++$upgradeID),$sAddonName,$currAddonVersion,$newAddonVersion,$OK).'';
            } else {
                echo ''.\sprintf('<h6 style="color: #7B7B7B">[%02d] : Template %s  - Your current version is %s %s</h6>', (++$upgradeID),$sAddonName,$currAddonVersion,$OK).'';
            }
        } else {
            if ($bShowDetails && \is_readable($sAddonAbsPath.'/info.php')){
            echo ''.\sprintf('<h6>[%02d] : Unproofed templates %s are not be upgrading automatically </h6>', (++$upgradeID),$sAddonName).'';
            }
        }
    }
    echo '</div>';
\flush();

    echo '<h2>Step '.(++$stepID).' : Reload all addons database entry (no upgrade)</h2>';
    echo '<div class="content">';
    if ($bShowDetails){
        echo 'TRUNCATE addons table<br />';
    }
    ////truncate addons
       if (!$database->query('TRUNCATE `'.TABLE_PREFIX.'addons`')){}
/*
    $sql = 'DELETE FROM `'.TABLE_PREFIX.'addons` '
         . 'WHERE `type` = \'module\'';
    $database->query($sql);
*/
    // Load all languages
        if( ($handle = \opendir(WB_PATH.'/languages/')) ) {
            while(false !== ($file = \readdir($handle))) {
                if($file != '' && \substr($file, 0, 1) != '.' && $file != 'index.php') {
                    load_language(WB_PATH.'/languages/'.$file);
                }
            }
            \closedir($handle);
        }
    if ($bShowDetails){
    echo 'Languages reloaded<br />';
    }
    // Load all modules
    if( ($handle = \opendir(WB_PATH.'/modules/')) ) {
        while(false !== ($file = \readdir($handle))) {
            if($file != '' && \substr($file, 0, 1) != '.' && $file != 'admin.php' && $file != 'index.php') {
                load_module(WB_PATH.'/modules/'.$file );
            }
        }
        \closedir($handle);
    }
    if ($bShowDetails){
    echo 'Modules reloaded<br />';
    }
    ////delete templates
    //$database->query("DELETE FROM ".TABLE_PREFIX."addons WHERE type = 'template'");
    // Load all templates
    if( ($handle = \opendir(WB_PATH.'/templates/')) ) {
        while(false !== ($file = \readdir($handle))) {
            if($file != '' && substr($file, 0, 1) != '.' && $file != 'index.php') {
                load_template(WB_PATH.'/templates/'.$file);
            }
        }
        \closedir($handle);
    }
    if ($bShowDetails){
    echo 'Templates reloaded<br />';
    }
    ////delete languages
    //$database->query("DELETE FROM ".TABLE_PREFIX."addons WHERE type = 'language'");
/**********************************************************
 *  - End of upgrade script
 */

// require(WB_PATH.'/framework/initialize.php');

    if(!\defined('DEFAULT_THEME')) {\define('DEFAULT_THEME', $DEFAULT_THEME); }
    if(!\defined('THEME_PATH')) {\define('THEME_PATH', WB_PATH.'/templates/'.DEFAULT_THEME);}
    if(!\defined('THEME_URL')) {\define('THEME_URL', WB_URL.'/templates/'.DEFAULT_THEME);}

    if(!\defined('DEFAULT_TEMPLATE')) {\define('DEFAULT_TEMPLATE', $DEFAULT_TEMPLATE); }
    if(!\defined('TEMPLATE_PATH')) {\define('TEMPLATE_PATH', WB_PATH.'/templates/'.DEFAULT_TEMPLATE);}
    if(!\defined('TEMPLATE_DIR')) {\define('TEMPLATE_DIR', WB_URL.'/templates/'.DEFAULT_TEMPLATE);}
/**********************************************************
 *  - Set Version to new Version
 */
\flush();
?>
    </div>
    <h2>Step <?=(++$stepID);?> :
    Update WebsiteBaker version number to <?= VERSION.' '.SP;?> . Revision/Patch [<?=REVISION;?>]
<?php

?>
    </h2>
<?php

    $admin_revision  = REVISION;
    $admin_timestamp = 0;
    // both files exists
    $sUpdateFile = ADMIN_PATH.'/interface/update';
    if (\is_readable($sUpdateFile) && \is_writable($sUpdateFile.'.fixed')){
        if (!\rename($sUpdateFile, $sUpdateFile.'.fixed')){
            echo '<div class="content">';
            echo \sprintf('renaming of %s %s <br />',\basename($sUpdateFile), $FAIL);
            echo '</div>';
        }
    }
    include $sUpdateFile.'.fixed';

    // echo ($database->query("UPDATE `".TABLE_PREFIX."settings` SET `value`='".VERSION."' WHERE `name` = 'wb_version'")) ? " $OK<br />" : " $FAIL<br />";
    db_update_key_value('settings', 'wb_version', VERSION);
    db_update_key_value('settings', 'wb_revision', REVISION);
    db_update_key_value('settings', 'wb_sp', SP);
    db_update_key_value('settings', 'patch_revision', $admin_revision);
    db_update_key_value('settings', 'patch_update', $admin_timestamp);

    $sInstallData = WB_PATH.'/install/update-sections.sql.php';
    $sNewInstallData = $sInstallData.'.001';
    if (\is_readable($sInstallData)){
        if (!\rename($sInstallData, $sNewInstallData)){
            echo '<div class="content">';
            echo \sprintf('renaming of %s %s <br />',\basename($sNewInstallData), $FAIL);
            echo '</div>';
       }
    }

    $fExecutiontime = StopWatch::stop();
    status_msg('Congratulations: The upgrade script is finished .. Executiontime.'.$fExecutiontime.' sec', 'info', 'h3');
//                <input class="w3-btn w3-blue-hover-wb" name="send" type="submit" value="Start upgrade script" />
    // show buttons to go to the backend or frontend
//    echo '<br />';
    $sActionUrl = $oReg->AppUrl.'install/upgrade-script.php';
    $aFtan = SecureTokens::getFTAN();
    if (\defined('WB_URL')) {
        $sActionFrontendUrl = $oReg->AppUrl.'index.php';
        $sInputFrontendText = 'kick me to the Frontend';
    }
    if (\defined('ADMIN_URL')) {
        $sActionBackendUrl = $oReg->AcpUrl.'logout/index.php';
        $sInputBackendText = 'kick me to the Backend';
    }
//    $aMatches = [];
    $sOldId = null;
    $sPattern = "/^.*?sess.*$/i";
    if (!preg_match ($sPattern, $oReg->AppSid)){
        // create Session ID
        $database = $oReg->getDatabase();
        $sOldId = $oReg->AppSid;
        $sNewId = 'PHPSESSID-wb-'.(\bin\SecureTokens::getUniqueFreeToken(6));
        $cfg = ['app_name', $sNewId];
        $database->replace('settings', ['name', 'value'], $cfg);
    }
?>
        <div class="w3-row-padding w3-margin-bottom">
          <div class="w3-quarter w3-container">
          <form action="<?= $sActionBackendUrl;?>" method="post" target="_blank" >
              <input type="hidden" name="old_id" value="<?= $sOldId;?>" />
              <input class="w3-btn w3-blue-hover-wb" name="frontend" type="submit" value="<?= $sInputFrontendText;?>" />
          </form>
          </div>

          <div class="w3-quarter w3-container" >
          <form action="<?= $sActionBackendUrl;?>" method="post" >
              <input type="hidden" name="old_id" value="<?= $sOldId;?>" />
              <input class="w3-btn w3-blue-hover-wb" name="backend" type="submit" value="<?= $sInputBackendText;?>" />
          </form>
          </div>
          <div class="w3-half">&nbsp;</div>
        </div>
    </div>

  </body>
</html>
