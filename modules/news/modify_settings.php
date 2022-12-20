<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: modify_settings.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/modify_settings.php $
 * @lastmodified    $Date: 2019-06-11 19:55:53 +0200 (Di, 11. Jun 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
use addon\news\NewsLib;



if (!defined('SYSTEM_RUN') ){ require( dirname(dirname((__DIR__))).'/config.php' );}

    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();

try {

// suppress to print the header, so no new FTAN will be set
//$admin_header = false;
// Tells script to update when this page was last updated
    $update_when_modified = false;
// show the info banner
    $print_info_banner = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

//    $oRequest = ($oRequest ?? \bin\requester\HttpRequester::getInstance());

//    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
//    $aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);

    $sAddonName = basename(__DIR__);
    $ModuleRel  = '/modules/'.$sAddonName.'/';
    $ModuleUrl  = WB_URL.$ModuleRel;
    $ModulePath = WB_PATH.$ModuleRel;
    $sAddonPath = WB_PATH.$ModuleRel;
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    $aMsg = [];
    $aSuccess = [];
    $aSelectLayout = [];
    $sSelect    = ' selected="selected"';
    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink  = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.$sSectionIdPrefix;
    $sAddonBackUrl = $sBacklink;

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
    if (!function_exists('edit_module_css')) {include(WB_PATH .'/framework/module.functions.php');}

// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oLang = Translate::getInstance();
    $oLang->enableAddon('modules\\'.basename(__DIR__));
/* ------------------------ */
    if (!(\bin\SecureTokens::checkFTAN()) && $sSecureToken) {
        $sErrorMessage = sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($sErrorMessage);
    }


    $aDefaultLayouts = [];
    $aDefaultLayouts[] = null;
// filename pattern without ext
    $sPattern = '/^.*?([^\/]*?)\.[^\.]*\.[^\.]*$/is';
    $aLayouts = \glob($sAddonPath.'/presets/*.inc.php');
    foreach ($aLayouts as $item){
         $aDefaultLayouts[] = preg_replace($sPattern,'$1',$item);
    }
    $aLayouts = [];
    $sSqlLauouts = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_layouts` '.$sPHP_EOL;
    if (($oRes = $oReg->Db->query($sSqlLauouts))){
        if (!($aLayouts = $oRes->fetchAll())){
            $aLayouts = $aDefaultLayouts;
        }
    }
    $old_page_id = $page_id;
    $sLayout = $sLayoutName = 'default_layout';
    $iLayoutId = 1;
    $target_section_id = -1;
    if (isset($aRequestVars['install_layout'])) {
        if (isset($aRequestVars)){
          if (!empty($aRequestVars['layout_id'])){
            $iLayoutId = (int)Sanitize::StripFromText($aRequestVars['layout_id'],Sanitize::REMOVE_DEFAULT);
//            $iLayoutId = Sanitize::StripFromText($aRequestVars['layout'],Sanitize::REMOVE_DEFAULT);
          }
//
          $sql = ''
               . 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_layouts` '.$sPHP_EOL
               .     'WHERE `id`='.$iLayoutId.''.$sPHP_EOL;
          if (!($oRes = $oReg->Db->query($sql))){
            $aMsg[] = $sql;
            $aMsg[] = \sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
          } else {
            if (($aRow = $oRes->fetchRow(MYSQLI_ASSOC))){;}
//            \extract($aRow);
            $iLayoutId = (int)$aRow['id'];
            $sLayout = $sLayoutName = $aRow['layout'];
            $sql  = 'UPDATE '.TABLE_PREFIX.'mod_news_settings SET '
/*
                  . '`header`=\''.$database->escapeString($aRow['header']).'\', '
                  . '`post_loop`=\''.$database->escapeString($aRow['post_loop']).'\', '
                  . '`footer`=\''.$database->escapeString($aRow['footer']).'\', '
                  . '`post_header`=\''.$database->escapeString($aRow['post_header']).'\', '
                  . '`post_footer`=\''.$database->escapeString($aRow['post_footer']).'\', '
                  . '`comments_header`=\''.$database->escapeString($aRow['comments_header']).'\', '
                  . '`comments_loop`=\''.$database->escapeString($aRow['comments_loop']).'\', '
                  . '`comments_footer`=\''.$database->escapeString($aRow['comments_footer']).'\', '
                  . '`comments_page`=\''.$database->escapeString($aRow['comments_page']).'\', '
*/
                  . '`layout` = \''.$database->escapeString($sLayoutName).'\', '
                  . '`layout_id` = '.(int)$aRow['id'].' '
                  . 'WHERE `section_id`='.(int)$section_id.' '
                  . '';
            if (!($oRes = $oReg->Db->query($sql))){
              $aMsg[] = $sql;
              $aMsg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
            }
          }
        }
    } // end of install_layout
    $sSqlSet = '';
    $bSqlSet = false;

