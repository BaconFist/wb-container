<?php
/*
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
 * cmdUpgrade.php
 *
 * @category     Addons
 * @package      Addons_wrapper
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2018-09-17 18:26:08 +0200 (Mo, 17 Sep 2018) $
 * @since        File available since 2015-12-17
 * @description  xyz
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

    $aErrorMsg    = [];
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__));
    $sAddonName = basename($sAddonPath);
    $sAddonTable  = 'mod_'.strtolower($sAddonName);
    $sActionFile  = strtolower(str_replace('', '', $sCommand).'.php');
    $oReg = WbAdaptor::getInstance();
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install/upgrade from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
        $sSqlDumpFile = $sAddonPath.'/install-struct.sql.php';
        if (is_readable($sSqlDumpFile)) {
            // upgrade database tables
            $database->SqlImport($sSqlDumpFile, TABLE_PREFIX, 'upgrade');
        }

// sanitize mod_wrapper.url
        $sql = 'SELECT `section_id`, `url` FROM `'.TABLE_PREFIX.'mod_wrapper` '
             . 'WHERE `url` NOT LIKE \'http%\'';
        if (($oInstances = $database->query($sql))) {
            while (($aInstance = $oInstances->fetchRow(MYSQLI_ASSOC))) {
                // add WB_URL to relative URLs
                $sNewUrl = WB_URL.'/'.ltrim($aInstance['url'], '\\/');
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_wrapper` '
                     . 'SET `url`=\''.$database->escapeString($sNewUrl).'\' '
                     . 'WHERE `section_id`='.(int)$aInstance['section_id'];
                if (!$database->query($sql)) {
                    $msg[] = $database->get_error();
                    break;
                }
            }
        } else { $msg = $database->get_error(); }
// replace local host by SYSVAR-Tag in mod_wrapper.url
        $sql = 'SELECT `section_id`, `url` FROM `'.TABLE_PREFIX.'mod_wrapper`';
        if (($oInstances = $database->query($sql))) {
            while (($aInstance = $oInstances->fetchRow(MYSQLI_ASSOC))) {
                // add WB_URL to relative URLs
                $sNewUrl = preg_replace(
                    '/^'.preg_quote(str_replace('\\', '/', WB_URL).'/', '/').'/si',
                    '{SYSVAR:AppUrl}',
                    ltrim(str_replace('\\', '/', $aInstance['url']), '/')
                );
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_wrapper` '
                     . 'SET `url`=\''.$database->escapeString($sNewUrl).'\' '
                     . 'WHERE `section_id`='.(int)$aInstance['section_id'];
                if (!$database->query($sql)) {
                    $msg[] = $database->get_error();
                    break;
                }
            }
        } else { $msg[] = $database->get_error(); }
/*-------------------------------------------------------------------------------*/
      $sSql = 'UPDATE `'.TABLE_PREFIX.'mod_wrapper` '
            .'SET '
            . '`min_height` = `height` '
            . 'WHERE `min_height` = \'\' ';
            if (!$database->query($sSql)) {
                $msg[] = $database->get_error();
            }
/*-------------------------------------------------------------------------------*/
/**
 * There are files which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*-------------------------------------------------------------------------------*/
            $aFilesToDelete = [
                '/cmd/cmdAdd.inc',
                '/cmd/cmdDelete.inc',
                '/cmd/cmdInstall.inc',
                '/cmd/cmdModify.inc',
                '/cmd/cmdSave.inc',
                '/cmd/cmdUninstall.inc',
                '/cmd/cmdUpgrade.inc',
                '/cmd/cmdView.inc',
                '/templates/PLACEHOLDER',
                '/install-struct.sql',
                '/frontend_body.js'
            ];
            PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);
    }
// end of file

