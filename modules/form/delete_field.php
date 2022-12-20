<?php
/**
 *
 * @category        module
 * @package         Form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: delete_field.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/delete_field.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @description
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
//use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
//if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
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
    $sBackLink = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id;

// Get id
    $field_id = intval($oApp->getIdFromRequest('field_id'));
    if (is_null($field_id)) {
     $oApp->print_error($oLang->MESSAGE_GENERIC_SECURITY_ACCESS, $sBackLink);
    }
    $sql  = '
    SELECT `title` FROM `'.$oReg->TablePrefix.'mod_form_fields`
    WHERE `field_id` = '.$oDb->escapeString($field_id).'
    ';
    if ($sTitle = $oDb->get_one($sql)){;}

    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );

// Delete row
    $sql  = '
    DELETE FROM `'.$oReg->TablePrefix.'mod_form_fields`
    WHERE `field_id` = '.$oDb->escapeString($field_id).'
    ';
    $oDb->query($sql);

// Include the ordering class
//require(WB_PATH.'/framework/class.order.php');

// Create new order object an reorder
    $order = new order($oReg->TablePrefix.'mod_form_fields', 'position', 'field_id', 'section_id');

    if(!$order->clean($section_id)) {
        $oApp->print_error($oDb->get_error(), $sBackLink.'#'.$sSectionIdPrefix.$section_id);
    } else {
//        $oLang->enableAddon('modules\\'.basename(__DIR__));
        $oApp->print_success(sprintf($oTrans->MESSAGE_FIELD_DELETED, $sTitle), $sBackLink.'#'.$sSectionIdPrefix.$section_id);
    }

// Print admin footer
$oApp->print_footer();
