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
 * Description of index.php
 *
 * @package      Installer
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 152 2018-10-09 15:19:47Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */

    $sMinPhpVersion = '7.4';
    if (\version_compare((string) (PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION), $sMinPhpVersion, '<')) {
        $sMsg = '<h3 style="color: #ff0000;">'
              . 'WebsiteBaker is not able to run with PHP-Version less then '.$sMinPhpVersion.'!!<br />'
              . 'Please change your PHP-Version to any kind from '.$sMinPhpVersion.' and up!<br />'
              . 'If you have problems to solve that, ask your hosting provider for it.<br  />'
              . 'The very best solution is the use of PHP-'.$sMinPhpVersion.' and up</h3>';
        die($sMsg);
    }

    $sAddonName     = \basename(__DIR__);
    $sScriptPath    = dirname($_SERVER["SCRIPT_FILENAME"]).'/';
    $sAddonPath     = $sScriptPath.'';
    $sAppDir        = str_replace('\\', '/',__DIR__).'/';
    $iSharedHosting = (\strcmp($sScriptPath,$sAppDir));
    $sDocRoot       = \str_replace('\\','/',realpath($_SERVER["DOCUMENT_ROOT"]));
    $sScriptName    = \str_replace('\\','/',realpath($_SERVER["SCRIPT_FILENAME"]));
    $sLink          = \str_replace('\\','/',__DIR__).'/';
    $sAppRel        = \str_replace($sDocRoot,'',\dirname(\dirname($sScriptName))).'/';
    $sAppRel        = (($iSharedHosting!==0) ? '' : $sAppRel);
    $sAcpRel        = $sAppRel.'admin/';
    $sAppRel        = rtrim((empty($sAppRel) ? '' : $sAppRel),'/').'/';
    $sPathPattern   = "/^(.*?\/)install\/.*$/";
    $sAppPath       = \preg_replace ($sPathPattern, "$1", $sLink, 1 );
    $sOldPath     = \str_replace('\\','/',\getcwd()).'/';
