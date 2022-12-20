<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2 and higher
 * @version         $Id: modify.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/modify.php $
 * @lastmodified    $Date: 2019-06-11 19:55:53 +0200 (Di, 11. Jun 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
//overwrite php.ini on Apache servers for valid SESSION ID Separator
/*
if (\function_exists('ini_set')) {
    \ini_set('arg_separator.output', '&amp;');
}
*/
try {
//
    $oReg = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $admin = $oReg->getApplication();
//
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = 'modules/'.$sAddonName;
    $sAddonUrl     = $oReg->AppUrl.$sAddonRel.'/';
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    $sAddonThemeUrl = $sAddonUrl.'/themes/default/';
    $sAppUrl = $oReg->AppUrl;

    $sDispatchFile = $sModulesPath.'SimpleCommandDispatcher.inc.php';
    if (\is_readable($sDispatchFile)){
        $bExcecuteCommand = false;
        include $sDispatchFile;
    } else {
        $aMessage  = sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($aMessage);
    }
    // Only for development for pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    // Only for development prevent secure token check,
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    // some default settings for template
    $sChecked      = ' checked="checked"';
    $sSelected     = ' selected="selected"';

    $FTAN = SecureTokens::getFTAN();
    $sFtan = $FTAN['name'].'='.$FTAN['value'];
/*
// load module language file
    if (\is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (\is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
*/
    $oLang = \Translate::getInstance();
    $oLang->enableAddon('modules\\'.\basename(__DIR__));
//
//    require (WB_PATH.'/framework/functions.php');
    $sQueryString = '?page_id='.(int)$page_id.'&section_id='.(int)$section_id.'&'.$sFtan;

?><script>
    var News = {
        iPageId : '<?php echo $page_id;?>',
        iSectionId : '<?php echo $section_id;?>',
        WB_URL : '<?php echo $oReg->AppUrl;?>',
        AddonUrl : '<?php echo $sAddonUrl.'/';?>',
        THEME_URL : '<?php echo $oReg->ThemeUrl;?>',
        ThemeUrl:  '<?php echo $sAddonThemeUrl.'/';?>'
    };
</script>

<article class="w3-container w3-margin-bottom news-block">
<h4 class="w3-margin-0" style="line-height: 0;">&nbsp;</h4>
<table class="w3-table fixed-headers" style="width: 100%;">
    <tbody>
        <tr style="width: 100%; line-height: 2.825em;">
            <td style="width: 25%;">
                <form action="<?php echo $sAddonUrl; ?>modify_post.php" method="get" >
                    <input type="hidden" value="<?php echo $page_id; ?>" name="page_id">
                    <input type="hidden" value="<?php echo $section_id; ?>" name="section_id">
                    <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>">
                    <input type="hidden" value="0" name="post_id"/>
                    <input type="submit" value="<?php echo $MOD_NEWS['TEXT_ADD_POST']; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-0" style="width: 100%;" />
                </form>
            </td>
            <td style="width: 25%;">
                <form action="<?php echo $sAddonUrl; ?>modify_group.php" method="get" >
                    <input type="hidden" value="<?php echo $page_id; ?>" name="page_id"/>
                    <input type="hidden" value="<?php echo $section_id; ?>" name="section_id"/>
                    <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>"/>
                    <input type="hidden" value="0" name="group_id"/>
                    <input type="submit" value="<?php echo $MOD_NEWS['TEXT_ADD_GROUP']; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-0" style="width: 100%;" />
                </form>
            </td>
<?php if ($admin->get_permission('modules_settings') ) {  ?>
            <td style="width: 25%;">
                <form action="<?php echo $sAddonUrl; ?>modify_settings.php" method="get" >
                    <input type="hidden" value="<?php echo $page_id; ?>" name="page_id"/>
                    <input type="hidden" value="<?php echo $section_id; ?>" name="section_id"/>
                    <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>"/>
                    <input type="submit" value="<?php echo $TEXT['SETTINGS']; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-0" style="width: 100%;" />
                </form>
            </td>
<?php } ?>
<?php if ($admin->ami_group_member('1')) { ?>
            <td style="width: 25%;">
                <form action="<?php echo $sAddonUrl; ?>reorgPosition.php" method="get" >
                    <input type="hidden" value="<?php echo $page_id; ?>" name="page_id"/>
                    <input type="hidden" value="<?php echo $section_id; ?>" name="section_id"/>
                    <input type="hidden" value="<?php echo $FTAN['value'];?>" name="<?php echo $FTAN['name'];?>"/>
                    <input type="submit" value="<?php echo $MENU['REORG_TABLE'];?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3-medium w3-padding-0" style="width: 100%;" />
                </form>
            </td>
<?php } ?>
        </tr>
    </tbody>
</table>
<?php
    $sql  = 'SELECT `order`, `order_field` FROM `'.TABLE_PREFIX.'mod_news_settings` '
          . 'WHERE `section_id` = '.(int)$section_id.' ';
    if (!($oOrder = $database->query($sql))){
        throw new \Exception($database->get_error());
    }
    if (!($aOrder = $oOrder->fetchRow(MYSQLI_ASSOC))){
        $aOrder['order_field'] = 'position';
        $aOrder['order']       = 'DESC';
    }
    $bDragDrop  = 'js-admin';
    $iTableHeight = 464;
// Loop through existing posts
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `section_id` = '.(int)$section_id.' '
          . 'ORDER BY `'.$aOrder['order_field'].'` '.$aOrder['order'];
    if ($oPosts = $database->query($sql)){
        $num_posts = $oPosts->numRows();
        $bShowPosts = (($num_posts > 0) ?? false);
        $iTableHeight = ($num_posts > 15 ? '464px' : 'auto');
        if (($aOrder['order'] != 'DESC') && ($aOrder['order_field']=='position')) {
            $bNoSort = true;
            $bDragDrop = 'jsadmin';
        } else {
            $bNoSort = false;
?>
          <div class="frm-warning w3-padding w3-section w3-sand w3-leftbar w3-medium w3-border-green w3-hover-border-green">
              <p><?php echo $oLang->MOD_NEWS_TEXT_ORDER ;?></p>
          </div>
<?php   } ?>
  <h2 class="w3-<?php echo ($bShowPosts ? 'hide' : 'show');?>"><?php echo $MOD_NEWS['TEXT_MODIFY_POST']; ?></h2>
  <div class="w3-row w3-padding-4 w3-<?php echo ($bShowPosts ? 'show' : 'hide');?>" style="width: 100%;">
    <div class="w3-display-container" style="position: relative;height: 50px;">
          <div class="w3-col w3-display-left ">
              <h2><?php echo $MOD_NEWS['TEXT_MODIFY_POST']; ?></h2>
        </div>

        <div class="w3-col w3-display-middle ">
            <select name="TableHeight" id="TableHeight" class="w3-select w3-border w3-left" style="visibility: hidden;">
                <option value="15">15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>

        <div class="w3-col w3-display-right" style="margin: 8px 0;">
            <div class="input-container">
            <i class="fa fa-search icon w3-blue-wb">&#160;</i>
            <input data-sec="<?= $section_id;?>" class="input-field w3-input w3-border" placeholder="Search" id="tableInput"  type="text" value="" style="width:auto;height: 36px!important;"/>
            </div>
        </div>
    </div>
  </div>

<form id="modify-post" action="<?php echo $sAddonUrl; ?>modify_post.php" method="post">
      <input type="hidden" name="page_id" value="<?php echo $page_id;?>"/>
      <input type="hidden" name="section_id" value="<?php echo $section_id;?>"/>
      <input type="hidden" name="<?php echo $FTAN['name'];?>" value="<?php echo $FTAN['value'];?>"/>
      <input type="hidden" name="module" value="<?php echo $sAddonName; ?>" />

  <div class="<?= $bDragDrop; ?> w3-hide">&nbsp;</div>
  <div id="table-scroll" class="table-scroll" style="height: 100%;width: auto;">
    <div id="table-wrap" class="table-wrap">
        <table class="news-post-table w3-table-all w3-border fixed-headers main-table mobile--optimised" style="width:100%!important;">
            <thead>
                <tr class="w3-header-blue-wb">
                    <th>&#160;</th>
                    <th >ID</th>
                    <th>Edit</th>
                    <th><span>&#160;</span></th>
                    <th><span class="w3-center"><?php echo $TEXT['MODIFIED']; ?></span></th>
                    <th><span class="w3-margin-left"><?php echo $TEXT['POST']; ?></span></th>
                    <th><?php echo $TEXT['GROUP']; ?></th>
                    <th><?php echo $TEXT['COMMENTS']; ?></th>
                    <th><?php echo $TEXT['ACTIVE']; ?></th>
                    <th>&#160;</th>
                    <th>&#160;</th>
                    <th>&#160;</th>
                    <th>&#160;</th>
                    <th>Pos</th>
                    <th>&#160;</th>
                </tr>
            </thead>
            <tbody id="postResult_<?= $section_id;?>">
<?php

        while(!is_null($post = $oPosts->fetchRow(\MYSQLI_ASSOC))) {
            $iPostId  = (int)$post['post_id'];
            $sPostIdKey = SecureTokens::getIDKEY($iPostId);
            $sMoveIdKey = SecureTokens::getIDKEY($iPostId);
//            $sid = \bin\SecureTokens::getIDKEY($section_id);
            $group_id = (int)$post['group_id'];
            $sGroupImageRel = MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg';
            $sGroupImage = (\is_readable(WB_PATH.$sGroupImageRel) ? WB_URL.$sGroupImageRel : THEME_URL.'/images/blank_16.gif' );
            if ($post['active'] == 1) {
                $activ_string = $TEXT['ENABLED'];
            } else {
                $activ_string = $TEXT['DISABLED'];
            }
            // Get number of comments
            $sqlComment = 'SELECT COUNT(*) `iComment` FROM `'.TABLE_PREFIX.'mod_news_comments`'
                        .' WHERE `post_id` = '.(int)$post['post_id'].'';
            if (!($iComment = $database->get_one($sqlComment))){ $iComment = 0; }
            // Get group title
            $sqlGroups  = 'SELECT `title`   FROM `'.TABLE_PREFIX.'mod_news_groups`  WHERE `group_id` = \''.$post['group_id'].'\'';
            if (($post['group_id'] !==0) && ($sGroupTitle = $database->get_one($sqlGroups))){
                $groupTitle = $sGroupTitle;
            } else {
                $groupTitle = $TEXT['NONE'];
            }

//            $sDateFormat = ((DATE_FORMAT == 'system_default') ? DEFAULT_DATE_FORMAT : DATE_FORMAT);
            $sDateFormat = str_replace(' ', '|', ($oApp->get_session('DATE_FORMAT')) ?? $oReg->DefaultDateFormat);
            $sDateFormat = (($oReg->DefaultDateFormat == $sDateFormat) ? 'system_default' : $sDateFormat);
            $sDateFormat    = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
            //$sDateFormat    = PreCheck::dateFormatToStrftime($sDateFormat);
            $sAjaxImgFile   = $sAddonThemeUrl.'/img/24/status_'.(int)$post['active'].'';
            $sQueryString   = '?page_id='.(int)$page_id.'&section_id='.(int)$section_id.'&'.$sFtan;
            $sExtendedQuery = '&position='.(int)$post['position'].'&move_id='.$sMoveIdKey;
            $sQueryString  .= '&post_id='.$sPostIdKey.'&module='.$sAddonName;

?>
            <tr class="sectionrow">
                <td class="C1"><i class="fa fa-arrows w3-hide">&nbsp;</i></td>
                <td class="w3-center C2"><span class="w3-right"><?php echo $iPostId;?></span></td>
                <td class="w3-center C3">
                    <button class="wb-image wb-edit" type="submit" name="post_id" value="<?php echo $sPostIdKey; ?>" data-post_id="<?php echo $iPostId;?>" >
                        <img src="<?php echo THEME_URL; ?>/images/modify_16.png" alt="Modify - " />
                    </button>
                </td>
                <td class="w3-center C4">
                    <button class="wb-image wb-edit" type="submit" name="post_id" value="<?php echo $sPostIdKey; ?>" data-post_id="<?php echo $iPostId;?>" >
                        <img src="<?php echo $sGroupImage; ?>" width="18" alt="" />
                    </button>
                </td>
                <td class="C5"><?php echo PreCheck::dateFormatToStrftime($sDateFormat, $post['published_when']+TIMEZONE); ?></td>
                <td class="C6">
                    <button class="wb-edit w3-medium w3-left-align" type="submit" name="post_id" value="<?php echo $sPostIdKey; ?>" data-post_id="<?php echo $iPostId;?>" >
                        <?php echo ($post['title']); ?>
                    </button>
                </td>
                <td class="C7">
                    <span><?php echo ($groupTitle ?? ''); ?></span>
                </td>
                <td class="w3-center C8">
                    <span><?php echo (($iComment>0) ? $iComment : (($post['commenting']=='none') ? '' : $iComment)); ?></span>
                </td>
                <td class="toggle_active_post w3-center C9">
                    <img class="w3-pointer" id="active_<?php echo $iPostId; ?>" src="<?php echo $sAjaxImgFile;?>.png" alt=""  />
                </td>
                <td class="C10">
<?php
                $start = $post['published_when'];
                $end = $post['published_until'];
                $now = time();
                $icon = '';
                if($start<=$now && $end==0){
                    $icon=THEME_URL.'/images/noclock_16.png';}
                elseif(($start<=$now || $start==0) && $end>=$now){
                    $icon=THEME_URL.'/images/clock_16.png';}
                else{
                    $icon=THEME_URL.'/images/clock_red_16.png';}
?>
                <button class="wb-edit" type="submit" name="post_id" value="<?php echo $sPostIdKey; ?>" data-post_id="<?php echo $iPostId;?>"  title="<?php echo $TEXT['MODIFY']; ?>">
                    <img class="w3-pointer" src="<?php echo $icon; ?>" alt="" />
                </button>
                </td>
                <td class="C11 w3-center">
<?php if (($aOrder['order']=='DESC') && ($post['position'] > 1) && ($post['position'] < $num_posts) && $bNoSort) { ?>
                    <input type="hidden" name="post_id" value="<?php echo $sPostIdKey; ?>" />
                    <button class="wb-edit C11-1" formmethod="post" formaction="<?php echo $sAddonUrl; ?>/move_down.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                        <img class="w3-pointer wbedit" src="<?php echo THEME_URL; ?>/images/up_16.png" alt="^" />
                    </button>
<?php } else if (($post['position'] > 1) && ($post['position'] < $num_posts) && $bNoSort) { ?>
                    <a class="wb-edit C11-2" href="<?php echo $sAddonUrl; ?>/move_up.php<?= $sQueryString.$sExtendedQuery; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/up_16.png" alt="^" />
                    </a>
<?php } else if ((empty($aOrder['order']) || ($aOrder['order']=='ASC')) && ($post['position'] == $num_posts) && $bNoSort){ ?>
                    <a class="wb-edit C11-3" href="<?php echo $sAddonUrl; ?>/move_up.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/up_16.png" alt="^" />
                    </a>
<?php } else if (($aOrder['order']=='DESC')&&($post['position'] == 1) && $bNoSort){ ?>
                    <a class="wb-edit C11-4" href="<?php echo $sAddonUrl; ?>/move_down.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/up_16.png" alt="^" />
                    </a>
<?php } else { ?>
                        <img src="<?php echo THEME_URL; ?>/images/blank_16.gif" alt="" />
<?php } ?>
                </td>
                <td class="C12 w3-center">
<?php if (($aOrder['order']=='DESC')&&($post['position'] > 1) && ($post['position'] < $num_posts) && $bNoSort) { ?>
                    <a class="wb-edit C12-1" href="<?php echo $sAddonUrl; ?>/move_up.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/down_16.png" alt="v" />
                    </a>
<?php } else if ((empty($aOrder['order'])||($aOrder['order']=='ASC')) && ($post['position'] < $num_posts) && $bNoSort){ ?>
                    <a class="wb-edit C12-2"  href="<?php echo $sAddonUrl; ?>/move_down.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/down_16.png" alt="^" />
                    </a>
<?php } else if (($aOrder['order']=='DESC') && ($post['position'] == $num_posts) && $bNoSort){ ?>
                    <a class="wb-edit C12-3" href="<?php echo $sAddonUrl; ?>/move_up.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
                        <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/down_16.png" alt="^" />
                    </a>
<?php } else { ?>
                        <img src="<?php echo THEME_URL; ?>/images/blank_16.gif" alt="" />
<?php } ?>
                </td>
                <td class="w3-center C13">
                <button type="button" class="wb-edit pform"
                id="pform<?= $iPostId; ?>"
                data-url="<?php echo $sAddonUrl.'delete_post.php'.$sQueryString; ?>"
                data-message="<?php echo sprintf($MOD_NEWS['TEXT_DELETE_POST'],$post['title'])."\n".$TEXT['ARE_YOU_SURE']; ?>"
                title="<?php echo $TEXT['DELETE']; ?>">
                    <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="X" />
                </button>

                </td>
                <td class="C14"><span class="w3-right"><?php echo $post['position']; ?></span></td>
                <th>&#160;</th>
            </tr>
<?php     } // end while posts ?>
            <tr class="w3-section<?php echo ($bShowPosts ? ' w3-hide' : '');?>">
              <td colspan="15" class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_POSTS_FOUND']; ?></td>
            </tr>
            </tbody>
        </table>
    </div>
  </div>
</form>

<?php } else { ?>
   <table>
        <tbody>
          <tr class="w3-section">
            <td class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_POSTS_FOUND']; ?></td>
          </tr>
        </tbody>
   </table>
<?php } ?>
<?php
// Loop through existing groups
    $sGroupSql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_groups` '
                . 'WHERE `section_id` ='.(int)$section_id.' '
                . 'ORDER BY `position`';
    if ($query_groups = $database->query($sGroupSql)){
        $num_groups = $query_groups->numRows();
        $bShowGroups = (($num_groups > 0) ?? false);
        $iGroupHeight = ($num_groups > 15 ?'464px' : 'auto');
        $bOrderGroups = true;
?>
<h2 class="w3-<?php echo ($bShowGroups ? 'hide' : 'show');?>"><?php echo $MOD_NEWS['TEXT_MODIFY_GROUP']; ?></h2>
<div class="w3-row w3-padding-4 w3-<?php echo ($bShowGroups ? 'show' : 'hide');?>" style="width: 100%;">
    <div class="w3-display-container" style="position: relative;height: 50px;">
          <div class="w3-col w3-display-left ">
              <h2><?php echo $MOD_NEWS['TEXT_MODIFY_GROUP']; ?></h2>
        </div>

    <div class="w3-col w3-display-middle ">
        <select name="GroupHeight" id="GroupHeight" class="w3-select w3-border w3-left" style="visibility: hidden;">
            <option value="15">15</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
    <div class="w3-col w3-display-right" style="margin: 8px 0;">
        <div class="input-container">
        <i class="fa fa-search icon w3-blue-wb">&#160;</i>
        <input data-sec="<?= $section_id;?>" class="input-field w3-input w3-border" placeholder="Search" id="groupInput" onkeyup="groupSelect(this)" type="text" value="" style="width:auto;height: 36px!important;"/>
        </div>
    </div>
  </div>
</div>

<div class="jsadmin w3-hide">&nbsp;</div>
<div class="group-scroll " id="group-scroll" style="height:100%;width: auto;">
<div id="faux-group" class="faux-table" ></div>
<div class="group-wrap" id="group-wrap">
  <form id="modify-group" action="<?php echo $sAddonUrl; ?>modify_group.php" method="post">
    <input type="hidden" name="page_id" value="<?php echo $page_id;?>"/>
    <input type="hidden" name="section_id" value="<?php echo $section_id;?>"/>
    <input type="hidden" name="module" value="<?php echo $sAddonName; ?>"/>
    <input type="hidden" name="<?php echo $FTAN['name'];?>" value="<?php echo $FTAN['value'];?>"/>
    <table class="news-post-table w3-table-all w3-border fixed-headers mobile-optimised" id="group-table">
        <thead>
            <tr class="w3-header-blue-wb">
                <th><span>&#160;</span></th>
                <th > ID </th>
                <th>Edit</th>
                <th><span>&#160;</span></th>
                <th><span class="w3-center">&#160;</span></th>
                <th style="padding-left: 5px; text-align: left;"><?php print $TEXT['GROUP']; ?></th>
                <th><span>&#160;</span></th>
                <th><span>&#160;</span></th>
                <th><?php echo $TEXT['ACTIVE']; ?></th>
                <th>&#160;</th>
                <th>&#160;</th>
                <th>&#160;</th>
                <th>&#160;</th>
                <th> Pos </th>
                <th>&#160;</th>
            </tr>
        </thead>
        <tbody id="groupResult_<?= $section_id;?>">
<?php
    while(!is_null($group = $query_groups->fetchRow( MYSQLI_ASSOC ))) {
        $group_id = (int)$group['group_id'];
        $iGroupId = $group_id;
        $sGroupIdKey = SecureTokens::getIDKEY($group_id);
        $sMoveIdKey = SecureTokens::getIDKEY($group_id);
        $sGroupImageRel = MEDIA_DIRECTORY.'/.news/image'.$iGroupId.'.jpg';
        $sGroupImage    = (is_readable(WB_PATH.$sGroupImageRel) ? WB_URL.$sGroupImageRel : THEME_URL.'/images/blank_16.gif' );
        $sQueryString   = '?page_id='.(int)$page_id.'&section_id='.(int)$section_id.'&'.$sFtan;
        $sExtendedQuery = '&position='.(int)$group['position'].'&move_id='.$sMoveIdKey;
        $sQueryString  .= '&group_id='.$sGroupIdKey.'&module='.$sAddonName;
?>
        <tr class="sectionrow">
            <td ><i class="fa fa-fw w3-hide">&nbsp;</i></td>
            <td class="w3--check"><span class="w3-right"><?php echo $iGroupId;?></span></td>
            <td class="w3-center">
                    <button class="wb-image wb-edit" type="submit" name="group_id" value="<?php echo $sGroupIdKey; ?>" data-group_id="<?php echo $iGroupId;?>" >
                        <img src="<?php echo THEME_URL; ?>/images/modify_16.png" alt="Modify - " />
                    </button>
            </td>
            <td class="w3-center">
                <button class="wb-image wb-edit" type="submit" name="group_id" value="<?php echo $sGroupIdKey; ?>" data-group_id="<?php echo $iGroupId;?>" >
                    <img src="<?php echo $sGroupImage; ?>" width="18" alt="" />
                </button>
            </td>
            <td class="w3-center"><span>&#160;</span></td>
            <td class="G6">
                <button class="wb-edit w3-left-align" type="submit" name="group_id" value="<?php echo $sGroupIdKey; ?>" data-group_id="<?php echo $iGroupId;?>" >
                    <?php echo ($group['title']); ?>
                </button>
            </td>
            <td class="w3-center"><span>&#160;</span></td>
            <td class="w3-center"><span>&#160;</span></td>
            <td class="toggle_active_group w3-center">
                <img id="groups_<?php echo $iGroupId; ?>" class="w3-pointer" src="<?php echo $sAddonThemeUrl; ?>/img/24/status_<?php echo (int)$group['active'];?>.png" alt=""  />
            </td>
            <td class="w3-center">
                <img class="w3-pointer"  src="<?php echo THEME_URL; ?>/images/blank_16.gif" alt=""  />
            </td>
            <td class="w3-center">
<?php if($group['position'] != 1 ) { ?>
                <a class="wb-edit" href="<?php echo $sAddonUrl; ?>/move_up.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
                    <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/up_16.png" alt="^" />
                </a>
<?php } else { ?>
                    <img src="<?php echo THEME_URL; ?>/images/blank_16.gif" alt="" />
<?php } ?>
            </td>
            <td class="w3-center">
<?php if($group['position'] != $num_groups ) { ?>
                <a class="wb-edit" href="<?php echo $sAddonUrl; ?>/move_down.php<?= $sQueryString.$sExtendedQuery;?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
                    <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/down_16.png" alt="v" />
                </a>
<?php } else { ?>
                    <img src="<?php echo THEME_URL; ?>/images/blank_16.gif" alt="" />
<?php } ?>
            </td>
            <td class="w3-center">
                <button type="button" class="wb-edit gform"
                id="gform<?= $iGroupId; ?>"
                data-url="<?php echo $sAddonUrl.'delete_group.php'.$sQueryString; ?>"
                data-message="<?php echo sprintf($MOD_NEWS['TEXT_DELETE_POST'],$group['title'])."\n".$TEXT['ARE_YOU_SURE']; ?>"
                title="<?php echo $TEXT['DELETE']; ?>">
                    <img class="w3-pointer" src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="X" />
                </button>
            </td>
            <td class="w3-center"><span class="w3-right"><?php echo $group['position']; ?></span></td>
            <td class="w3-center"><span>&#160;</span></td>
        </tr>
<?php     } // end while ?>
          <tr class="w3-section<?php echo ($bShowGroups ? ' w3-hide' : '');?>">
            <td colspan="15" class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_GROUP_FOUND']; ?></td>
          </tr>
        </tbody>
    </table>
  </form>
</div>
</div>

<?php } else { ?>
   <table>
        <tbody>
          <tr class="w3-section">
            <td class="w3-text-blue-wb w3-large w3-margin"><?php echo $MOD_NEWS['NO_GROUP_FOUND']; ?></td>
          </tr>
        </tbody>
    </table>
<?php } ?>

</article>
    <script src="<?php echo WB_URL;?>/include/plugins/default/sweetalert2/09.17.2/dist/sweetalert2.all.min.js"></script>

<?php
// include the required file for Javascript admin
    if (($aOrder['order'] != 'DESC') || $bOrderGroups) {
        if (!\function_exists('jsadminLoaded') && \is_readable(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php')){
            include(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php');
        }
    }
}catch(ParseError $p){
    echo $p->getMessage();
}catch(Exception $ex){
    echo $ex->getMessage();
}

