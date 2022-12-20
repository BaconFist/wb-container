<?php
/**
 *
 * @category        modules
 * @package         JsAdmin
 * @author          WebsiteBaker Project, modified by Swen Uth for Website Baker 2.7
 * @copyright       (C) 2006, Stepan Riha
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: move_to.php 94 2018-09-20 18:54:33Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/move_to.php $
 * @lastmodified    $Date: 2018-09-20 20:54:33 +0200 (Do, 20. Sep 2018) $
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

    $aJsonRespond = [];
/*
    $aJsonRespond['jsadmin'] = [];
    $aJsonRespond['module'] = '';
    $aJsonRespond['module_dir'] = '';
*/
    $aJsonRespond['message'] = 'ajax operation failed';
    $aJsonRespond['success'] = false;

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

    $position = ($oApp->getIdFromRequest('newposition'));
    $module   = ($oRequest->getParam('module'));

    $aJsonRespond['success'] = true;
/*
    $aJsonRespond['module'] = $module;
    $aJsonRespond['modules_dir'] = '/modules/'.$module;
*/
    $cleanOrder = (function($common_id) use ($database){ // ,$table,$sFieldOrderName,$common_field
        global $table,$sFieldOrderName,$common_field;
// Loop through all records and give new order
        $sql  = 'SET @c:=0';
        $database->query($sql);
        $sql  = 'UPDATE `'.$table.'` SET `'.$sFieldOrderName.'`=(SELECT @c:=@c+1) '
              . 'WHERE `'.$common_field.'`=\''.$database->escapeString($common_id).'\' '
              . 'ORDER BY `'.$sFieldOrderName.'` ASC;';
        if ($database->query($sql)){
            echo nl2br(sprintf("$sql\n"));
//          \trigger_error(sprintf("[%d] Db UPDATE %s",__LINE__, $sql), E_USER_NOTICE);
        } else {
          \trigger_error(sprintf('[%d] Db Error %s',__LINE__, $database->get_error()), E_USER_NOTICE);
          $aJsonRespond['message'] = $sFieldOrderName."\n".$database->get_error();
          $aJsonRespond['success'] = false;
          exit (\json_encode($aJsonRespond));
        }
    });

    if (\is_numeric($page_id) && \is_numeric($position)){
    // Interface move_to.php from modules
        if (!is_null($module)) {
//            $aJsonRespond['jsadmin'] = $aRequestVars;
            $sParameterFileName = WB_PATH.'/modules/'.$module.'/move_to.php';
//            echo sprintf("%s",$sParameterFileName);
            if (\is_readable($sParameterFileName)){
                require $sParameterFileName;
//            exit(\json_encode($aJsonRespond));
            }
        } elseif ((\is_numeric($section_id) || \is_numeric($page_id)) && is_null($module))
        {  // default Interface move_to.php from core
            // Get common fields
            if (($section_id > 0)) {
//                $page_id = (int)$page_id;
                $id = (int)$section_id;
                $id_field = 'section_id';
//                $group = (int)$aRequestVars['section_id'];
                $sFieldOrderName = 'position';
                $common_field = 'page_id';
                $table = TABLE_PREFIX.'sections';
                $aJsonRespond['modules'] = '/'.ADMIN_DIRECTORY.'/pages/sections.php';
            } elseif (($page_id > 0)) {
                $id = (int)$page_id;
                $id_field = 'page_id';
                // $group = (int)$aRequestVars['page_id'];
                $sFieldOrderName = 'position';
                $common_field = 'parent';
                $table = TABLE_PREFIX.'pages';
                $aJsonRespond['modules'] = '/'.ADMIN_DIRECTORY.'/pages/index.php';
            }
        }
    // Get current index
        $sql = sprintf('SELECT `'.$common_field.'`, `'.$sFieldOrderName.'`'."\n".' FROM `'.$table.'`'."\n".' WHERE `'.$id_field.'` = '.(int)$id);
        echo  nl2br(sprintf("$sql\n"));
        if ($oRes = $database->query($sql)){
            if ($row = $oRes->fetchRow(MYSQLI_ASSOC)) {
                $common_id = $row[$common_field];
                $old_position = (int)$row['position'];
            }
        } else {
          $aJsonRespond['message'] = $sFieldOrderName."\n".$database->get_error();
          $aJsonRespond['success'] = false;
          exit (\json_encode($aJsonRespond));
        }
/*
    echo nl2br(sprintf("Move Position: $old_position"."\n"));
    echo nl2br(sprintf("To Position: $position"."\n"));
    echo nl2br(sprintf("$common_field: $common_id"."\n"));
*/
        if ($old_position === $position){
          $cleanOrder($common_id);
          return;
        }

    // Build query to update affected rows
        if ($old_position < $position){
            $sql  = sprintf('UPDATE '.'`'.$table.'` SET '."\n".'`'.$sFieldOrderName.'` = `'.$sFieldOrderName.'` -1 '."\n"
                  . 'WHERE `'.$sFieldOrderName.'` > '.$old_position.' '."\n"
                  .   'AND `'.$sFieldOrderName.'` <= '.(int)$position.' '."\n"
                  .   'AND `'.$common_field.'` = '.(int)$common_id."\n");
        } else {
            $sql  = sprintf('UPDATE '.'`'.$table.'` SET '."\n".'`position` = `position` +1 '."\n"
                  . 'WHERE `'.$sFieldOrderName.'` >= '.$position.' '."\n"
                  .   'AND `'.$sFieldOrderName.'` < '.(int)$old_position.' '."\n"
                  .   'AND `'.$common_field.'` = '.(int)$common_id."\n");
        }
        if ($database->query($sql)){
            echo  nl2br(sprintf("$sql\n"));
        }
        if ($database->is_error()){
            $aJsonRespond['message'] = $sFieldOrderName."\n".$database->get_error();
            $aJsonRespond['success'] = false;
            exit (\json_encode($aJsonRespond));
        }

// Build query to update specified row
        $sql  = sprintf('UPDATE '.'`'.$table.'` SET '."\n".'`'.$sFieldOrderName.'` = '.(int)$position.' '."\n"
              . 'WHERE `'.$id_field.'` = '.(int)$id."\n");
        if ($database->query($sql)){
            echo nl2br(sprintf("$sql\n"));
            $cleanOrder($common_id);
            $aJsonRespond['success'] = true;
            echo (\json_encode($aJsonRespond));
        }
        if ($database->is_error()){
            $aJsonRespond['message'] = $sFieldOrderName."\n".$database->get_error();
            $aJsonRespond['success'] = false;
            exit (\json_encode($aJsonRespond));
        }
    } else {
        $aJsonRespond['message'] = "Missing parameters";
        $aJsonRespond['success'] = false;
        exit (\json_encode($aJsonRespond));
    }

}catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $oApp->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

