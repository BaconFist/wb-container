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
 * @subpackage      field_export
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
if (!function_exists('getFormFieldExport')){
    function getFormFieldExport ()
    {
      global $section_id;
      $sAddonPath   = str_replace(['\\','//'],'/',__DIR__).'/';
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
      try {
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
            $sMessage = '';
            $aMessage = [];
//  create Variables from request
            foreach ($aRequestVars as $index=>$value){
                ${$index} = $value;
            }
            $xml_file = $file;
/* -------------------------------------------------------- */
            $sTargetPath = $sAddonPath.'data/fields/';
            $file  = $oApp->removeExtension($xml_file);
            $title = $oApp->removeExtension($title);
/* --------------------------------------------------------------------- */
            $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
            $sBacklink        = $sAddonUrl.'modify_backup.php?page_id='.$page_id;
            $sAddonBackUrl    = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
/* -------------------------------------------------------- */
            $sGetOldSecureToken = (SecureTokens::checkFTAN() ?? false);
            $aFtan = SecureTokens::getFTAN();
            $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
// already done by wrapper admin
//            $sSectionIdKey = SecureTokens::getIDKEY($section_id);
//            $section_id = (($oApp->getIdFromRequest('section_id')));
            $sSectionIdKey = $section_id;
            $sBacklink       .= '&section_id='.$sSectionIdKey;
/* -------------------------------------------------------- */
            if ($sSecureToken && !$sGetOldSecureToken){
                $sAddonBackUrl = $sBacklink;
                $sMessage = \sprintf("%s",$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
                throw new \Exception ($sMessage);
            }
/* ----------------------------------------------------------------------------- */
            $sFilename    = ParentList::tidyFilename($oApp->StripCodeFromText($title));
            $sOldFilename = ParentList::tidyFilename($oApp->StripCodeFromText($file));
// check for executing getUniqueName()
            $sOldFilename = (empty($sOldFilename) ? $sFilename : $sOldFilename);
            $bCallUnique = ($sFilename == $sOldFilename) && is_readable($sTargetPath.$sFilename.'.xml');
/* ---------------------------------------------------------------------- */
            $sFilename    = (($bCallUnique) ? $oApp->getUniqueName($sTargetPath,$sOldFilename,'*.xml') : $sFilename);
            $sDownloadUrl = '';
            $sDescription = ''.$oApp->StripCodeFromText($aRequestVars['description']);
            $sDescription = !empty($sDescription) ? $sDescription : 'Description of the form…';
            $sBacklink   .= '?page_id='.$page_id.'&section_id='.$sSectionIdKey;
/* ---------------------------------------------------------------------- */
        $sAddonBackUrl = $sBacklink;
        if (!file_exists($sTargetPath) && !make_dir($sTargetPath))
        {
            $sMessage = sprintf("Couldn't create %s",$sAddonName."/data/fields/");
            throw new \Exception ($sMessage );
        } else {
            if (!empty($sFilename)){
                $sLayout    = $sFilename;
                $sFilename .= '.xml'; //'_'.$section_id.
                $sAbsFilename = $sTargetPath.$sFilename;
// Export from table mod_form_fields
                $sql  = '
                SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields`
                WHERE `section_id` = '.(int)$section_id.'
                ';
                if (($oDb->get_one($sql)==0)){
                    $sMessage = \sprintf("Couldn't export, there no existing form fields in section %s",$section_id);
                    throw new \Exception ($sMessage);
                } else {
                    // Select all the fields in the from table
                    $sql  = 'SELECT * FROM `'.$oReg->TablePrefix.'mod_form_fields` '
                          . 'WHERE `section_id` = '.(int)$section_id.' '
                          . 'ORDER BY `position` ASC';
                    if (!$oRes = $oDb->query($sql)) {
                        $sMessage = \sprintf("%s",$oDb->get_error());
                        throw new \Exception ($sMessage);
                    }
                    //fetch array from db
                    $aTmpXml = $aXml = $oRes->fetchAll();
                    //foreach($aTmpXml as $key => $aItem){$aXml = $aItem;}
                    require $sAddonPath.'createFieldXML.php';
                } // no existing form fields
            } // $sFilename
            else {
                $sAddonBackUrl = $sBacklink;
                $sMessage = sprintf("%s",$oTrans->FORM_MESSAGE_FILE_TITLE_VALUE);
                throw new \Exception ($sMessage );
            }
        } // end make_dir
/* -------------------------------------------------------- */
        $oApp->print_success(sprintf($oTrans->FORM_MESSAGE_EXPORT_SUCCESS,$sFilename), $sBacklink);
      }catch (\Exception $ex) {
//        $admin->print_header();
          $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
          $admin->print_error ($sErrMsg, $sAddonBackUrl);
          exit;
      }

    } // end getFormFieldExport
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
    getFormFieldExport();
// Print admin footer
    $admin->print_footer();
// end of file