// layout save handling
    if (isset($aRequestVars['save_layout']) || isset($aRequestVars['add_layout'])){
        $sLayout = Sanitize::StripFromText( $admin->get_post('new_layout'),Sanitize::REMOVE_DEFAULT);
        $sOldLayout = Sanitize::StripFromText( $admin->get_post('old_layout'),Sanitize::REMOVE_DEFAULT);
        $sLayout = (empty($sLayout) ? $sOldLayout : $sLayout);
        $header = Sanitize::StripFromText( $admin->get_post('header'),Sanitize::REMOVE_DEFAULT);
        $post_loop = Sanitize::StripFromText( $admin->get_post('post_loop'),Sanitize::REMOVE_DEFAULT);
        $footer = Sanitize::StripFromText( $admin->get_post('footer'),Sanitize::REMOVE_DEFAULT);
        $post_header = Sanitize::StripFromText( $admin->get_post('post_header'),Sanitize::REMOVE_DEFAULT);
        $post_footer = Sanitize::StripFromText( $admin->get_post('post_footer'),Sanitize::REMOVE_DEFAULT);

        $comments_header = Sanitize::StripFromText( $admin->get_post('comments_header'),Sanitize::REMOVE_DEFAULT);
        $comments_loop = Sanitize::StripFromText( $admin->get_post('comments_loop'),Sanitize::REMOVE_DEFAULT);
        $comments_footer = Sanitize::StripFromText( $admin->get_post('comments_footer'),Sanitize::REMOVE_DEFAULT);
        $comments_page = Sanitize::StripFromText( $admin->get_post('comments_page'),Sanitize::REMOVE_DEFAULT);

        $sSqlSet  = ''.$sPHP_EOL
                  . '`header`=\''.$oReg->Db->escapeString($header).'\', '.$sPHP_EOL
                  . '`post_loop`=\''.$oReg->Db->escapeString($post_loop).'\', '.$sPHP_EOL
                  . '`footer`=\''.$oReg->Db->escapeString($footer).'\', '.$sPHP_EOL
                  . '`post_header`=\''.$oReg->Db->escapeString($post_header).'\', '.$sPHP_EOL
                  . '`post_footer`=\''.$oReg->Db->escapeString($post_footer).'\', '.$sPHP_EOL
                  . '`comments_header`=\''.$oReg->Db->escapeString($comments_header).'\', '.$sPHP_EOL
                  . '`comments_loop`=\''.$oReg->Db->escapeString($comments_loop).'\', '.$sPHP_EOL
                  . '`comments_footer`=\''.$oReg->Db->escapeString($comments_footer).'\', '.$sPHP_EOL
                  . '`comments_page`=\''.$oReg->Db->escapeString($comments_page).'\' '.$sPHP_EOL;
      $bSqlSet = true;
    } // end of layout settings save_layout or add_layout
// now update or insert layout
    if ($oRequest->issetParam('save_layout')) {
        $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_layouts` SET '.$sPHP_EOL
              . '`layout` = \''.$oReg->Db->escapeString($sLayout).'\', '.$sPHP_EOL;
        $sWhere = 'WHERE `layout`= \''.$oReg->Db->escapeString($sLayout).'\'';
        $aSuccess[] = sprintf('The Layout <i>%s</i> succesfully updated',$sLayout);
    }
    if ($oRequest->issetParam('add_layout')) {
        $sNewName = NewsLib::getUniqueName($oReg->Db, 'layout', $sLayout);
        $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_layouts` SET '.$sPHP_EOL
              . '`layout` = \''.$oReg->Db->escapeString($sNewName).'\', '.$sPHP_EOL;
        $sWhere = '';
        $aSuccess[] = sprintf('A Layout named <i>%s</i> succesfully added',$sNewName);
    }
