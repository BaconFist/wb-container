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
 * @version         $Id: save_comment.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/save_comment.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Include config file
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sAddonName   = basename($sAddonPath);
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment if you have to load config.php
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}

    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
try {
    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $comment_id = ($admin->getIdFromRequest('comment_id') ?? false);
    $post_id    = ($admin->getIdFromRequest('post_id') ?? false);

    $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $bBackLink = ($aRequestVars['save_close'] ?? false);
    $bBackLink = ($aRequestVars['close'] ?? $bBackLink);

    $sBackLink        = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackCommonLink  = WB_URL.'/modules/'.$sAddonName.'/modify_comment.php?page_id='.$page_id.'&section_id='.$section_id.'&comment_id='.SecureTokens::getIDKEY($comment_id);
    $sBackPostLink    = WB_URL.'/modules/'.$sAddonName.'/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$post_id;
    $sBackLinkUrl     = ($bBackLink ? $sBackPostLink.'&'.$sFtanQuery : $sBackCommonLink.'&'.$sFtanQuery );

    $oTrans = Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonName);
// Get id

/*
    if (!isset($aRequestVars['comment_id']) || !is_numeric($aRequestVars['comment_id']) || !isset($aRequestVars['post_id']) || !is_numeric($aRequestVars['post_id']))
    {
        $wb->send_header($sBacklink);
        exit(0);
    }else{
        $comment_id = (int)$aRequestVars['comment_id'];
    }
*/

    if (!SecureTokens::checkFTAN())
    {
        $admin->print_header();
        $sErrorMessage = sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($sErrorMessage);
    }

    $sFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $sFtan['name'].'='.$sFtan['value'];

// Validate all fields
    if($admin->get_post('title') == '' && $admin->get_post('comment') == '')
    {
        $admin->print_header();
//        $admin->print_error($MESSAGE['GENERIC_FILL_IN_ALL'], );
        $sErrorMessage = sprintf($MESSAGE['GENERIC_FILL_IN_ALL']);
        throw new \Exception ($sErrorMessage);
    }else{
        $title = strip_tags($admin->get_post('title'));
        $comment = strip_tags($admin->get_post('comment'));
        $active = intval(isset($aRequestVars['active']) ? $aRequestVars['active'] : 0);
        // do not allow droplets in user input!
        $title   = $admin->StripCodeFromText( $title);
        $comment = $admin->StripCodeFromText( $comment);
        $admin->print_header();

        // Update row
        $sql  = 'UPDATE '.TABLE_PREFIX.'mod_news_comments SET '
              . '`title`=\''.$database->escapeString($title).'\', '
              . '`comment`=\''.$database->escapeString($comment).'\', '
              . '`active` = '.(int)$active.' '
              . ' WHERE `comment_id`=\''.$database->escapeString($comment_id).'\'';
        if (!$database->query($sql)){
            $sErrorMessage = sprintf($database->get_error());
            throw new \Exception ($sErrorMessage);
        }
    }
}catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
$sExtra = '';
$aMessage = PreCheck::xnl2br(sprintf("%s %s\n",$sExtra,$oTrans->MOD_NEWS_SUCCESS_COMMENT));
$admin->print_success($aMessage, $sBackLinkUrl);

// Print admin footer
    $admin->print_footer();
