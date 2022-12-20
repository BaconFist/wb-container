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
 * @subpackage      layout_import
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
/* -------------------------------------------------------- */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
use vendor\phplib\Template;

/* -------------------------------------------------------- */
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

    $aMessage = [];
// suppress to print the header, so no new FTAN will be set
    $admin_header = true;
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require($sModulesPath.'admin.php');

    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
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
    $aLang = $oTrans->getLangArray();

    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBackUrl = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
/* -------------------------------------------------------- */
    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBacklink        = $sAddonUrl.'modify_settings.php?page_id='.$page_id;
    $sAddonBackUrl    = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;

    $sGetOldSecureToken = (SecureTokens::checkFTAN());
    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];
    if (!$sGetOldSecureToken){
         $oApp->print_error( 'checkFTAN ::'.$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS, $sAddonBackUrl );
    }


//$FTAN = $admin->getFTAN('GET');
    $sBacklink  = WB_URL.'/modules/'.$sAddonName.'/modify_settings.php';
    $sBacklink .= '?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery;

    $version  = '1.0';
    $encoding = 'utf-8';

    $sFilename = $oApp->removeExtension(''.$admin->StripCodeFromText($aRequestVars['file']));

    if ($sFilename==''){
        $sFilename = $oApp->removeExtension(''.$admin->StripCodeFromText($aRequestVars['file']));
//        $aMessage[] = sprintf('%1$.04d ) Couldn\'t import, because no layout has been selected', __LINE__);
    }

    $sTargetPath  = $sAddonPath.'data/layouts/';
    $sAbsFilename = $sTargetPath.$sFilename.'.xml';

    $action = 'cancel';
    $action = (isset($aRequestVars['import'])?'import':$action);
    $action = (isset($aRequestVars['delete'])?'delete':$action);

try {
    if (isset($aRequestVars['cmd'])&&(is_file($sAbsFilename)||($sFilename!=''))) {
        switch ($action):
            case 'import':
                    // insert all the fields in the form table
//                    $sXmlStr = file_get_contents($sAbsFilename);
                    $sLayout = \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', basename($sAbsFilename));
                    if ($oXml = simplexml_load_file($sAbsFilename)) {
                        $title = $oXml->title;
                        $sDescription = $oXml->description;
                        foreach ($oXml->fields as $field) {
                            $header     = $field->header;
                            $field_loop = $field->field_loop;
                            $extra      = $field->extra;
                            $footer     = $field->footer;
    //                        print '<hr>'.$field->title.' Typ = '.$field->type;
                        }// end foreach
                            $sql  = '
                            UPDATE `'.TABLE_PREFIX.'mod_form_settings` SET
                            `header` = \''.$database->escapeString($header).'\',
                                  `field_loop` = \''.$database->escapeString($field_loop).'\',
                                  `extra`  = \''.$database->escapeString($extra).'\',
                                  `footer` = \''.$database->escapeString($footer).'\',
                                  `layout` = \''.$database->escapeString($sLayout).'\',
                                  `description` = \''.$database->escapeString($sDescription).'\'
                                  WHERE `section_id` = '.(int)$section_id.' '
                                  . '';
                            if (!$oRes = $database->query($sql)) {
                              $aMessage[] = $sql.'<br />';
                              $aMessage[] = 'Invalid query: ' . $database->get_error();
                            }
                    } else {
                        $aMessage[] = 'Invalid simplexml: '. libxml_get_errors();
                    }

                break;
            case 'delete':
    //          unset($_REQUEST['cmd']);
              if (!unlink($sAbsFilename)){
                  $aMessage[] = sprintf('%1$.04d ) Couldn\'t delete %2$s '.'<br />', __LINE__,$sFilename);
              }
              break;
            default:
        $aMessage[] = sprintf('%1$.04d ) Couldn\'t do anything for file %2$s '.'!<br />', __LINE__, $sFilename);
        endswitch;
    } else {
        $aMessage[] = sprintf('%1$.04d ) Couldn\'t find file %2$s for import or delete '.'!<br />', __LINE__, $sFilename);
    }
} catch ( Exception $e ){
    $aMessage[] = sprintf('%1$.04d ) Tried to set form in DOMElement!<br />'.$e, __LINE__);
}

    if (!sizeof($aMessage)){
        switch ($action):
            case 'import':
                $admin->print_success(sprintf($FORM_MESSAGE['IMPORT_SUCCESS'],$sFilename), $sBacklink);
                break;
            case 'delete':
                $admin->print_success(sprintf($FORM_MESSAGE['IMPORT_DELETED'],$sFilename), $sBacklink);
                break;
            default:
        endswitch;
    } else {
      $admin->print_error(implode('<br />',$aMessage), $sBackUrl);
    }

// Print admin footer
    $admin->print_footer();
// end of file
//