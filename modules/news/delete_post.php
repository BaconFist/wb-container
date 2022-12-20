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
 * @version         $Id: delete_post.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/delete_post.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};


    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'/config.php')) {require($sAppPath.'/config.php');}
    $sDumpPathname = \basename($sAddonPath).'/'.\basename($sAddonFile);
    // Only for development for pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    // Only for development prevent secure token check,
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

try {

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = false;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();;
    $oApp     = $oReg->getApplication();
    $sAddonBackUrl = $oReg->AcpUrl;
    $oTrans->enableAddon('modules\\'.$sAddonName);

    $iPostId = $oApp->getIdFromRequest('post_id');

    $post_id = $iPostId;
    $aSql = [];

    if (is_null($iPostId)) {
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }

    $sGetOldSecureToken = SecureTokens::checkFTAN();
//    $aFtan = \bin\SecureTokens::getFTAN();
//    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = $sAddonBackUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix;
    $sAddonBackUrl = $sBacklink;
//
    if (!$sGetOldSecureToken){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
//
    // Get post details
    $aSql[1]  = 'SELECT `link` FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id` ='.$post_id;
    if (!($sLink = $database->get_one($aSql[1]))) {
        $aMessage = sprintf('%s'."\n".' %s',$oTrans->MOD_NEWS_NO_POSTS_FOUND, $aSql[1]);
        throw new \Exception ($aMessage);
    } else {
        $sPostFile = PAGES_DIRECTORY.$sLink.PAGE_EXTENSION;
// Unlink post access file
        if (is_writable(WB_PATH.$sPostFile)) {
/* comment out */
            if (!(unlink(WB_PATH.$sPostFile))){
              $aMessage = sprintf($oTrans->MOD_NEWS_NO_DELETED_POST, \basename($sLink));
              throw new \Exception ($aMessage);
            }
        }
    }
// Delete post
    $aSql[2]  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id` = '.(int)$post_id;
/*  comment out */
    if (!($database->query($aSql[2]))){
        $aMessage = \sprintf('%s ',$database->get_error());
        throw new \Exception ($aMessage);
    }

// delete comments refering to the post
    $aSql[3]  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_comments` '
          . 'WHERE `post_id` = '.(int)$post_id;
/*  comment out */
    if (!($database->query($aSql[3]))){
        $aMessage = \sprintf('%s ',$database->get_error());
        throw new \Exception ($aMessage);
    }

// Clean up ordering
    $order = new order(TABLE_PREFIX.'mod_news_posts', 'position', 'post_id', 'section_id');
    $order->clean($section_id);
// Check if there is a db error, otherwise say successful
    if($database->is_error()) {
        $aMessage = \sprintf('%s ',$database->get_error());
        throw new \Exception ($aMessage);
    }
} catch (\Exception $ex) {
    $oApp->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $oApp->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
// Print admin footer

$oApp->print_header();
$oApp->print_success(\sprintf($oTrans->MOD_NEWS_DELETED_POST,\basename($sLink)), $sAddonBackUrl);
$oApp->print_footer();
