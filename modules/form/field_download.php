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
 * @subpackage      field_download
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

    class FormException extends Exception { }

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
    ob_start();
      require($sModulesPath.'admin.php');
    ob_get_contents();
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
    $aJsonRespond = [];
//  create Variables from request
    foreach ($aRequestVars as $index=>$value){
        ${$index} = $value;
    }
/* --------------------------------------------------------------------- */
    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBacklink        = $sAddonUrl.'modify_backup.php?page_id='.$page_id;
    $sAddonBackUrl    = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
/* -------------------------------------------------------- */
    $sGetOldSecureToken = (SecureTokens::checkFTAN() ?? false);
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
// already done by wrapper admin
//      $sSectionIdKey = SecureTokens::getIDKEY($section_id);
//      $section_id = (($oApp->getIdFromRequest('section_id')));
    $sSectionIdKey = $section_id;
    $sBacklink .= '&section_id='.$sSectionIdKey; //.'&'.$sFtanQuery
/* -------------------------------------------------------- */
    if ($sSecureToken && !$sGetOldSecureToken){
        $sAddonBackUrl = $sBacklink;
        $sMessage = \sprintf("%s",$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new FormException ($sMessage);
    }
/* -------------------------------------------------------- */
    try {
          $sTargetRel  = $sAddonRel.'data/fields/';
          $sTargetPath = $sAddonPath.'data/fields/';
          $sTargetFieldPath  = $sAddonPath.'data/fields/';
          $sTargetLayoutPath  = $sAddonPath.'data/layouts/';

          $file = $oApp->StripCodeFromText(urldecode($file ?? ''));
          $iFileSize = filesize($sTargetPath.$file);

//          $admin = new admin('Modules', 'module_view', FALSE, FALSE);

          if (is_readable($sTargetPath.$file) && ($file!=''))
          {
              header('Content-Description: File Transfer');
              header("Content-Type: text/xml");
              header("Content-Disposition: attachment; filename=$file");
              header("Content-Length: $iFileSize" );
              header('Expires: 0');
              header('Cache-Control: must-revalidate');
              header('Pragma: public');
              readfile($sTargetPath.$file);
              $aJsonRespond['success'] = true;
              exit;
          } else {
              $sAddonBackUrl = $sBacklink;
              $sMessage = sprintf('[%04d] File does not exist or no file selected!', __LINE__);
              throw new FormException ($sMessage);
          }
        } catch (FormException $ex) {
//            $admin->print_header();
//            $aJsonRespond['message'] = $e->getMessage();
//            $aJsonRespond['success'] = false;
            $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
            $oApp->print_error ($sErrMsg, $sAddonBackUrl);
            exit;
        }

//    echo the json_respond to the ajax function
//    exit(json_encode($aJsonRespond));
//    require(WB_PATH.'/modules/admin.php');

// Print admin footer
    $admin->print_footer();


