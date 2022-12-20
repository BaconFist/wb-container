<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: modify_comment.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/modify_comment.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* --------------------------------------------------------------- */
// execute config.php
/* --------------------------------------------------------------- */
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

//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
    $sChecked     = ' checked="checked"';
    $sSelected    = ' selected="selected"';
try {
    // to print with or without header, default is with header
    $admin_header=true;
    // Workout if the developer wants to show the info banner
    $print_info_banner = ($aRequestVars['infoBanner'] ?? true); // true/false
    // Tells script to update when this page was last updated
    $update_when_modified = false;
    // Include WB admin wrapper script to sanitize page_id and section_id, print SectionInfoLine
    require(WB_PATH.'/modules/admin.php');

    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
    $sAppUrl  = $oReg->AppUrl;
    $sAddonUrl         = $oReg->AppUrl.$sAddonRel;
    $sAddonTemplateUrl = $sAddonUrl.'templates/default/';
    $sAddonThemeUrl    = $sAddonUrl.'themes/default/';
    $sAbsAddonPath     = $oReg->AppPath.$sAddonRel;

// load module language file
    if (\is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (\is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}

    $comment_id = ($admin->getIdFromRequest('comment_id') ?? false);
    $post_id    = ($admin->getIdFromRequest('post_id') ?? false);

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $bBackLink = ($aRequestVars['save_close'] ?? false);
    $bBackLink = ($aRequestVars['close'] ?? $bBackLink);

    $sBackLink        = $oReg->AcpUrl.'pages/modify.php?page_id='.$page_id;
    $sBackCommonLink  = $sAddonUrl.'modify_comment.php?page_id='.$page_id.'&section_id='.$section_id.'&comment_id='.SecureTokens::getIDKEY($comment_id);
    $sBackPostLink    = $sAddonUrl.'modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$post_id;
    $sBackLinkUrl     = ($bBackLink ? $sBackPostLink.'&'.$sFtanQuery : $sBackCommonLink.'&'.$sFtanQuery );

    if ($sSecureToken && !$comment_id) {
        $sBackLinkUrl = $sBackPostLink;
        $aMessage = \sprintf("%s\n",$MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($aMessage);
    }

    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];

/**/
?>
<div id="news-wrapper" class="news-block">
    <h2><?php echo $TEXT['MODIFY'].' '.$TEXT['COMMENT']; ?></h2>
<?php
// Get header and footer
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_comments` '
          . 'WHERE `comment_id` = \''.$comment_id.'\'';
    if ($query_content = $database->query($sql)) {
        if (!($fetch_content = $query_content->fetchRow(MYSQLI_ASSOC))) {
            $sBackLinkUrl = $sBackPostLink;
            $aMessage = \sprintf("%s\n",$MESSAGE['PAGES_NOT_FOUND']);
            throw new \Exception ($aMessage);
        }
?>
    <form id="modify_save" action="<?php echo $sAddonUrl;?>save_comment.php" method="post" style="margin: 0;">
        <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
        <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
        <input type="hidden" name="post_id" value="<?php echo $fetch_content['post_id']; ?>" />
        <input type="hidden" name="comment_id" value="<?php echo $fetch_content['comment_id']; ?>" />
        <input type="hidden" name="<?= $sFtan['name'];?>" value="<?= $sFtan['value'];?>" />

        <table class="w3-table">
            <tbody>
                  <tr>
                      <td class="setting_name w3-right-align" style="width: 25%;"><?php echo $TEXT['TITLE']; ?>:</td>
                      <td class="w3-rest">
                          <input type="text" name="title" value="<?php echo (htmlspecialchars($fetch_content['title'])); ?>" style="width: 90%;" />
                      </td>
                  </tr>
                  <tr>
                      <td class="setting_name w3-right-align" style="width: 25%;"><?php echo $TEXT['COMMENT']; ?>:</td>
                      <td class="w3-textarea">
                          <textarea name="comment" rows="10" cols="1" style="width: 90%; height: 100px;"><?php echo (htmlspecialchars($fetch_content['comment'])); ?></textarea>
                      </td>
                  </tr>
                    <tr>
                       <td class="setting_name w3-right-align"><?php echo $TEXT['ACTIVE']; ?>:</td>
                       <td>

                       <div>
                            <label class="radio" for="active_true">
                                <input id="active_true" type="radio" name="active" value="1"<?php echo (($fetch_content['active']) ? $sChecked : '');?> />
                                <span><?php echo $TEXT['YES']; ?></span>
                            </label>
                            <label class="radio" for="active_false">
                                <input id="active_false" type="radio" name="active" value="0"<?php echo ((!$fetch_content['active']) ? $sChecked : '');?> />
                                <span><?php echo $TEXT['NO']; ?></span>
                            </label>
                        </div>
                       </td>
                    </tr>
        </tbody>
        </table>

    <div class="w3-bar w3-margin-top">
        <div class="w3-bar-item w3-mobile" style="margin-left: 21%!important;">&nbsp;</div>
        <div class="w3-bar-item w3-mobile">
             <input class="w3-btn w3-blue-wb w3-hover-green w3-medium w3-btn-padding" name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" />
        </div>
        <div class="w3-bar-item w3-mobile">
            <input class="w3-btn  w3-blue-wb w3-hover-green w3-medium w3-btn-padding" name="save_close" type="submit" value="<?php echo $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>" />
        </div>
        <div class="w3-bar-item w3-mobile">
            <button id="cancel" name="close" class="w3-right w3-btn w3-blue-wb w3-hover-red w3-medium w3-btn-padding" formaction="<?php echo $sBackPostLink; ?>" ><?php echo $TEXT['CLOSE']; ?></button>
        </div>
    </div>
    </form>
<?php
    }
?>
</div>
<?php
    if ($print_info_banner) { ?>
      <!--
</div>
-->
<?php }

}catch (\Exception $ex) {
    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBackLinkUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
