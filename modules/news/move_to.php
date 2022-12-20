<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for Website Baker 2.7
 * @copyright       (C) 2006, Stepan Riha
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 7.2 and higher
 * @version         $Id: move_to.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/move_to.php $
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

    // Include WB admin wrapper script
    $update_when_modified = false;
// Tells script to update when this page was last updated
    $admin_header = false;
    require($sAppPath.'/modules/admin.php');

    $oReg     = WbAdaptor::getInstance();
    $database = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    $sAddonBackUrl = $oReg->AcpUrl;

    $aJsonRespond['success'] = true;
/* */

    $post_id = $oApp->getIdFromRequest('post_id');
    $move_id = $oApp->getIdFromRequest('move_id');
    $group_id = $oApp->getIdFromRequest('group_id');
/* */
    if ($sLocalDebug){
        $aJsonRespond['module'] = $aRequestVars['module'];
        $aJsonRespond['modules_dir'] = '/modules/'.$aRequestVars['module'];
        $aJsonRespond['postId']  = $post_id;
        $aJsonRespond['moveId']  = $move_id;
        $aJsonRespond['groupId'] = $group_id;
    }

// Get id
    if (($post_id > 0) && ($move_id > 0)){
        $table = TABLE_PREFIX.'mod_news_posts';
        $id = (int)$move_id;
        $id_field = 'post_id';
        $common_field = 'section_id';
        $sFieldOrderName = 'position';
        $aJsonRespond['message'] = 'Activity position '.$id.' successfully changed';
    } else
    if (($group_id > 0) && ($move_id > 0)){
        $table = TABLE_PREFIX.'mod_news_groups';
        $id = (int)$move_id;
        $id_field = 'group_id';
        $common_field = 'section_id';
        $sFieldOrderName = 'position';
        $aJsonRespond['message'] = 'Activity position '.$id.' successfully changed';
    } else {
      $aJsonRespond['message'] = 'ajax operation failed';
      $aJsonRespond['success'] = false;
      exit (json_encode($aJsonRespond));
    }

