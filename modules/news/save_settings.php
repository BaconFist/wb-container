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
 * @version         $Id: save_settings.php 366 2019-06-02 11:55:33Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/save_settings.php $
 * @lastmodified    $Date: 2019-06-02 13:55:33 +0200 (So, 02. Jun 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')) {require( dirname(dirname((__DIR__))).'/config.php');}

    $sAddonName = \basename(__DIR__);
    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;

try {
//  Only for Development as pretty mysql dump
    $sLocalDebug  = (is_readable($sAddonPath.'/.setDebug'));
    $sSecureToken = (is_readable($sAddonPath.'/.setToken'));
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $oRequest = ($oRequest ?? \bin\requester\HttpRequester::getInstance());
    $admin->print_header();

    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackSettingsLink = $sAddonUrl.'/modify_settings.php?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery;
    $sBacklink = ($oRequest->getParam('save_close') ? $sBacklink.'#'.$sSectionIdPrefix.$section_id : $sBackSettingsLink );
    $sAddonBackUrl = $sBacklink;

    $sGetOldSecureToken = \bin\SecureTokens::checkFTAN();
    $aFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];

    $oTrans = \Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonName);

    if (!$sGetOldSecureToken){
        $aMessage = \sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($aMessage);
    }

    $bSettingsRights = (($admin->ami_group_member('1')||$admin->get_permission('settings_view')) ? true : false);
// This code removes any <?php tags and adds slashes
    $friendly = ['&lt;', '&gt;', '?php'];
    $raw = ['<', '>', ''];
/*
    $header = Sanitize::StripFromText( $admin->get_post('header'),Sanitize::REMOVE_DEFAULT);
    $post_loop = Sanitize::StripFromText( $admin->get_post('post_loop'),Sanitize::REMOVE_DEFAULT);
    $footer = Sanitize::StripFromText( $admin->get_post('footer'),Sanitize::REMOVE_DEFAULT);
    $post_header = Sanitize::StripFromText( $admin->get_post('post_header'),Sanitize::REMOVE_DEFAULT);
    $post_footer = Sanitize::StripFromText( $admin->get_post('post_footer'),Sanitize::REMOVE_DEFAULT);

    $comments_header = Sanitize::StripFromText( $admin->get_post('comments_header'),Sanitize::REMOVE_DEFAULT);
    $comments_loop = Sanitize::StripFromText( $admin->get_post('comments_loop'),Sanitize::REMOVE_DEFAULT);
    $comments_footer = Sanitize::StripFromText( $admin->get_post('comments_footer'),Sanitize::REMOVE_DEFAULT);
    $comments_page = Sanitize::StripFromText( $admin->get_post('comments_page'),Sanitize::REMOVE_DEFAULT);
*/

    $sLayout = Sanitize::StripFromText( $admin->get_post('new_layout'),Sanitize::REMOVE_DEFAULT);
    $sLayoutId = Sanitize::StripFromText($admin->get_post('layout_id'),Sanitize::REMOVE_DEFAULT);
    $sOrderField = Sanitize::StripFromText( $admin->get_post('order_field'),Sanitize::REMOVE_DEFAULT);
    $sOrder = Sanitize::StripFromText( $admin->get_post('order'),Sanitize::REMOVE_DEFAULT);
    $sCommenting = Sanitize::StripFromText($admin->get_post('commenting'),Sanitize::REMOVE_DEFAULT);
    $iPostsPerPage = Sanitize::StripFromText($admin->get_post('posts_per_page'),Sanitize::REMOVE_DEFAULT);
    $bUseCaptcha = Sanitize::StripFromText($admin->get_post('use_captcha'),Sanitize::REMOVE_DEFAULT);
    $bUseDataProtection = Sanitize::StripFromText($admin->get_post('use_data_protection'),Sanitize::REMOVE_DEFAULT);
    $iDataProtectionLink = Sanitize::StripFromText($admin->get_post('data_protection_link'),Sanitize::REMOVE_DEFAULT);
    $iResize = Sanitize::StripFromText($admin->get_post('resize'),Sanitize::REMOVE_DEFAULT);

    $sLayout = \filter_var(
                  $sLayout,
                  \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                  ['options' => ['default' => 'default_layout']]
    );

    $sLayoutId = \filter_var(
                  $sLayoutId,
                  \FILTER_VALIDATE_INT,
                  ['options' => ['default' => 1]]
    );

    $sOrderField = \filter_var(
                  $sOrderField,
                  \FILTER_VALIDATE_REGEXP,
                  ['options' => ['regexp' => '/published\_when|title$/i', 'default' => 'position']]
    );
    $sOrder = \filter_var(
                  $sOrder,
                  \FILTER_VALIDATE_REGEXP,
                  ['options' => ['regexp' => '/ASC$/i','default'=>'DESC']]
    );
    $sCommenting = \filter_var(
                  $sCommenting,
                  \FILTER_VALIDATE_REGEXP,
                  ['options' => ['regexp' => '/public|private$/i','default'=>'none']]
    );
    $iPostsPerPage = \filter_var(
              $iPostsPerPage,
              \FILTER_VALIDATE_INT,
              ['options' => ['min_range' => 0, 'max_range' => 100, 'default' => 10]]
    );
    $bUseCaptcha = \filter_var($bUseCaptcha,\FILTER_VALIDATE_BOOLEAN);
    $bUseDataProtection = \filter_var($bUseDataProtection,\FILTER_VALIDATE_BOOLEAN);
    $iDataProtectionLink = \filter_var($iDataProtectionLink,\FILTER_VALIDATE_INT);
    $iResize = \filter_var(
              $iResize,
              \FILTER_VALIDATE_INT,
              ['options' => ['min_range' => 0, 'max_range' => 150, 'default' => 0]]
    );
