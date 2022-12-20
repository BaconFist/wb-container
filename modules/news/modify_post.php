<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of modules/news/modify_post.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: modify_post.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* ---------------------------------------------------------------------------------------- */
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = 'modules/'.$sAddonName.'/';
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}
/* ---------------------------------------------------------------------------------------- */

//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

// Tells script to update when this page was last updated
    $update_when_modified = true;
// print with or without header
//    $admin_header = false;
// show the info banner
    $print_info_banner = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    if (\is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (\is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}

    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
    $sAddonBackUrl = $oReg->AcpUrl;
    $oTrans->enableAddon('modules\\'.$sAddonName);

    $sAddonUrl     = $oReg->AppUrl.$sAddonRel.'';
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    $sAddonThemeUrl = $sAddonUrl.'/themes/default/';
    $sAppUrl = $oReg->AppUrl;

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink  = $sAddonBackUrl.'pages/modify.php?page_id='.$page_id.$sSectionIdPrefix;

    if ($sSecureToken && !SecureTokens::checkFTAN()) {
        $admin->print_error(sprintf('[%03d] '.$MESSAGE['GENERIC_SECURITY_ACCESS'],__LINE__), $sBacklink);
    }

    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];

//    $iPostId = (int)$aRequestVars['post_id'];
//    $iPostId = (int)(\bin\SecureTokens::checkIDKEY('post_id'));
    $iPostId = $oApp->getIdFromRequest('post_id');
