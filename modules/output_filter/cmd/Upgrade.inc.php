<?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6.x and higher
 * @version         $Id: cmdUpgrade.inc 113 2018-09-28 11:34:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/output_filter/cmd/cmdUpgrade.inc $
 * @lastmodified    $Date: 2018-09-28 13:34:16 +0200 (Fr, 28 Sep 2018) $
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

/* -------------------------------------------------------- */
//require (WB_PATH.'/framework/functions.php');

if (!\is_callable('mod_output_filter_upgrade')){
    function mod_output_filter_upgrade($bDebug=false) {
        global $OK ,$FAIL; // from core upgrade-script
        $oDb = \database::getInstance();

        $msg = [];
        $sErrorMsg = null;
        $sAddonPath = \str_replace('\\', '/', \dirname(__DIR__));
        $sAddonName = \basename($sAddonPath);
        $globalStarted = \preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
        $sWbVersion = ($globalStarted && \defined('VERSION') ? VERSION : WB_VERSION);
        $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
        if (\version_compare($sWbVersion, $sModulePlatform, '<')){
            $msg[] = $sErrorMsg = \sprintf('It is not possible to upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
            if ($globalStarted){
                echo $sErrorMsg;
            }else{
                throw new \Exception ($sErrorMsg);
            }
        } else {
            $sTable = TABLE_PREFIX.'mod_output_filter';
            $i = (!isset($i) ? 1 : $i);
            $OK   = "<span class=\"ok\">OK</span>";
            $FAIL = "<span class=\"error\">FAILED</span>";
            $iErr = false;
            $aOldSettings = [];
/*
        if (is_writable($sAddonPath.'/Filters/filterShortUrl.php')){
            unlink($sAddonPath.'/Filters/filterShortUrl.php');
        }
*/
            //By default, we assume that PHP is NOT running on windows.
            $isWindows = false;
            if (\strcasecmp(\substr(PHP_OS, 0, 3), 'WIN') == 0){
                $isWindows = true;
            }
            if (!$isWindows){
                $aRemoveFilter = [
                      '/filters/',
                    ];
                PreCheck::deleteFiles($sAddonPath,$aRemoveFilter);
            }

            $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
            if ( !\is_readable($sInstallStruct)) {
                $msg[] = '<b>\'missing or not readable file [install-struct.sql]\'</b> '.$FAIL;
                $iErr = true;
            } else {
// TODO feature developent craete this list as read.ini file
                $aDeletFilterList = ['Canonical','Script','ReduceMwst','InsertW3Css','InsertW3Css_force'];
// remove obselete files and folders
                $aRemoveList = [
//                     '/languages/',
                      '/Filters/ProtoType/',
                      '/cmd/cmdInstall.inc',
                      '/cmd/cmdSave.inc',
                      '/cmd/cmdTool.inc',
                      '/cmd/cmdUninstall.inc',
                      '/cmd/cmdUpgrade.inc',
                      '/cmd/cmdInstall.inc.php',
                      '/cmd/cmdSave.inc.php',
                      '/cmd/cmdTool.inc.php',
                      '/cmd/cmdUninstall.inc.php',
                      '/cmd/cmdUpgrade.inc.php',
                      '/themes/default/css/backend.css',
                      '/Filters/canonical.php',
/*
                      '/Filters/filterScript.php',
                      '/Filters/filterReduceMwst.php',
                      '/Filters/InsertW3Css/',
                      '/Filters/filterSysvarMedia.php',
//                     '/Filters/filterjQuery.php',
//                     '/Filters/filterjQueryUI.php',
*/
                    ];
                    foreach($aDeletFilterList as $index => $sFilter ){
                       $aRemoveList[] = '/Filters/filter'.$sFilter.'.php';
                    }
                PreCheck::deleteFiles($sAddonPath,$aRemoveList);
//                $aFiles = glob($sAddonPath.'/Filters/*', \GLOB_MARK);
                $aFiles = \glob($sAddonPath.'/Filters/*', \GLOB_NOSORT);
                $getDefaultSettings = (function() use ( $aFiles ){
                    $aDefaults = [];
                    $aAutoFilter = [
                        'WbLink' => 1,
                        'ReplaceSysvar' => 1,
                        'CssToHead' => 1,
                        'CleanUp' => 1,
                        'ShortUrl' => 1,
                        'SnippetCss' => 1,
                        'FrontendCss' => 1,
                    ];

                    $SettingsDenied = [
                        'at_replacement',
                        'dot_replacement',
                        'email_filter',
                        'mailto_filter',
                        'OutputFilterMode',
                        'W3Css_force',
                        'WbLink',
                        'ReplaceSysvar',
                        'CssToHead',
                        'ShortUrl',
                        'Short_force',
                        'edit_force',
                        'CleanUp',
                        'Abstract'
                    ];

                    $aExtendedDefaults = [
                        'at_replacement'  => '@',
                        'dot_replacement' => '.',
                        'email_filter'    => 0,
                        'mailto_filter'   => 0,
                        'Short_force'=> 0,
                        'edit_force'=> 0,
                        'W3Css_force'=> 0,
                        'OutputFilterMode' => 0,
                    ];

                    $aInactiveList = ['OpF', 'RelUrl','Jquery', 'JqueryUI','W3Css_force','W3Css','WbLinkXXL']; //
                    $aOldSettings  = getOutputFilterSettings();

                    \array_walk(
                        $aFiles,
                        function (& $sItem, $iKey) use (& $aDefaults,$aInactiveList) {
                            $sItem = \str_replace(['%filter', '%'], '', '%'.\basename($sItem, '.php'));
                            $aDefaults[$sItem] = (in_array($sItem, $aInactiveList) ? 0 : 1);
                        }
                    );
                    $aDefaults = \array_merge($aDefaults, $aExtendedDefaults,$aAutoFilter );
                    \ksort($aDefaults, \SORT_NATURAL | \SORT_FLAG_CASE );
                    $aAllowedFilters  = \array_keys ( $aDefaults );
                    $aFilterExists    = \array_diff ( $aAllowedFilters, $SettingsDenied );
/* */
                    foreach ( $aFiles  as $sFilterFile) {
                        if (\substr(\str_replace(['\\','//'], '/', $sFilterFile), -1) == '/') {
                            $key = \basename($sFilterFile);
                        } else {
                            $key = \preg_replace('=^.*?filter([^\.\/\\\\]+)(\.[^\.]+)?$=is', '\1', $sFilterFile);
                        }
                        if (in_array($key, ['Abstract'])){
//                        echo nl2br(sprintf("%s\n \n",$key));
                          continue;
                        }
                        if (\in_array($key,$aInactiveList)){
                            //echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,));
                            $aDefaults[$key] = (isset($aOldSettings[$key]) && ($aOldSettings[$key] ==='1') ? '1' : '0');
                        } else {
                            //$aDefaults[$key] = '1';
                        }
                    }
                  \ksort($aDefaults);
                  return $aDefaults;
                });

                $aDefaults =  $getDefaultSettings();
//            $aAllowedFilters = array_keys ( $aDefaults );
            // try to create table if not exists
                $oDb->SqlImport($sInstallStruct, TABLE_PREFIX, true );
            // read settings first
                $sql = 'SELECT * FROM `'.$sTable.'`';
            // check if table already upgraded
                if ( $bOldStructure = $oDb->field_exists($sTable, 'sys_rel') )
                {
                    if (($oSettings = $oDb->query($sql)))
                    {
                      //
                        if (!($aOldSettings = $oSettings->fetchRow(\MYSQLI_ASSOC))) {
                            $msg[] = '<strong>\'Output Filter backup old settings\'</strong> '.$FAIL;
                            $iErr = true;
                        } else {
                            // add new defaults to old settings without invalide values
                            $aNewSettings = \array_intersect_key( $aOldSettings, $aDefaults );
                            $aOldSettings = array_replace_recursive( $aDefaults, $aNewSettings );
                        }
                    }
                } elseif ( $oDb->field_exists($sTable, 'name')) {
                      $aOldSettings = $aDefaults;
                      // overwrite standardsettings ($aOldSettings)
                      $sql = 'SELECT * FROM `'.$sTable.'`';
                      if (($oSettings = $oDb->query($sql))) {
                        while($aSettings = $oSettings->fetchRow( MYSQLI_ASSOC ) )
                        {
                            $key = $aSettings['name'];
                            $aOldSettings[$key] = $aSettings['value'];
                        }
                      }
                  }
                // delete not existing filter in table
                $SettingsDenied = ['at_replacement', 'dot_replacement', 'email_filter', 'mailto_filter', 'OutputFilterMode','Abstract','Short_force','edit_force'];
                $aAllowedFilters  = \array_keys ( $aOldSettings );
                $aFilterExists    = \array_diff ( $aAllowedFilters, $SettingsDenied );
                foreach ( $aFilterExists  as $sFilterName) {
                    $sFilterFile = WB_PATH.'/modules/'.$sAddonName.'/Filters/filter'.$sFilterName.'.php';
                    if (!\is_readable($sFilterFile)) {
                        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_output_filter`'
                              . 'WHERE `name` = \''.$sFilterName.'\'';
                        if( $oDb->query( $sql ) ){
                            unset($aOldSettings[$sFilterName]);
                        }
                    }
                }

            // drop old table and create new one
                if ($oDb->SqlImport($sInstallStruct, TABLE_PREFIX, false))
                {
                    if ($aOldSettings) {
                        // add new defaults to old settings without invalide values
                        $aNewSettings = \array_intersect_key( $aOldSettings, $aDefaults );
                        $aOldSettings = array_replace_recursive( $aDefaults, $aNewSettings );

                    // restore old settings if there any
                        $sNameValPairs = '';
                        foreach ($aOldSettings as $index => $val) {
                            if (in_array($index, ['FilterAbstract'])){continue;}
                            $sNameValPairs .= ', (\''.$index.'\', \''.$oDb->escapeString($val).'\')';
//                            echo nl2br(sprintf("%s\n \n",$index));
                        }
                        $sValues = \ltrim($sNameValPairs, ', ');
                        $sql = 'REPLACE INTO `'.$sTable.'` (`name`, `value`) '
                             . 'VALUES '.$sValues;
                        if (!$oDb->query($sql)) {
                            $msg[] = '<strong>\'Output Filter restore old settings\'</strong> '.$FAIL;
                            $iErr = true;
                        }
                    }
                    $aFilterToDelete= ['Canonical','OldModFiles'];
                    foreach ($aFilterToDelete  as $sFilterName) {
                        $sFilterFile = WB_PATH.'/modules/'.$sAddonName.'/Filters/filter'.$sFilterName.'.php';
                        if (\is_writable($sFilterFile)){\unlink($sFilterFile);}
                    }
                } else {
                    $msg[] = '<strong>\'Output Filter recreate table\'</strong> '.$FAIL;
                    $iErr = true;
                }
                if (!$iErr) {
                    $msg[] = '<strong>\'Output Filter successful updated\'</strong> '.$OK;
                }
                unset($getDefaultSettings);
            }
            //By default, we assume that PHP is NOT running on windows.
            $isWindows = false;
            if (\strcasecmp(\substr(PHP_OS, 0, 3), 'WIN') == 0){
                $isWindows = true;
            }
            $sAddonPath    = \str_replace(['\\','//','\\\\'],'/',\dirname(__DIR__));
            $sAbsAddonPath = \str_replace(['\\','//','\\\\'],'/',\dirname(__DIR__));
            return ((isset($globalStarted) && $globalStarted) ? $globalStarted : $msg);
        }
    }
}
// ------------------------------------

// this var comes from outside
    $bDebugModus = ((isset($bDebugModus)) ? $bDebugModus : false);
    if (is_array($msg = mod_output_filter_upgrade($bDebugModus))) {
        if (!$bDebugModus) {
            echo '<b>'.implode('<br />',$msg).'</b><br />';
        }
    }

