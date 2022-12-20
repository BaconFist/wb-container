<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for Website Baker 2.7
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: tool.php 288 2019-03-26 15:14:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/tool.php $
 * @lastmodified    $Date: 2019-03-26 16:14:03 +0100 (Di, 26. Mrz 2019) $
 *
*/

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */
    $sAddonName = \basename(__DIR__);
    $bExcecuteCommand = false;
/*******************************************************************************************/
//      SimpleCommandDispatcher
/*******************************************************************************************/
    if (\is_readable(\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php')) {
        require (\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php');
    }
// check if module language file exists for the language set by the user (e.g. DE, EN)
    $sAddonName = basename(__DIR__);
    $sModulName = $sAddonName;
    if (\is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (\is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oTrans = \Translate::getInstance();
    $oTrans->enableAddon('modules\\'.basename(__DIR__));

    $sActionUrl = ADMIN_URL.'/admintools/tool.php';
    $ToolQuery  = '?tool='.$sAddonName;
    $ToolRel    = '/admintools/tool.php'.$ToolQuery;
    $js_back    = $sActionUrl;
    $ToolUrl    = $sActionUrl.'?tool='.$sAddonName;
    $sAdminToolRel = ADMIN_DIRECTORY.'/admintools/index.php';
    $sAdminToolUrl = $oReg->AcpUrl.$sAdminToolRel;
    if( !$admin->get_permission($sModulName,'module' ) ) {
        $admin->print_error($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES'], $js_back);
    }

    if (!\function_exists('get_setting')) {require(WB_PATH.'/modules/'.basename(__DIR__).'/jsadmin.php');}

// Check if user selected what add-ons to reload
// Display form
    $aSelect['persist_order']       = (get_setting('mod_jsadmin_persist_order' ) ? 'checked="checked"' : '');
    $aSelect['ajax_order_pages']    = (get_setting('mod_jsadmin_ajax_order_pages'  ) ? 'checked="checked"' : '');
    $aSelect['ajax_order_sections'] = (get_setting('mod_jsadmin_ajax_order_sections' ) ? 'checked="checked"' : '');

// THIS ROUTINE CHECKS THE EXISTING OFF ALL NEEDED YUI FILES
  $YUI_ERROR=false; // ist there an Error
  $YUI_PUT ='';   // String with javascipt includes
  $YUI_PUT_MISSING_Files=''; // String with missing files
  reset($js_yui_scripts);
    foreach($js_yui_scripts as $script) {
        if (!\file_exists(WB_PATH.$script)){
            $YUI_ERROR=true;
            $YUI_PUT_MISSING_Files =$YUI_PUT_MISSING_Files."- ".WB_URL.$script."<br />";   // catch all missing files
        }
    }
    if($YUI_ERROR) {
?>
    <div id="jsadmin_install" style="border: solid 2px #c99; background: #ffd; padding: 0.5em; margin-top: 1em">
     <?php echo $MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'].$YUI_PUT_MISSING_Files; ?>
    </div>
<?php
  } else {
    if(!$admin_header) { $admin->print_header(); }

    if ($doSave) {

        if (!$admin->checkFTAN()) {
//            if(!$admin_header) { $admin->print_header(); }
//          show title if not function 'save' is requested
            echo $admin->format_message($MESSAGE['GENERIC_SECURITY_ACCESS'],'ok',$ToolUrl);
        }
/* */
        $persist_order = (int)filter_var($admin->get_post('persist_order'), FILTER_VALIDATE_BOOLEAN);
        $ajax_order_pages = (int)filter_var($admin->get_post('ajax_order_pages'), FILTER_VALIDATE_BOOLEAN);
        $ajax_order_sections = (int)filter_var($admin->get_post('ajax_order_sections'), FILTER_VALIDATE_BOOLEAN);

        $aSql = [];
        $aSql[] = save_setting('mod_jsadmin_persist_order', $persist_order);
        $aSql[] = save_setting('mod_jsadmin_ajax_order_pages', $ajax_order_pages);
        $aSql[] = save_setting('mod_jsadmin_ajax_order_sections', $ajax_order_sections);
        // check if there is a database error, otherwise say successful implode('<br />',$aSql ).
        $aSelect['persist_order']       = (get_setting('mod_jsadmin_persist_order' ) ? 'checked="checked"' : '');
        $aSelect['ajax_order_pages']    = (get_setting('mod_jsadmin_ajax_order_pages'  ) ? 'checked="checked"' : '');
        $aSelect['ajax_order_sections'] = (get_setting('mod_jsadmin_ajax_order_sections' ) ? 'checked="checked"' : '');

//        if(!$admin_header) { $admin->print_header(); }
//          show title if not function 'save' is requested
                print '<h4 style="margin:0!important;font-size:1.25em!important;"><a href="'.$sAdminToolUrl.'" '.
                      'title="'.$HEADING['ADMINISTRATION_TOOLS'].'">'.
                      $HEADING['ADMINISTRATION_TOOLS'].'</a>'.
                      '&nbsp;&raquo;&nbsp;'.$toolName.'</h4>'."\n";

        if($database->is_error()) {
//            echo $admin->format_message($database->get_error(),'error', $ToolUrl);
            $admin->print_error($database->get_error(), $ToolUrl);
        } else {
//            echo $admin->format_message($MESSAGE['PAGES_SAVED'],'ok', $ToolUrl);
            $admin->print_success($MESSAGE['PAGES_SAVED'], $ToolUrl);
        }

  }

?>
<div class="block-outer">
    <h5 class="w3-panel w3-note w3-row w3-padding"><?= $MOD_JSADMIN['TXT_HEADING_B']; ?></h5>
    <form id="jsadmin_form" style="margin-top: 1em; display: true;" action="<?= $sActionUrl;?>" method="post">
      <input type="hidden" name="tool" value="<?= basename(__DIR__); ?>" />
      <input type="hidden" name="action" value="save" />
      <input type="hidden" name="SaveSettings" value="1" />
      <?php echo $admin->getFTAN(); ?>
            <div class="w3-content w3-margin">
             <input class="w3-check" type="checkbox" name="persist_order" id="persist_order" value="true" <?php echo $aSelect['persist_order']; ?>/>
             <label class="w3-validate" for="persist_order"><?php echo $MOD_JSADMIN['TXT_PERSIST_ORDER_B']; ?></label>
            </div>

            <div class="w3-content w3-margin">
             <input class="w3-check" type="checkbox" name="ajax_order_pages" id="ajax_order_pages" value="true" <?php echo $aSelect['ajax_order_pages']; ?>/>
             <label class="w3-validate" for="ajax_order_pages"><?php echo $MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B']; ?></label>
            </div>

            <div class="w3-content w3-margin">
             <input class="w3-check" type="checkbox" name="ajax_order_sections" id="ajax_order_sections" value="true" <?php echo $aSelect['ajax_order_sections']; ?>/>
             <label class="w3-validate" for="ajax_order_sections"><?php echo $MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B']; ?></label>
            </div>

            <div class="w3-content w3-margin">
                <div class="w3-quarter"></div>
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" type="submit" name="SaveSettings" value="<?php echo $TEXT['SAVE']; ?>" />
                <button class="w3-btn w3-btn-default w3-blue-wb w3-hover-red w3--medium w3-btn-min-width url-close" data-overview="<?= $sAdminToolRel;?>" type="button"><?= $TEXT['CLOSE'];?></button>

            </div>
   </form>
</div>
 <?php
}
