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
 * @subpackage      modify_settings
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

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
/* -------------------------------------------------------- */
    $sLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
    $sSecureToken = (!is_readable($sAddonPath.'.setToken'));
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
    $sqlEOL       = ($sLocalDebug ? "\n" : "");
/* ------------------------------------------------------------------ */
// print with or without header
    $admin_header = true;
// Workout if the developer wants to show the info banner
    $print_info_banner = true; // true/false
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require($sModulesPath.'admin.php');
/* ----------set to deprecated----------------------------- */
// load module language file
    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
/* -------------------------------------------------------- */
    $oDb = $oReg->getDatabase();
    $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
    $ModuleUrl    = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl    = $oReg->AppUrl.$sAddonRel;
    $sTargetPath  = $sAddonPath.'data/layouts/';
    $sDomain      = \basename(\dirname($sCallingScript)).'/'.\basename($sCallingScript);
/* -------------------------------------------------------- */
    $target_section_id = -1;
    if (!ini_get('arg_separator.output')!='&') { \ini_set('arg_separator.output', '&'); }
    $bExcecuteCommand = false;
    if (\is_readable($sModulesPath.'SimpleCommandDispatcher.inc.php')) {require ($sModulesPath.'/SimpleCommandDispatcher.inc.php');}
/* -------------------------------------------------------- */
    $bCanBackup   = ($admin->ami_group_member('1') ||
                    $admin->get_permission('settings') ||
                    $admin->get_permission('admin_tools')); // true false
/* -------------------------------------------------------- */
    $aCaptchaAction = ['all','image','image_iframe','input','text']; // feature settings
    $aCaptchaAction = ['all'];

//    $sQueryStr = $_SERVER['QUERY_STRING'];
    $sQueryStr = $oRequest->getServerVar('QUERY_STRING');
    $sArgSeperator = ini_get('arg_separator.output');
    $aQueryStr = explode($sArgSeperator, $sQueryStr);

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
    if (!is_callable('edit_module_css')) {include(WB_PATH .'/framework/module.functions.php');}

//$MediaTools = \media\ParentList::getInstance();
// load module language file
//$sAddonName = basename(__DIR__);

    $sSelect    = ' selected="selected"';
    $sChecked   = ' checked="checked"';
    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
