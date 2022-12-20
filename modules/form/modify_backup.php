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
 * @subpackage      modify_backup
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

    $sTargetFieldPath  = $sAddonPath.'data/fields/';
    $sTargetLayoutPath  = $sAddonPath.'data/layouts/';
    $aMessage = [];

    // Only for development for pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'.setDebug');
    // Only for development prevent secure token check,
    $sSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    PreCheck::increaseMemory('512M');
    $iMaxSize = PreCheck::convertToByte('upload_max_filesize');
    $sMaxSize = PreCheck::convertByteToUnit($iMaxSize);
    $UploadMaxFilesize = ini_get('upload_max_filesize');
    $iPostMaxSize = PreCheck::convertToByte('post_max_size');
    $sPostMaxSize = PreCheck::convertByteToUnit($iPostMaxSize);

try {
    // print with or without header
    $admin_header=true; //
    // Workout if the developer wants to show the info banner
    $print_info_banner = ($aRequestVars['infoBanner'] ?? true); // true/false
    // Tells script to update when this page was last updated
    $update_when_modified = false;
    // Include WB admin wrapper script to sanitize page_id and section_id, print SectionInfoLine
    require($sModulesPath.'admin.php');

//  Create new frontend object
//    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {$wb = new \frontend();}
    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();
    $oTrans   = $oReg->getTranslate();
    $sDomain  = $oApp->getDirNamespace(__DIR__);
    $oTrans->enableAddon($sDomain);

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $bBackLink     = ($aRequestVars['save_close'] ?? false);
    $sBacklink     =  $oReg->AcpUrl.'pages/modify.php?page_id='.(int)$page_id.$sSectionIdPrefix;
    $sPagelink     =  $oReg->AcpUrl.'pages/index.php';
    $sAddonBackUrl =  $oReg->AcpUrl.'pages/modify.php?page_id='.(int)$page_id.$sSectionIdPrefix;
    $sAddonBackUrl = ($bBackLink ? $sBacklink : $sAddonBackUrl);
    $sAddonUrl     = $oReg->AppUrl.$sAddonRel;

    $sAddonThemePath= $sAddonPath.'themes/default/';

/*
//$backUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix.$section_id;
    $backUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $backModuleUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackLink = (isset($_POST['save_close']) ? $backUrl : $backModuleUrl).'';
*/

    $sGetOldSecureToken = (SecureTokens::checkFTAN());
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];

//    if ($sLocalDebug && $admin->ami_group_member('1')){}

// Get token id
//    $sSectionIdKey = SecureTokens::getIDKEY($section_id);
//    $section_id = (($oApp->getIdFromRequest('section_id')));
    $sSectionIdKey = $section_id;

    if (is_null($section_id)) {
        $aMessage = sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }

    $action = 'show';
    $action = (isset($aRequestVars['cancel']) ? 'cancel' : $action);
    $action = (isset($aRequestVars['delete_all']) ? 'delete_all' : $action);

      switch ($action):
          case 'delete_all':
              $sqlDeleteAll  = '
              DELETE FROM `'.$oReg->TablePrefix.'mod_form_fields`
              WHERE `section_id` = '.(int)$section_id.'
              ';
              if(!$oFields = $oDb->query($sqlDeleteAll)) {
//                  $admin->print_error($oDb->get_error(), $sAddonBackUrl );
                  $aMessage = sprintf($oDb->get_error() );
                  throw new \Exception ($aMessage);
              } else {
                  $admin->print_success( $oTrans->FORM_MESSAGE_ALL_DELETED.'', $sAddonBackUrl );
              }
              break;
          default:
//              $sSectionIdKey = \bin\SecureTokens::getIDKEY($section_id);
              $aResFieldXML = [];
              $aResFieldXML = [];
              $aResFieldXML['layout'] = '';
              $aResFieldXML['description'] = '';
              $sSelected = ' selected="selected"';
