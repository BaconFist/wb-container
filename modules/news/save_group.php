<?php
/**
 */
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of modules/news/save_group.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: save_group.php 151 2018-10-09 11:54:18Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/* ************************************************************************** */
if (!\defined('SYSTEM_RUN')) {require(\dirname(\dirname((__DIR__))).'/config.php');}

try {

    $sAddonName = \basename(__DIR__);
    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;
//  Only for Development as pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $sGroupIdKey = $iGroupId = $oRequest->getParam('group_id',\FILTER_VALIDATE_INT);
    $saveType    = $oRequest->getParam('save-type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
/*
    $iGroupId    =  \bin\SecureTokens::checkIDKEY('group_id');
    $sGroupIdKey = \bin\SecureTokens::getIDKEY($iGroupId);
*/
    $sGetOldSecureToken = (\bin\SecureTokens::checkFTAN());
    $aFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];

    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackGroupLink = WB_URL.'/modules/'.$sAddonName.'/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery.'&group_id=';
    $sBacklink = ($oRequest->getParam('save_close') ? $sBacklink.'#'.$sSectionIdPrefix.$section_id : $sBackGroupLink );
    $sAddonBackUrl = $sBacklink;

    $oTrans = \Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonName);

    if ($sSecureToken && !$sGetOldSecureToken){
        $aMessage = \sprintf($MESSAGE['GENERIC_SECURITY_ACCESS']);
        $sAddonBackUrl = $sBacklink;
        throw new \Exception ($aMessage);
    }

// Validate all fields
    if (empty($admin->get_post('title'))) {
       $aMessage = \sprintf('%s (empty title) ',$MESSAGE['GENERIC_FILL_IN_ALL']);
       throw new \Exception ($aMessage);
    }else {
       $title  = $admin->StripCodeFromText($admin->get_post('title'));
       $active = \intval($admin->get_post('active'));
    }

    $order = new \order(TABLE_PREFIX.'mod_news_groups', 'position', 'group_id', 'section_id');
    $sqlBodySet = ''
                . '`title`=\''.$database->escapeString($title).'\', '.$sPHP_EOL
                . '`active`=\''.$database->escapeString($active).'\' '.$sPHP_EOL;

    if ($saveType == 'insert'){
// Get new order
        $position = $order->get_new($section_id);
        $sqlType    = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_groups` SET '.$sPHP_EOL
                . '`section_id`='.(int)$section_id.', '.$sPHP_EOL
                . '`page_id`='.(int)$page_id.', '.$sPHP_EOL
                . '`position`='.(int)$position.', '.$sPHP_EOL;
        $sSqlWhere  = '';
    } else {
    // Update row
        $sqlType   = 'UPDATE `'.TABLE_PREFIX.'mod_news_groups` SET '.$sPHP_EOL;
        $sSqlWhere = 'WHERE `group_id`='.(int)($iGroupId).''.$sPHP_EOL;
    }
    $sSql = $sqlType.$sqlBodySet.$sSqlWhere;
    if ($database->query($sSql)){ //
        $sGroupIdKey = ((($iGroupId == 0) && ($saveType == 'insert')) ? $database->getLastInsertId() : $iGroupId);
//        $sGroupIdKey = (($iGroupId != 0) ? $iGroupId : \bin\SecureTokens::getIDKEY($sGroupIdKey);
// Check if there is a db error, otherwise say successful
        if ($database->is_error()) {
           $aMessage = \sprintf('%s',$database->get_error());
           throw new \Exception ($aMessage);
        } else {
    $sGroupImageRel = MEDIA_DIRECTORY.'/.news/image'.$iGroupId.'.jpg';
// first delete old group image if checked if you want no longer a group image
          if (isset($aRequestVars['delete_image']) && $aRequestVars['delete_image'] != '')
          {
             // Try unlinking image
             if (\is_readable(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$sGroupIdKey.'.jpg')){
                \unlink(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$sGroupIdKey.'.jpg');
             }
          }
            $bUploadImage = !empty($_FILES['image-select']['tmp_name']) && ($_FILES['image-select']['error']==0);
// Check if the user uploaded an image
            if ((bool)$bUploadImage) {
           // Get real filename and set new filename
              $sFilename = $_FILES['image-select']['name'];
              $file_image_type = $_FILES['image-select']['type'];
              $new_filename = WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$sGroupIdKey.'.jpg';
      // Make sure the target directory exists
//              require (WB_PATH.'/framework/functions.php');
              make_dir(WB_PATH.MEDIA_DIRECTORY.'/.news');
      // Upload image
              \move_uploaded_file($_FILES['image-select']['tmp_name'], $new_filename);
      // Check if we need to create a thumb
              $sSql = 'SELECT `resize` FROM `'.TABLE_PREFIX.'mod_news_settings` WHERE `section_id` = '.(int)$section_id.'';
              if (!($query_settings = $database->query($sSql))){
                   $aMessage = \sprintf('%s',$database->get_error());
                   throw new \Exception ($aMessage);
              }
              if (\is_null($resize = $database->get_one($sSql))){
                $resize = 0;
              }
              $height = $resize;
              if ($resize == 0)
              {
                  list($resize, $height) = \getimagesize($new_filename);
              }
          // Resize the image
              $thumb_location = WB_PATH.MEDIA_DIRECTORY.'/.news/thumb'.$sGroupIdKey.'.jpg';
              if (make_thumb($new_filename, $thumb_location, $resize, $height))
              {
                 \unlink($new_filename);
                 \rename($thumb_location, $new_filename);
              }
          } // Upload $_FILES
          elseif ($_FILES['image-select']['error'] !==4) {
            $aMessage = sprintf('Image Upload Error No %d',$_FILES['image-select']['error']);
            throw new \Exception ($aMessage);
          }
        }// no dberror
    } // query sql
    else {
       $aMessage = \sprintf('%s ',$database->get_error());
       throw new \Exception ($aMessage);
    }

    unset($_FILES);

} catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl.$sGroupIdKey);
    exit;
}
    $order->clean($section_id);
    $admin->print_header();
    $admin->print_success(sprintf('[%03d] '.$oTrans->MOD_NEWS_SUCCESS_GROUP,__LINE__, $title), $sAddonBackUrl.$sGroupIdKey);
    // Print admin footer
    $admin->print_footer();
