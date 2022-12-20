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
 * @subpackage      field_import
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

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
//use vendor\phplib\Template;

/* -------------------------------------------------------- */
if (!function_exists('getFormFieldImport')){
    function getFormFieldImport ()
    {
      global $section_id;
      $sAddonPath   = str_replace('\\','/',__DIR__).'/';
      $sModulesPath = \dirname($sAddonPath).'/';
      $sModuleName  = basename($sModulesPath);
      $sAddonName   = basename($sAddonPath);
      $ModuleRel    = ''.$sModuleName.'/';
      $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
      $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
      $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
      if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/* -------------------------------------------------------- */
      $sLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
      $sSecureToken = (!is_readable($sAddonPath.'.setToken'));
      $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
      $sqlEOL       = ($sLocalDebug ? "\n" : "");
/* ----------set to deprecated----------------------
// load module language file
        if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
        if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
        if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
------- */
/* -------------------------------------------------------- */
// print with or without header
      $admin_header = true;
// Workout if the developer wants to show the info banner
      $print_info_banner = true; // true/false
// Tells script to update when this page was last updated
      $update_when_modified = true;
// Include WB admin wrapper script
      require($sModulesPath.'admin.php');
/* -------------------------------------------------------- */
      try {
/* -------------------------------------------------------- */
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
            $sMessage = '';
            $aMessage = [];
//  create Variables from request
            foreach ($aRequestVars as $index=>$value){
                ${$index} = $value;
            }
/* -------------------------------------------------------- */
            $iPostMaxSize = PRECHECK::convertToByte('post_max_size');
            $sPostMaxSize = PRECHECK::convertByteToUnit($iPostMaxSize);
/* -------------------------------------------------------- */
            $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
            $sBacklink        = $sAddonUrl.'modify_backup.php?page_id='.$page_id;
            $sAddonBackUrl    = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
/* -------------------------------------------------------- */
            $sGetOldSecureToken = (SecureTokens::checkFTAN() ?? false);
            $aFtan = SecureTokens::getFTAN();
            $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
            if (!$sGetOldSecureToken){
                 $oApp->print_error( $oTrans->MESSAGE_GENERIC_SECURITY_ACCESS, $sAddonBackUrl );
            }
// already done by wrapper admin
//           $sSectionIdKey = SecureTokens::getIDKEY($section_id);
//            $section_id = (($oApp->getIdFromRequest('section_id')));
            $sSectionIdKey = $section_id;
            $sBacklink .= '&section_id='.$sSectionIdKey;
/* --------------------------------------------------------------------- */
            if ($sSecureToken && !$sGetOldSecureToken){
                $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
                throw new \Exception ($aMessage);
            }
/*
//($aRequestVars['xml_file'] ?? ($aRequestVars['upload_file'] ?? ''))
    $sFilename  = $admin->StripCodeFromText((
                      isset($aRequestVars['xml_file']) && ($aRequestVars['xml_file']!=='')
                    ? $aRequestVars['xml_file']
                    : (isset($aRequestVars['upload_file']) && ($aRequestVars['upload_file']!=='')
                    ? $aRequestVars['upload_file']
                    : '')
                    )
                  );
*/
/* ----------------------------------------------------------------------------- */
            $sLayout      = ($oApp->StripCodeFromText(($aRequestVars['file'] ?? ($aRequestVars['upload_file'] ?? ''))));
            $sFilename    = ($oApp->StripCodeFromText(($aRequestVars['file'] ?? ($aRequestVars['upload_file'] ?? ''))));
            $sDescription = ($oApp->StripCodeFromText(($aRequestVars['description'] ?? 'Description of the form…')));
            $sTargetPath  = $sAddonPath.'data/fields/';
            $sAbsFilename = $sTargetPath.$sFilename.'';
/* ----------------------------------------------------------------------------- */
            if (!empty($_FILES)){
              require $sAddonPath.'upload.php';
              $sAbsFilename = $sTargetPath.$sFilename.'';
            }
/* ----------------------------------------------------------------------------- */
            $action = 'cancel';
            $action = (isset($aRequestVars['import']) ? 'import' : $action);
            $action = (isset($aRequestVars['delete']) ? 'delete' : $action);
/* ----------------------------------------------------------------------------- */
            if (isset($aRequestVars['cmd']) && (is_file($sAbsFilename) || ($sFilename !== '')))
            {
                $sLayout = $oApp->removeExtension($sFilename);
                switch ($action):
                    case 'import':
                        $sql  = 'SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields` '
                              . 'WHERE `section_id` = '.(int)$section_id.' '
                              . '';
                        $sDomain = \basename(__DIR__).'/'.\basename(__FILE__);
                        if ($oDb->get_one($sql) > 0){
                            $sAddonBackUrl = $sBacklink;
                            $sMessage = sprintf("Couldn't import, because a Formular already exists in section %s",$section_id);
                            throw new \Exception ($sMessage );
                        } else {
                            if (($oXml = simplexml_load_file($sAbsFilename))) {
                                $sLayout = ($field->layout ?? $sLayout);
                                $sDescription = ($field->description ?? $sDescription);
                                foreach ($oXml->fields->field as $field) {
                                    $position = $field->position;
                                    $title    = $field->title;
                                    $type     = $field->type;
                                    $required = $field->required;
                                    $extra    = $field->extra;
                                    $value    = $field->value;
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s => %s</div>\n",__LINE__,$type,htmlspecialchars($value)));
                                    $sql  = '
                                    INSERT INTO `'.$oReg->TablePrefix.'mod_form_fields` SET
                                    `section_id` = '.(int)($section_id).',
                                    `page_id` = '.(int)($page_id).',
                                    `layout` = \''.$oDb->escapeString($sLayout).'\',
                                    `position`= '.(int)($position).',
                                    `title`= \''.$oDb->escapeString($title).'\',
                                    `type`= \''.$oDb->escapeString($type).'\',
                                    `required`= '.(int)($required).',
                                    `extra`= \''.$oDb->escapeString($extra).'\',
                                    `value`= \''.$oDb->escapeString($value).'\'
                                    ';
                                    if (!$oRes = $oDb->query($sql)) {
                                      $sAddonBackUrl = $sBacklink;
                                      $sMessage = sprintf("%s",$oDb->get_error());
                                      throw new \Exception ($sMessage );
                                    }
                                }// end foreach
                            }
                        }
                        break;
                    case 'delete':
                        $sql  = '
                        SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields`
                        WHERE `section_id` = '.(int)$section_id.'
                          AND `layout` LIKE \''.$oDb->escapeString($sLayout).'\'
                        ';
                        if (($oDb->get_one($sql) > 0)){
                            $sAddonBackUrl = $sBacklink;
                            $sMessage = sprintf("Couldn't delete %s because Fields in use ",$sFilename);
                            throw new \Exception ($sMessage );
                        }elseif (! unlink($sAbsFilename)){
                            $sAddonBackUrl = $sBacklink;
                            $sMessage = sprintf("Unknown Error! Couldn't delete %s",$sFilename);
                            throw new \Exception ($sMessage );
                        }
                      break;
                    default:
                endswitch;
            } else {
                $sAddonBackUrl = $sBacklink;
                $sMessage = sprintf("File does not exist or no file selected!");
                throw new \Exception ($sMessage );
            }
/* -------------------------------------------------------- */
            switch ($action):
                case 'import':
                    //$sAddonBackUrl = $sBacklink;
                    $admin->print_success(sprintf($oTrans->FORM_MESSAGE_IMPORT_SUCCESS,$sFilename), $sAddonBackUrl );
                    break;
                case 'delete':
                    $sAddonBackUrl = $sBacklink;
                    $admin->print_success(sprintf($oTrans->FORM_MESSAGE_IMPORT_DELETED,$sFilename), $sAddonBackUrl);
                    break;
                default:
            endswitch;
          }catch (\Exception $ex) {
          //    $admin->print_header();
              $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
              $admin->print_error ($sErrMsg, $sAddonBackUrl);
              exit;
          }
    } // end getFormFieldImport
}
/* -------------------------------------------------------- */
//
/* -------------------------------------------------------- */
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/* -autoloader needed and permission ------------------------------- */
    $admin_header = ($admin_header ?? false);
    $admin_auth   = ($admin_auth ?? true);
    $admin        = new \admin('Pages', 'pages_modify',(bool)$admin_header, $admin_auth);
    getFormFieldImport();
// Print admin footer
    $admin->print_footer();
// end of file