/*
              $sSqlXml = '
              SELECT `section_id`, `layout`, `description` FROM `'.$oReg->TablePrefix.'mod_form_settings`
              WHERE `section_id`='.(int)$section_id.'
              ';
              if (($oResXML = $oDb->query($sSqlXml))){
                  if (!($aResFormsXML = $oResXML->fetchArray())){
                      // error handling
                  }
                  $aResFormsXML['description'] = (!empty($aResFormsXML['description']) ? $aResFormsXML['description'] : 'Description of the form…');
              }
*/
              $sSqlFields = '
              SELECT `se`.`page_id`,`fi`.`section_id`, `fi`.`layout` FROM `'.$oReg->TablePrefix.'mod_form_settings` `se`
              INNER JOIN `'.$oReg->TablePrefix.'mod_form_fields` `fi`
              ON `se`.`section_id` = `fi`.`section_id`
              GROUP BY `fi`.`section_id`
              ORDER BY `fi`.`section_id`
              ';
              if (($oResFieldXML = $oDb->query($sSqlFields))){
                  if (!($aResFieldXML = $oResFieldXML->fetchAll())){
                      // error handling
                  }
              }
              reset ($aResFieldXML);

              $aResXML = [];
              $aPreventDefaults = [];
              $aDisabledLayout = [];
              $sDisabledLayout = '';
              $aResTmpXML = $aResFieldXML;
              foreach ($aResTmpXML as $key => $aResFieldXML){
                  $aResFieldXML['description'] = (!empty($aResFieldXML['description']) ? $aResFieldXML['description'] : 'Description of the field-form…');
                  $aResXML[$aResFieldXML['section_id']] = $aResFieldXML;
                  array_push ($aPreventDefaults, $aResFieldXML['layout']);
              }
              $aPreventDefaults = array_unique($aPreventDefaults);
              $sPreventDefaults = json_encode($aPreventDefaults,JSON_OBJECT_AS_ARRAY);

              if (!isset($aResXML[$section_id])){
                  $aResXML[$section_id]['page_id'] = 0;
                  $aResXML[$section_id]['section_id'] = 0;
                  $aResXML[$section_id]['layout'] = 'none';
                  $aResXML[$section_id]['description'] = 'none';
              }
              $aTplData = [
                  'MODULE_URL' => WB_URL.'/modules/'.$sAddonName,
                  'ADMIN_URL' => ADMIN_URL,
                  'ADMIN_DIR' => ADMIN_DIRECTORY,
                  'PAGE_ID' => $page_id,
                  'SECTION_ID' => $sSectionIdKey,
                  'SECT_ID' => $sSectionIdKey,
                  'FTAN' => $admin->getFTAN(),
                  'FILENAME' => 'FieldMap',
                  'SECTIONID_PREFIX' => $sSectionIdPrefix.$section_id,
                  'MAX_FILE_SIZE' => PRECHECK::convertToByte('upload_max_filesize')*128,
                  'TYPE' => 'export',
              ];

              $tpl = new Template($sAddonThemePath);
              $tpl->set_file('page', 'field_backup.htt');
              $tpl->set_block('page', 'main_block', 'main');
              $tpl->set_block('main_block', 'show_export_block', 'show_export');
              $tpl->set_var($oTrans->getLangArray());
              $tpl->set_var($aTplData);
              $tpl->set_block('main_block', 'show_import_block', 'show_import');
              $tpl->set_block('show_import_block', 'show_input_import_block', 'show_input_import');
              $tpl->set_block('show_import_block', 'show_upload_import_block', 'show_upload_import');

              $aImportFiles = glob($sTargetFieldPath.'*.xml', GLOB_NOSORT);
              sort($aImportFiles,  SORT_NATURAL | SORT_FLAG_CASE );
              if (sizeof($aImportFiles)){
                  $tpl->set_block('show_import_block', 'file_list_block', 'file_list');
                  foreach ($aImportFiles as $sFilename){
                      $sLayoutFile = (basename($sFilename));
                      $sLayoutName = trim($oApp->removeExtension($sLayoutFile));
                      $sSelect = (($aResXML[$section_id]['layout']==$sLayoutName) ? $sSelected : '');
                      // first check disable status and save
                      if (empty($sDisabledLayout) && !empty($sSelect) ){
                          $sDisabledLayout = (in_array($sLayoutName, $aPreventDefaults) ? ' disabled="disabled"' : '');
                      }
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] \n%s\n%s\n%s</div>\n",__LINE__,$sDisabledLayout,$sLayoutName,$sSelect));
                      $tpl->set_var('XML_FILE_SELECTED', $sSelect);
                      $tpl->set_var('IMPORT_FILENAME', ($sLayoutFile));
                      $tpl->set_var('IMPORT_LAYOUT',$sLayoutName);
                      $tpl->parse('file_list', 'file_list_block', true);
                  }
                  $tpl->parse('show_import', 'show_import_block', true);
              } else {
                  $tpl->set_block('show_import', '');
              }
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s %s</div>\n",__LINE__,$sLayoutName,$sDisabledLayout));

              $sql  = 'SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields` '
                    . 'WHERE `section_id` = '.(int)$section_id.' '
                    . '';
              if ($oDb->get_one($sql)==0){
                  $tpl->set_block('show_export', '');
                  $tpl->parse('show_input_import', 'show_input_import_block', true);
                  $tpl->parse('show_upload_import', 'show_upload_import_block', true);
                  $tpl->set_var('TYPE','import');
                  $tpl->set_var('PREVENT_DEFAULT', $sPreventDefaults);//javascript var
                  $tpl->set_var('PREVENT_DELETE_LAYOUT', $sDisabledLayout);
              } else {
                  $tpl->set_var('TYPE','export');
                  $tpl->set_var('XML_LAYOUT',$aResXML[$section_id]['layout']);
                  $tpl->set_var('XML_DESCRIPTION',$aResXML[$section_id]['description']);
                  $tpl->set_var('PREVENT_DEFAULT', $sPreventDefaults);//javascript var
                  $tpl->set_var('PREVENT_DELETE_LAYOUT', $sDisabledLayout);
                  $tpl->parse('show_export', 'show_export_block', true);
              }
              $tpl->parse('main', 'main_block', false);
              $tpl->pparse('output', 'page');
      endswitch;

}catch (\Exception $ex) {
    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
    $admin->print_footer();
