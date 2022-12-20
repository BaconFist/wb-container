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
 * @subpackage      save_flied_new
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
use bin\helpers\{PreCheck};
//use vendor\phplib\Template;
use dispatch\Dispatcher;

    $sAddonPath   = str_replace(['\\','//'],'/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}


try {

    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $aMessage = [];

    $oDispatch  = new Dispatcher();
    extract($oDispatch->callAdminWrapperVars(true),EXTR_OVERWRITE);
    require(WB_PATH.'/modules/admin.php');
    $aAddonConfig = [
          'page_id' => $page_id,
          'section_id' => $section_id,
    ];
    $oDispatch->setProperties($aAddonConfig);
    $oDispatch->setProperties(['aRequestVars' => $aRequestVars]);
    extract($oDispatch->getInitializePaths($sAddonPath),EXTR_OVERWRITE); //

    $oApp      = $oReg->getApplication();
    $isAuth    = $oApp->is_authenticated();
    $bBackLink = ($aRequestVars['save_close'] ?? false);

    extract($oDispatch->getBackLinks($sDumpPathname),EXTR_OVERWRITE); //
    $sAddonBackUrl = $sBacklink;

    $sGetOldSecureToken = (SecureTokens::checkFTAN() ?? false);
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];

// check FTAN
    if ($sSecureToken && !$sGetOldSecureToken){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
// Get id
    $field_id = ($admin->getIdFromRequest('field_id') ?? false);
    if ($sSecureToken && $field_id===false){
//    if ($field_id === false){
        $aMessage = \sprintf("%s\n",$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
// link to modify page
    $sAddonBackUrl = WB_URL.'/modules/'.basename(__DIR__).'/modify_field.php?page_id='.$page_id.'&section_id='.$section_id;
    $bBackLink = ($aRequestVars['save_close'] ?? false);
// sanitize Input Requests and store to $_SESSION['form']
    foreach($aRequestVars as $key=>$value){
        $_SESSION['form'][$key] = $value;
    }
// check empty requests
    $title = $admin->StripCodeFromText(($aRequestVars['title'] ?? ''));
    $sLayout = $admin->StripCodeFromText(($aRequestVars['layout'] ?? ''));
    $sDescription = $admin->StripCodeFromText(($aRequestVars['description'] ?? 'Description of the form…'));
    if (empty($title)){
        $aMessage = \sprintf($oTrans->FORM_MESSAGE_GENERIC_FILL_TITLE);
        throw new \Exception ($aMessage);
    }
    $type = $admin->StripCodeFromText(($aRequestVars['type'] ?? ''));
    if (empty($type)){
        $aMessage = \sprintf($oTrans->FORM_MESSAGE_GENERIC_FILL_TYPE);
        throw new \Exception ($aMessage);
    }
// santize only one e-mail field
    $sEMailFilter = $admin->StripCodeFromText(($aRequestVars['type'] ?? 'none'));
    if (($sEMailFilter == 'email') && ($field_id == 0) && $oDispatch->countRowsSql('mod_form_fields','type',$sEMailFilter)){
        $aMessage = \sprintf($oTrans->FORM_MESSAGE_EMAIL_TAKEN);
        throw new \Exception ($aMessage);
    }
    //
    $active     = (int)($aRequestVars['active'] ?? 0);
    $required   = (int)($aRequestVars['required'] ?? 0);
    $value      = $extra = '';
    $list_count = (int)($aRequestVars['list_count'] ?? 0);
// If field type has multiple options, get all values and implode them
    if (is_numeric($list_count)) {
        $values = '';
        for ($i = 1; $i <= $list_count; $i++) {
            $sValue = ($aRequestVars['value'.$i] ?? '');
            if (!empty($sValue)) {
//                $values .= ','.$sValue;
                $sDelimiter = ",";
                $values .= ','.str_replace($sDelimiter,"&#44;",$sValue); //SGML entity
            }
        }
        $value = trim($values,',');
    } else {
        $aMessage = \sprintf($oTrans->FORM_MESSAGE_MODIFIED_FAILED."\n",'in '.$type);
        throw new \Exception ($aMessage);
    }
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,htmlspecialchars($values)));
// prepare sql-statement
    switch($type):
        case 'textfield':
            $value = $admin->StripCodeFromText(($aRequestVars['value'] ?? ''));
            //$value = $admin->StripCodeFromText($admin->get_post('value'));
            $extra = (int)($aRequestVars['length'] ?? 0);
//            $extra = intval($admin->get_post('length'));
            break;
        case 'textarea':
            $value = $admin->StripCodeFromText(($aRequestVars['value'] ?? ''));
            $extra = '';
            break;
        case 'heading':
            $extra = $admin->StripCodeFromText(($aRequestVars['template'] ?? ''));
            $extra = (empty(trim($extra)) ? '{TITLE}{FIELD}' : $extra);
            break;
        case 'select':
            $extra =($aRequestVars['size'] ?? 0).','.($aRequestVars['multiselect'] ?? '' );
//            $extra = rtrim($extra,',');
            break;
        case 'checkbox':
        case 'radio':
            $extra = $admin->StripCodeFromText(($aRequestVars['seperator'] ?? ' '));
            break;
        default:
            $value = '';
            $extra = '';
            break;
    endswitch;
/* -- exit script, commit out until it has finished
    $aMessage = \sprintf($oTrans->FORM_MESSAGE_MODIFIED_FAILED."\n",'Add Field');
    throw new \Exception ($aMessage);
-- */
// add or update field
//            `description`=\''.$database->escapeString($sDescription).'\',
        $sqlBodySet  = '
            `section_id`='.(int)$section_id.',
            `page_id`='.(int)$page_id.',
            `layout`=\''.$database->escapeString($sLayout).'\',
            `title`=\''.$database->escapeString($title).'\',
            `type`=\''.$database->escapeString($type).'\',
            `required`=\''.$database->escapeString($required).'\',
            `value`=\''.$database->escapeString(($value)).'\',
            `extra`=\''.$database->escapeString($extra).'\',
            `active`='.(int)$active.'
            ';
        if (!($msg = $oDispatch->replaceIntoSql('mod_form_fields', 'field_id', $field_id,$sqlBodySet))){
            $aMessage = \sprintf($oTrans->FORM_MESSAGE_MODIFIED_FAILED."\n",'Error '.$msg."\n".$oDb->get_error());
            throw new \Exception ($aMessage);
        }
    $field_id = $oDispatch->getFieldId('field_id');
// create new IdKey
    $sFieldIdKey = SecureTokens::getIDKEY($field_id); //.'&field_id='.$sFieldIdKey
    $sAddonBackUrl = ($bBackLink ? $sBacklink : $sAddonBackUrl.'&field_id='.$sFieldIdKey);
    $admin->print_success(sprintf($oTrans->FORM_MESSAGE_FIELD_SUCCESS, $title.' ('.$field_id.')'), $sAddonBackUrl);

}catch (\Exception $ex) {
    $admin->print_header(null,false);
// create new IdKey
    $sFieldIdKey = SecureTokens::getIDKEY($field_id);
    // $admin->print_header(null,false);
    $sAddonBackUrl = ($bBackLink ? $sBacklink : $sAddonBackUrl.'&field_id='.$sFieldIdKey);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
    $admin->print_footer();