// INSERT INTO or UPDATE settings table
    $sqlBodySet = ''
          . '`layout` = \''.$database->escapeString($sLayout).'\', '
          . '`layout_id` = '.(int)$sLayoutId.', '
//          . '`header`=\''.$database->escapeString($header).'\', '
//          . '`post_loop`=\''.$database->escapeString($post_loop).'\', '
//          . '`footer`=\''.$database->escapeString($footer).'\', '
//          . '`post_header`=\''.$database->escapeString($post_header).'\', '
//          . '`post_footer`=\''.$database->escapeString($post_footer).'\', '
//          . '`comments_header`=\''.$database->escapeString($comments_header).'\', '
//          . '`comments_loop`=\''.$database->escapeString($comments_loop).'\', '
//          . '`comments_footer`=\''.$database->escapeString($comments_footer).'\', '
//          . '`comments_page`=\''.$database->escapeString($comments_page).'\', '
          . '`posts_per_page`= '.(int)($iPostsPerPage).', '
          . '`commenting`=\''.$database->escapeString($sCommenting).'\', '
          . '`resize`= '.(int)$iResize.', '
          . '`use_captcha`= '.(int)$bUseCaptcha.', '
          . '`order_field`=\''.$database->escapeString($sOrderField).'\', '
          . '`use_data_protection`= '.(int)$bUseDataProtection.', '
          . '`data_protection_link`='.(int)$iDataProtectionLink.', '
          . '`order`=\''.$database->escapeString($sOrder).'\' ';

          $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_settings` '
          . 'WHERE `section_id`='.(int)$section_id;
          if (!($iNumRow = $database->get_one($sql))){
              $sqlType    = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_settings` SET '.$sPHP_EOL
                          . '`section_id`='.(int)$section_id.', '.$sPHP_EOL
                          . '`page_id`='.(int)$page_id.', '.$sPHP_EOL;
              $sSqlWhere  = '';
          } else {
              $sqlType   = 'UPDATE `'.TABLE_PREFIX.'mod_news_settings` SET ';
              $sSqlWhere  = 'WHERE `section_id`='.(int)$section_id.' ';
          }
    $sSql = $sqlType.$sqlBodySet.$sSqlWhere;
// write settings
    if (!$database->query($sSql)){
        $aMessage = sprintf('%s %s ',$sSql,$database->get_error());
        throw new \Exception ($aMessage);
    }

} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
    $sExtra = '';
    $aMessage = PreCheck::xnl2br(sprintf('%s %s ',$sExtra,$MESSAGE['SETTINGS_SAVED']));
    $admin->print_success($aMessage, $sAddonBackUrl);

// Print admin footer
$admin->print_footer();
