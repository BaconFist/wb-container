<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * uprade.php
 *
 * @category     Modules
 * @package      Modules_MultiLingual
 * @author       Werner v.d.Decken <wkl@isteam.de>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websiteBaker.org>
 * @copyright    Werner v.d.Decken <wkl@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      1.6.9
 * @revision     $Revision: 300 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/WBLingual/upgrade.php $
 * @lastmodified $Date: 2019-03-27 10:00:11 +0100 (Mi, 27. Mrz 2019) $
 * @since        File available since 09.01.2013
 * @description  provides a flexible posibility for changeing to a translated page
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

if (!\function_exists('mod_MultiLingual_upgrade')){
    function mod_MultiLingual_upgrade($bDebug=false) {
        global $OK ,$FAIL;
        $oDb = ( $GLOBALS['database'] ?: null );

        $msg = [];
        $sErrorMsg = null;
        $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
        $sAddonName = basename($sAddonPath);
        $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
        $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
        $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
        if (version_compare($sWbVersion, $sModulePlatform, '<')){
            $msg[] = $sErrorMsg = sprintf('It is not possible to upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
            if ($globalStarted){
                echo $sErrorMsg;
            }else{
                throw new \Exception ($sErrorMsg);
            }
        } else {
// change table structure
            $sTable = TABLE_PREFIX.'pages';
            $sFieldName = 'page_code';
            $sDescription = "INT NOT NULL DEFAULT '0' AFTER `language`";
            if (!$oDb->field_add($sTable,$sFieldName,$sDescription)) {
                $msg[] = ''.$oDb->get_error();
            } else {
                $msg[] = 'Field ( `page_code` ) description has been add successfully '.$OK;
            }
// Work-out if we should check old format for existing page_code
            $sql = 'DESCRIBE `'.TABLE_PREFIX.'pages` `page_code`';
            $field_sql = $oDb->query($sql);
//        $field_set = $field_sql->numRows();
            $format = $field_sql->fetchRow(MYSQLI_ASSOC) ;
        // upgrade only if old format
            if ($format['Type'] == 'varchar(255)' )
            {
                $sql = 'SELECT `page_code`,`page_id` FROM `'.TABLE_PREFIX.'pages` ORDER BY `page_id`';
                if ($query_code = $oDb->query($sql))
                {
                    // extract page_id from old format
                    $pattern = '/(?<=_)([0-9]{1,11})/s';
                    while( $page = $query_code->fetchRow(MYSQLI_ASSOC))
                    {
                        \preg_match($pattern, $page['page_code'], $array);
                        $page_code = $array[0];
                        $page_id =  $page['page_id'];
                        $sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET ';
                        $sql .= ((empty($array[0])) ? '`page_code` = 0 ' : '`page_code` = '.$page_code.' ');
                        $sql .= 'WHERE `page_id` = '.$page_id;
                        $oDb->query($sql);
                    }
                    $field_set = $oDb->field_modify($sTable,$sFieldName,$sDescription);
                    $msg[] = 'Field ( `page_code` ) description has been changed successfully '.$OK;
// only for upgrade-script
                    if($globalStarted) {
                        if($bDebug) {
//                        echo '<strong>'.implode('<br />',$msg).'</strong><br />';
                        }
                    }
                }  else {
                    $msg[] = ''.$oDb->get_error();
                }
            }
            $aRemoveFiles = [
                '/Helper.inc',
                '/uninstall.php',
                '/frontend_body.js',
                '/tpl/lang.twig',
                '/templates/default/css/3/',
                '/templates/default/css/4/',
                '/themes/default/css/3/',
                '/themes/default/css/4/',
                ];
            PreCheck::deleteFiles($sAddonPath,$aRemoveFiles);
//            require (WB_PATH.'/framework/functions.php');
            if (\is_writable(WB_PATH.'/modules/mod_multilingual')) {rm_full_dir (WB_PATH.'/modules/mod_multilingual');}
            if (\file_exists(WB_PATH.'/modules/mod_multilingual')){
                $sErrMsg = $msg[] = 'Old multiLingual folder /modules/mod_multilingual couldn\'t be deleted';
                \trigger_error($sErrMsg, E_USER_DEPRECATED);
            } else {
                $msg[] = 'MultiLingual upgrade successfull finished ';
            }
            if($globalStarted) {
    //            echo "<strong>MultiLingual upgrade successfull finished $OK</strong><br />";
            } // end foreach
        }
        return ((isset($globalStarted) && $globalStarted) ? $globalStarted : $msg);

    }
}
// ------------------------------------
// this var comes from outside
    $bDebugModus = ((isset($bDebugModus)) ? $bDebugModus : false);
    // Don't show the messages twice
    if (\is_array($msg = mod_MultiLingual_upgrade($bDebugModus))) {
        echo '<b>'.\implode('<br />',$msg).'</b><br />';
    }

// ------------------------------------

