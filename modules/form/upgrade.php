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
 *
 * @category        addons
 * @package         form
 * @subpackage      upgradeXml
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 not found'; \flush(); exit;}
/* -------------------------------------------------------- */

if (!function_exists('mod_form_upgrade')){
    function mod_form_upgrade($bDebug=false) {
        global $OK ,$FAIL; // needed for upgrade-script
/* -------------------------------------------------------- */
        $sAddonPath   = str_replace('\\','/',__DIR__).'/';
        $sModulesPath = \dirname($sAddonPath).'/';
        $sModuleName  = basename($sModulesPath);
        $sAddonName   = basename($sAddonPath);
        $ModuleRel    = ''.$sModuleName.'/';
        $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
        $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
        $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
/* -------------------------------------------------------- */
        $sLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
        $sSecureToken = (!is_readable($sAddonPath.'.setToken'));
        $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
        $sqlEOL       = ($sLocalDebug ? "\n" : "");
/* -------------------------------------------------------- */
        $oReg           = WbAdaptor::getInstance();
        $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
        $ModuleUrl      = $oReg->AppUrl.$ModuleRel;
        $sAddonUrl      = $oReg->AppUrl.$sAddonRel;
/* -------------------------------------------------------- */
        $oApp     = $oReg->getApplication();
        $oDb      = $oReg->getDatabase();
        $sDomain  = $oApp->getDirNamespace(__DIR__);
        $oTrans   = $oReg->getTranslate();
        $oTrans->enableAddon($sDomain);
        $aLang    = $oTrans->getLangArray();
        $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */
        $msg = [];
        $succes = [];
        $aOutputMsg = [];
/* -------------------------------------------------------- */
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
            if (is_writable(WB_PATH.'/temp/cache')) {
                Translate::getInstance()->clearCache();
            }

            $getMissingTables = (function (array $aTablesList) use ($oReg, $oDb )
            {
                $aTablesList = array_flip($aTablesList);
                $sPattern =  $oDb->escapeString( $oReg->TablePrefix, '%_' );
                $sql = 'SHOW TABLES LIKE \''.$sPattern.'%\'';
                if (($oTables = $oDb->query( $sql ))) {
                    while ($aTable = $oTables->fetchRow(MYSQLI_NUM)) {
                        $sTable =  preg_replace('/^'.preg_quote($oReg->TablePrefix, '/').'/s', '', $aTable[0]);
                        if (isset($aTablesList[$sTable])) {
                            unset($aTablesList[$sTable]);
                        }
                    }
                }
                return (sizeof($aTablesList) ? array_flip($aTablesList) : []);
            });
            // check for missing tables, if true stop the upgrade
            $aTable = ['mod_form_fields','mod_form_settings','mod_form_submissions'];
            $aPackage = $getMissingTables($aTable);
            if (sizeof($aPackage) > 0){
                $msg[] =  'TABLE '.implode(' missing! '.$FAIL.'<br />TABLE ',$aPackage).' missing! '.$FAIL;
                $msg[] = 'Form upgrade failed'.' '.$FAIL;
            } else {
                $sTable = $oReg->TablePrefix.'mod_form_settings';
                if ($bOldStructure = $oDb->field_exists($sTable, 'success_page')) {
                    $oField = $oDb->query('DESCRIBE `'.$sTable.'` `success_page`');
                    $aFormat = $oField->fetchRow(MYSQLI_ASSOC) ;
                    if ((($aFormat['Type'] == 'text')&& $oDb->field_remove($sTable, 'success_page'))) {;}
                }
                $sInstallStruct = $sAddonPath.'install-struct.sql.php';
                if (!$oDb->SqlImport($sInstallStruct, $oReg->TablePrefix, 'upgrade')){
                    $msg[] = sprintf('[%d] %s', __LINE__,$oDb->get_error()).PHP_EOL;
                } else {
                }

/*
                for($x=0; $x<sizeof($aTable);$x++) {
                    if (($sOldType = $oDb->getTableEngine($oReg->TablePrefix.$aTable[$x]))) {
                        if(('myisam' != strtolower($sOldType))) {
                            if(!$oDb->query('ALTER TABLE `'.$oReg->TablePrefix.$aTable[$x].'` Engine = \'MyISAM\' ')) {
                                $msg[] = sprintf('[%d] %s', __LINE__,$oDb->get_error()).PHP_EOL;
                            } else{
                                $succes[] = $oReg->TablePrefix.$aTable[$x].'` changed to Engine = \'MyISAM\' '.$OK;
                            }
                        }
                    }
                }
*/
    // looking for missing placeholder CALL_CAPTCHA and CALL_DSGVO_LINK in older form layouts
                    $aHtmlTags = [];
                    $sql  = '
                    SELECT `footer`,`extra`,`section_id` FROM `'.$sTable. '`
                    WHERE `section_id` != 0
                    ';
                  //        .   'AND `footer` LIKE \'%CALL_CAPTCHA%\' ';
                    if ($oFooter = $oDb->query($sql)){
                        while (($aRecord = $oFooter->fetchRow(MYSQLI_ASSOC))) {
                            if (is_readable($sAddonPath.'data/layouts/Layout_Default.inc.php')){require ($sAddonPath.'data/layouts/Layout_Default.inc.php');}
                            $sFooter = (str_replace(['{CALL_CAPTCHA}','{TEXT_VERIFICATION}','{CALL_DSGVO_LINK}'],['{CALL_CUSTOM}','{TEXT_CUSTOM}','{CALL_CUSTOM}'], $aRecord['footer']));
                            $iSectionId  = (int)$aRecord['section_id'];
                            $aHtmlTags[$iSectionId]['isTable'] = strpos($sFooter, '/table');
                            $sqlSection = 'UPDATE `'.$sTable.'` SET '
                                        . '`footer` = \''.$oDb->escapeString($sFooter).'\' '
                                    . 'WHERE `section_id` = '.$iSectionId.';';
                            if (!$oDb->query($sqlSection)){
                                $msg[] = $oDb->get_error();
                            }

                            if ($oDb->field_exists($sTable,'extra')){
                                if ($aHtmlTags[$iSectionId]['isTable']){
//                                    $sInsertCaptcha = $sInsertTableCaptcha;
//                                    $sInsertDSGVO   = $sInsertTableDSGVO;
                                }

                                $iFlags = 0;
                                $bDivStyle = false;
                                $mCaptcha = stripos($aRecord['extra'],'{CALL_CAPTCHA}');
                                $iFlags |= (($mCaptcha !== false) ? 0 : 1);
                                $mDsgvo = stripos($aRecord['extra'],'{CALL_DSGVO_LINK}');
                                $iFlags |= (($mDsgvo !== false) ? 0 : 2);
                                $iFlags = (int)($iFlags);
//             echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$iFlags));
                                switch ($iFlags):
                                    case 0:   // no changes already exists
                                      $sExtra = $aRecord['extra'];
                                      break;
                                    case 1:   // no captcha
                                      $sExtra = $aRecord['extra'].$sInsertCaptcha;
                                      break;
                                    case 2:   // no DSGVO
                                      $sExtra = $sInsertDSGVO.$aRecord['extra'];
                                      break;
                                    case 3:   // no captcha  no DSGVO
                                      $sExtra = $sInsertDSGVO.$sInsertCaptcha;
                                      break;
                                    default:
                                      $sExtra = $aRecord['extra'];
                                      break;
                                endswitch;

                                $sqlSection = 'UPDATE `'.$sTable.'` SET '
                                            . '`extra` = \''.$oDb->escapeString($sExtra).'\' '
                                        . 'WHERE `section_id` = '.$iSectionId.';';
                                if (!$oDb->query($sqlSection)){
                                    $msg[] = $oDb->get_error();
                                }
                            }
                        } // end while
                    }
/*------------------------------------------------------------------------------*/
//  Switch for development environment, only needed for production
          if (!is_readable($sAddonPath.'.setTesting')){require $sAddonPath.'upgradeXml.php';}

/*------------------------------------------------------------------------------*/
/**
 * There are files and folder which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
/*------------------------------------------------------------------------------*/
            $aFilesToDelete = [
                    '/frontend.000.css',
                    '/css/',
                    '/htt/',
                    'index.php',
                    '/themes/default/css/3/',
                    '/themes/default/settings.htt',
                    '/lib/',
                    '/templates/default/css/3/',
                    '/data/fields/DE_FieldTableMap.xml',
                    '/data/fields/EN_FieldTableMap.xml',
                    '/data/fields/TestVorlage_Div.xml',
                    '/data/layouts/Default_Layout.inc.php',
                    '/data/layouts/Modern_Table_Layout.xml',
                    '/data/layouts/Modern_Table_Layout_001.xml',
                    '/data/layouts/Layout_Default_Table.xml',
                    '/data/layouts/Layout_Extended_Table.xml',
                    '/data/layouts/Layout_Modern_Table.xml',
                    '/data/layouts/LayoutDefaultTable.xml',
                    '/data/layouts/Simple-DIV.xml',
                    '/data/layouts/Simple-DIV_Placeholder.xml',
                    '/data/layouts/Layout_Simple-DIV_Placeholder.xml',
                ];
            PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);

// only for upgrade-script
                if (!$globalStarted) {
                    if($bDebug) {
                        $msg[] = '<b>'.implode('<br />',$msg).'</b><br />';
                    }
                }
            }

            $sTemplateDir  = $oReg->AppPath.'modules/'.\basename(__DIR__).'/templates/';
            $sTemplateName = (($oReg->DefaultTemplate !== 'DefaultTemplate') && !\is_dir($sTemplateDir.$oReg->DefaultTemplate) ? $oReg->DefaultTemplate : 'default');
            $sTemplatePath = $sTemplateDir.$sTemplateName;
            if (!\is_dir($sTemplatePath)){
                if (!make_dir($sTemplatePath)){
                    $msg[] = sprintf('couldn\'t create %s','/templates/'.$sTemplateName);
                }
            }
            if ($globalStarted) {$msg[] = 'Form upgrade successfull finished '.$OK;}
//  remove obselete files and folder
            $aRemoveList = [
                '/install-data.sql',
                '/install-struct.sql',
                '/install-struct.php',
            ];
            PreCheck::deleteFiles($sAddonPath,$aRemoveList);

        }
        return $msg;
    } // function
}  // end mod_form_upgrade
// ------------------------------------
//  set by upgrade-script to surpress echo msg
    $callingScript = $_SERVER["SCRIPT_NAME"];
    $globalStarted = \preg_match('/upgrade\-script\.php$/', $callingScript);

    $aMsg = mod_form_upgrade();
    if (!$globalStarted && sizeof($aMsg)) {print implode("\n", $aMsg)."\n";}

