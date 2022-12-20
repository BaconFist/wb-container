<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: comment_page.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/comment_page.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use addon\news\NewsLib;
use bin\helpers\{PreCheck,ParentList};

  $sAddonFile   = str_replace('\\','/',__FILE__).'/';
  $sAddonPath   = \dirname($sAddonFile).'/';
  $sModulesPath = \dirname($sAddonPath).'/';
  $sModuleName  = basename($sModulesPath);
  $sAddonName   = basename($sAddonPath);
  $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
  $sPattern     = "/^(.*?\/)modules\/.*$/";
  $sAppPath     = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
  $sDomain      = $sModulesPath.'/'.$sAddonPath;
  // Only for development for pretty mysql dump
  $bLocalDebug  =  is_readable($sAddonPath.'.setDebug');
  // Only for development prevent secure token check,
  $bSecureToken = !is_readable($sAddonPath.'.setToken');
  $sPHP_EOL     = ($bLocalDebug ? "\n" : '');

  $oReg     = WbAdaptor::getInstance();
  $oTrans   = $oReg->getTranslate();
  $oRequest = $oReg->getRequester();
  $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
  $aRequestVars  = [];
// get POST or GET requests, never both at once
  $aVars = $oReg->Request->getParamNames();
  foreach ($aVars as $sName) {
      $aRequestVars[$sName] = $oReg->Request->getParam($sName);
  }
// Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {
      $wb = new \frontend();
      $page_id    = ($wb->getIdFromRequest('page_id'));
      $wb->page_select();
      $wb->get_page_details();
    }
//  $oApp     = $oReg->getApplication();
    $oApp     = ($GLOBALS['wb'] ?? $oReg->getApplication());

    $sAddonUrl  = WB_URL.$sAddonRel;
    $sModulesTemplateUrl = $sAddonUrl.'/templates/default';

/*
  $sAddonName = basename(__DIR__);
  $sAddonRel  = '/modules/'.$sAddonName;
  $sAddonUrl  = WB_URL.$sAddonRel;
  $sAddonPath = WB_PATH.$sAddonRel;

  $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
  $aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);
*/

// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oTrans = Translate::getInstance();
    $oTrans->enableAddon($sDomain);

//    $sRecallAddress = WB_URL.PAGES_DIRECTORY.$oApp->page['link'].PAGE_EXTENSION;
    $sPageLink  = PAGES_DIRECTORY.$oApp->page['link'].PAGE_EXTENSION;
    $sScriptRel = WB_REL.$sPageLink;
    $sScriptUrl = WB_URL.$sPageLink;
    $sShortUrl  = WB_URL.$oApp->page['link'].'/' ;
    $sRecallUrl = (\is_readable(WB_PATH.'/short.php') ? $sShortUrl : $sScriptUrl);
    $sRecallPageAddress = $sRecallUrl;

    $page_id    = PAGE_ID;
    $post_id    = POST_ID;
    $section_id = SECTION_ID;
    $sAddonSettings = [];

    $IdIndex = 'News_'.$section_id;
    if(isset($_SESSION['captcha_error'])) {
//        $_SESSION['MESSAGE'][] = 'Captcha Error';
        $_SESSION['MESSAGE'][] = $_SESSION['captcha_error'];
        $_SESSION['captcha_retry_news'] = true;
    }
