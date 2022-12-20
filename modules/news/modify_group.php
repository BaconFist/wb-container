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
 * @version         $Id: modify_group.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/modify_group.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* ---------------------------------------------------------------------------------------- */
/*
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = '/modules/'.$sAddonName;
*/
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = '/modules/'.$sAddonName.'/';
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}
/* ---------------------------------------------------------------------------------------- */
/*
    $sAddonUrl     = WB_URL.$sAddonRel;
    $sAddonThemeUrl = $sAddonUrl.'templates/default/';
*/
//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

// Tells script to update when this page was last updated
    $update_when_modified = true;
// show the info banner
    $print_info_banner = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

try {
/*
    $sAddonUrl = $sAddonUrl = WB_URL.$sAddonRel;
    $sAddonName = \basename(__DIR__);
    $sAddonRel = '/modules/'.$sAddonName.'/';
    $sLocalDebug  =  is_readable($sAbsAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAbsAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
*/

// Tells script to update when this page was last updated
    $update_when_modified = true;
// print with or without header
    $admin_header = false;
// show the info banner
    $print_info_banner = false;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $oReg      = WbAdaptor::getInstance();
    $database  = $oReg->getDatabase();
    $oRequest  = $oReg->getRequester();
    $oTrans    = $oReg->getTranslate();
    $oApp      = $oReg->getApplication();
    $sAppUrl   = $oReg->AppUrl;
    $sAddonUrl = $oReg->AppUrl.$sAddonRel;
    $sAddonTemplateUrl = $sAddonUrl.'templates/default/';
    $sAddonThemeUrl    = $sAddonUrl.'themes/default/';
    $sAbsAddonPath     = $oReg->AppPath.$sAddonRel;

    $sAddonBackUrl = $oReg->AcpUrl;

    if (\is_readable($sAbsAddonPath.'languages/EN.php')) {require($sAbsAddonPath.'/languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.LANGUAGE.'.php');}

    $oTrans->enableAddon('modules\\'.$sAddonName);

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink  = $sAddonBackUrl.'pages/modify.php?page_id='.$page_id.$sSectionIdPrefix;

    if ($sSecureToken && !SecureTokens::checkFTAN()) {
        $admin->print_error(sprintf('[%03d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],__LINE__), $sBacklink);
    }

    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];

    $iGroupId = $oApp->getIdFromRequest('group_id');

    $fetch_content = [];
// Get header and footer
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_groups` '
          . 'WHERE `group_id` = '.(int)$iGroupId;
    if ($query_content = $database->query( $sql )){
        if (is_null($fetch_content = $query_content->fetchRow( MYSQLI_ASSOC ))){
            $fetch_content['title'] = '';
            $fetch_content['active'] = 1;
            $fetch_content['group_id'] = $iGroupId;
            $fetch_content['section_id'] = $section_id;
            $fetch_content['page_id'] = $page_id;
        }
     }
    $sGroupImageRel = MEDIA_DIRECTORY.'/.news/image'.$iGroupId.'.jpg';
}catch (\Exception $ex) {
//    $oApp->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $oApp->print_error ($sErrMsg, $sBacklink);
    exit;
}

$sPageIdKey = SecureTokens::getIDKEY($page_id);
?>
<article class="news-block w3-container w3-margin-bottom">
<h2><?php echo $TEXT['ADD'].'/'.$TEXT['MODIFY'].' '.$TEXT['GROUP']; ?></h2>

<form id="modify-group" action="<?php echo $sAddonUrl;?>save_group.php" method="post" enctype="multipart/form-data" >
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" name="group_id" value="<?php echo $iGroupId; ?>" />
    <input type="hidden" name="<?= $sFtan['name'];?>" value="<?= $sFtan['value']; ?>" />
    <input type="hidden" name="save-type" value="<?php echo (($iGroupId!=0) ? 'update' : 'insert'); ?>" />
    <table class="w3-table groups">
      <tbody>
        <tr>
           <td class="setting_name"><?php echo $TEXT['TITLE']; ?>:</td>
           <td class="w3-input w3-border-0">
              <input class="w3-margin-left w3-padding-4" accept="image/png, image/jpeg" type="text" name="title" value="<?php echo (htmlspecialchars($fetch_content['title'])); ?>" style="width: 98%;height: 32px;" maxlength="255" />
           </td>
        </tr>
        <tr>
           <td class="setting_name"><?php echo $TEXT['IMAGE']; ?>:</td>
           <td class="">
              <div class="w3-bar">
                  <div class="w3-bar-item upload" style="font-weight: normal;">
                  <label class="fileContainer w3-blue-wb w3-opennav w3-hover-text-orange" style="padding: 8px;">
                      <i class="fa fa-upload fa-fw"></i>
                      <span id="uploadText" class="w3-medium w3-btn-min-width"><?= $TEXT['BROWSE_UPLOAD_FILE']; ?></span>
                      <input class=" w3-opennav" id="image-select" type="file" name="image-select"/>
                  </label>
                  <label><b class="w3-text-blue-wb">&nbsp;</b> </label>
                  </div>
                  <div id="photos" class="w3-bar-item ">
<?php
if (\file_exists(WB_PATH.$sGroupImageRel)) { ?>
                    <span><img class="w3-medium thumb" src="<?php echo WB_URL.$sGroupImageRel;?>" alt="" style="width:100%;max-width:46px"/></span>
<?php } ?>
                  </div>
                  <div class="w3-bar-item ">
                  <input type="checkbox" id="delete_image" value="1" name="delete_image" class="w3-check w3-bar-item" />
                  <label class="w3-validate" title='<?= $TEXT['DELETE'];?>' for="delete_image"><?= $TEXT['DELETE'];?></label>
                  </div>
              </div>
           </td>
        </tr>

        <tr>
           <td class="setting_name"><?php echo $TEXT['ACTIVE']; ?>:</td>
           <td class="w3-input w3-border-0">
              <div class="w3-margin-left">
                  <label class="check-container" for="active_true" >
                  <input type="radio" name="active" id="active_true" style="width: 14px; height: 14px;" value="1"<?php if ($fetch_content['active'] == 1) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['YES']; ?></span></label>
                  <label class="check-container" for="active_false" >
                  <input type="radio" name="active" id="active_false" style="width: 14px; height: 14px;" value="0"<?php if ($fetch_content['active'] == 0) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['NO']; ?></span></label>
              </div>

           </td>
        </tr>
      </tbody>

    </table>

<table class="w3-table">
    <tbody>
        <tr>
            <td colspan="2">
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-6" name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" />
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-6" name="save_close" type="submit" value="<?php echo $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" />
                <button name="page_id" value="<?php echo $page_id;?>" class="url-close w3-btn w3-blue-wb w3-hover-red w3-medium " type="button" data-overview="<?php echo ADMIN_DIRECTORY; ?>/pages/modify.php?page_id=<?= $page_id; ?>" >
                    <i class="fa fa-times w3-left-align">&nbsp;</i>
                    <span class="w3-padding--0"><?php echo $TEXT['CLOSE']; ?></span>
                </button>
            </td>
        </tr>
    </tbody>
</table>
</form>
</article>
<script>
    var News = {
        WB_URL : '<?php echo $sAppUrl;?>',
        AddonUrl : '<?php echo $sAddonUrl;?>',
        THEME_URL : '<?php echo THEME_URL;?>',
        ThemeUrl:  '<?php echo $sAddonThemeUrl;?>',
        PluginUrl:'<?php echo $sAppUrl;?>include/plugins/',
        FancyboxUrl:'<?php echo $sAppUrl;?>include/plugins/default/fancybox/1.3.4/'
    };
/*
    var JqFancyBoxCss = News.FancyboxUrl+"jquery.fancybox-1.3.4.css";
    if (typeof LoadOnFly==='undefined'){
        $.insert(JqFancyBoxCss);
    } else {
        LoadOnFly('head', JqFancyBoxCss);
    }
*/
</script>
<?php if ($print_info_banner){ ?>
  </div>
<?php }
// Print admin footer
$admin->print_footer();