// delete only layout not in use
    if ($oRequest->issetParam('delete_layout')){
        $sTmpLayoutId = $oRequest->getParam('layout_id');
        $sTmpLayout = ($aLayouts[$sTmpLayoutId-1]['layout'] ?? null);

        $bCannotDeleteDefault = \in_array($sTmpLayoutId,$aDefaultLayouts);
        $sInuseSql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_settings` '.$sPHP_EOL
                    . 'WHERE `layout_id` = \''.$oReg->Db->escapeString($sTmpLayoutId).'\' '.$sPHP_EOL
                    . '';
        if (($iLayoutInUse = $oReg->Db->get_one($sInuseSql) > 0)){
            $aMsg[] = sprintf('[%05d] Deleting of used "<i>%s</i>" not possible',__LINE__,$sTmpLayout);
        } elseif ($bCannotDeleteDefault){
            $aMsg[] = sprintf('[%05d] Deleting of preset "<i>%s</i>" not possible',__LINE__,$sTmpLayout);
        } else {
            $bSqlSet = true;
            $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_layouts` ';
            $sWhere = 'WHERE `id`= \''.$oReg->Db->escapeString($sTmpLayoutId).'\' ';
            $aSuccess[] = sprintf('The Layout <i>%s</i> succesfully deleted',$sTmpLayout);
        }
    }
// execute save_layout or add_layout
    if (($bSqlSet==true) && !$oReg->Db->query($sql.$sSqlSet.$sWhere)){
       $aMsg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
       $aMsg[] = sprintf('[%05d] %s %s',__LINE__,$sql,$sWhere);
    }

// load layouts for select list
    $sql  = 'SELECT `id`, `layout` FROM `'.TABLE_PREFIX.'mod_news_layouts` '
          . 'ORDER BY `id`';
    if ($oLayout = $database->query( $sql )){
        if (!is_null($aLayouts = $oLayout->fetchAll( MYSQLI_ASSOC ))){
            $iNumRow  = count($aLayouts);
            foreach ($aLayouts as $index=>$aLayout){
              $aSelectLayout[] = $aLayout; // ['layout']
            }
        }
    }
//    \natcasesort($aSelectLayout);
// Get settings and layout
    $sql  = 'SELECT `ns`.*,`nl`.*  FROM `'.TABLE_PREFIX.'mod_news_settings` `ns` '.$sPHP_EOL
          . 'INNER JOIN `'.TABLE_PREFIX.'mod_news_layouts` `nl` ON `ns`.`layout_id` = `nl`.`id` '.$sPHP_EOL
          . 'WHERE `ns`.`section_id` = '.(int)$section_id;
    if ($oSettings = $database->query( $sql )){
        if (is_null($aSetting = $oSettings->fetchRow( MYSQLI_ASSOC ))){
// load default presets if settingsfor this section is empty
            require $sAddonPath.'/presets/db/mod_news_settings.inc.php';
            $aSetting = $aDefault;
        }
        $target_section_id = $aSetting['data_protection_link'];
        $aLayout['id'] = $aSetting['id'];
        $aLayout['layout'] = $aSetting['layout'];
        $sLayout = $sLayoutName = $aSetting['layout'];
        $iLayoutId = $aSetting['id'];
    } else {
        //
    }
    $FTAN  = \bin\SecureTokens::getFTAN();
    $sFtan = $FTAN['name'].'='.$FTAN['value'];

    $sDisabledLayout = (\in_array($aSetting['layout'],$aDefaultLayouts) ? true : false);
    $sTmpName = NewsLib::getUniqueName($oReg->Db, 'layout', $aSetting['layout']);
    $sTooltip = sprintf($NEWS_MESSAGE['ADD_LAYOUT'],$sTmpName,$aSetting['layout']);