/*
// Get comments page template details from db
$sql  = 'SELECT `comments_page`, `use_captcha`, `commenting`, `use_data_protection`, `data_protection_link` FROM `'.TABLE_PREFIX.'mod_news_settings` '
      . 'WHERE `section_id` = '.(int)$section_id.'';
*/
// Get settings and layout comments page template
    $sql  = 'SELECT `ns`.*,`nl`.`comments_page` FROM `'
          .TABLE_PREFIX.'mod_news_settings` `ns` '.$sPHP_EOL
          . 'INNER JOIN `'.TABLE_PREFIX.'mod_news_layouts` `nl` ON `ns`.`layout_id` = `nl`.`id` '.$sPHP_EOL
          . 'WHERE `ns`.`section_id` = '.(int)$section_id;
    if (!($query_settings = $database->query($sql))){}
    if ($query_settings->numRows() == 0){
        $wb->send_header($sRecallPageAddress."");
        exit( 0 );
    } else{
    $sAddonSettings = $query_settings->fetchRow( MYSQLI_ASSOC );

    if ($sAddonSettings['use_captcha'] && !function_exists('call_captcha')){
      require(WB_PATH.'/include/captcha/captcha.php');
    }
    // Get post link
    $sql  = 'SELECT `link` FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id` = '.(int)$post_id;
    if (!$sPostLink = $database->get_one($sql)){
        throw new \Exception('Unkown Exception '.$database->get_error());
    }
//    $sBackPostLink = WB_URL.PAGES_DIRECTORY.$sPostLink.PAGE_EXTENSION;
    $sActionRel = '/modules/'.basename(__DIR__).'/submit_comment.php';
    $sBackPostRel   = PAGES_DIRECTORY.$sPostLink.PAGE_EXTENSION;
    $sBackPostLink  = WB_URL.$sBackPostRel;
    $sScriptRel     = WB_REL.$sBackPostRel;
    $sScriptUrl     = WB_URL.$sBackPostRel;
    $sShortUrl      = WB_URL.$sPostLink.'/' ;
    $sRecallUrl     = (\is_readable(WB_PATH.'/short.php') ? $sShortUrl : $sScriptUrl);
    $sRecallPostAddress = $sRecallUrl;

    // Print comments page
    $vars   = ['[POST_TITLE]','[TEXT_COMMENT]'];
    $values = [POST_TITLE, $MOD_NEWS['TEXT_COMMENT']];
    echo str_replace($vars, $values, ($sAddonSettings['comments_page']));

    if (isset($_SESSION['MESSAGE']) ){
?>
    <div class="w3-panel w3-pale-red w3-leftbar w3-border w3-border-red">
        <h4><?php
        echo $sOutput = (isset($_SESSION['SECURITY_ACCESS'])&& $_SESSION['SECURITY_ACCESS']!=''?'':$MOD_NEWS['REQUIRED_FIELDS']);
        ?></h4>
        <ol>
<?php
        foreach ($_SESSION['MESSAGE'] as $iKey=>$sContent){
?>
        <li><?php echo $sContent;?></li>
<?php   } ?>
        </ol>

    </div>
<?php
       unset($_SESSION['MESSAGE']);
    }

?>
    <form action="<?php echo WB_URL.$sActionRel; ?>" method="post" autocomplete="off">
      <input type="hidden" name="page_id" value="<?php echo PAGE_ID ;?>" />
      <input type="hidden" name="section_id" value="<?php echo SECTION_ID ;?>" />
      <input type="hidden" name="post_id" value="<?php echo POST_ID ;?>" />
      <input type="hidden" name="redirect" value="<?php echo $sRecallPostAddress ;?>" />
      <?php echo $wb->getFTAN(); ?>
    <?php if (ENABLED_ASP) { // add some honeypot-fields
    ?>
    <input type="hidden" name="submitted_when" value="<?php $t=time(); echo $t; $_SESSION['submitted_when']=$t; ?>" />
    <p class="nixhier">
    email address:
    <label for="email">Leave this field email blank:</label>
    <input id="email" name="email" size="60" value="" /><br />
    Homepage:
    <label for="homepage">Leave this field homepage blank:</label>
    <input id="homepage" name="homepage" size="60" value="" /><br />
    URL:
    <label for="url">Leave this field url blank:</label>
    <input id="url" name="url" size="60" value="" /><br />
    Comment:
    <label for="comment">Leave this field comment blank:</label>
    <input id="comment" name="comment" size="60" value="" /><br />
    </p>
    <?php } ?>
    <p>
    <span style="display: inline-block;padding-right:16px;min-width:110px;"><?php echo $TEXT['TITLE'];?>:</span>
    <input class="w3-input w3-border w3-mobile" type="text" name="title" maxlength="255" style="width: 75%;display: inline-block;"<?php if(isset($_SESSION['comment_title'])) { echo ' value="'.$_SESSION['comment_title'].'"'; unset($_SESSION['comment_title']); } ?> />
    </p>
    <span style="display: inline-block;padding-right:16px;min-width:110px;"><?php echo $TEXT['COMMENT'];?>:</span>
    <?php if(ENABLED_ASP) { ?>
        <textarea class="w3-textarea w3-border w3-mobile" name="comment_<?php echo date('W'); ?>" rows="10" cols="1" style="width: 75%; height: 150px;"><?php if(isset($_SESSION['comment_body'])) { echo $_SESSION['comment_body']; unset($_SESSION['comment_body']); } ?></textarea>
    <?php } else { ?>
        <textarea class="w3-textarea w3-border w3-mobile" name="comment" rows="10" cols="1" style="width: 75%; height: 150px;"><?php if(isset($_SESSION['comment_body'])) { echo $_SESSION['comment_body']; unset($_SESSION['comment_body']); } ?></textarea>
    <?php } ?>
<?php
    // Captcha
    if ($sAddonSettings['use_captcha']) {
        $aCaptachs['ct_color'] = 1;
        if ($oCaptcha = $database->query('SELECT * FROM `'.TABLE_PREFIX.'mod_captcha_control` ')){
            $aCaptachs = $oCaptcha->fetchRow(MYSQLI_ASSOC);
        }

?><div style="margin-top:1.5225em;"></div>
    <table>
        <tbody>
            <tr>
                <td><span style="display: inline-block;padding-right:16px;min-width:110px;"><?php echo $TEXT['VERIFICATION']; ?>:</span></td>
                <td><?php echo call_captcha('all','','',false, $aCaptachs['ct_color']); ?></td>
            </tr>
        </tbody>
    </table>
<?php
  } // end captcha
    if ($sAddonSettings['use_data_protection']) {
        $target_section_id = $sAddonSettings['data_protection_link'];
        $sDataLink = ParentList::build_access_file($target_section_id);
?>
    <div class="w3-bar" style="margin-top: 1.5225em;height: 80px;">
        <input class="w3-bar-item w3-check w3-border" id="data_protection" name="data_protection" value="1" type="checkbox" />
        <label for="data_protection" class="description w3-bar-item" style="width: 95%;margin-top:-0.525em;">
            <?php echo sprintf($NEWS_MESSAGE['DSGVO'], $sDataLink); ?>
        </label>
    </div>
<?php } // end dsgvo
    if (isset($_SESSION['captcha_error'])) {
        unset($_SESSION['captcha_error']);
?><script>document.querySelector('form#news-wrapper').captcha.focus();</script>
<?php } ?>
      <div style="margin-top: 1.5225em;height: 80px;">
          <div class="w3-container w3-cell w3-mobile">
               <input class="news-btn btn-default w3-mobile" id="save" name="save" type="submit" value="<?php echo $MOD_NEWS['TEXT_ADD_COMMENT']; ?>" >
          </div>
          <div class="w3-container w3-cell w3-mobile">
              <input id="cancel" name="cancel" class="news-btn btn-default w3-mobile" type="button" value="<?php echo $TEXT['BACK']; ?>" onclick="window.location='<?php echo $sRecallUrl;?>';" />
          </div>
      </div>
    </form>
<?php } ?>
