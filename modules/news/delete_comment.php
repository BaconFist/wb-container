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
 * @version         $Id: delete_comment.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/delete_comment.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* --------------------------------------------------------------- */
// execute config.php
/* --------------------------------------------------------------- */
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = str_replace('\\','/',\dirname(__DIR__)).'/';
    $sAddonName   = basename(__DIR__);
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment if you have to load config.php
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}

    $sAddonRel    = '/modules/'.$sAddonName;
    $sAddonUrl    = WB_URL.$sAddonRel;
//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
try {
    $admin_header = true;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $comment_id = ($admin->getIdFromRequest('comment_id') ?? false);
    $post_id    = ($admin->getIdFromRequest('post_id') ?? false);

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $bBackLink = ($aRequestVars['save_close'] ?? false);
    $bBackLink = ($aRequestVars['close'] ?? $bBackLink);

    $sBackLink          = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackCommonLink    = WB_URL.'/modules/'.$sAddonName.'/modify_comment.php?page_id='.$page_id.'&section_id='.$section_id.'&comment_id='.SecureTokens::getIDKEY($comment_id).'&amp;'.$sFtanQuery;
    $sBackPostLink      = WB_URL.'/modules/'.$sAddonName.'/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$post_id.'&'.$sFtanQuery;
    $sBackPostShortLink = WB_URL.'/modules/'.$sAddonName.'/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$post_id.'&'.$sFtanQuery;
    $sBackPostLink      = (is_readable($oReg->AppPath.'short.php') ? $sBackPostShortLink : $sBackPostLink);
    $sBackLinkUrl       = ($bBackLink ? $sBackPostLink : $sBackCommonLink );

    $oTrans = Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonName);

// Update row
//    $comment_id = 999;
    if ($comment_id) {
        if ($database->get_one('SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_comments` WHERE `comment_id`='.(int)$comment_id))
        {
            $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_comments` '
                  . 'WHERE `comment_id` = '.(int)$comment_id;
            if ($oDelete = $database->query($sql)){
                $sErrorMessage = \sprintf("comment_id (%d) %s\n",($comment_id ?? 0),$TEXT['DELETED']);
                $admin->print_success($sErrorMessage, $sBackPostLink);
//                throw new \Exception ($sErrorMessage);
            }
// Check if there is a db error, otherwise say successful
            if($database->is_error()) {
                $sErrorMessage = \sprintf($database->get_error());
                throw new \Exception ($sErrorMessage);
            }
        } else {
          $sErrorMessage = \sprintf("comment_id (%d) %s\n",($comment_id ?? 0),$TEXT['NOT_FOUND']);
          throw new \Exception ($sErrorMessage);
        }
    } else {
       $sErrorMessage = \sprintf("comment_id (%d) %s\n",($comment_id ?? 0),$TEXT['NOT_FOUND']);
       throw new \Exception ($sErrorMessage);
    }

}catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBackPostLink);
    exit;
}

// Print admin footer
$admin->print_footer();