/* */
    $sProtokol = ((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off' ) ? 'http' : 'https') . '://';
    $sPort = (\in_array((int) $_SERVER["SERVER_PORT"], [80, 443]) ? '' : ':'.$_SERVER["SERVER_PORT"]);
    $sHostname = \str_replace($sPort, '',$_SERVER["HTTP_HOST"]);
    $sUrl = $sProtokol.$sHostname.$sPort.$sAppRel;// end new routine
//    $sScriptPath = \str_replace(DIRECTORY_SEPARATOR, '/', ($_SERVER["SCRIPT_FILENAME"]));
    $sScriptUrl = $sUrl.''.\str_replace($sAppPath, '', $sScriptPath);
    $sScriptUrl = \str_replace('\\','/',$sScriptUrl).'';
    $sAppUrl    = (isset($_SESSION['wb_url']) ? $_SESSION['wb_url'] : $sProtokol.$sHostname.$sPort.$sAppRel);
    $sAcpUrl    = $sAppUrl.'admin/';
    $aTestLinks = [
      'Protokol' => $sProtokol,
      'Port' => $sPort,
      'Hostname' => $sHostname,
      '$sAddonName' => $sAddonName,
      //'DOCUMENT_ROOT' => $_SERVER["DOCUMENT_ROOT"],
      //'SCRIPT_FILENAME' => $_SERVER["SCRIPT_FILENAME"],
      'realpath(SCRIPT_FILENAME)' => $sScriptName,
      'realpath(DOCUMENT_ROOT)' => $sDocRoot,
      'dirname(SCRIPT_FILENAME)' => $sScriptPath,
      '__DIR__' => \str_replace('\\','/',__DIR__),
      '$sAppDir' => $sAppDir,
      '$iSharedHosting' => (int)$iSharedHosting,
      '$sAppPath' => $sAppPath,
      '$sAcpRel' => $sAcpRel,
      '$sAppRel' => $sAppRel,
      '$sUrl' => $sUrl,
      '$sAppUrl' => $sAppUrl,
      '$sAcpUrl' => $sAcpUrl,
     '$sScriptUrl' => $sScriptUrl,
    ];
/*
print '<pre  class="mod-pre" style="margin-left:30px;">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> '."\n";
print_r( $aTestLinks ); print '</pre>'; \flush (); //  sleep(10); die();
*/
    $args = [
        'overwrite'   => [
                            'filter'   => FILTER_VALIDATE_BOOLEAN,
                            'flags'    => FILTER_REQUIRE_SCALAR,
                            ],
        'restart'    => [
                            'filter'   => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                            'flags'    => FILTER_REQUIRE_SCALAR,
                           ],

    ];

    $aInputs = filter_input_array(INPUT_POST, $args);

    $sAction    = 'install';
    $sAction    = ($aInputs['restart'] ?? $sAction);
    $bOverwrite = ($aInputs['overwrite'] ?? null);

    if (($sAction == 'restart') && $bOverwrite){
        $sFile = $sAppPath.'config.php';
        for ($i=0; $i<=10; $i++){
            $sNewFile = $sAppPath.sprintf('config.%03d.php',$i);
            if (!is_readable($sNewFile)){break;}
        }
        rename($sFile,$sNewFile);
    }

    $bLocalDebug  = is_readable($sAddonPath.'.setDebug');
    // Only for development prevent secure token check,
    $bSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
/**
 * create a new 4-digit secure token
 * @return string
 */
    function getNewToken()
    {
        $aToBase = \str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $iToBaseLen = \sizeof($aToBase);
        \shuffle($aToBase);
        $iNumber = \rand($iToBaseLen**3, ($iToBaseLen**4)-1);
        $sRetval = '';
        while ($iNumber != 0) {
            $sRetval = $aToBase[($iNumber % $iToBaseLen)].$sRetval;
            $iNumber = \intval($iNumber / $iToBaseLen);
        }
        return $sRetval;
    }

    function make_dir($sRelPath, $dir_mode = 493, $recursive=true){
        $sAbsPath = dirname(__DIR__).DIRECTORY_SEPARATOR.$sRelPath;
        $bRetval = is_dir($sAbsPath);
        if (!$bRetval)
        {
            $bRetval = mkdir($sAbsPath, $dir_mode,$recursive);
        }
        return $bRetval;
    }

    function showVardump($mValue,$iLine,$mFunction){
        $sAddonPath = str_replace('\\', '/',__DIR__).'/';
        if (is_readable($sAddonPath.'.setDebug')){
            $sDomain = \basename(__DIR__).'/'.\basename(__FILE__);
            ob_start();
            $sHeadLine = nl2br(sprintf("function: <span>%s</span> (%s) Filename: <span>%s</span> Line %d\n",(!empty($mFunction) ? $mFunction : 'global'),'myVar',$sDomain,$iLine));
            echo '<div class="w3-margin w3-pre"><pre>'.$sHeadLine;
            \print_r( $mValue ); echo "</pre>\n</div>"; \flush (); // htmlspecialchars() ob_flush();;sleep(10); die();
            return ob_get_clean();
        }
    }
// ---------------------------------------------------------------------------------------
// check start requirements --------------------------------------------------------------
// ---------------------------------------------------------------------------------------
    $sMsg = ((isset($_SESSION['message']) && !empty($_SESSION['message'])) ? $_SESSION['message'] : '').PHP_EOL;

    $config_content = "<?php\n";
    $sCfgFile = $sAppPath.'config.php';
    if (!\is_readable($sCfgFile)) {
      if (!file_put_contents($sCfgFile, $config_content)) {
        $sMsg .= 'There is no \'config.php\' available. Please create an empty \'config.php\' !!'.PHP_EOL;
      }
    } else {
      if (!\is_writeable($sCfgFile)) {
            $sMsg .= 'Sorry, \'config.php\' is not writable! Please change the file mode!'.PHP_EOL;
        }
    }
    if (\is_readable($sCfgFile) && \filesize($sCfgFile) > 64) {
        $sMsg .= 'WebsiteBaker seems to be already installed. Access denied!'."\n";
        $sMsg .= '<b>WARNING!!</b> '."\n";
        $sMsg .= 'Rename or delete your config.php and pages folder then click on <b>Restart Wizard</b>';
        $sMsg .= ' and installer will be start from beginning'."\n";
        $sMsg .= 'or clicking <b>Go To Backend</b> will be jump to Backend Login';
    }
    $sMsg = trim($sMsg);

    if (!empty($sMsg)) {
    // show error message and exit -------------------------------------------------------
?><!DOCTYPE HTML>
<html lang="en">
    <head>
      <meta charset="utf-8" />
      <title>WebsiteBaker Installation Wizard</title>

      <link rel="stylesheet" href="<?= $sAppUrl; ?>include/assets/w3-css/w3.css" type="text/css" />
      <link rel="stylesheet" href="<?= $sAppUrl; ?>include/assets/w3-themes/w3-theme-wb-install.css" type="text/css" />
      <link rel="stylesheet" href="stylesheet.css" type="text/css" />
      <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<style type="text/css">
.w3-container{padding: 0.03em 6px !important;}
</style>

    </head>
    <body >
        <div class="body">
            <form id="website_baker_installation_wizard" action="#" method="post">
<?php
    // remove session cookie  'wb-installer'
        // delete the session itself
        if (session_status() === \PHP_SESSION_ACTIVE ) {
            session_unset();
            session_destroy();
        }
?>

                <table>
                    <tbody>
                        <tr style="background-color: #a9c9ea;">
                            <td>
                                <img src="wbLogo.svg" alt="" style="margin-top: -20px;" />
                            </td>
                            <td>
                                <h1 style="border:none; margin:auto;font-size:200%;text-align: center;">Installation Wizard</h1>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="welcome">
                    Welcome to the WebsiteBaker Installation Wizard.
                </div>
                <div><?= showVardump($aTestLinks,__LINE__,__FUNCTION__);?></div>
                <div class="w3-panel w3-pale-red w3-leftbar w3-border w3-border-red w3-padding">
                    <b>Error:</b><br/><?php echo nl2br($sMsg);?>
                </div>
                <div style="padding: 0.525em; margin: 10px auto; text-align:center;">
                        <button class="w3-btn w3-btn-default w3-blue w3-hover-green w3-medium" type="submit" name="restart" value="restart" ><span class="w3--padding w3-xlarge">&nbsp;</span><span style="vertical-align: text-bottom;">Restart Wizard</span>&nbsp;</button>
                        <button class="w3-btn w3-btn-default w3-blue w3-hover-green w3-medium" formaction="<?= $sAcpUrl;?>login/index.php" ><span class="w3-padding w3-xlarge">&#x2699;</span><span style="vertical-align: text-bottom;">Go to Backend</span></button>
                </div>
            </form>
        </div>
        <div style="font-size: 0.8em; margin: 0 0 3em; padding: 0; text-align:center;">
            <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
            <a href="https://websitebaker.org/" target="_blank" rel="noopener" style="color: #000000;">WebsiteBaker</a>
            is released under the
            <a href="https://www.gnu.org/licenses/gpl.html" target="_blank" rel="noopener" style="color: #000000;">GNU General Public License</a>
            <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
        </div >
    </body>
</html>
<?php
        exit;
    }
    unset($sMsg, $sCfgFile);
// ---------------------------------------------------------------------------------------
    if (!\defined('SESSION_STARTED')) {
    // remove session cookie  'wb-installer'
        // delete the session itself
        if (session_status() === \PHP_SESSION_ACTIVE ) {
            session_unset();
            session_destroy();
        }

        // To delete whole session
        $is_secure = ((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off' ) ? false : true);
        if (ini_get("session.use_cookies") && !empty($_COOKIE)) {
            foreach ($_COOKIE as $key => $value)
            {
            $params = session_get_cookie_params();
                setcookie(session_name($key), '', time() - 42000, $params["path"],
                    $params["domain"], $params["secure"], $params["httponly"]
                );
            }
        }
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
/**
 * highlight input fields which contain wrong/missing data
 * @param string $field_name
 * @return string
 */
    function field_error($field_name='') {
        $sRetval = '';
        if (\defined('SESSION_STARTED') || !empty($field_name)){
            if (isset($_SESSION['ERROR_FIELD']) && ($_SESSION['ERROR_FIELD'] == $field_name)) {
                $sRetval = ' class="wrong" autofocus ';
            }
        }
        return $sRetval;
    }

    $mod_path = (str_replace(DIRECTORY_SEPARATOR, '/', __DIR__));
    $doc_root = \str_replace(DIRECTORY_SEPARATOR,'/',\rtrim(\realpath($_SERVER['DOCUMENT_ROOT']),'/'));
    $mod_name = \basename($mod_path);
    $wb_path = \str_replace(DIRECTORY_SEPARATOR,'/',(\dirname(\realpath( __DIR__))));

    if (!\defined('WB_PATH')) { \define('WB_PATH', $wb_path); }
    if (!\defined('SYSTEM_RUN')) {\define('SYSTEM_RUN', true); }
    $wb_root = \str_replace($doc_root,'',$wb_path);
/*
// begin new routine
    $sInstallFolderRel = str_replace($doc_root,'',\dirname(\dirname($_SERVER["SCRIPT_FILENAME"])));
    $sProtokol = ((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off' ) ? 'http' : 'https') . '://';
    $sPort = (\in_array((int) $_SERVER["SERVER_PORT"], [80, 443]) ? '' : ':'.$_SERVER["SERVER_PORT"]);
    $sHostname = \str_replace($sPort, '',$_SERVER["HTTP_HOST"]);
    $sUrl = $sProtokol.$sHostname.$sPort.$sInstallFolderRel;// end new routine
    $sScriptPath = \str_replace(DIRECTORY_SEPARATOR, '/', ($_SERVER["SCRIPT_FILENAME"]));
    $sScriptUrl = $sUrl.\str_replace($wb_path, '', $sScriptPath);
    $sAppUrl    = (isset($_SESSION['wb_url']) ? $_SESSION['wb_url'] : $sUrl);
*/
    $installFlag = true;
// Check if the page has been reloaded
    //$bSessioncheck = (isset($_GET['sessions_checked']));
    $sSessioncheck = filter_input(INPUT_GET, 'sessions_checked', FILTER_SANITIZE_SPECIAL_CHARS,['options'=>['default'=>null]]);
    if (!isset($sSessioncheck)) {
        // Set session variable
        $_SESSION['session_support'] = '<span class="good">Enabled</span>';
        // Reload page
        \header('Location: index.php?sessions_checked=true');
        exit(0);
    } else {
        // Check if session variable has been saved after reload
        if (isset($_SESSION['session_support'])) {
            $session_support = $_SESSION['session_support'];
            if (isset($_SESSION['wb_url']) && ($_SESSION['wb_url'] != $sUrl)){$_SESSION['wb_url']=$sUrl;}
        } else {
            $installFlag = false;
            $session_support = '<span class="bad">Disabled</span>';
        }
    }
// create security tokens
    $aToken = [getNewToken(), getNewToken()];
    $_SESSION['token'] = [
        'name' => $aToken[0],
        'value' => $aToken[1],
        'expire' => \time() + 900
    ];
// Check if AddDefaultCharset is set
    $e_adc=false;
    $sapi = \php_sapi_name();
    if(\strpos($sapi, 'apache')!==FALSE || \strpos($sapi, 'nsapi')!==FALSE) {
        \flush();
        $apache_rheaders = \apache_response_headers();
        foreach($apache_rheaders AS $h) {
            if(\strpos($h, 'html; charset')!==FALSE) {
               \preg_match('/charset\s*=\s*([a-zA-Z0-9- _]+)/', $h, $match);
                $apache_charset=$match[1];
                $e_adc=$apache_charset;
            }
        }
    }

?><!DOCTYPE HTML>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <title>WebsiteBaker Installation Wizard</title>

    <link rel="stylesheet" href="<?= $sAppUrl; ?>include/assets/w3-css/w3.css" media="screen" />
    <link rel="stylesheet" href="<?= $sAppUrl; ?>include/assets/w3-themes/w3-theme-wb-install.css" type="text/css" />
    <link rel="stylesheet" href="<?= $sAppUrl; ?>include/assets/css/fontawesome.min.css" media="screen" />
    <link rel="stylesheet" href="stylesheet.css" media="screen" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<style type="text/css">
.w3-container{padding: 0.03em 6px !important;}
</style>

</head>
<body>
<div class="body">
    <table>
        <tbody>
            <tr style="background: #a9c9ea;">
                <td >
                    <img src="wbLogo.svg" alt="" style="margin-top: -20px;" />
                </td>
                <td>
                    <h1 style="border:none; margin:auto;font-size:200%;text-align: center;">Installation Wizard</h1>
                </td>
            </tr>
        </tbody>
    </table>

<form id="website_baker_installation_wizard" action="save.php" method="post" autocomplete="off">
    <input type="hidden" name="url" value="" />
    <input type="hidden" name="username_fieldname" value="admin_username" />
    <input type="hidden" name="password_fieldname" value="admin_password" />
    <input type="hidden" name="remember" id="remember" value="true" />
    <input type="hidden" name="ERROR_FIELD" id="ERROR_FIELD" value="" />
    <input type="hidden" name="<?php echo $aToken[0]; ?>" value="<?php echo $aToken[1]; ?>" />
    <div class="welcome">
        Welcome to the WebsiteBaker Installation Wizard.
    </div>
    <div><?= showVardump($aTestLinks,__LINE__,__FUNCTION__);?></div>

<?php if (isset($_SESSION['message']) && $_SESSION['message'] != '') { ?>
    <div class="error w3-panel w3-pale-red w3-leftbar w3-border-red" style="line-height: 2.9;">
        <p><b>Error:</b> <?php echo $_SESSION['message']; ?></p>
    </div>
<?php } ?>

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom w3-medium">
            <div class="w3-row w3-section w3-theme w3-container">
                <h1 class="step-row w3-xlarge">Step 1</h1>
                    <p class="w3-large">Please check the following requirements are met before continuing...</p>
            </div>
            <div class="w3-hide">
<?php if ($session_support != '<span class="good">Enabled</span>') { ?>
                <div class="w3-row">
                    <div class="w3-container w3-cell">
                    <div class="w3-panel w3-leftbar w3-sand w3-large w3-serif" style="line-height: 2.9;">
                    <p><i>"Please note: PHP Session Support may appear disabled if your browser does not support cookies."</i></p>
                    </div>
                    </div>
                </div>
<?php } ?>
                <div class="w3-row">
                    <div class="w3-container w3-cell" style="color: #666666;width: 30%;">PHP Version >= 7.4.0</div>
                    <div class="w3-container w3-cell" style="width: 20%;">
<?php
               if (version_compare(PHP_VERSION, '7.4.0', '>='))
               {
?>
                        <span class="good"><?php echo PHP_VERSION;?></span>
<?php
                } else {
                    $installFlag = false;
?>
                        <span class="bad"><?php echo PHP_VERSION;?></span>
<?php }?>
                    </div>
                    <div class="w3-container w3-cell" style="color: #666666;width: 25%;">PHP Session Support</div>
                    <div class="w3-container w3-cell" style="width: 15%;"><?php echo $session_support; ?></div>
                </div>
                <div class="w3-row">
                    <div class="w3-container w3-cell" style="color: #666666;width: 25%;">Server Default Charset</div>
                    <div class="w3-container w3-cell" style="width: 15%;">
<?php
            $chrval = (($e_adc != '') && (strtolower($e_adc) != 'utf-8') ? true : false);
            if($chrval == false) {
?>
                        <span class="good"><?php echo (($e_adc=='') ? 'OK' : $e_adc) ?></span>
<?php
                    } else {
?>
                        <span class="bad"><?php echo $e_adc ?></span><?php
                    }
?>
                    </div>
                    <div class="w3-container w3-cell w3-steel" style="color: #666666;width: 23%;">PHP Safe Mode</div>
                    <div class="w3-container w3-cell w3-lime" style="width: 15%;">
<?php
                if(ini_get('safe_mode')=='' || strpos(strtolower(ini_get('safe_mode')), 'off')!==FALSE || ini_get('safe_mode')==0) {
?>
                      <span class="good">Disabled</span>
<?php
                } else {
                    $installFlag = false;
?>
                      <span class="bad">Enabled</span>
<?php
                }
?>
                    </div>
                </div>
                <div class="w3-row">
                    <div class="w3-container w3-col">&nbsp;</div>
                </div>
            </div>

            <table class="">
                <tbody>
<?php if ($session_support != '<span class="good">Enabled</span>') { ?>
                  <tr>
                      <td colspan="6">
                      <div class="w3-panel w3-leftbar w3-sand w3-large w3-serif" style="line-height: 2.9;">
                      <p><i>"Please note: PHP Session Support may appear disabled if your browser does not support cookies."</i></p>
                      </div>
                      </td>
                  </tr>
<?php } ?>
                  <tr>
                      <td style="color: #666666;">PHP Version >= 7.4.0</td>
                      <td>
<?php
               if (version_compare(PHP_VERSION, '7.4.0', '>='))
               {
?>
                        <span class="good"><?php echo PHP_VERSION;?></span>
<?php
                } else {
                    $installFlag = false;
?>
                        <span class="bad"><?php echo PHP_VERSION;?></span>
<?php } ?>
                      </td>
                      <td style="color: #666666;">PHP Session Support</td>
                      <td><?php echo $session_support; ?></td>
                  </tr>
                  <tr>
                      <td style="color: #666666;">Server Default Charset</td>
            <td>
<?php
                    $chrval = (($e_adc != '') && (strtolower($e_adc) != 'utf-8') ? true : false);
                    if($chrval == false) {
?>
                          <span class="good"><?php echo (($e_adc=='') ? 'OK' : $e_adc) ?></span>
<?php
                    } else {
?>
                          <span class="bad"><?php echo $e_adc ?></span><?php
                    }
?>
                      </td>
                      <td style="color: #666666;">PHP Safe Mode</td>
                      <td>
<?php
                if (ini_get('safe_mode')=='' || strpos(strtolower(ini_get('safe_mode')), 'off')!==FALSE || ini_get('safe_mode')==0) {
?>
                          <span class="good">Disabled</span><?php
                } else {
                    $installFlag = false;
?>
                          <span class="bad">Enabled</span>
<?php } ?>
                      </td>
                  </tr>
        <?php if ($chrval == true) { ?>
                  <tr>
                      <td colspan="6" style="font-size: 10px;" class="w3-medium">
                          <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
                          <p class="">
                          <b>Please note:</b> Your webserver is configured to deliver <?php echo $e_adc;?></b> charset only.
                          To display national special characters (e.g.: ä á) in a clear manner, please switch this preset off (or request this change from your hosting provider).
                          In any case you can choose <b><?php echo $e_adc;?></b> in the settings of WebsiteBaker.
                          But this solution does not guaranty that content from all modules will display correctly!
                          </p>
                          </div>
                      </td>
                  </tr>
<?php } ?>
                </tbody>
            </table>
            <div class="w3-row">
                <div class="w3-container w3-col">&nbsp;</div>
            </div>
        </div><!-- end of card step1  -->

<?php
    $config = '<span class="good">Writable</span>';
    $config_content = "<?php\n";
    $configFile = '/config.php';
/* to set config.php Messages */
    if (is_writeable($wb_path.$configFile)){
        //unlink($wb_path.$configFile);
        if (isset($_SESSION['config_exists'])) {
            unset($_SESSION['config_exists']);
        }
    }

    if (!isset($_SESSION['config_exists']))
    {
// config.php or config.php.new
        if ((is_readable($wb_path.$configFile)===true)) {
// next operation only if file is writeable
            if (is_writeable($wb_path.$configFile)) {
// already installed? it's not empty
                if (filesize($wb_path.$configFile) > 64) {
                    $installFlag = false;
                    $config = '<span class="bad">Not empty! WebsiteBaker already installed?</span>';
// try to open and to write
                } elseif(!$handle = fopen($wb_path.$configFile, 'w')) {
                    $installFlag = false;
                    $config = '<span class="bad">Not Writable</span>';
                } else {
                    if (fwrite($handle, $config_content) === FALSE) {
                        $installFlag = false;
                        $config = '<span class="bad">Not Writable</span>';
                    } else {
                        $config = '<span class="good">Writable</span>';
                        $_SESSION['config_exists'] = true;
                    }
                    // Close file
                    fclose($handle);
                    }
            } else {
                $installFlag = false;
                $config = '<span class="bad">Not Writable</span>';
            }
        } else
        {
            $installFlag = false;
            $config = '<span class="bad">There is no \'config.php\' available. Please create an empty \'config.php\'</span>';
        }
    }
?>

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom w3-medium">
            <div class="w3-row w3-section w3-theme w3-container">
            <h1 class="step-row w3-xlarge">Step 2</h1>
            <p class="w3-large _header">Please check the following files/folders are writable before continuing...</p>
            </div>

        <div class="w3-row w3-medium">
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root.$configFile ?></div>
            <div class="w3-container w3-col" style="width: 80%;"><?php  echo $config;?></div>
        </div>

        <div class="w3-row w3-medium">
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/pages/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('pages') && is_writable('../pages/')) { echo '<span class="good">Writable</span>'; } elseif (!file_exists('../pages/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/media/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if(make_dir('media') && is_writable('../media/')) { echo '<span class="good">Writable</span>'; } elseif (!file_exists('../media/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/templates/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('templates') && is_writable('../templates/')) { echo '<span class="good">Writable</span>'; } else if(!file_exists('../templates/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
        </div>
        <div class="w3-row w3-medium">
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/modules/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('modules') && is_writable('../modules/')) { echo '<span class="good">Writable</span>'; } else if(!file_exists('../modules/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/languages/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('languages') && is_writable('../languages/')) { echo '<span class="good">Writable</span>'; } else if(!file_exists('../languages/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/temp/</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('temp') && is_writable('../temp/')) { echo '<span class="good">Writable</span>'; } else if(!file_exists('../temp/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
        </div>
        <div class="w3-row w3-medium">
            <div class="w3-container w3-col" style="color: #666666;width: 19%;"><?php print $wb_root ?>/var/log</div>
            <div class="w3-container w3-col" style="width: 10%;"><?php if (make_dir('var/log') && is_writable('../var/')) { echo '<span class="good">Writable</span>'; } else if(!file_exists('../languages/')) {$installFlag = false; echo '<span class="bad">Directory Not Found</span>'; } else { echo '<span class="bad">Unwritable</span>'; } ?></div>
            <div class="w3-container w3-col" style="width: 10%;">&nbsp;</div>
        </div>
        <div class="w3-row">
            <div class="w3-container w3-col">&nbsp;</div>
        </div>

        </div><!-- end of card step2  -->

<?php  if ($installFlag == true) { ?>

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom w3-medium">
            <div class="w3-row w3-section w3-theme w3-container">
                <h1 class="step-row w3-xlarge">Step 3</h1>
                <p class="w3-large _header">Please check URL settings, select a default timezone and default backend language...</p>
            </div>
            <div style="padding: 0.2em 0 1.2em;">
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Absolute URL:</div>
                <div class="w3-half value">
                    <input  <?php echo field_error('wb_url');?> class="w3-padding-small" type="text" tabindex="1" name="wb_url" style="width: 99%;" value="<?php echo $sAppUrl; ?>" />
                </div>
            </div>
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Default Timezone:</div>
                <div class="w3-half value">
                    <input type="hidden" name="default_timezone" value="0" />
                    <button type="button" tabindex="2"  tabindex="2" class="w3-input w3-padding-small" >UTC</button>
                </div>
            </div>
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Default Language:</div>
                <div class="w3-half value">
<?php
/* */
    $sLinuxSelected   = ((isset($_SESSION['operating_system']) && $_SESSION['operating_system'] == 'linux') ? ' checked="checked"' : '');
    $sWindowsSelected = ((isset($_SESSION['operating_system']) && $_SESSION['operating_system'] == 'windows') ? ' checked="checked"' : '');
    $sDefaultSelected = ((empty($sLinuxSelected) && empty($sWindowsSelected)) ? ' checked="checked"' : '');
    $sDatabaseHost    = (isset($_SESSION['database_host']) ? $_SESSION['database_host'] : 'localhost');
    $sDatabaseName    = (isset($_SESSION['database_name']) ? $_SESSION['database_name'] : '');
    $sTablePrefix     = (isset($_SESSION['table_prefix']) ? $_SESSION['table_prefix'] : '');

/*
 Find all available languages in /language/ folder and build option list from
*/
// -----
    $getLanguage = function($sFile) {
        $aRetval = null;
        $language_code = $language_name = '';
        include $sFile;
        if ($language_code && $language_name) {
            $aRetval = ['code' => $language_code, 'name' => $language_name];
        }
        return $aRetval;
    };
// -----
    $aMatches = [];
    $sDefaultLang = (isset($_SESSION['default_language']) ? $_SESSION['default_language'] : 'EN');
    $sLangDir = str_replace('\\', '/', dirname(__DIR__).'/languages/');
    foreach(glob($sLangDir.'*.php') as $sFilename) {
        if (preg_match('/[A-Z]{2}\.php$/s', basename($sFilename)) && is_readable($sFilename)) {
            if (!($aMatch = $getLanguage($sFilename))) {continue;}
            $aMatch['status'] = ($aMatch['code'] == $sDefaultLang);
            $aMatches[] = $aMatch;
        }
    }
// create HTML-output
    if (sizeof($aMatches) > 0) {
?>
        <div class="w3-half value">
            <select class="w3-select w3-padding-small" <?= field_error('default_language'); ?> tabindex="3" name="default_language" >
<?php   foreach ($aMatches as $aMatch) { ?>
                <option value="<?= $aMatch['code'];?>" <?= ($aMatch['status'] ? 'selected="selected"' : ''); ?>><?= $aMatch['name'];?></option>
<?php   } ?>
            </select>
        </div>
<?php
    } else {
        $installFlag = false;
?>
        echo 'WARNING: No language definition files available!!!';
<?php
    }
    unset($aMatches, $aMatch, $getLanguage);
?>

                </div>
            </div>
            </div>

        </div><!-- end of card step3  -->


        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom w3-medium">
            <div class="w3-row w3-section w3-container w3-theme">
                <h1 class="step-row w3-xlarge">Step 4</h1>
                <p class="w3-large _header">the only operating system information below is...</p>
            </div>
            <div class="w3-row w3-section" style="padding:0.2em 0 1.2em 0;">
              <div class="w3-col w3-medium name">Server Operating System:</div>
                <div class="w3-half value ">
                <input type="hidden" name="operating_system" value="linux" />
                <button tabindex="10" class="w3-padding-small​ w3-tag w3-large w3-text-blue" >Linux</button>
                </div>
            </div>
        </div><!-- end of card step4  -->

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom">
          <div class="w3-row w3-section w3-container w3-theme">
              <h1 class="step-row w3-xlarge">Step 5</h1><p class="w3-large _header">Please enter your MySQL database server details below</p>
          </div>
          <div style="padding: 0.2em 0 1.2em;" >
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Host Name:</div>
                <div class="w3-half value">
                    <input  <?php echo field_error('database_host');?> class="w3-padding-small" type="text" tabindex="7" name="database_host" value="<?php echo $sDatabaseHost; ?>" />
                </div>
            </div>
             <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Database Name:</div>
                <div class="w3-half value">
                    <input  <?php echo field_error('database_name')?> class="w3-padding-small" type="text" tabindex="8" placeholder="DatabaseName" name="database_name" value="<?php echo $sDatabaseName; ?>" />
                </div>
                <div class="value w3-rest">
                    <span class="pre-fix">([a-zA-Z0-9_])</span>
                </div>
            </div>
             <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Table Prefix:</div>
                <div class="w3-half value">
                    <input <?php echo field_error('table_prefix')?> class="w3-padding-small" type="text" tabindex="9" placeholder="Table Prefix (default wb_)" name="table_prefix" value="<?php echo $sTablePrefix; ?>" />
                </div>
                <div class="value w3-rest">
                    <span class="pre-fix">([a-z0-9_])</span>
                </div>
            </div>
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">DB Charset Collation:</div>
                <div class="w3-half value">
                <input type="hidden" name="db_collation" value="utf8mb4" />
                <button type="button" tabindex="10" class="w3-padding-small" >utf8mb4_unicode_ci</button>
                </div>
            </div>
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Username:</div>
                <div class="w3-half value">
                    <input  <?php echo field_error('database_username');?> class="w3-padding-small" type="text" autocomplete="off" tabindex="10" placeholder="Enter Username e.g. root" name="database_username" value="<?php if(isset($_SESSION['database_username'])) { echo $_SESSION['database_username']; } else { echo ''; } ?>" />
                </div>
            </div>
            <div class="w3-row w3-section">
              <div class="w3-col w3-medium name">Password:</div>
                <div class="w3-half value">
                    <input  <?php echo field_error('database_password');?> autocomplete="off" tabindex="11" id="database_password" type="password" class="w3-col l12 w3-input form-control database_password" name="database_password" placeholder="Enter Password" value="<?php if (isset($_SESSION['database_password'])) { echo $_SESSION['database_password']; } ?>" />
                    <span toggle=".database_password" class="fa fa-fw fa-eye-slash field-icon toggle-password"></span>
                </div>
            </div>
          </div>
       </div> <!-- end of card step5  -->

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom">
            <div class="w3-row w3-section w3-container w3-theme">
                <h1 class="step-row w3-xlarge">Step 6</h1><p class="w3-large _header w3-theme">Please enter your website title below...</p>
            </div>
            <div style="padding: 0.2em 0 1.2em;">
                <div class="w3-row w3-section">
                  <div class="w3-col w3-medium name">Website Title:</div>
                    <div class="w3-half value">
                        <input  <?php echo field_error('website_title');?> class="w3-padding-small" type="text" tabindex="13" placeholder="Enter your website title" name="website_title" value="<?php if(isset($_SESSION['website_title'])) { echo $_SESSION['website_title']; } else { echo ''; } ?>" />
                    </div>
                </div>
            </div>
        </div><!-- end of card step6  -->

        <div class="w3--container w3-card-4 w3-light-grey w3-margin-bottom">
            <div class="w3-row w3-section w3-container w3-theme">
                <h1 class="step-row w3-xlarge">Step 7</h1><p class="w3-large _header">Please enter your Administrator account details below...</p>
            </div>
            <div style="padding: 0.2em 0 1.2em;" >
                <div class="w3-row w3-section">
                  <div class="w3-col w3-medium name w3-cell-middle">Login Name:</div>
                    <div class="w3-half value">
                        <input  placeholder="Administrator Login Name" <?php echo field_error('admin_username');?> class="w3-padding-small" type="text" tabindex="14" name="admin_username" value="<?php if(isset($_SESSION['admin_username'])) { echo $_SESSION['admin_username']; } ?>" />
                    </div>
                </div>
                <div class="w3-row w3-section">
                  <div class="w3-col w3-medium name">Email:</div>
                    <div class="w3-half value">
                        <input  <?php echo field_error('admin_email');?>  class="w3-padding-small" type="text" tabindex="15" name="admin_email" value="<?php if(isset($_SESSION['admin_email'])) { echo $_SESSION['admin_email']; } ?>" />
                    </div>
                </div>
                <div class="w3-row w3-section">
                  <div class="w3-col w3-medium name">Password:</div>
                    <div class="w3-half value">
                        <input  <?php echo field_error('admin_password');?> autocomplete="off" tabindex="16" id="admin_password" type="password" class="w3-col l12 w3-input form-control admin_password" name="admin_password" placeholder="Enter Password" value="<?php if (isset($_SESSION['admin_password'])) { echo $_SESSION['admin_password']; } ?>" />
                        <span toggle=".admin_password" class="fa fa-fw fa-eye-slash field-icon toggle-password"></span>
                    </div>
                </div>
                <div class="w3-row w3-section">
                  <div class="w3-col w3-medium name">Re-Password:</div>
                    <div class="w3-half value">
                    <input  <?php echo field_error('admin_repassword');?> class="w3-padding-small" type="password" tabindex="17" name="admin_repassword" value="" autocomplete="off" />
                    </div>
                </div>
            </div>
        </div> <!-- end of card step7 -->

<?php  }    ?>

        <table>
        <tbody>
<?php if ($installFlag == true) { ?>
                <tr>
                    <td><strong>Please note: &nbsp;</strong></td>
                </tr>
                <tr>
                    <td>

                        <div class="w3-panel w3-pale-green w3-leftbar w3-border-green w3-border">
                        <p>
                        WebsiteBaker is released under the
                        <a href="https://www.gnu.org/licenses/gpl.html" target="_blank" rel="nofollow noopener" tabindex="19">GNU General Public License</a>
                        </p>
                        <p>
                        By clicking install, you are accepting the license.
                        </p>
                        </div>
                    </td>
                </tr>
                <tr>
            <td>
<?php  }    ?>
            <p class="center w3-margin-bottom">
<?php if ($installFlag == true) { ?>
                <input class="w3-btn w3-btn-default w3-blue w3-hover-green w3-padding" type="submit" tabindex="20" name="install" value="Install WebsiteBaker" />
<?php
                } else {
                    if (isset($_SESSION['token'])) { unset($_SESSION['token']); }
                    // remove session cookie  'wb-installer'
                        // delete the session itself
                        if (session_status() === \PHP_SESSION_ACTIVE ) {
                            session_unset();
                            session_destroy();
                        }

                        $sCfgFile = $sAppPath.'config.php';
                        if ((\is_writeable($sCfgFile) && \filesize($sCfgFile) < 64)) {
                            \unlink($sCfgFile);
                        }

?>
                <input class="w3-btn w3-btn-default w3-pale-red w3-hover-green w3-medium w3-padding w3-border" type="submit" tabindex="20" name="restart" value="Check your Settings in Step1 or Step2" class="submit" onclick="window.location='<?php print $sScriptUrl; ?>';" />
<?php } ?>
            </p>
            </td>
        </tr>
        </tbody>
        </table>

</form>
</div>

<div style="margin: 0 0 3em; padding: 0; text-align:center;">
    <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
    <a href="https://websitebaker.org/" target="_blank" rel="noopener" style="color: #000000;">WebsiteBaker</a>
    is released under the
    <a href="https://www.gnu.org/licenses/gpl.html" target="_blank" rel="noopener" style="color: #000000;">GNU General Public License</a>
    <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
</div >
<script>

    window.onload = function () {
        localStorage.clear(); //Remove all saved data from sessionStorage
    }

    let toggle = document.querySelectorAll("span.toggle-password");
    if (toggle){
        for (var i = 0; i < toggle.length; i++) {
            if (typeof toggle[i] === "object"){
                toggle[i].addEventListener("click", function(){
                    var attr  = this.getAttribute("toggle");
                    var input = document.querySelector("input"+attr)
                    if (input.getAttribute("type") === "password") {
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                        input.setAttribute("type", "text");
                    } else {
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                        input.setAttribute("type", "password");
                    }
    //console.log(input);
                });
            }// toggle[i] is object
        }// for
    }
/*
        const toggleDBPassword = document.querySelector("#toggleDBPassword");
        if (toggleDBPassword){
            toggleDBPassword.addEventListener("click", function () {
                // toggle the type attribute
                const password1 = document.querySelector("#pw1");
                const type = password1.getAttribute("type") === "password" ? "text" : "password";
                password1.setAttribute("type", type);
//console.log(this.classList);
                // toggle the icon
                this.classList.toggle("fa-eye-slash");
                this.classList.add("fa-eye");
            });
        }
        const toggleAdminPassword = document.querySelector("#toggleAdminPassword");
        if (toggleAdminPassword){
            toggleAdminPassword.addEventListener("click", function () {
                // toggle the type attribute
                const password2 = document.querySelector("#pw3");
                const type = password2.getAttribute("type") === "password" ? "text" : "password";
                password2.setAttribute("type", type);
//console.log(this.classList);
                // toggle the icon
                this.classList.toggle("fa-eye-slash");
                this.classList.add("fa-eye");
            });
        }
*/
</script>

</body>
</html>
