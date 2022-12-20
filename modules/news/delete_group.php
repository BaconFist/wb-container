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
 * @version         $Id: delete_group.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/delete_group.php $
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
//
try {
//

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = false;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');
//
    $iGroupId = $oRequest->getParam('group_id',\FILTER_VALIDATE_INT);
    $sGroupIdKey = $iGroupId; // checkIDKEY
    $group_id = $iGroupId;

    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();;
    $oApp     = $oReg->getApplication();
    $sAddonBackUrl = $oReg->AcpUrl;
    $oTrans->enableAddon('modules\\'.$sAddonName);

    $iGroupId = $oApp->getIdFromRequest('group_id');

    $group_id = ($iGroupId ?? 0);
    $aSql = [];

    if (is_null($iGroupId)) {
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }

    $sGetOldSecureToken = SecureTokens::checkFTAN();
//    $aFtan = \bin\SecureTokens::getFTAN();
//    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = $sAddonBackUrl.'pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix;
    $sAddonBackUrl = $sBacklink;

    if (!$sGetOldSecureToken){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
// Delete group  - do not delete if inuse and fetch title for messages
    $sTitle = $oReg->Db->get_one('SELECT `title` FROM `'.TABLE_PREFIX.'mod_news_groups` WHERE `group_id` = '.(int)$group_id);
    $sInuseSql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_posts` '
                . 'WHERE `group_id` = '.(int)$group_id.'';
    if (($iLayoutInUse = $oReg->Db->get_one($sInuseSql) > 0)){
        $aMessage = sprintf('Deleting of used Group "<i>%s</i>" not possible',$sTitle);
        throw new \Exception ($aMessage);
    } else {
        $bSqlSet = true;
        $sql  = 'DELETE FROM `'.TABLE_PREFIX.'mod_news_groups` '
              . 'WHERE `group_id`= '.(int)$group_id.' ';
    }
// Check if there is a db error, otherwise say successful
    if (!($database->query($sql))) {
        $aMessage = \sprintf('%s ',$database->get_error());
        throw new \Exception ($aMessage);
    }
// Clean up ordering
    $order = new order(TABLE_PREFIX.'mod_news_group', 'position', 'group_id', 'section_id');
    $order->clean($section_id);

} catch (\Exception $ex) {
    $oApp->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $oApp->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
// Print admin footer

$oApp->print_header();
$oApp->print_success(\sprintf($oTrans->MOD_NEWS_DELETED_GROUP,$sTitle), $sAddonBackUrl);
$oApp->print_footer();