/* */
    if (!\bin\SecureTokens::checkFTAN() && $sSecureToken) {
        $admin->print_error(sprintf('[%30d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],__LINE__), $sBacklink.'#'.$sSectionIdPrefix.$section_id);
    }

   $emailAdmin = (function () use ( $database, $admin )
   {
        $retval = $admin->get_email();
        if($admin->getUserId()!='1') {
            $sql  = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                  . 'WHERE `user_id`=\'1\' ';
            $retval = $database->get_one($sql);
        }
        return $retval;
    });

    $aGetDbFields = (function($sName, $iSectionId) use ($database) {
        $mRetval = null;
        $sqlField  = 'SELECT `field_id`, `title`, `required` FROM `'.TABLE_PREFIX.'mod_form_fields` '
              . 'WHERE `section_id` = '.(int)$iSectionId.' '
              . '  AND  `type` = \''.$sName.'\' '
              . 'ORDER BY `position` ASC ';
        if ($oFields = $database->query($sqlField)) {
            if (is_null($mRetval = $oFields->fetchAll())){
                $mRetval = false;
            }
        }
        return $mRetval;
    });

    $removeExtension = (function ($sFilename){
        return preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sFilename);
    });
    $getDefaultSql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_form_settings` '
                    . 'WHERE `section_id` = '.(int)$section_id.'';
    if (!($numRow = $database->get_one($getDefaultSql)))
    {
        require 'add.php';
    }

    $aDefaultLayouts = [];
    $sDefaultLayouts = '';
// filename pattern without ext
    $sAddonPath = str_replace(DIRECTORY_SEPARATOR,'/',$sAddonPath).'/';
    $sPattern = '/^.*?([^\/]*?)\.[^\.]*\.[^\.]*$/is';
    $aLayouts = \glob($sAddonPath.'data/layouts/*.xml');
    foreach ($aLayouts as $item){
        $aDefaultLayouts[] = $removeExtension($item);
    }

// Get Settings from DB $aSettings['
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_settings` '
          . 'WHERE `section_id` = '.(int)$section_id.'';
    if ( $oSetting = $database->query($sql)) {
        if (!is_null($aSettings = $oSetting->fetchRow(MYSQLI_ASSOC))){
            $aSettings['email_to'] = ( ($aSettings['email_to'] != '') ? $aSettings['email_to'] : $emailAdmin());
            $aSettings['email_subject'] = ( ($aSettings['email_subject']  != '') ? $aSettings['email_subject'] : '' );
            $aSettings['success_email_subject'] = ($aSettings['success_email_subject']  != '') ? $aSettings['success_email_subject'] : '';
            $aSettings['success_email_from'] = $admin->add_slashes(SERVER_EMAIL);
            $aSettings['success_email_fromname'] = ($aSettings['success_email_fromname'] != '' ? $aSettings['success_email_fromname'] : WBMAILER_DEFAULT_SENDERNAME);
//            $aSettings['success_email_subject'] = ($aSettings['success_email_subject']  != '') ? $aSettings['success_email_subject'] : '';
            $sDivider          = ($aSettings['divider'] ?? "");
            $sPlaceHolder      = ($aSettings['title_placeholder'] ?? "");
            $sFrontendCss      = ($aSettings['frontend_css'] ?? "");
            $target_section_id = ($aSettings['data_protection_link'] ?? "");
            $target_page_id    = ($aSettings['success_page'] ?? "");
        }
    } else {
        $admin->print_error($database->get_error(), $sBacklink);
    }
// Set raw html <'s and >'s to be replace by friendly html code
    $raw = ['<', '>'];
    $friendly = ['&lt;', '&gt;'];

/* ------------------------------------------------------------------------------ */
    $sPlaceHolder = (((bool)$aSettings['title_placeholder'] == true) ? $sChecked : 'not::checked');
    $sFrontendCss = (((bool)$aSettings['frontend_css'] == true) ? $sChecked : '');
    $aResFormsXML = [];
    $aResFormsXML['layout'] = '';
    $aResFormsXML['description'] = '';
    $sSelected = ' selected="selected"';
    $sSqlForm ='
        SELECT `layout` FROM `'.$oReg->TablePrefix.'mod_form_settings`
        GROUP BY `section_id`
        ORDER BY `section_id`
    ';
    if (($oResFieldXML = $oDb->query($sSqlForm))){
        if (!($aResFormsXML = $oResFieldXML->fetchAll())){
            // error handling
        }
    }

    if (!$oDb->is_error()){
        reset ($aResFormsXML);
//        $aResTmpXML = $aResFormsXML;
        $aPreventDefaults = [null,'Layout_Simple-DIV'];
//        $aPreventDefaults = [null,'Layout_Default_Table','Layout_Extended_Table','Layout_Modern_Table','Layout_Simple-DIV','Layout_Simple-DIV_Placeholder'];
        foreach ($aResFormsXML as $key => $aResFieldXML){
            array_push ($aPreventDefaults, $aResFieldXML['layout']);
        }
        $aPreventDefaults = array_unique($aPreventDefaults);
        $sPreventDefaults = json_encode($aPreventDefaults,JSON_OBJECT_AS_ARRAY);
    } else {
        $admin->print_error(sprintf("[%30d] %s\n%s",__LINE__,$oDb->get_error(),$sSqlForm), $sBacklink.'#'.$sSectionIdPrefix.$section_id);
    }
/* ------------------------------------------------------------------------------ */
    $sLayoutTitle = '';
    $cLayoutTitle = '';
    $sLayoutDescription = '';
// overwrite settings
    if (isset($aRequestVars['install_layout'])) {
        if (is_readable(__DIR__.'/data/layouts/Layout_Default.inc.php')){
          require (__DIR__.'/data/layouts/Layout_Default.inc.php');
        }
        foreach ($aSettings as $sKey=>$sValue){
          if (!isset(${$sKey})) {continue;}
          $aSettings[$sKey] = ${$sKey};
        }
    } else {
      $sLayoutDescription = $aSettings['description'];
      $sLayout = $aSettings['layout'];
      $sAbsFilename = __DIR__.'/data/layouts/'.$sLayout.'.xml';
      if (is_readable($sAbsFilename)&& ($oXml = simplexml_load_file($sAbsFilename)))
      {
          $sLayoutTitle = $oXml->title;
          $sLayoutDescription = $oXml->description;
      }
/*
        $sql  = '
        SELECT `layout`,`description` FROM `'.$oReg->TablePrefix.'mod_form_fields`
        WHERE `section_id` = '.(int)$section_id.'
          AND `layout` LIKE \''.$oDb->escapeString($sLayout).'\'
        ';
*/
    }
    $cLayoutTitle = (trim($sLayoutTitle) ? ' - '.$sLayoutTitle : '');
    $aFtan  = SecureTokens::getFTAN();
    $sFtan = $aFtan['name'].'='.$aFtan['value'];

    $aImportFiles = glob($sTargetPath.'*.xml', GLOB_NOSORT);
    sort($aImportFiles);

    $aSelectPages = [];
    $aSelectPages = ParentList::menulink_make_tree(0, $aSelectPages);

    if ($print_info_banner) {
?></div><!--end class="block-outer" -->
<?php }
    // include the button to edit the optional module CSS files
    $isTpldir = (\basename(trim($sAddonTemplateRel,'/')) === $oReg->Template);
?>
    <article id="settings" class="form-block w3-padding w3-bar w3-row block-outer">
      <h2 style="display: none;">&nbsp;</h2>
      <div class="w3-container w3-bar-item">
        <h2><?php echo $MOD_FORM['SETTINGS']; ?></h2>
      </div>
      <div class="w3-container w3-bar-item w3-margin-top w3-center" style="padding: 0.31em 10px!important;">
          <form action="<?php echo $ModuleUrl; ?>modify_settings.php" method="get" >
              <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
              <input type="hidden" name="section_id" value="<?php echo $section_id; ?>"/>
              <input type="hidden" name="<?php echo $aFtan['name'];?>" value="<?php echo $aFtan['value'];?>"/>
              <input type="submit" name="install_layout" value="<?php echo $MOD_FORM['LOAD_LAYOUT']; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" />
          </form>
      </div>
<?php if ($isTpldir) {?>
      <div class="w3-container w3-bar-item w3-margin-top w3-center " style="padding: 0.31em 10px!important;">
          <form action="<?= $ModuleUrl; ?>modifyTemplates.php" method="post" >
              <input type="hidden" value="show" name="action"/>
              <input type="hidden" value="<?= $page_id; ?>" name="page_id"/>
              <input type="hidden" value="<?= $section_id; ?>" name="section_id"/>
              <input type="hidden" value="<?= $aFtan['value'];?>" name="<?php echo $aFtan['name'];?>"/>
              <input type="submit" name="edit_tpl" value="<?= $MOD_FORM['EDIT_TPL']; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" />
          </form>
      </div>
<?php } else { ?>
      <div class="w3-container w3-bar-item w3-margin-top w3-center " style="padding: 0.31em 10px!important;width: 13%;">&nbsp;</div>
<?php } ?>
      <div class="w3-container w3-bar-item w3-margin-top w3-center" style="padding: 0.31em 10px!important;">
          <input id="topcancel" class="w3-btn w3-btn-default w3-blue-wb w3-hover-red w3--medium w3-btn-min-width" type="button" value="<?= $TEXT['CLOSE']; ?>" onclick="window.location='<?php echo $sBacklink.'#'.$sSectionIdPrefix.$section_id; ?>';" style="min-width: 10.25em;" />
      </div>

      <div class="w3-container w3-bar-item w3-margin-top w3-center" style="padding: 0.31em 10px!important;">
        <div class="w3-dropdown-hover">
          <button class="w3-btn w3-btn-default w3-blue-wb w3-hover-text-orange w3--medium w3-btn-min-width"><?= $TEXT['GO_TO'];?>&nbsp;&nbsp;<i class="fa fa-arrow-down w3-padding-4">&nbsp;</i></button>
          <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="#general-setting" class="w3-bar-item w3-button w3-nowrap"><?= $HEADING['GENERAL_SETTINGS']; ?></a>
            <a href="#layout-setting" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT_SETTINGS']; ?></a>
            <a href="#layout-output" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT']; ?></a>
            <a href="#email-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_RECIPIENT']; ?></a>
            <a href="#email-confirmation" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_SENDER']; ?></a>
            <a href="#dsgvo-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['DSGVO'].' '.$MOD_FORM['CONFIRM']; ?></a>
            <a href="#save-settings" class="w3-bar-item w3-button w3-nowrap"><?= $MOD_FORM['SAVE_SETTINGS']; ?></a>
          </div>
        </div>
      </div>

      <div class="w3-container w3-bar-item w3-margin-top w3-center" style="padding: 0.31em 10px!important;">
<?php
    if (function_exists('edit_module_css')){edit_module_css($sAddonName);}
?>
      </div>
  </article><!--end class="header" -->
  <article class="form-block w3-container w3-margin w3--medium">
      <h2 style="display: none;">&nbsp;</h2>
      <form name="edit" action="<?php echo $ModuleUrl; ?>save_settings.php" method="post">
          <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
          <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
          <input type="hidden" name="success_email_to" value="" />
          <input type="hidden" name="cmd" value="" />
          <input type="hidden" name="<?php echo $aFtan['name']; ?>" value="<?php echo $aFtan['value']; ?>" />
      <div id="general-setting" class="block-outer">
        <table class="form w3-table w3-border-0">
            <caption class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?= $HEADING['GENERAL_SETTINGS']; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
            <tbody>
            <tr>
                <th class="frm-setting_name w3-cell-middle"><?php echo $TEXT['CAPTCHA_VERIFICATION']; ?>:</th>
                <td class="w3-padding">
                  <div class="toggle-buttons together">
                      <input data-off="#5a9900" type="radio" id="use_captcha_true" name="use_captcha" value="1"<?php echo (($aSettings['use_captcha'] == true) ? $sChecked : '');?> />
                      <label class="w3-btn w3-border-0 w3-blue-wb w3-hover-green w3--medium" for="use_captcha_true"><?php echo $TEXT['ENABLED']; ?></label>
                      <input data-off="#c32e04" type="radio" id="use_captcha_false" name="use_captcha" value="0"<?php echo (($aSettings['use_captcha'] == false) ? $sChecked : '');  ?> />
                      <label class="w3-btn  w3-border-0 w3-blue-wb w3-hover-red w3--medium" for="use_captcha_false"><?php echo $TEXT['DISABLED']; ?></label>

                      <span id="captcha_auth" <?php echo ($aSettings['use_captcha'] ? '' : 'style="display: none"'); ?> >
                      <input style="margin-right: 16px;" class="switch w3-border-grey w3-nowrap w3-border-grey w3-margin-left" type="checkbox" name="use_captcha_auth" id="use_captcha_auth" value="1" <?php echo (($aSettings['use_captcha_auth'] == true) ? $sChecked : ''); ?>/>
                      <span class="slider"><span style="display: none;">&nbsp;</span></span>
                      <label for="use_captcha_auth" class="tooltip w3--medium w3-border-0 w3-validate" title="<?php echo $TEXT['USE_CAPTCHA_AUTH']; ?>"><span class="slider"><i><?php echo $TEXT['USE_CAPTCHA_AUTH']; ?></i></span></label><br />
                      </span>

                  </div>
                </td>
            </tr>

            <tr id="captcha-info" <?php echo ($aSettings['use_captcha'] ? '' : 'style="display: none"'); ?> >
                <td colspan="2">
                   <div class="w3-panel w3-leftbar w3-light-grey" style="padding-bottom: 1.25em;">
                   <h5><?php echo $MOD_FORM['CAPTCHA_PLACEHOLDER'];?></h5>
                    <code class="w3-codespan">
                    {CALL_CAPTCHA}
                    </code>
                  </div>
                 </td>
            </tr>
<?php ?>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $FORM_MESSAGE['CAPTCHA_STYLE']; ?>:</label></th>
                <td>
                    <input class="w3-input" style="width: 98%;" type="text" name="captcha_style" value="<?php echo str_replace($raw, $friendly, ($aSettings['captcha_style'])); ?>" />
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $FORM_MESSAGE['CAPTCHA_ACTION']; ?>:</label></th>
                <td>
                    <select class="w3-select" style="width: 98%;" name="captcha_action">
<?php
                    foreach ($aCaptchaAction as $sAction) {
                      $selected = (($sAction==$aSettings['captcha_action'])?' selected="selected"':'');
?>
                        <option value="<?php echo $sAction;?>"<?php echo $selected;?>><?php echo $sAction.' => '.$FORM_MESSAGE[strtoupper($sAction)];?></option>
<?php
                    }?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name">
               <label class="frm-setting_name"><?php echo $TEXT['MAX_SUBMISSIONS_PER_HOUR']; ?>:</label>
                </th>
                <td>
                    <div class="w3-row">
                        <div class="w3-col" style="width:50px;">
                            <input class="w3-check" type="text" name="max_submissions" style="width: 45px;" value="<?php echo str_replace($raw, $friendly, ($aSettings['max_submissions'])); ?>" />
                        </div>
                        <div class="w3-col " style="width:120px;">
                            <span class="w3-left-align"><?php echo $MOD_FORM['SPAM_PROTECT'];?></span>
                        </div>
                        <div class="w3-col" style="width:50px;">
                            <input class="w3-check" type="text" name="spam_submissions" style="width: 45px;" value="<?php echo str_replace($raw, $friendly, ($aSettings['spam_submissions'])); ?>" />
                        </div>
                        <div class="w3-col " style="width:15%">
                            <span class="w3-left-align"><?php echo $MOD_FORM['MINUTES'];?></span>
                        </div>
                        <div class="w3-col" style="width:30%">
                            <span></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['SUBMISSIONS_STORED_IN_DATABASE']; ?>:</label></th>
                <td>

                    <div class="w3-row">
                        <div class="w3-col" style="width:50px;">
                            <input class="w3-check" type="text" name="stored_submissions" style="width: 45px;" value="<?php echo str_replace($raw, $friendly, ($aSettings['stored_submissions'])); ?>" />
                        </div>
                        <div class="w3-col " style="width:120px;">
                            <span style="margin-left: 0.9em;"><?php echo $MOD_FORM['DELETE_SUBS_AFTER'];?></span>
                        </div>
                        <div class="w3-col" style="width:50px;">
                            <input id="delete_submissions" class="w3-check" type="test" name="delete_submissions" style="width: 45px;" value="<?php echo str_replace($raw, $friendly, ($aSettings['delete_submissions'])); ?>" />
                        </div>
                        <div class="w3-col " style="width:120px;">
                            <span><?php echo $MOD_FORM['DAYS'];?></span>
                        </div>
                            <label class="w3-hide"><a href="https://www.finanz-tools.de/tageszaehler" target="_blank" rel="nofollower" ><?php echo $MOD_FORM['DAY_CONUTER'];?></a></label>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['SUBMISSIONS_PERPAGE']; ?>:</label></th>
                <td>
                    <div class="w3-row">
                        <div class="w3-col" style="width:50px;">
                        <input class="w3-check" type="text" name="perpage_submissions" style="width: 45px;" value="<?php echo str_replace($raw, $friendly, ($aSettings['perpage_submissions'])); ?>" />
                        </div>
                        <div class="w3-col " style="width:120px;">
                            <span><?php echo $TEXT['PERPAGE'];?></span>
                        </div>
                    </div>
                </td>
            </tr>

                <tr class="w3-hide">
                    <th class="frm-setting_name w3-right-align">
                        <label class="frm-setting_name"><?php echo $MOD_FORM['REPLACE_EMAIL_SUBJECT']; ?>:</label>
                    </th>
                    <td>
                        <select class="w3-select" name="email_subject" style="width: 98%;">
                            <option value="" ><?php echo $TEXT['NONE']; ?></option>
<?php
                        $subject_email = str_replace($raw, $friendly, ($aSettings['subject_email']));
                        $aFields = $aGetDbFields('subject',$section_id);
                        foreach ($aFields as $field){
                            $required = ($field['required'] ? ' (required)' : '');
?>
                            <option value="field<?php echo $field['field_id']; ?>"<?php if ($subject_email == 'field'.$field['field_id']) { echo ' selected'; $selected = true; } ?> >
                                <?php echo $TEXT['FIELD'].': '.$field['title'].$required; ?>
                            </option>
<?php
                                }
?>
                        </select>
                    </td>
                </tr>

            <tr>
                <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?php echo $TEXT['SUCCESS'].' '.$TEXT['PAGE']; ?>:</label></th>
                <td class="frm-newsection">
                        <div class="w3-row" style="width: 98%;">
                            <div class="input-container">
                            <i class="fa fa-search icon"><span style="display: none;">&nbsp;</span></i>
                            <input class="input-field" placeholder="Search" id="pageInput" onkeyup="pageSelect(this)" type="text" value=""/>
                            </div>
                        </div>
                    <select id="page" size="4" class="w3-select" name="success_page" style="width: 98%;min-height: 10em;">
<?php
                  $sSelected = (($target_page_id==0) || ($target_page_id==-1) ? ' selected="selected"' : '');
?>
                    <option class="level" value="-1"<?php echo $sSelected;?>><?php echo $TEXT['NONE']; ?></option>
<?php
        foreach( $aSelectPages as $iKey=> $aValue ) {
            $sPrefix = str_repeat(' -- ', $aValue['level']);
            $sSelected = (($iKey==$target_page_id) ? ' selected="selected"' : '');
?>
                            <option data-key="<?= $iKey;?>" class="level<?php echo $aValue['level'];?>" value="<?php echo $iKey;?>" <?php echo $sSelected;?>><?php echo $sPrefix.$aValue['menu_title'];?></option>
<?php  }?>
                        </select>
                    </td>
                </tr>
            <tr id="divider-info" >
                <td colspan="2">
                   <div class="w3-panel w3-leftbar w3-light-grey" style="padding-bottom: 1.25em;">
                   <h5><?php echo $MOD_FORM['DIVIDER_SEPERATOR'];?></h5>
                  </div>
                 </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="w3-row">
                        <div class="w3-col m4 l3 frm-setting_name">
                            <label><?php echo $TEXT['DIVIDER']; ?>:</label>
                        </div>
                        <div class="w3-col m8 l9">
                            <input class="w3-input" type="text" name="divider" style="width: 128px;" value="<?= $sDivider; ?>" />
                            <label></label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="w3-row">
                        <div class="w3-col m4 l3 frm-setting_name">
                            <label><?php echo $TEXT['PLACEHOLDER']; ?>:</label>
                        </div>
                        <div class="w3-col m8 l9">
                              <input style="margin-right: 8px;" class="switch w3-border-grey w3-nowrap w3-border-grey" type="checkbox" name="title_placeholder" id="use_title_placeholder" value="1"<?php echo $sPlaceHolder; ?> />
                              <label for="use_title_placeholder" class="tooltip w3--medium w3-border-0 w3-validate" title="">
                                  <span class="span-block"><?= $MOD_FORM['PLACEHOLDER']; ?></i></span>
                              </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="w3-row">
                        <div class="w3-col m4 l3 frm-setting_name">
                            <label><?php echo $TEXT['FORM_REQUIRED']; ?>:</label>
                        </div>
                        <div class="w3-col m8 l9">
                              <input style="margin-right: 8px;" class="switch w3-border-grey w3-nowrap w3-border-grey" type="checkbox" name="form_required" id="use_form_required" value="1" <?php echo (isset($aSettings['form_required']) && ($aSettings['form_required'] == true) ? $sChecked : ''); ?>/>
                              <label for="use_form_required" class="tooltip w3--medium w3-border-0 w3-validate" title="">
                                  <span class="span-block"><?= $MOD_FORM['REQUIRED']; ?></i></span>
                              </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="w3-row">
                        <div class="w3-col m4 l3 frm-setting_name">
                            <label><?php echo $TEXT['FORM_FRONTEND_CSS']; ?>:</label>
                        </div>
                        <div class="w3-col m8 l9">
                              <input style="margin-right: 8px;" class="switch w3-border-grey w3-nowrap w3-border-grey" type="checkbox" name="frontend_css" id="use_frontend_css" value="1" <?php echo $sFrontendCss; ?>/>
                              <label for="use_frontend_css" class="tooltip w3--medium w3-border-0 w3-validate" title="">
                                  <span class="span-block"><?= $MOD_FORM['CSS_REQUIRED']; ?></i></span>
                              </label>
                        </div>
                    </div>
                </td>

            </tr>

            <tr>
                <td colspan="2">&#160;</td>
            </tr>

            </tbody>
        </table>
    </div>

      <div class="w3-container w3-cell w3-mobile">
           <input class="w3-btn w3--medium w3-blue-wb w3-hover-green" name="save" type="submit" value="<?= $TEXT['SAVE']; ?>" style="min-width: 10.25em;" />
      </div>
      <div class="w3-container w3-cell w3-mobile">
          <input class="w3-btn w3--medium  w3-blue-wb w3-hover-green" name="save_pagetree" type="submit" value="<?= $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" style="min-width: 10.25em;"/>
      </div>
      <div class="w3-container w3-cell w3-mobile">
          <input class="w3-btn w3--medium url-cancel btn-size w3-blue-wb w3-hover-red" type="button" value="<?= $TEXT['CLOSE']; ?>" onclick="window.location='<?php echo $sBacklink.'#'.$sSectionIdPrefix.$section_id; ?>';" style="min-width: 10.25em;" />
      </div>

      <div class="w3-container w3-cell w3-margin-top w3--center w3-mobile" style="padding: 0.31em 10px!important;">
        <div class="w3-dropdown-hover">
          <button class="w3-btn w3-btn-default w3-blue-wb w3-hover-text-orange w3--medium w3-btn-min-width"><?= $TEXT['GO_TO'];?>&nbsp;&nbsp;<i class="fa fa-arrow-down w3-padding-4">&nbsp;</i></button>
          <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="#general-setting" class="w3-bar-item w3-button w3-nowrap"><?= $HEADING['GENERAL_SETTINGS']; ?></a>
            <a href="#layout-setting" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT_SETTINGS']; ?></a>
            <a href="#layout-output" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT']; ?></a>
            <a href="#email-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_RECIPIENT']; ?></a>
            <a href="#email-confirmation" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_SENDER']; ?></a>
            <a href="#dsgvo-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['DSGVO'].' '.$MOD_FORM['CONFIRM']; ?></a>
            <a href="#save-settings" class="w3-bar-item w3-button w3-nowrap"><?= $MOD_FORM['SAVE_SETTINGS']; ?></a>
          </div>
        </div>
      </div>

<?php if ($bCanBackup) {;?>
    <div id="layout-setting" class="block-outer w3-margin-top">
        <table class="form w3-table">
              <caption class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?= $FORM_MESSAGE['LAYOUT_SETTINGS']; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
<?php
          if (sizeof($aImportFiles)){
?>
            <tr>
                <td colspan="2">
                   <div class="w3-panel w3-leftbar w3-light-grey">
                    <p>
                       <i class="frm-border-0">
                          <b class="w3-text-red w3-xlarge"></b>
                          <?= $MOD_FORM['IMPORT_LAYOUT']; ?>
                       </i>
                   </p>
                  </div>
                 </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $FORM_MESSAGE['LAYOUT_TITLE']; ?></label></th>
                <td>
                    <div class="w3-row">
                        <div class="w3-col m12 w3-nav-bar">
                            <div class="w3-col m4 w3-padding-2">
                                <select id="layout" class="w3-select" style="vertical-align: top;" name="file">
                                    <option value=""><?php echo $FORM_MESSAGE['TEXT_SELECT_BOX'] ?></option>
<?php
    $sDisabledLayout = (\in_array($aSettings['layout'], $aPreventDefaults) ? ' disabled="disabled"' : '');

    foreach ($aImportFiles  as $sFilename) {
        $sSelected = (basename($sFilename)==$aSettings['layout'].'.xml') ? ' selected="selected"' : '';
?>
                                   <option value="<?php print basename($sFilename); ?>"<?php echo $sSelected; ?> ><?php print $removeExtension(basename($sFilename)); ?></option>
<?php } ?>
                                </select>
                            </div>
                            <div class="w3-col m2 w3-center w3-padding-0">
                                <button type="submit" class="w3-button w3-blue-wb w3-round w3-hover-green w3-pointer" name="import" formaction="<?php echo $sAddonUrl;?>/layout_import.php" ><?php echo $TEXT['IMPORT'];?></button>
                            </div>
                            <div class="w3-col m2 w3-center w3-padding-0">
                                <button id="download_xml" class="w3-button w3-blue-wb w3-round w3-hover-green w3-pointer" formenctype="application/xhtml+xml" formaction="<?php echo $sAddonUrl;?>/layout_download.php"><?php echo $FORM_MESSAGE ['DOWNLOAD'];?></button>
                            </div>
                            <div class="w3-col m2 w3-center w3-padding-0">
                                <input id="delete_layout" class="w3-button w3-blue-wb w3-round w3-hover-red w3-pointer" name="delete" type="submit" formaction="<?php echo $sAddonUrl;?>/layout_import.php" value="<?php echo $TEXT['DELETE'];?>"<?php echo $sDisabledLayout;?> />
                            </div>
                            <div class="w3-col m2 w3-center w3-padding-0">
                                <input class="w3-button w3-blue-wb w3-round w3-hover-red w3-pointer" type="button" value="<?php echo $TEXT['BACK'];?>" onclick="window.location.href='<?php echo $sBacklink.'#'.$sSectionIdPrefix.$section_id;?>'" />
                            </div>
                        </div>
                  </div>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $FORM_MESSAGE['LAYOUT_TITLE_NEW']; ?>:</label></th>
                <td>
                    <div class="w3-row">
                        <div class="w3-col m12 w3-nav-bar">
                            <div class="w3-col m4 w3-padding-0">
                                <input id="LayoutTitle" class="w3-input w3-border" type="text" name="title" value="<?php echo $sLayoutTitle;?>" />
                            </div>
                            <div class="w3-col m4 w3-center w3-padding-0">
                                <button type="submit" class="w3-button w3-blue-wb w3-round w3-btn-min-width w3-hover-green w3-pointer" name="export" formaction="<?php echo $sAddonUrl;?>/layout_export.php" ><?php echo $TEXT['EXPORT'];?></button>
                            </div>
                        </div>
                  </div>
                </td>
            </tr>
            <tr>
                <td class="frm-setting_name"><label class="frm-setting_name"><?= $FORM_MESSAGE['LAYOUT_DESCRIPTION']; ?>:</label></td>
                <td>
                    <div class="w3-col m12 w3-nav-bar">
                        <div class="w3-col m12 w3-padding-2">
                          <textarea id="LayoutDescription" class="w3-textarea w3-border w3-mobile" name="description" style="max-width: 98%; height: 120px;"><?php echo $sLayoutDescription;?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">&#160;</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div id="layout-output" class="block-outer w3-margin-top">
        <table class="form w3-table">
              <caption id="cLayoutTitle" class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?= $FORM_MESSAGE['LAYOUT'].$cLayoutTitle; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
            <tbody>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['HEADER']; ?>:</label></th>
                <td>
                    <textarea class="w3-textarea w3-border" name="header" cols="80" rows="3" style="max-width: 98%;"><?php echo ($aSettings['header']); ?></textarea>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['FIELD'].' '.$TEXT['LOOP']; ?>:</label></th>
                <td>
                    <textarea class="w3-textarea w3-border" name="field_loop" cols="80" rows="5" style="max-width: 98%;"><?php echo ($aSettings['field_loop']); ?></textarea>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['EXTRA']; ?>:</label></th>
                <td>
                    <textarea class="w3-textarea w3-border" name="extra" cols="80" rows="10" style="max-width: 98%;"><?php echo str_replace($raw, $friendly, ($aSettings['extra'])); ?></textarea>
                </td>
            </tr>
            <tr>
                <th class="frm-setting_name"><label class="frm-setting_name"><?= $TEXT['FOOTER']; ?>:</label></th>
                <td>
                    <textarea class="w3-textarea w3-border" name="footer" cols="80" rows="7" style="max-width: 98%;"><?php echo str_replace($raw, $friendly, ($aSettings['footer'])); ?></textarea>
                </td>
            </tr>
              <tr><td colspan="2">&#160;</td></tr>
            </tbody>
        </table>
        </div>
        <div id="email-setting" class="form block-outer w3-margin-top">
    <!-- E-Mail Optionen -->
          <table title="<?= $TEXT['EMAIL_RECIPIENT']; ?>"  class="form frm-table" style="margin-top: 3px;">
              <caption class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?= $TEXT['EMAIL_RECIPIENT']; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
              <tbody>
              <tr>
                  <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $MOD_FORM['EMAIL_RECIPIENT']; ?>:</label></th>
                  <td>
                      <input class="w3-input w3-border" type="text" name="email_to" style="width: 98%;" maxlength="255" value="<?php echo str_replace($raw, $friendly, ($aSettings['email_to'])); ?>" />
                  </td>
              </tr>
              <tr>
                  <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['DISPLAY_NAME']; ?>:</label></th>
                  <td>
                      <input class="w3-input w3-border" type="text" name="email_fromname" id="email_fromname" style="width: 98%;" maxlength="255" value="<?php  echo $aSettings['email_fromname'];  ?>" />
                  </td>
              </tr>
              <tr>
                  <th class="frm-setting_name"><label class="frm-setting_name"><?php echo $TEXT['SUBJECT']; ?>:</label></th>
                  <td>
                      <input class="w3-input w3-border" type="text" name="email_subject" style="width: 98%;" maxlength="255" value="<?php echo str_replace($raw, $friendly, ($aSettings['email_subject'])); ?>" />
                  </td>
              </tr>
              <tr><td colspan="2">&nbsp;</td></tr>
              </tbody>
          </table>
        </div>
<?php }
     $success_email_to = str_replace($raw, $friendly, ($aSettings['success_email_to']));
     $aFields = $aGetDbFields('email',$section_id);
?>
        <div id="email-confirmation" class="form block-outer w3-margin-top">
    <!-- Erfolgreich Optionen -->
            <table class="form w3-table" title="<?= $TEXT['EMAIL_SENDER']; ?>" style="margin-top: 3px;">
                <caption class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?php echo $TEXT['EMAIL_SENDER']; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
                <tbody>
                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?= $MOD_FORM['WARNING']; ?>:</label></th>
                    <td><p class="frm-warning w3-container w3-section w3-pale-red w3-leftbar w3-border-red w3-hover-border-green" style="width: 98%;"><?php echo  $MOD_FORM['RECIPIENT'] ?><br /><?php echo $MOD_FORM['SPAM']; ?> </p>   </td>
                </tr>
                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?= $MOD_FORM['REPLY_TO']; ?>:</label></th>
                    <td>
                        <select class="w3-select" name="success_email_to" style="width: 98%;">
                            <option value="" ><?php echo $TEXT['NONE']; ?></option>
<?php
                        foreach ($aFields as $field){
                            $required  = ($field['required'] ? ' (required)' : '');
                            $sSelected = (($success_email_to == 'field'.$field['field_id']) ? ' selected="selected"' : '');
?>
                            <option value="field<?= $field['field_id']; ?>"<?= $selected ?> >
                                <?= $TEXT['FIELD'].': '.$field['title'].$required; ?>
                            </option>
<?php
                                }
?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?= $MOD_FORM['CONFIRM']; ?>:</label></th>
                    <td>
                    <input class="w3-check w3-margin-left" type="checkbox" name="prevent_user_confirmation" id="prevent_user_confirmation" value="1"<?php if ($aSettings['prevent_user_confirmation'] == true) { echo $sChecked; } ?> />
                    <label for="prevent_user_confirmation"><?= $TEXT['PREVENT_USER_CONFIRMATION']; ?></label>
                    </td>
                </tr>
                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?= $TEXT['DISPLAY_NAME']; ?>:</label></th>
                    <td>
                        <?php $aSettings['success_email_fromname'] = ($aSettings['success_email_fromname'] != '' ? $aSettings['success_email_fromname'] : WBMAILER_DEFAULT_SENDERNAME); ?>
                        <input class="w3-input w3-border" type="text" name="success_email_fromname" style="width: 98%;" maxlength="255" value="<?php echo str_replace($raw, $friendly, ($aSettings['success_email_fromname'])); ?>" />
                    </td>
                </tr>
                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?= $TEXT['SUBJECT']; ?>:</label></th>
                    <td>
                        <input class="w3-input w3-border" type="text" name="success_email_subject" style="width: 98%;" maxlength="255" value="<?php echo str_replace($raw, $friendly, ($aSettings['success_email_subject'])); ?>" />
                    </td>
                </tr>
                <tr>
                    <th class="frm-setting_name w3-right-align"><label class="frm-setting_name"><?php echo $TEXT['MESSAGE']; ?>:</label></th>
                    <td>
                        <textarea class="w3-textarea w3-border" name="success_email_text" cols="80" rows="1" style="max-width: 98%; height: 80px;"><?php echo str_replace($raw, $friendly, ($aSettings['success_email_text'])); ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="dsgvo-setting" class="form block-outer w3-margin-top">
<?php
//    $aSelectSections = [];
    $aSelectSections = ParentList::build_sectionlist(0, $page_id, $aSelectSections);
?>
    <!-- DSGVO -->
            <table class="form w3-table" title="<?= $TEXT['DSGVO'].' '.$MOD_FORM['CONFIRM']; ?>" style="margin-top: 3px;">
                <caption class="form-header w3-header-blue-wb"><a href="#settings" class="w3-text-white w3-hover-text-orange"><?= $TEXT['DSGVO'].' '.$MOD_FORM['CONFIRM']; ?><i class="fa fa-arrow-up w3-padding">&nbsp;</i></a></caption>
                <tbody style="margin-bottom: 0.925em;">
                  <tr>
                     <th class="setting_name w3-cell-middle"><label class="frm-setting_name"><?php echo $TEXT['DSGVO']; ?></label></th>
                     <td class="w3-cell-middle">
                        <div class="toggle-buttons together">
                            <input data-off="#5a9900" onclick="w3.show('#data-protection')" type="radio" id="use_data_protection_true" name="use_data_protection" value="1"<?php echo (($aSettings['use_data_protection'] == true) ? $sChecked : '');?> />
                            <label class="w3-btn w3-border-0 w3-blue-wb w3-hover-green w3--medium" for="use_data_protection_true"><?php echo $TEXT['ENABLED']; ?></label>
                            <input data-off="#c32e04" onclick="w3.hide('#data-protection')" type="radio" id="use_data_protection_false" name="use_data_protection" value="0"<?php echo (($aSettings['use_data_protection'] == false) ? $sChecked : '');  ?> />
                            <label class="w3-btn w3-border-0 w3-blue-wb w3-hover-red w3--medium" for="use_data_protection_false"><?php echo $TEXT['DISABLED']; ?></label>
                            <span id="use_data_protection" >
                            <input style="margin-right: 16px;" class="switch w3-border-gray w3-nowrap w3-margin-left" type="checkbox" name="info_dsgvo_in_mail" id="info_dsgvo_in_mail" value="1" <?php echo (($aSettings['info_dsgvo_in_mail'] == true) ? $sChecked : ''); ?>/>
                            <span class="slider"><span style="display: none;">&nbsp;</span></span>
                            <label for="info_dsgvo_in_mail" class="tooltip w3--medium w3-border-0 w3-validate" title="<?php echo $TEXT['INFO_DSGVO_IN_MAIL']; ?>"><span class="slider"><i><?php echo $TEXT['INFO_DSGVO_IN_MAIL']; ?></i></span></label><br />
                            </span>
                        </div>
                     </td>
                  </tr>
                  <tr id="data-protection" <?php echo ($aSettings['use_data_protection'] ? '' : 'style="display: none"'); ?> >
                      <td colspan="2">
                         <div class="w3-panel w3-leftbar w3-light-grey" style="padding-bottom: 1.25em;width: 98%;">
                         <h5><?php echo $MOD_FORM['DSGVO_PLACEHOLDER'];?></h5>
                          <code class="w3-codespan">
                          {CALL_DSGVO_LINK}
                          </code>
                        </div>
                       </td>
                  </tr>

                  <tr id="dsgv-link" style="line-height: 3.5;">
                     <th class="setting_name"><label class="frm-setting_name"><?php echo $TEXT['DSGVO_LINK']; ?></label></th>
                     <td>
                        <div class="w3-row" style="width: 98%;">
                            <div class="input-container">
                            <i class="fa fa-search icon"><span style="display: none;">&nbsp;</span></i>
                            <input class="input-field" placeholder="Search" id="dsgvoInput" onkeyup="searchSelect(this)" type="text" value=""/>
                            </div>
                        </div>
                        <select
                        id="dsgvo"
                        class="w3-border w3-select js-dsgvo-multiple w3-select"
                        size="4"
                        style="max-width: 98%!important;min-height: 10em;font-family:monospace;font-size: 14px;height: 10em;"
                        name="data_protection_link"
                        >
                            <option value="-1"><?php echo $TEXT['PLEASE_SELECT']; ?></option>
<?php
                          foreach($aSelectSections as $aRes) {
                              $option_link = explode('||',$aRes['descr']);
                              $sDisabled = $option_link[0] ? '':' disabled="disabled"';
                              $sSelected = (((int)$option_link[0] == $target_section_id) ? $sSelect : '');
                              $sFlagUrl  = WB_URL.'/modules/WBLingual/flags/png/'.strtolower($aRes['language']);  // {ADDON_LANG_URL}flags/png/{PAGE_LANG}
?>
                              <option <?php echo $sDisabled ;?>value="<?php echo $option_link[0];?>"<?php echo $sSelected;?> class="flag-box" style="background-image: url(<?php echo $sFlagUrl;?>-24.png);"><?php echo $option_link[1];?></option>
<?php
                          }
?>
                      </select>
                     </td>
                  </tr>
                </tbody>
            </table>
        </div>
      <div id="save-settings" class="w3-container w3-cell w3-mobile">
           <input class="w3-btn w3--medium  w3-blue-wb w3-hover-green" name="save" type="submit" value="<?= $TEXT['SAVE']; ?>" style="min-width: 10.25em;" />
      </div>
      <div class="w3-container w3-cell w3-mobile">
          <input class="w3-btn w3--medium  w3-blue-wb w3-hover-green" name="save_pagetree" type="submit" value="<?= $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" style="min-width: 10.25em;"/>
      </div>
      <div class="w3-container w3-cell w3-mobile">
          <input class="w3-btn w3--medium url-cancel btn-size w3-blue-wb w3-hover-red" type="button" value="<?= $TEXT['CLOSE']; ?>" onclick="window.location='<?php echo $sBacklink.'#'.$sSectionIdPrefix.$section_id; ?>';" style="min-width: 10.25em;" />
      </div>
      <div class="w3-container w3-cell w3-margin-top w3--center w3-mobile" style="padding: 0.31em 10px!important;">
        <div class="w3-dropdown-hover">
          <button class="w3-btn w3-btn-default w3-blue-wb w3-hover-text-orange w3--medium w3-btn-min-width"><?= $TEXT['GO_TO'];?>&nbsp;&nbsp;<i class="fa fa-arrow-down w3-padding-4">&nbsp;</i></button>
          <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="#general-setting" class="w3-bar-item w3-button w3-nowrap"><?= $HEADING['GENERAL_SETTINGS']; ?></a>
            <a href="#layout-setting" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT_SETTINGS']; ?></a>
            <a href="#layout-output" class="w3-bar-item w3-button w3-nowrap"><?= $FORM_MESSAGE['LAYOUT']; ?></a>
            <a href="#email-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_RECIPIENT']; ?></a>
            <a href="#email-confirmation" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['EMAIL_SENDER']; ?></a>
            <a href="#dsgvo-setting" class="w3-bar-item w3-button w3-nowrap"><?= $TEXT['DSGVO'].' '.$MOD_FORM['CONFIRM']; ?></a>
            <a href="#save-settings" class="w3-bar-item w3-button w3-nowrap"><?= $MOD_FORM['SAVE_SETTINGS']; ?></a>
          </div>
        </div>
      </div>
    </form>
</article>
<script>
var section_id = '<?php echo $section_id;?>';
var aDefaultLayout = '<?php echo $sPreventDefaults;?>';
</script>

<script>
    var sel = document.getElementById('layout');
    var xhttp = new XMLHttpRequest();
    var title ='';
//console.log(sel);
    sel.onchange = function() {
        path   = this.value;
        var m = path.match(/([^:\\/]*?)(?:\.([^ :\\/.]*))?$/)
        var fileName = (m === null)? "" : m[1]
        var show   = document.getElementById('LayoutTitle');
        show.value = fileName;
        xhttp.open("GET", "/modules/form/data/layouts/"+fileName+".xml", true);
        xhttp.send();
    }
/*-----------------------------------------------------*/
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var xmlDoc = this.responseXML;
            document.getElementById("LayoutDescription").innerHTML =
            xmlDoc.getElementsByTagName("description")[0].childNodes[0].nodeValue
        }
    };
</script>
<script>
    function pageSelect() {
        var input, filter, select, opt, txt, i, txtValue;
        input = document.getElementById("pageInput");
        filter = input.value.toUpperCase();
        select = document.getElementById("page");
        opt = select.getElementsByTagName("option");
        for (i = 0; i < opt.length; i++) {
            txtValue = opt[i].textContent || opt[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                opt[i].style.display = "";
            } else {
                opt[i].style.display = "none";
            }
        }
    }

    function searchSelect() {
        var input, filter, select, opt, txt, i, txtValue;
        input = document.getElementById("dsgvoInput");
        filter = input.value.toUpperCase();
        select = document.getElementById("dsgvo");
        opt = select.getElementsByTagName("option");
        for (i = 0; i < opt.length; i++) {
            txtValue = opt[i].textContent || opt[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                opt[i].style.display = "";
            } else {
                opt[i].style.display = "none";
            }
        }
    }
</script>
<script src="<?php echo $sAddonUrl;?>/themes/default/js/w3.js"></script>

<?php
if ($print_info_banner) { ?>
<?php
}

// Print admin footer
$admin->print_footer();
