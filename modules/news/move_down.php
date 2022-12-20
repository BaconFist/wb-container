<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2019, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: move_down.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/move_down.php $
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
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'/config.php')) {require($sAppPath.'/config.php');}
    $sDumpPathname = \basename($sAddonPath).'/'.\basename($sAddonFile);

try {

    $sAddonName = \basename(__DIR__);
    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;

// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $oReg = WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $database = $oReg->getDatabase();
    $oApp   = $oReg->getApplication();

    $sAddonUrl  = $oReg->AcpUrl.$sAddonRel;
    $sAddonPath = $oReg->AppPath.$sAddonRel;

    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sAddonBackUrl = $oReg->AcpUrl.'pages/modify.php?page_id='.(int)$page_id.$sSectionIdPrefix;

    $oTrans = \Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonName);
    if (!SecureTokens::checkFTAN()) {
        $sMessage = sprintf('%s ',$MESSAGE['GENERIC_SECURITY_ACCESS']);
        throw new \Exception ($sMessage);
    }

// Get id
//    $pid = isset($aRequestVars['post_id']) ? $admin->checkIDKEY('post_id', false, 'GET') : 0;
//    $gid = isset($aRequestVars['group_id']) ? $admin->checkIDKEY('group_id', false, 'GET') : 0;
//    $pid = isset($aRequestVars['post_id']) ? $aRequestVars['post_id'] : 0;
//    $gid = isset($aRequestVars['group_id']) ? $aRequestVars['group_id'] : 0;
    $aJsonRespond['$aRequestVars'] = $aRequestVars;

    $iPostId   = ($oApp->getIdFromRequest('post_id'));
    $iGroupId  = ($oApp->getIdFromRequest('group_id'));

    if (!$iGroupId && !$iPostId) {
        $sMessage = sprintf('%s ',$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($sMessage);
    }

    if (is_null($iPostId) && ($iGroupId > 0)) {
        $id = $iGroupId;
        $id_field = 'group_id';
        $table = TABLE_PREFIX.'mod_news_groups';
    } else if (is_null($iGroupId) && ($iPostId > 0)){
        $id = $iPostId;
        $id_field = 'post_id';
        $table = TABLE_PREFIX.'mod_news_posts';
    }
    $sMessage = \json_encode($aJsonRespond);
//    exit ($sMessage);
// Create new order object an reorder
    $order = new order($table, 'position', $id_field, 'section_id');
    if ($order->move_down($id)) {
        $admin->print_success(sprintf("%s\n%s",$oTrans->TEXT_SUCCESS,''), $sAddonBackUrl);
    } else {
        $sMessage = sprintf("%s \n%s",$oTrans->TEXT_ERROR,'');
        throw new \Exception ($sMessage);
    }
} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}// Print admin footer
$admin->print_footer();
