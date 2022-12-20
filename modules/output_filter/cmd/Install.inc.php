<?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: cmdInstall.inc 93 2018-09-20 18:09:30Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/output_filter/cmd/cmdInstall.inc $
 * @lastmodified    $Date: 2018-09-20 20:09:30 +0200 (Do, 20 Sep 2018) $
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

    $msg = '';
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__));
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (\version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg = $sErrorMsg = \sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform).PHP_EOL;
        if ($globalStarted){
            echo $sErrorMsg.PHP_EOL;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
        Translate::getInstance ()->disableAddon ('modules\\'.$sAddonName);
        Translate::getInstance()->enableAddon(ADMIN_DIRECTORY.'\\addons');
        if (\is_writable(WB_PATH.'/temp/cache')) {
            Translate::getInstance()->clearCache();
        }
        $sTable = TABLE_PREFIX.'mod_output_filter';
        $i = (!isset($i) ? 1 : $i);
        $OK   = "<span class=\"ok\">OK</span>";
        $FAIL = "<span class=\"error\">FAILED</span>";
        $iErr = false;
        $msg .= '<div style="margin:1em auto;font-size:1.1em;">'.PHP_EOL;
        $msg .= '<h4>Step '.$i++.': Installing Output Filter Settings</h4>'.PHP_EOL;
/*
        if (\is_writable($sAddonPath.'/Filters/filterShortUrl.php')){
            \unlink($sAddonPath.'/Filters/filterShortUrl.php');
        }
*/
    // create tables from sql dump file
        $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
        if (!\is_readable($sInstallStruct)) {
            $sInstallStruct = $sAddonPath.'/install-struct.sql';
        }
        if ( !\is_readable($sInstallStruct)) {
            $msg .= \sprintf('<strong>missing or not readable file [%s]</strong> %s<br /> ',basename($sInstallStruct),$FAIL);
            $iErr = true;
        } else {
            $getDefaultSettings = (function() use ( $sAddonName,$sAddonPath ){
                $aDefaults = [
                    'at_replacement'  => '[at]',
                    'dot_replacement' => '[dot]',
                    'email_filter'    => '1',
                    'mailto_filter'   => '1',
                    'W3Css_force'=> 0,
                    'OutputFilterMode' => 0,
                ];
                $aFiles = \glob($sAddonPath.'/Filters/*', \GLOB_MARK);
                $aInactiveList = ['OpF', 'RelUrl','jQuery', 'jQqueryUI','W3Css_force','W3Css','WbLinkXXL'];
                foreach ( $aFiles  as $sFilterFile) {
                    if (\substr(\str_replace(['\\','//'], '/', $sFilterFile), -1) == '/') {
                        $key = \basename($sFilterFile);
                    } else {
                        $key = \preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', $sFilterFile);
                    }
                    if (in_array($key, ['Abstract'])){continue;}
                    if (\in_array($key,$aInactiveList)){
                        $aDefaults[$key] = '0';
                    } else {
                        $aDefaults[$key] = '1';
                    }
                }
              ksort($aDefaults, \SORT_NATURAL | \SORT_FLAG_CASE );
              return $aDefaults;
            });

            $aDefaults =  $getDefaultSettings();
            if ($database->SqlImport($sInstallStruct, TABLE_PREFIX, 'install')){
                if (count($aDefaults)) {
                // restore old settings if there any
                    $sNameValPairs = '';
                    foreach ($aDefaults as $index => $val) {
                        $sNameValPairs .= ', (\''.$index.'\', \''.$database->escapeString($val).'\')';
                    }
                    $sValues = ltrim($sNameValPairs, ', ');
                    $sql = 'INSERT INTO `'.$sTable.'` (`name`, `value`) '
                         . 'VALUES '.$sValues;
                    if (!$database->query($sql)) {
                        $msg .= '<strong>Output Filter install settings</strong> '.$FAIL.PHP_EOL;
                        $msg .= $database->get_error().'';
                        $iErr = true;
                    } else {
                        $msg .= '<strong>Output Filter settings successful installed</strong> '.$OK.PHP_EOL;
                    }
                }
                unset($getDefaultSettings);
            } else {
                $msg .= '<strong>Output Filter install settings</strong> '.$FAIL.PHP_EOL;
                $msg .= $database->get_error().PHP_EOL;
            }
        }
        $msg .= '</div>'.PHP_EOL;
        if ($globalStarted){echo nl2br($msg)."<br />";}
    }