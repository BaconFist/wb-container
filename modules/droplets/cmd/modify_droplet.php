<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: modify_droplet.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/modify_droplet.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use vendor\phplib\Template;


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $sAddonPath   = rtrim(str_replace('\\','/',dirname(__DIR__)),'/').'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
/* ------------------------------------------------------------------ */
    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $database = database::getInstance();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */
    if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s %s \n",__LINE__,$action,$droplet_id));}

    $iDropletAddId = ($oApp->getIdFromRequest($droplet_id));
    if (isset($iDropletAddId) && ($iDropletAddId >-1)) {
        $droplet_id = $iDropletAddId;
    }
    if (!isset($iDropletAddId)) {
        $sErrorMsg = sprintf("%s :: %s",$action,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        $oApp->print_error($sErrorMsg, $ToolUrl);
        exit();
    }
    $dropletAddId = $droplet_id;
    $sOverviewDroplets = $oTrans->DR_TEXT_DROPLETS;
    $sTimeStamp = (isset($sTimeStamp) ? $sTimeStamp : '');
    $modified_by = $oApp->getUserId();
    if (($droplet_id > 0)) {
        $sql  = 'SELECT
        *
        FROM `'.TABLE_PREFIX.'mod_droplets`
        WHERE `id` = '.$droplet_id.'
        ';
        if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s \n",__LINE__,$sql));}
        $oDroplet = $oDb->query($sql);
        $aDroplet = $oDroplet->fetchRow(MYSQLI_ASSOC);
        $content  = (htmlspecialchars($aDroplet['code']));
        $DropletName   = $aDroplet['name'];
        $sSubmitButton = $oTrans->TEXT_SAVE;
        $iDropletIdKey = $oApp->getIDKEY($droplet_id);
        $dropletAddId = $droplet_id;
    } else {
        $aDroplet = [];
        // check if it is a normal add or a copy
        if (sizeof($aDroplet)===0) {
            $aDroplet = array(
                'id' => $dropletAddId,
                'name' => 'Dropletname',
                'code' => 'return true;',
                'description' => '',
                'modified_when' => 0,
                'modified_by' => 0,
                'active' => 0,
                'admin_edit' => 0,
                'admin_view' => 0,
                'show_wysiwyg' => 0,
                'comments' => ''
                );
            $content = '';
        }
        $dropletAddId = 0;
        $sSubmitButton = $oTrans->TEXT_ADD;
        $iDropletIdKey = $oApp->getIDKEY($droplet_id);
    }
    if (!\function_exists('loader_help')){require($oReg->AppPath . '/include/editarea/wb_wrapper_edit_area.php');}
    $aInitEditArea = [
      'id' => 'contentedit',
      'syntax' => 'php',
      'syntax_selection_allow' => false,
      'allow_resize' => true,
      'allow_toggle' => true,
      'start_highlight' => true,
      'toolbar' => 'search, go_to_line, fullscreen, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, |, help',
      'font_size' => '14'
    ];

    echo registerEditArea ($aInitEditArea);
//'contentedit','php',true,'both',true,true,600,450,'search,fullscreen, |, undo, redo, |, select_font,|,highlight, reset_highlight, |, help');

?><br />
<div class="block-outer droplets">
<section class="droplets-block w3-container">
<form id="modify" action="<?php echo $js_back; ?>" method="post" style="margin: 0;">
    <input type="hidden" name="tool" value="<?= $sAddonName; ?>" />
    <input type="hidden" name="command" value="save_droplet" />
    <input type="hidden" name="data_codepress" value="" />
    <input type="hidden" name="droplet_id" value="<?php echo $iDropletIdKey; ?>" />
    <input type="hidden" name="id" value="<?php echo $dropletAddId; ?>" />

     <input type="hidden" name="title" value="<?php echo $DropletName; ?>" />
   <input type="hidden" name="show_wysiwyg" value="<?php echo $aDroplet['show_wysiwyg']; ?>" />
    <?php echo $oApp->getFTAN(); ?>



    <table class="droplets droplets-modify" style="width: 100%;">
        <tbody>
        <tr style="line-height: 5;">
            <td class="setting_name">
                <?php echo $oTrans->TEXT_NAME; ?>:
            </td>
            <td >
                <div class="block-outer w3-margin-top" style="width: 98%;">
<?php if ($droplet_id ==0 ){ ?>
                     <input class="w3-input w3-border-0 w3-padding-4" type="text" class="rename-input" name="title" value="<?php echo stripslashes($aDroplet['name']).$sTimeStamp; ?>" style="width: 100%;" maxlength="32" />
<?php } else { ?>
                     <div class="noInput w3-input w3-border-0 w3-padding-4"><?php echo stripslashes($aDroplet['name']).$sTimeStamp; ?></div>
<?php }?>
                </div>
            </td>
        </tr>
        <tr style="line-height: 3;">
            <td class="setting_name" ><?php echo $oTrans->TEXT_DESCRIPTION; ?>:</td>
            <td>
                <input class="w3-input w3-border w3-padding-4" type="text" name="description" value="<?php echo stripslashes($aDroplet['description']); ?>" style="width: 98%;" />
            </td>
        </tr>
        <tr style="line-height: 3;">
            <td class="setting_name" >
                <span><?php echo $oTrans->TEXT_ACTIVE; ?>:</span>
            </td>
            <td class="">
                <label class="check-container w3-validate" for="active_true">
                <input class="w3-radio" type="radio" name="active" id="active_true" value="1" <?php if($aDroplet['active'] == 1) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('active_true').checked = true;"></span>
                <?php echo $oTrans->TEXT_YES; ?></label>
                <label class="check-container w3-validate" for="active_false">
                <input class="w3-radio" type="radio" name="active" id="active_false" value="0" <?php if($aDroplet['active'] == 0) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('active_false').checked = true;"></span>
                <?php echo $oTrans->TEXT_NO; ?></label>

            </td>
        </tr>
<?php
// Next show only if admin is logged in, user_id = 1
if ($modified_by == 1) { ?>
        <tr style="line-height: 3;">
            <td class="setting_name">
                <?php echo $oTrans->TEXT_ADMIN; ?>:
            </td>
            <td class="w3-col" style="line-height: 2.6;">
                <span class="pre-label"><?php echo $oTrans->DR_TEXT_ADMIN_EDIT; ?>&nbsp;</span>
                <label class="check-container w3-validate" for="admin_edit_true">
                <input class="w3-radio" type="radio" name="admin_edit" id="admin_edit_true" value="1" <?php if($aDroplet['admin_edit'] == 1) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('admin_edit_true').checked = true;"></span>
                <?php echo $oTrans->TEXT_YES; ?></label>
                <label class="check-container w3-validate" for="admin_edit_false">
                <input class="w3-radio" type="radio" name="admin_edit" id="admin_edit_false" value="0" <?php if($aDroplet['admin_edit'] == 0) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('admin_edit_false').checked = true;"></span>
                <?php echo $oTrans->TEXT_NO; ?></label>

                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <span class="pre-label"><?php echo $oTrans->DR_TEXT_ADMIN_VIEW; ?></span>
                <label class="check-container w3-validate" for="admin_view_true">
                <input class="w3-radio" type="radio" name="admin_view" id="admin_view_true" value="1" <?php if($aDroplet['admin_view'] == 1) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('admin_view_true').checked = true;"></span>
                <?php echo $oTrans->TEXT_YES; ?></label>
                <label class="check-container w3-validate" for="admin_view_false">
                <input class="w3-radio" type="radio" name="admin_view" id="admin_view_false" value="0" <?php if($aDroplet['admin_view'] == 0) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn" onclick="document.getElementById('admin_view_false').checked = true;"></span>
                <?php echo $oTrans->TEXT_NO; ?></label>

            </td>
        </tr>
<?php } ?>
        <tr style="line-height: 3;">
            <td class="setting_name"><?php echo $oTrans->TEXT_CODE; ?>:</td>
            <td >
            <textarea class="w3-border" name="savecontent" id ="contentedit" style="width: 98%; height: 460px;" rows="53" cols="120"><?php echo $content; ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="setting_name" ><?php echo $oTrans->TEXT_COMMENTS; ?>:</td>
            <td>
                <textarea class="w3-border" name="comments" style="width: 98%; min-height: 20.525em; height: 15.525em !important;" rows="50" cols="120"><?php echo ($aDroplet['comments']); ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        </tbody>
    </table>
<br />
<table>
    <tr>
        <td>
<?php
// Show only save button if allowed....
if ($modified_by == 1 || $aDroplet['admin_edit'] == 0 ) {
?>
            <button  class="btn w3-blue-wb w3-hover-green" name="command" value="save_droplet?droplet_id=<?php echo $iDropletIdKey; ?>" type="submit"><?php echo $sSubmitButton; ?></button>
<?php } ?>
            <button class="btn w3-blue-wb w3-hover-red url-reset" data-overview="<?= $ToolRel; ?>" type="button"><?php echo $oTrans->TEXT_CANCEL; ?></button>
        </td>
    </tr>
</table>
</form>
</section>
<br />
</div>
