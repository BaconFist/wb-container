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
 * @version         $Id: comment.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/comment.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Include config file
if (!\defined('SYSTEM_RUN')){ require(dirname(dirname((__DIR__))).'/config.php' ); }

    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();

    $sAddonName = basename(__DIR__);

// Check if there is a post id
// $post_id = $oApp->checkIDKEY('post_id', false, 'GET');
  $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
  $aRequestVars = [];
// get POST or GET requests, never both at once
  $aVars = $oReg->Request->getParamNames();
  foreach ($aVars as $sName) {
      $aRequestVars[$sName] = $oReg->Request->getParam($sName);
  }

// Create new frontend object
    if (!isset($wb) || (isset($wb) && !($wb instanceof \frontend))) {
      $wb = new \frontend();
// needed to get page details
      $page_id = ($wb->getIdFromRequest('page_id'));
// Figure out which page to display
      $wb->page_select();
// Collect info about the currently viewed page and check permissions
      $wb->get_page_details();
// Collect general website settings
      $wb->get_website_settings();
    }
    $oApp       = ($GLOBALS['wb'] ?? $oReg->getApplication());
    $post_id    = ($oApp->getIdFromRequest('post_id'));
    $position   = ($oApp->getIdFromRequest('p'));
    $section_id = ($oApp->getIdFromRequest('section_id'));
    $isAuth     =  $oApp->is_authenticated();

    $sPageLink       = PAGES_DIRECTORY.$oApp->page['link'].PAGE_EXTENSION;
//    $sPageShortLink  = $oApp->page['link'].'/';
//    $bShortUrl       = is_readable($oReg->AppPath.'short.php');
//    $sPageLink       = ltrim(($bShortUrl ? $sPageShortLink : $sPageLink),'/');

/*
$sDomain = \basename(__DIR__).'/'.\basename(__FILE__);
print '<pre class="w3-pre w3-border w3-white w3-small w3-container w3-padding" style="width:100%;">'.nl2br(sprintf("function: <span>%s</span> (%s) Filename: <span>%s</span> Line %d\n",(!empty(__FUNCTION__) ? __FUNCTION__ : 'global'),'myVar',$sDomain,__LINE__));
\print_r( [$sPageLink,$sPageShortLink] ); print "</pre>"; \flush (); // htmlspecialchars() ob_flush();;sleep(10); die();

$section_id = intval(isset($aRequestVars['section_id'])) ? $aRequestVars['section_id'] : (isset($section_id) ? intval($section_id) : 0);
$post_id = (intval(isset($aRequestVars['post_id'])) ? $aRequestVars['post_id'] : (isset($post_id) ? intval($post_id) : 0));
$position = (isset($aRequestVars['p']) ? $aRequestVars['p'] : '' );
*/
if (!$post_id || !isset($aRequestVars['section_id']) || !is_numeric($aRequestVars['section_id'])) {
    $_SESSION['MESSAGE'][] = ('Wrong Parameter::'.$MESSAGE['GENERIC_SECURITY_ACCESS'] );
//    exit();
}

// Query post for page id
  $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_news_posts` '
        . 'WHERE `post_id` = '.(int)$post_id;
  if (!$query_post = $database->query($sql)){
      throw new \Exception('Database Exception '.$database->get_error());
  }
  if ($query_post->numRows() == 0)
  {
      header("Location: ".$oReg->AppUrl.$sPageLink);
      exit( 0 );
  }else{
    $fetch_post = $query_post->fetchRow( MYSQLI_ASSOC );
    $page_id = $fetch_post['page_id'];
    $section_id = $fetch_post['section_id'];
    $post_id = $fetch_post['post_id'];
    $post_title = $fetch_post['title'];

    $sPostLink      = $fetch_post['link'];
    $sBackPostRel   = PAGES_DIRECTORY.$sPostLink.PAGE_EXTENSION;
    $sBackPostLink  = WB_URL.$sBackPostRel;
    $sScriptRel     = WB_REL.$sBackPostRel;
    $sScriptUrl     = WB_URL.$sBackPostRel;
    $sShortUrl      = WB_URL.$sPostLink.'/' ;
    $sRecallUrl     = (\is_readable(WB_PATH.'/short.php') ? $sShortUrl : $sScriptUrl);
    $sRecallAddress = $sRecallUrl;
    define('SECTION_ID', $section_id);
    define('POST_ID', $post_id);
    define('POST_TITLE', $post_title);
    // don't allow commenting if its disabled, or if post or group is inactive
    $t = time();
    $table_posts  = TABLE_PREFIX."mod_news_posts";
    $table_groups = TABLE_PREFIX."mod_news_groups";
    $sql  = 'SELECT p.post_id FROM `'.$table_posts.'` AS p '
          . 'LEFT OUTER JOIN `'.$table_groups.'` AS g ON p.`group_id` = g.`group_id` '
          . 'WHERE p.`post_id`='.$post_id.' '
          .   'AND p.commenting != \'none\' '
          .   'AND p.`active` = 1 '
          .   'AND ( g.`active` IS NULL OR g.`active` = 1 ) '
          .   'AND (p.`published_when`  = 0 OR p.`published_when`  <= '.$t.') '
          .   'AND (p.`published_until` = 0 OR p.`published_until` >= '.$t.') ';
    if (!$query = $database->query($sql)){
      throw new \Exception('ERROR::'.$database->get_error());
    }
    if($query->numRows() == 0)
    {
        header("Location: ".$oReg->AppUrl.$sPageLink);
        exit;
    }

    // don't allow commenting if ASP enabled and user doesn't comes from the right view.php
    if (
        ENABLED_ASP &&
        !isset($_SESSION['comes_from_view']) ||
        (isset($_SESSION['comes_from_view']) && $_SESSION['comes_from_view'] != $post_id)
    ){
            header("Location: ".$sBackPostLink);
            exit;

    }
    // Get page details
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
          . 'WHERE `page_id` = '.(int)$page_id.'';
    if (!$query_page = $database->query($sql)){
      throw new \Exception('ERROR::'.$database->get_error());
    }
    if($query_page->numRows() == 0)
    {
        header("Location: ".$oReg->AppUrl.$sPageLink);
        exit;
    }else{
        $page = $query_page->fetchRow( MYSQLI_ASSOC );
        // Required page details
        $sCommonPageLink = '/modules/'.$sAddonName.'/comment_page.php';
        define('PAGE_CONTENT', WB_PATH.$sCommonPageLink);
        // Include index (wrapper) file
        require(WB_PATH.'/index.php');
    }
}

/*
  throw new ErrorMsgException (__LINE__.') <pre>'.print_r(WB_PATH.'/modules/'.$sAddonName.'/comment_page.php',true).'</pre>');
*/