//$aSetting
// Set raw html <'s and >'s to be replace by friendly html code
    $raw = ['<', '>'];
    $friendly = ['&lt;', '&gt;'];

    $aSelectSections = [];
    $aSelectSections = ParentList::build_sectionlist(0, $page_id, $aSelectSections);

}catch (\Exception $ex) {
    $admin->print_header('',false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

?>
<article class="news-block w3-container">
<div class="w3-padding w3-row w3-panel">
    <div class="w3-container w3-col m6 w3-padding">
      <h2 style="margin: 0.425em 0 0;"><?php echo $MOD_NEWS['SETTINGS']; ?></h2>
    </div>
    <div class="w3-right">
        <div class="w3-container w3-cell m2 w3-margin-top">&#160;</div>
        <div class="w3-container w3-cell m2 w3-mobile">
            <input style="min-width: 10.5em;" id="topcancel" class="w3-btn w3-btn-default w3-blue-wb w3-hover-red w3-medium w3-btn-min-width w3-padding--0 close" type="button" value="<?php echo $TEXT['CLOSE']; ?>" data-backlink="<?php echo $sBacklink; ?>" />
        </div>
        <div class="w3-container w3-cell m2 w3-margin-top">
<?php
    if(function_exists('edit_module_css')){ edit_module_css($sAddonName);}
?>
        </div>
</div>

  <form id="modify-setting" action="<?php echo $ModuleUrl; ?>save_settings.php" method="post" style="margin: 0;">
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" value="<?php echo $aSetting['layout']; ?>" name="old_layout"/>
    <input type="hidden" value="<?php echo $sLayoutName; ?>" name="new_layout"/>
    <input type="hidden" value="<?php echo $iLayoutId; ?>" name="layout_id"/>
    <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>"/>
    <div class="block_outer">
      <table class="news">
          <caption class="news-header"><?php echo $HEADING['GENERAL_SETTINGS']; ?></caption>
          <tbody>
              <tr>
                 <td class="setting_name"><?php echo $TEXT['POSTS_PER_PAGE']; ?></td>
                 <td class="setting_value">
                    <select class="w3-select w3-border" name="posts_per_page">
                       <option value="0"><?php echo $TEXT['UNLIMITED']; ?></option>
<?php
                       for($i = 1; $i <= 20; $i++) {
                          if($aSetting['posts_per_page'] == ($i*5)) { $selected = ' selected="selected"'; } else { $selected = ''; }
                          echo '<option value="'.($i*5).'"'.$selected.'>'.($i*5).'</option>';
                       }
?>
                    </select>
                 </td>
              </tr>
              <tr>
                 <td class="setting_name"><?php echo $MOD_NEWS['TEXT_ORDER_FROM']; ?></td>
                 <td class="setting_value">
                    <select class="w3-select w3-border" name="order_field" >
<?php
                       $sOrderField['published_when']  = $MOD_NEWS['TEXT_PUBLISHED_WHEN'];
                       $sOrderField['title']  = $TEXT['TITLE'];
                       $sOrderField['position']  = $MOD_NEWS['TEXT_POSITION'];
                       foreach($sOrderField AS $key => $name) {
                          if($aSetting['order_field'] == $key) {
?>
                      <option value="<?php echo $key;?>" selected="selected" ><?php echo $name;?></option>
<?php } else { ?>
                      <option value="<?php echo $key;?>" ><?php echo $name;?></option>
<?php }
                       }
?>
                    </select>
                 </td>
              </tr>
              <tr>
                 <td class="setting_name"><?php echo $MOD_NEWS['TEXT_ORDER_TO']; ?></td>
                 <td class="setting_value">
                    <select class="w3-select w3-border" name="order" >
                       <?php
                       $sOrder['DESC']  = $MOD_NEWS['TEXT_ORDER_DESC'];
                       $sOrder['ASC']   = $MOD_NEWS['TEXT_ORDER_ASC'];
                       foreach($sOrder as $key => $name) {
                          if($aSetting['order'] == $key) {
?>
                      <option value="<?php echo $key;?>" selected="selected" ><?php echo $name;?></option>
<?php } else { ?>
                      <option value="<?php echo $key;?>" ><?php echo $name;?></option>
<?php }
                     }
?>
                    </select>
                 </td>
              </tr>
          <tr class="w3-hide">
             <td class="setting_name"><?php echo $TEXT['COMMENTING']; ?></td>
             <td class="setting_value">
                <select class="w3-select w3-border" name="commenting">
                   <option value="none"><?php echo $TEXT['DISABLED']; ?></option>
                   <option value="public" <?php if ($aSetting['commenting'] == 'public') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PUBLIC']; ?></option>
                   <option value="private" <?php if ($aSetting['commenting'] == 'private') { echo 'selected="selected"'; } ?>><?php echo $TEXT['PRIVATE']; ?></option>
                </select>
             </td>
          </tr>
          </tbody>
      </table>
      <table class="news file-ext">
        <caption class="news-header"><?php echo $HEADING['GENERAL_COMMENTS']; ?></caption>
          <tbody>
          <tr>
             <td class="setting_name"><?php echo $TEXT['CAPTCHA_VERIFICATION']; ?></td>
             <td>
                <label class="check-container" for="use_captcha_true" >
                <input type="radio" name="use_captcha" id="use_captcha_true"  style="width: 14px; height: 14px;" value="1"<?php if ($aSetting['use_captcha'] == 1) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
                <span style="padding-left:10px!important;"><?php echo $TEXT['ENABLED']; ?></span></label>
                <label class="check-container" for="use_captcha_false" >
                <input type="radio" name="use_captcha" id="use_captcha_false"  style="width: 14px; height: 14px;" value="0"<?php if ($aSetting['use_captcha'] == 0) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
                <span style="padding-left:10px!important;"><?php echo $TEXT['DISABLED']; ?></span></label>
             </td>
          </tr>
          <tr>
             <td class="setting_name"><?php echo $TEXT['DSGVO']; ?></td>
             <td>
                <label class="check-container" for="use_data_protection_true" >
                <input type="radio" name="use_data_protection" id="use_data_protection_true"  style="width: 14px; height: 14px;" value="1"<?php if ($aSetting['use_data_protection'] == 1) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
                <span style="padding-left:10px!important;"><?php echo $TEXT['ENABLED']; ?></span></label>
                <label class="check-container" for="use_data_protection_false" >
                <input type="radio" name="use_data_protection" id="use_data_protection_false"  style="width: 14px; height: 14px;" value="0"<?php if ($aSetting['use_data_protection'] == 0) { echo ' checked="checked"'; } ?> />
                <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
                <span style="padding-left:10px!important;"><?php echo $TEXT['DISABLED']; ?></span></label>
             </td>
          </tr>

          <tr>
             <td class="setting_name"><?php echo $TEXT['DSGVO_LINK']; ?></td>
             <td>
                <div class="w3-row" style="width: 98%;">
                    <div class="input-container">
                    <i class="fa fa-search icon w3-blue-wb">&#160;</i>
                    <input class="input-field" placeholder="Search" id="dsgvoInput" type="text" value=""/>
                    </div>
                </div>
                <select
                id="dsgvo"
                class="w3-border w3-select-multi js-dsgvo-multiple w3-select"
                size="4"
                style="max-width: 98%!important;font-family:monospace;font-size: 14px;"
                name="data_protection_link"
                >
                  <option value="-1"><?php echo $TEXT['PLEASE_SELECT']; ?></option>
<?php
                  foreach($aSelectSections as $aRes) {
                      $option_link = explode('||',$aRes['descr']);
                      $sDisabled = $option_link[0] ? '':' disabled="disabled"';
                      $sSelected = (($option_link[0] == $target_section_id) ? $sSelect : '');
                      $sFlagUrl  = WB_URL.'/modules/WBLingual/flags/png/'.strtolower($aRes['language']);  // {ADDON_LANG_URL}flags/png/{PAGE_LANG}
?>
                      <option <?php echo $sDisabled ;?>value="<?php echo $option_link[0];?>"<?php echo $sSelected;?> class="flag-box" style="background-image: url(<?php echo $sFlagUrl;?>-24.png);"><?php echo $option_link[1];?></option>
<?php
                  }?>
              </select>
             </td>
          </tr>

<?php
      /* Make's sure GD library is installed */
      if(extension_loaded('gd') && function_exists('imageCreateFromJpeg')) {
?>
          <tr>
             <td class="setting_name"><?php echo $TEXT['RESIZE_IMAGE_TO']; ?>:</td>
             <td class="setting_value">
                <select class="w3-select w3-border" name="resize"  >
                   <option value="0"><?php echo $TEXT['NONE']; ?></option>
<?php
                   $SIZES['50']  = '50x50px';
                   $SIZES['75']  = '75x75px';
                   $SIZES['100'] = '100x100px';
                   $SIZES['125'] = '125x125px';
                   $SIZES['150'] = '150x150px';
                   foreach($SIZES as $size => $size_name) {
                      if($aSetting['resize'] == $size) { $selected = ' selected="selected"'; } else { $selected = ''; }
                      echo nl2br(sprintf('<option value="%s"%s>%s</option>'."\n",$size,$selected,$size_name));
//                      echo '<option value="'.$size.'"'.$selected.'>'.$size_name.'</option>';
                   }
?>
                </select>
             </td>
          </tr>
<?php } ?>
          </tbody>
       </table>
    </div><!--  -->

    <div class="w3-bar w3-margin-top">
        <div class="w3-bar-item w3-mobile">
             <input  class="w3-padding-4 w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width" name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" />
        </div>
        <div class="w3-bar-item w3-mobile">
            <input  class="w3-padding-4 w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width" name="save_close" type="submit" value="<?php echo $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" style="padding: 4px 24px!important;;"/>
        </div>
        <div class="w3-bar-item w3-mobile">
            <input  id="cancel" class="w3-padding-4 w3-right w3-btn w3-btn-default w3-blue-wb w3-hover-red w3-medium w3-btn-min-width close" type="button" value="<?php echo $TEXT['CLOSE']; ?>" data-backlink="<?php echo $sBacklink; ?>" />
        </div>
    </div>
  </form>

</div>
</article>

<article class="news-block w3-container block_outer">

    <form action="<?php echo WB_URL; ?>/modules/<?php echo $sAddonName;?>/modify_settings.php#news_layout" method="post" >
        <input type="hidden" value="<?php echo $page_id; ?>" name="page_id"/>
        <input type="hidden" value="<?php echo $section_id; ?>" name="section_id"/>
        <input type="hidden" value="<?php echo $sLayoutName; ?>" name="new_layout"/>
        <input type="hidden" value="<?= $iLayoutId;?>" name="layout_id"/>
        <input type="hidden" value="news_layout" name="<?= $sLayoutName;?>"/>
        <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>"/>
        <input type="hidden" value="<?php echo $aSetting['layout']; ?>" name="old_layout"/>
      <table class="news file-ext">
        <caption id="news_layout" class="news-header"><?php echo $HEADING['GENERAL_LAYOUTS']; ?></caption>
        <thead>
            <tr>
              <th colspan="2">
<?php
                if (count($aMsg)){
?>
                   <div class="w3-panel w3-pale-red w3-leftbar w3-border-red w3-padding ">
                      <p class="w3-large w3-serif">
<?PHP
                    foreach ($aMsg as $msg){
                      if (empty(($msg))){continue;}
                        echo nl2br(sprintf("%s\n",$msg));
                    }
?>
                      </p>
                   </div>
<?php
                }elseif (count($aSuccess)){
?>
                   <div class="w3-panel w3-pale-green w3-leftbar w3-border-green w3-padding ">
                      <p class="w3-large w3-serif">
<?php
                    foreach ($aSuccess as $msg){
                      if (empty(($msg))){continue;}
                        echo nl2br(sprintf("%s\n",$msg));
                    }
?>
                      </p>
                   </div>
<?php
                } else {
?>
                   <div class="w3-panel w3-leftbar w3-light-grey">
                       <p class="w3-large w3-serif">
                          <i class="w3-border-0"><b class="w3-text-red w3-medium">&#160;</b> New placeholder [GROUP_BACK] link for active group filter in post_header!</i>
                       </p>
                   </div>
<?php
                }
?>
              </th>
            </tr>
            <tr style="line-height: 2.9;">
               <th class="setting_name" style="vertical-align: text-top;"><?php echo $HEADING['CHOOSE_LAYOUTS']; ?></th>
                <td class="setting_value">

                    <div class="w3-bar">
                        <div class="w3-bar-item">
                          <select name="layout_id" id="layout_id" class="w3-select w3-border" style="min-width:13.1em;">
<?php
                foreach($aSelectLayout as $aLayout) {
                    $sSelected = (($aLayout['id']==$aSetting['id']) ? ' selected="selected"' : '');
?>
                    <option value="<?php echo $aLayout['id'];?>"<?php echo $sSelected;?>><?php echo $aLayout['layout'];?> [<?= $aLayout['id'];?>]</option>
<?php } ?>
                          </select>
                        </div>

                        <div class="w3-bar-item" style="margin-top: -5px;">
                            <input id="install_layout" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-padding-0" type="submit" name="install_layout" value="<?php echo $MOD_NEWS['LOAD']; ?>" style="margin-right: 0.525em;" />
                        </div>
                        <div class="w3-bar-item" style="margin-top: -5px;">
                          <input id="save_layout" class="bar-item w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-padding-0" type="submit" name="save_layout" value="<?php echo $TEXT['SAVE']; ?>" style="margin-right: 0.525em;" />
                        </div>
                        <div class="w3-bar-item" style="margin-top: -5px;">
<?php if ($sDisabledLayout) { ?>
                            <input id="delete_layout" class="bar-item w3-btn w3-btn-default w3-blue-wb w3-medium w3-btn-min-width w3-padding-0 w3-border" type="submit" name="delete_layout" value="<?php echo $TEXT['DELETE']; ?>"  disabled="disabled" style="margin-right: 0.525em;" />
<?php } else { ?>
                            <input id="delete_layout" class="bar-item w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-padding-0" type="submit" name="delete_layout" value="<?php echo $TEXT['DELETE']; ?>" style="margin-right: 0.525em;" />
<?php } ?>
                        </div>
                    </div>

                    <div class="tooltip w3-content" style="width:45%;">
                        <div class="w3-bar">
                          <div class="w3-bar-item" style="padding-right:0.9em;">
                              <input value="" type="text" name="new_layout" id="new_layout" class="w3-input w3-border" placeholder="" pattern=".*\S.*" style="width: 13.45em !important;height: 36px !important;" />
                          </div>
                          <div id="add_layout" class="w3-bar-item" style="margin-top: -5px;">
                              <input id="add_layout" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-padding-0" type="submit" name="add_layout" value="<?php echo $TEXT['ADD']; ?>" style="margin-right: 10px;;" />
                          </div>
                        <span class="w3-text w3-tag tooltiptext"><?php echo $sTooltip;?></span>
                      </div>
                   </div>

                </td>
            </tr>
        </thead>
        <tbody>
              <tr>
                 <th class="setting_name"><?php echo $TEXT['HEADER']; ?></th>
                 <td class="setting_value">
                    <textarea name="header" rows="5" cols="1" style="width: 98%;"><?php echo ($aSetting['header']); ?></textarea>
                 </td>
              </tr>
              <tr>
                 <th class="setting_name"><?php echo $TEXT['POST'].' '.$TEXT['LOOP']; ?></th>
                 <td class="setting_value">
                    <textarea name="post_loop" rows="10" cols="1" style="width: 98%;"><?php echo ($aSetting['post_loop']); ?></textarea>
                 </td>
              </tr>
              <tr>
                 <th class="setting_name"><?php echo $TEXT['FOOTER']; ?></th>
                 <td class="setting_value">
                    <textarea name="footer" rows="10" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['footer'])); ?></textarea>
                 </td>
              </tr>
              <tr>
                 <th class="setting_name"><?php echo $TEXT['POST_HEADER']; ?></th>
                 <td class="setting_value">
                    <textarea name="post_header" rows="10" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['post_header'])); ?></textarea>
                 </td>
              </tr>
              <tr>
                 <th class="setting_name"><?php echo $TEXT['POST_FOOTER']; ?></th>
                 <td class="setting_value">
                    <textarea name="post_footer" rows="5" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['post_footer'])); ?></textarea>
                 </td>
              </tr>
              </tbody>
              <tbody>
              <tr>
                  <th class="setting_name">&#160;</th>
                  <td class="setting_value"><h3 ><?php echo $HEADING['LAYOUT_COMMENTS']; ?></h3></td>
              </tr>
          <tr>
             <th class="setting_name"><?php echo $TEXT['COMMENTS'].' '.$TEXT['HEADER']; ?></th>
             <td class="setting_value">
                <textarea name="comments_header" rows="4" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['comments_header'])); ?></textarea>
             </td>
          </tr>
          <tr>
             <th class="setting_name w3-cell-middle"><?php echo $TEXT['COMMENTS'].' '.$TEXT['LOOP']; ?></th>
             <td class="setting_value">
                <textarea name="comments_loop" rows="8" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['comments_loop'])); ?></textarea>
             </td>
          </tr>
          <tr>
             <th class="setting_name w3-cell-middle"><?php echo $TEXT['COMMENTS'].' '.$TEXT['FOOTER']; ?></th>
             <td class="setting_value">
                <textarea name="comments_footer" rows="3" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['comments_footer'])); ?></textarea>
             </td>
          </tr>
          <tr>
             <th class="setting_name w3-cell-middle"><?php echo $TEXT['COMMENTS'].' '.$TEXT['PAGE']; ?></th>
             <td class="setting_value">
                <textarea name="comments_page" rows="4" cols="1" style="width: 98%;"><?php echo str_replace($raw, $friendly, ($aSetting['comments_page'])); ?></textarea>
             </td>
          </tr>
          </tbody>
        </table>
     </form>

    </article>
<?php
    if ($print_info_banner) { }
// Print admin footer
    $admin->print_footer();