// Get header and footer
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id` = '.(int)$iPostId.' '
          . 'ORDER BY `position` ASC';
    if ($oNewsPost = $database->query($sql)){
        if (is_null($aNewsPost = $oNewsPost->fetchRow(MYSQLI_ASSOC))){
            $aNewsPost['post_id'] = 0;
            $aNewsPost['group_id'] = 0;
            $aNewsPost['title'] = '';
            $aNewsPost['commenting'] = 'none';
            $aNewsPost['moderated']  = 0;
            $aNewsPost['active']= 1;
            $aNewsPost['published_when'] = 0;
            $aNewsPost['published_until'] = 0;
            $aNewsPost['content_short'] = '';
            $aNewsPost['content_long'] = '';
        }
    }
    if (!defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR=="none" || !file_exists($sModulesPath.'/'.WYSIWYG_EDITOR.'/include.php')) {
       function show_wysiwyg_editor($name,$id,$content,$width,$height) {
          echo '<textarea name="'.$name.'" id="'.$id.'" rows="10" cols="1" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
       }
    } else {
       $sTablename = 'mod_news_posts';
       $id_list=array("content_short","content_long");
       require($sModulesPath.'/'.WYSIWYG_EDITOR.'/include.php');
    }

// include jscalendar-setup
    $jscal_use_time = true; // whether to use a clock, too
    require_once(WB_PATH."/include/jscalendar/wb-setup.php");

// get groups from table
    $aNewsGroup = [];
     $sql = 'SELECT `group_id`, `title` FROM `'.TABLE_PREFIX.'mod_news_groups` '
     .'WHERE `section_id` = '.$section_id.' '
     .'ORDER BY `position` ASC';
     if ($oNewsGroup = $database->query($sql)){
        if (($iNewsGroup = $oNewsGroup->numRows()) > 0) {
        // Loop through groups
            while(is_null($aNewsGroup = $oNewsGroup->fetchRow(MYSQLI_ASSOC))) {
                  $oNewsGroup['group_id']   = 0;
                  $oNewsGroup['section_id'] = $section_id;
                  $oNewsGroup['page_id']    = $page_id;
                  $oNewsGroup['active']     = 1;
                  $oNewsGroup['position']   = 0;
                  $oNewsGroup['title']      = '';
           }//end while
      }// numrow
    }
?>
<article class="news-block w3-container w3-margin-bottom">
<h2><?php echo $TEXT['ADD'].'/'.$TEXT['MODIFY'].' '.$TEXT['POST']; ?></h2>
    <div class="jsadmin jcalendar hide"></div>
    <div id="AddonFolder" class="w3-hide"><?php echo basename(__DIR__);?></div>
    <script >
        if (typeof Addon!=="object") {
          var Addon = {
                AddonName: "<?php echo $sAddonName; ?>",
                page_id:<?php echo $page_id; ?>,
                section_id:<?php echo $section_id; ?>,
          }
        };
    </script>
<div class="w3-row">
<form id="modify-post-save" action="<?php echo $sAddonUrl;?>savePost.php" method="post">
    <input type="hidden" name="<?= $sFtan['name'];?>" value="<?= $sFtan['value']; ?>" />
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" name="post_id" value="<?php echo $iPostId; ?>" />
    <input type="hidden" name="save-type" value="<?php echo (($iPostId!=0) ? 'update' : 'insert'); ?>" />

    <table class="news w3-table">
        <tbody>
            <tr>
               <td class="setting_name"><?php echo $TEXT['TITLE']; ?>:</td>
               <td>
                  <input class="w3-input w3-border w3-padding-4" type="text" name="title" value="<?php echo (htmlspecialchars($aNewsPost['title'])); ?>" style="width: 94.8%;" />
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $TEXT['GROUP']; ?>:</td>
               <td>
                  <select class="w3-select w3-border" name="group" style="width: 94.8%;">
                     <option value="0"><?php echo $TEXT['NONE']; ?></option>
<?php
                     $sql = 'SELECT `group_id`, `title` FROM `'.TABLE_PREFIX.'mod_news_groups` '
                     .'WHERE `section_id` = '.$section_id.' '
                     .'ORDER BY `position` ASC';
                     if ($query = $database->query($sql)){
                        if ($query->numRows() > 0) {
                        // Loop through groups
                            while(!is_null($group = $query->fetchRow(MYSQLI_ASSOC))) {
?>
                           <option value="<?php echo $group['group_id']; ?>"<?php if($aNewsPost['group_id'] == $group['group_id']) { echo ' selected="selected"'; } ?>><?php echo $group['title']; ?></option>
<?php
                            }
                        }
                     }
?>
                  </select>
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $TEXT['COMMENTING']; ?>:</td>
               <td>
                  <select class="w3-select w3-border" name="commenting" style="width: 94.8%;">
                     <option value="none"><?php echo $TEXT['DISABLED']; ?></option>
                     <option value="public" <?php if ($aNewsPost['commenting'] == 'public') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PUBLIC']; ?></option>
                     <option value="private" <?php if ($aNewsPost['commenting'] == 'private') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PRIVATE']; ?></option>
                  </select>
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $oTrans->MOD_NEWS_MODERATED_COMMENT; ?>:</td>
               <td>
                  <label class="check-container" for="moderated_true" >
                  <input type="radio" name="moderated" id="moderated_true"  style="width: 14px; height: 14px;" value="1"<?php if ($aNewsPost['moderated'] == 1) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['YES']; ?></span></label>
                  <label class="check-container" for="moderated_false" >
                  <input type="radio" name="moderated" id="moderated_false"  style="width: 14px; height: 14px;" value="0"<?php if ($aNewsPost['moderated'] == 0) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['NO']; ?></span></label>
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $TEXT['ACTIVE']; ?>:</td>
               <td>
                  <label class="check-container" for="active_true" >
                  <input type="radio" name="active" id="active_true"  style="width: 14px; height: 14px;" value="1"<?php if ($aNewsPost['active'] == 1) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['YES']; ?></span></label>
                  <label class="check-container" for="active_false" >
                  <input type="radio" name="active" id="active_false"  style="width: 14px; height: 14px;" value="0"<?php if ($aNewsPost['active'] == 0) { echo ' checked="checked"'; } ?> />
                  <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
                  <span style="padding-left:10px!important;"><?php echo $TEXT['NO']; ?></span></label>
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $TEXT['PUBL_START_DATE']; ?>:</td>
               <td>
               <ul class="horizontal">
<?php
            if ($aNewsPost['published_when']==0) {
                $iPublishedWhen = date($jscal_format, strtotime((date('Y-m-d H:i')))+TIMEZONE);
            } else {
                $iPublishedWhen = date($jscal_format, $aNewsPost['published_when']+TIMEZONE);
            }
?>
               <li><input class="w3-input w3-border" type="text" id="publishdate" name="publishdate" value="<?php echo $iPublishedWhen;?>" style="width: 10.5em;" /></li>
               <li><img src="<?php echo THEME_URL ?>/images/clock_16.png" id="publishdate_trigger" style="cursor: pointer;" title="<?php echo $TEXT['CALENDAR']; ?>" alt="<?php echo $TEXT['CALENDAR']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" /></li>
               <li><img src="<?php echo THEME_URL ?>/images/clock_del_16.png" style="cursor: pointer;" title="<?php echo $TEXT['DELETE_DATE']; ?>" alt="<?php echo $TEXT['DELETE_DATE']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" onclick="document.modify.publishdate.value=''" /></li>
               </ul>
               </td>
            </tr>
            <tr>
               <td class="setting_name"><?php echo $TEXT['PUBL_END_DATE']; ?>:</td>
               <td>
               <ul class="horizontal">
<?php
            if ($aNewsPost['published_until']==0) {
                $iPublishedUntil = '';
            } else {
                $iPublishedUntil = date($jscal_format, $aNewsPost['published_until']+TIMEZONE);
            }
?>
               <li><input class="w3-input w3-border" type="text" id="enddate" name="enddate" value="<?php echo $iPublishedUntil;?>" style="width: 10.5em;" /></li>
               <li><img src="<?php echo THEME_URL ?>/images/clock_16.png" id="enddate_trigger" style="cursor: pointer;" title="<?php echo $TEXT['CALENDAR']; ?>" alt="<?php echo $TEXT['CALENDAR']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" /></li>
               <li><img src="<?php echo THEME_URL ?>/images/clock_del_16.png" style="cursor: pointer;" title="<?php echo $TEXT['DELETE_DATE']; ?>" alt="<?php echo $TEXT['DELETE_DATE']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" onclick="document.modify.enddate.value=''" /></li>
               </ul>
               </td>
            </tr>
        </tbody>
    </table>

<table class="news w3-table">
    <tbody>
        <tr>
           <td  class="setting_name"><?php echo $TEXT['SHORT']; ?>:</td>
           <td class="setting_value">
        <?php
              $contentShort = $aNewsPost['content_short'];
              $contentLong  = $aNewsPost['content_long'];
              $sFilterApi   = WB_PATH.'/modules/output_filter/OutputFilterApi.php';
              if (is_readable($sFilterApi) && !function_exists('getOutputFilterSettings')){require($sFilterApi);}
              if (function_exists('OutputFilterApi')){
                  $contentShort = OutputFilterApi('ReplaceSysvar', $contentShort);
                  $contentLong  = OutputFilterApi('ReplaceSysvar', $contentLong);
              }
              show_wysiwyg_editor("content_short","content_short",htmlspecialchars($contentShort),"100%","240px","utf8mb4",'WB_Basic');
           ?>
           </td>
        </tr>
        <tr>
           <td class="setting_name"><?php echo $TEXT['LONG']; ?>:</td>
           <td class="setting_value">
        <?php
              show_wysiwyg_editor("content_long","content_long",htmlspecialchars($contentLong),"100%","350px","utf8mb4",'WB_Basic');
        ?>
           </td>
        </tr>
    </tbody>
</table>

<div class="w3-row ">
    <div class="w3-bar w3-margin-top">
        <div class="w3-bar-item w3-mobile" style="margin-left: 21%!important;">&nbsp;</div>
        <div class="w3-bar-item w3-mobile">
             <input class="w3-btn w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-btn-padding" name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" >
        </div>
        <div class="w3-bar-item w3-mobile">
            <input class="w3-btn w3-blue-wb w3-hover-green w3-medium w3-btn-min-width w3-btn-padding" name="save-close" type="submit" value="<?php echo $TEXT['SAVE'].' & '.$TEXT['CLOSE']; ?>">
        </div>
        <div class="w3-bar-item w3-mobile">
            <input id="cancel" class="w3-btn w3-blue-wb w3-hover-red w3-medium w3-btn-min-width w3-btn-padding" type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="window.location='<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id.'#'.$sSectionIdPrefix.$section_id; ?>';" />
        </div>
    </div>
</div>

</form>
</div>

<script>
   Calendar.setup(
      {
         inputField  : "publishdate",
         ifFormat    : "<?php echo $jscal_ifformat ?>",
         button      : "publishdate_trigger",
         firstDay    : <?php echo $jscal_firstday ?>,
         <?php if(isset($jscal_use_time) && $jscal_use_time==TRUE)
            { ?>
            showsTime   : "true",
            timeFormat  : "24",
         <?php
            } ?>
         date        : "<?php echo $jscal_today ?>",
         range       : [1970, 2037],
         step        : 1
      }
   );
   Calendar.setup(
      {
         inputField  : "enddate",
         ifFormat    : "<?php echo $jscal_ifformat ?>",
         button      : "enddate_trigger",
         firstDay    : <?php echo $jscal_firstday ?>,
         <?php if(isset($jscal_use_time) && $jscal_use_time==TRUE)
            { ?>
            showsTime   : "true",
            timeFormat  : "24",
         <?php
            } ?>
         date        : "<?php echo $jscal_today ?>",
         range       : [1970, 2037],
         step        : 1
      }
   );
</script>

<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['COMMENT']; ?></h2>

<?php

// Loop through existing posts
    $sCommandSql  = '
    SELECT * FROM `'.TABLE_PREFIX.'mod_news_comments`
    WHERE `section_id` = '.$section_id.'
      AND `post_id` ='.$iPostId.'
    ORDER BY `commented_when` DESC
    ';

    $query_comments = $database->query($sCommandSql);
    $iCommentRow = $query_comments->numRows();
    $bShowCommonts = (($iCommentRow > 0) ?? false);
    if ($iCommentRow > 0) {
        $pid = \bin\SecureTokens::getIDKEY($iPostId);
?>
<form id="modify-post-common" action="<?php echo $sAddonUrl;?>modify_comment.php" method="post">
    <input type="hidden" name="<?= $sFtan['name'];?>" value="<?= $sFtan['value']; ?>" />
    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
    <input type="hidden" name="post_id" value="<?php echo $iPostId; ?>" />
    <table class="w3-table-all news w3-hoverable">
        <tbody>
<?php
    while(!is_null($comment = $query_comments->fetchRow(MYSQLI_ASSOC))) {
      $cid = $comment['comment_id'];
      $cidKey = \bin\SecureTokens::getIDKEY($cid);
      $iStatus = (int)$comment['active'];
      $sQueryString   = '?page_id='.(int)$page_id.'&amp;section_id='.(int)$section_id.'&amp;'.$sFtanQuery;
      $sQueryString  .= '&amp;post_id='.$iPostId.'&amp;comment_id='.$cidKey.'&amp;module='.$sAddonName;

?>
          <tr>
             <td  style="width:20px;padding-left: 5px;">
                <button name="comment_id" value="<?php echo $cid; ?>" class="wb-edit ">
                   <img src="<?php echo THEME_URL; ?>/images/modify_16.png" alt="edit" />
                </button>
             </td>
             <td>
                <button name="comment_id" value="<?php echo $cid; ?>" class="wb-edit" style="font-size: 16px;">
                    <?php echo $comment['title']; ?>
                </button>
             </td>
             <td style="width:20px;padding-left: 5px;">
                   <img src="<?php echo THEME_URL; ?>/images/status_<?php echo $iStatus;?>.png" alt="^" />
             </td>
             <td style="width:1.825em;">
                <button type="button" class="wb-edit cform"
                id="cform<?= $cid; ?>"
                data-url="<?php echo $sAddonUrl.'delete_comment.php'.$sQueryString; ?>"
                data-message="<?php echo sprintf($MOD_NEWS['TEXT_DELETE_POST'],$comment['title'])."\n".$TEXT['ARE_YOU_SURE']; ?>"
                title="<?php echo $TEXT['DELETE']; ?>">
                    <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="X" />
                </button>
<!--

                <button name="comment_id" value="<?php echo $cid; ?>" class="wb-edit " formaction="<?= $sAddonUrl;?>delete_comment.php" onclick="confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>',null);" title="<?php echo $TEXT['DELETE']; ?>">
                   <img src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="edit" />
                </button>
-->

             </td>
          </tr>
<?php     }// while comment ?>
        </tbody>
   </table>
</form>
<?php } else {
?>
   <table>
        <tbody>
          <tr class="w3-section">
            <td class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_COMMENT_FOUND'] ?></td>
          </tr>
        </tbody>
   </table>
<?php } ?>
</article>

<?php
// Print admin footer
$admin->print_footer();
