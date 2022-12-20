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
 * @subpackage      modify_field
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
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use dispatch\Dispatcher;

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}

try {

    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $aMessage = [];

    $oDispatch  = new Dispatcher([]);
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
    extract($oDispatch->getBackLinks($sDumpPathname),EXTR_OVERWRITE); //
    $sAddonBackUrl = $sBacklink;
    $field_id = ($oApp->getIdFromRequest('field_id') ?? false);
    if ($sSecureToken && $field_id===false){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
// load module language file
    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
    $type = 'none';
    $aDefault = [
        'field_id' => $field_id,
        'section_id' => $section_id,
        'page_id' => $page_id,
        'title' => '',
        'type' => 'none',
        'required' => 0,
        'value' => '',
        'extra' => '',
        'active' => 0,
    ];

// get
    $aForm = $oDispatch->getSqlRecord('mod_form_fields','field_id',$field_id, $aDefault);
// set new idkey for save
    $sFieldIdKey = SecureTokens::getIDKEY($aForm['field_id']);
// check for stored Keys in $_SESSION['form'] and fill in $aRecord set $aNotAllowedKeys
    $aNotAllowedKeys = ['page_id','section_id','field_id'];
    $sSessions = ($_SESSION['form'] ?? []);
    foreach ($sSessions as $key=>$item){
        if (in_array($key,$aNotAllowedKeys)){continue;}
        $aForm[$key] = ($_SESSION['form'][$key] ?? $aForm[$key]);
//        $aForm['value'] = htmlspecialchars_decode($aForm['value']);
    }

    $type = (empty($aForm['type']) ? 'none' : $aForm['type']);
/*
// Set raw html <'s and >'s to be replaced by friendly html code
    $raw      = ['<', '>'];
    $friendly = ['&lt;', '&gt;'];
*/
    $aFtan = SecureTokens::getFTAN();
    $sToken = $aFtan['name'].'='.$aFtan['value'];
?><form id="modify_field" action="<?php echo WB_URL; ?>/modules/form/save_field_new.php" method="post" style="margin: 0;">
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" name="field_id" value="<?php echo $sFieldIdKey; ?>" />
    <input type="hidden" name="<?php echo $aFtan['name']; ?>" value="<?php echo $aFtan['value']; ?>" />

    <article class="form-block w3-container w3-margin">
      <h2 class="w3-text-blue-wb"><?php echo $TEXT['MODIFY_FIELD']; ?></h2>
      <table class="frm-table">
          <tbody>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['TITLE']; ?>:</td>
                  <td>
                      <input type="text" name="title" value="<?php echo htmlspecialchars(($aForm['title'])); ?>" style="width: 95%;padding-left: 12px;" maxlength="255" />
                  </td>
              </tr>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['TYPE']; ?>:</td>
                  <td>
                      <select class="w3-select w3-border w3-small" name="type" style="width: 95%;">
                          <option value=""><?php echo $TEXT['PLEASE_SELECT']; ?>...</option>
                          <option value="heading"<?php if ($type == 'heading') { echo ' selected="selected"'; } ?>><?php echo $TEXT['HEADING']; ?></option>
                          <option value="email"<?php if ($type == 'email') { echo ' selected="selected"'; } ?>><?php echo $TEXT['EMAIL_ADDRESS']; ?></option>
                          <option value="subject"<?php if ($type == 'subject') { echo ' selected="selected"'; } ?>><?php echo $TEXT['SUBJECT'].' '.$TEXT['TEXT']; ?> (input)</option>
                          <option value="textfield"<?php if ($type == 'textfield') { echo ' selected="selected"'; } ?>><?php echo $TEXT['SHORT'].' '.$TEXT['TEXT']; ?> (input)</option>
                          <option value="textarea"<?php if ($type == 'textarea') { echo ' selected="selected"'; } ?>><?php echo $TEXT['LONG'].' '.$TEXT['TEXT']; ?> (textarea)</option>
                          <option value="select"<?php if ($type == 'select') { echo ' selected="selected"'; } ?>><?php echo $TEXT['SELECT_BOX']; ?> (select)</option>
                          <option value="checkbox"<?php if ($type == 'checkbox') { echo ' selected="selected"'; } ?>><?php echo $TEXT['CHECKBOX_GROUP']; ?> (checkbox)</option>
                          <option value="radio"<?php if ($type == 'radio') { echo ' selected="selected"'; } ?>><?php echo $TEXT['RADIO_BUTTON_GROUP']; ?> (radiobox)</option>
                          <option value="checkbox"<?php if ($type == 'DSGVO') { echo ' selected="selected"'; } ?>><?php echo $TEXT['DSGVO']; ?> (checkbox)</option>
                      </select>
                  </td>
              </tr>
          <?php if (($type != 'none') && ($type != 'email')) { ?>
              <?php if($type == 'heading') { ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['TEMPLATE']; ?>:</td>
                  <td>
                      <textarea class="w3-border w3--medium w3-select" name="template" style="width: 95%;min-height: 6em;"><?php echo htmlspecialchars(($aForm['extra'])); ?></textarea>
                  </td>
              </tr>
<?php } else if (($type == 'textfield')) { ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['LENGTH']; ?>:</td>
                  <td>
                      <input type="text" name="length" value="<?php echo $aForm['extra']; ?>" style="width: 95%;padding-left: 12px;" maxlength="3" />
                  </td>
              </tr>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['DEFAULT_TEXT']; ?>:</td>
                  <td>
                      <input type="text" name="value" value="<?php echo htmlspecialchars_decode($aForm['value']); ?>" style="width: 95%;" />
                  </td>
              </tr>
<?php } else if (($type == 'textarea')) { ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['DEFAULT_TEXT']; ?>:</td>
                  <td>
                      <textarea class="w3-border w3-select w3--medium" name="value" style="width: 95%; min-height: 6em;"><?php echo htmlspecialchars_decode($aForm['value']); ?></textarea>
                  </td>
              </tr>
<?php } else if (($type == 'select') || ($type == 'radio') || ($type == 'checkbox')) {
              $option_count = 0;
              $list = explode(',', htmlspecialchars_decode($aForm['value']));
?>
              <tr>
                  <td  class="setting_name"><?php echo $TEXT['LIST_OPTIONS']; ?>:</td>
                  <td>
                      <table class="w3-table" >
<?php
                      foreach($list as $option_value) {
                          $option_count++;
?>
                          <tr>
                              <td class="setting_name"><?php echo $TEXT['OPTION'].' '.$option_count; ?>:</td>
                              <td>
                                  <input type="text" name="value<?php echo $option_count; ?>" value="<?php echo $option_value; ?>" style="width: 250px;" />
                              </td>
                          </tr>
<?php
                      }
                      for($i = 0; $i < 2; $i++) {
                          $option_count++;
?>
                          <tr>
                              <td class="setting_name"><?php echo $TEXT['OPTION'].' '.$option_count; ?>:</td>
                              <td>
                                  <input type="text" name="value<?php echo $option_count; ?>" value="" style="width: 250px;" />
                              </td>
                          </tr>
<?php
                      }
?>
                          </table>
                      <input type="hidden" name="list_count" value="<?php echo $option_count; ?>" />
                  </td>
              </tr>
<?php }
              if (($type == 'select')) {
?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['SIZE']; ?>:</td>
                  <td>
<?php
                      $aForm['extra'] = explode(',',$aForm['extra']);
                      $aForm['extra'][0] = ($aForm['extra'][0] ? : 1);
                      $aForm['extra'][1] = ($aForm['extra'][1] ?? '');
?>
                      <input type="text" name="size" value="<?php echo trim($aForm['extra'][0]); ?>" style="width: 95%;" maxlength="3" />
                  </td>
              </tr>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['ALLOW_MULTIPLE_SELECTIONS']; ?>:</td>
                  <td>

                      <div class="w3-bar w3-margin" style="left: -12px!important;position: relative!important;margin: -12px 0px auto !important;">
                          <label class="check-container w3-bar-item w3-margin-right" for="multiselect_true">
                              <input type="radio" name="multiselect" id="multiselect_true" value="multiple"<?php echo (($aForm['extra'][1] == 'multiple') ? ' checked="checked"' :'');  ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['YES']; ?>&nbsp;</span>
                          </label>
                          <label class="check-container w3-bar-item" for="multiselect_false">
                              <input type="radio" name="multiselect" id="multiselect_false" value=""<?php if (empty($aForm['extra'][1])) { echo ' checked="checked"'; } ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['NO']; ?>&nbsp;</span>
                          </label>
                      </div>

                  </td>
              </tr>
  <?php } else if (($type == 'checkbox') || ($type == 'radio')) { ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['SEPERATOR']; ?>:</td>
                  <td>
                      <input type="text" name="seperator" value="<?php echo $aForm['extra']; ?>" style="width: 95%;" />
                  </td>
              </tr>
  <?php } ?>
  <?php } ?>
  <?php if (($type != 'heading') && ($type != 'none')) { ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['REQUIRED']; ?>:</td>
                  <td>
                      <div class="w3-bar w3-margin" style="left: -12px!important;position: relative!important;margin: -12px 0px auto !important;">
                          <label class="check-container w3-bar-item w3-margin-right" for="required_true">
                              <input type="radio" name="required" id="required_true" style="width: 14px; height: 14px;" value="1"<?php if ($aForm['required'] == 1) { echo ' checked="checked"'; } ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['YES']; ?>&nbsp;</span>
                          </label>
                          <label class="check-container w3-bar-item" for="required_false">
                              <input type="radio" name="required" id="required_false" style="width: 14px; height: 14px;" value="0"<?php if ($aForm['required'] == 0) { echo ' checked="checked"'; } ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['NO']; ?>&nbsp;</span>
                          </label>
                      </div>
                  </td>
              </tr>
  <?php } ?>
              <tr>
                  <td class="setting_name"><?php echo $TEXT['ACTIVE']; ?>:</td>
                  <td>
                      <div class="w3-bar w3-margin" style="left: -12px!important;position: relative!important;margin: -12px 0px auto !important;">
                          <label class="check-container w3-bar-item w3-margin-right" for="active_true">
                              <input type="radio" name="active" id="active_true" value="1"<?php if ($aForm['active'] == 1) { echo ' checked="checked"'; } ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['YES']; ?>&nbsp;</span>
                          </label>
                          <label class="check-container w3-bar-item" for="active_false">
                              <input type="radio" name="active" id="active_false" value="0"<?php if ($aForm['active'] == 0) { echo ' checked="checked"'; } ?> />
                              <span class="radiobtn w3-margin">&nbsp;</span>
                              <span style="vertical-align: super;"><?php echo $TEXT['NO']; ?>&nbsp;</span>
                          </label>
                      </div>
                  </td>
              </tr>

          </tbody>
      </table>
      <table class="frm-table" style="margin: 15px;">
          <tbody>
              <tr>
                  <td>
                      <input class="w3-btn w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" />
              <?php if (($type != '') && ($type != 'none')) { ?>
                      <input class="w3-btn w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="save_close" type="submit" value="<?php echo $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" />
              <?php } ?>
                      <input class="w3-btn w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="window.location='<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id.'#'.$sSectionIdPrefix.$section_id; ?>';" />
                  </td>
                  <td>&nbsp;</td>
              </tr>
          </tbody>
      </table>
  </article>

</form>
<?php
if ($print_info_banner){ }
}catch (\Exception $ex) {
//    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
// Print admin footer
$admin->print_footer();
