<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        modules
 * @package         news
 * @subpackage      reorgPosition
 * @author          Dietmar Wöllbrink
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: reorgPosition.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/reorgPosition.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')){require(\dirname(\dirname((__DIR__))).'/config.php');}
//
try {
//
    $sAddonName = \basename(__DIR__);
    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;
//  Only for Development as pretty mysql dump
    $sLocalDebug  = (\is_readable($sAddonPath.'/.setDebug'));
    $sSecureToken = false;
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
//
    $admin_header = false;
    // Tells script to update when this page was last updated
    $update_when_modified = true;
    // Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');
//
    $sGetOldSecureToken = \bin\SecureTokens::checkFTAN();
//    $aFtan = \bin\SecureTokens::getFTAN();
//    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.$sSectionIdPrefix;
    $sAddonBackUrl = $sBacklink;
    $admin->print_header();
//
    if (!$sGetOldSecureToken){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    }
// truncate table posts if empty
    $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_posts` ';
    if (!($iNumPost = $database->get_one($sql))){
        if (!($database->query('TRUNCATE TABLE `'.TABLE_PREFIX.'mod_news_posts` '))){
            $aMessage = \sprintf('%s ',$database->get_error());
            throw new \Exception ($aMessage);
        }
    } else {
// reorg posts positions
//        \trigger_error(\sprintf('[%03d] mod_news_posts checked, starting reorder',__LINE__), E_USER_NOTICE);
        $news   = new \order(TABLE_PREFIX.'mod_news_posts', 'position', 'post_id', 'section_id');
        $news->clean( $section_id );
    }
// truncate table groups if empty
    $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_groups` ';
    if (!($iNumGroup = $database->get_one($sql))){
        if (!($database->query('TRUNCATE TABLE `'.TABLE_PREFIX.'mod_news_groups` '))){
            $aMessage = \sprintf('%s ',$database->get_error());
            throw new \Exception ($aMessage);
        }
    } else {
// reorg groups positions
        $groups = new \order(TABLE_PREFIX.'mod_news_groups', 'position', 'group_id', 'section_id');
        $groups->clean( $section_id );
    }
//
} catch (\Exception $ex) {
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
//
    $admin->print_success(\sprintf($MESSAGE['REORG_SUCCESS']), $sAddonBackUrl );
