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
 * @requirements    PHP 7.4 and higher
 * @version         $Id: save_post.php 292 2019-03-26 20:09:43Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/save_post.php $
 * @lastmodified    $Date: 2019-03-26 21:09:43 +0100 (Di, 26. Mrz 2019) $
 *
 */
declare(strict_types=1);

use bin\{WbAdaptor,SecureTokens,Sanitize,requester};
use bin\helpers\{PreCheck,msgQueue};


   function createNewsAccessFile($aOptional=[])
   {
//      $sAddonName = \basename(__DIR__);
      $iVariables = \extract($aOptional);
      $bExcecuteCommand = false;
      $bCaseSensitiv = true; // TODO set by settings

      include \dirname(__DIR__).'/SimpleCommandDispatcher.inc.php';

      $admin = $oReg->App;
      $aMessage = [];
// Work-out all needed path and filenames
      $sAccessFileRootPath = $oReg->AppPath.$oReg->PagesDir.$sNewsLinkSubdir;
      $sOldLink     = \preg_replace('/^\/?'.\preg_quote($sNewsLinkSubdir, '/').'/', '', \str_replace('\\', '/', $oldLink));
      $sOldFilename = $sAccessFileRootPath.$sOldLink.$oReg->PageExtension;
 //     $sNewLink     = page_filename($title,$bCaseSensitiv).$oReg->PageSpacer.$post_id;
      $sNewLink     = PreCheck::sanitizeFilename($title,$bCaseSensitiv).$oReg->PageSpacer.$post_id;
//      $newFile      = $sPagesPath.$newLink.PAGE_EXTENSION;
      $sNewFilename = $sAccessFileRootPath.$sNewLink.$oReg->PageExtension;
// create /posts/ - directory if not exists
      if (!\is_readable($sPostsPath)) {
         if (\is_writable($sPagesPath)) {
            make_dir($sPostsPath);
         }else {
            $aMessage = (\sprintf('[%d] %s',__LINE__,$MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE']));
            throw new \Exception ($aMessage);
         }
      }
   // check if /posts/ - dir is writable
      if (!\is_writable($sPostsPath.'/')) {
         $aMessage = (\sprintf('[%d] %s',__LINE__,$MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE']));
      }
        $sDoWhat = (($sNewLink == $sOldLink) && (\is_readable($sNewFilename)))
                   ? "nothing"
                   : ((\is_writable($sOldFilename)) ? "update" : "create");
        switch($sDoWhat)
        {
            case "update":
                try {
//                    $oAF = new \AccessFile($sAccessFileRootPath, $sOldLink, $page_id);
//                    $oAF->rename($sNewLink);
//                    unset($oAF);
                      \unlink($sOldFilename);
                }catch(\AccessFileException $e) {
                    $aMessage = $e;
                    throw new \Exception ($aMessage);
                }
//            break;
            case "create":
                try {
                    $oAF = new \AccessFile($sAccessFileRootPath, $sNewLink, $page_id);
                    $oAF->addVar('section_id', $section_id, \AccessFile::VAR_INT);
                    $oAF->addVar('post_id', $post_id, \AccessFile::VAR_INT);
                    $oAF->addVar('post_section', $section_id, \AccessFile::VAR_INT);
                    $oAF->write();
                    unset($oAF);
                }catch(\AccessFileException $e) {
                    $aMessage = $e;
                    throw new \Exception ($aMessage);
                }
            break;
        }
        return (!empty($sNewLink) ? '/posts/'.$sNewLink : false);
   } // end of function createNewsAccessFile

/* ************************************************************************** */


//    if (!\defined('SYSTEM_RUN')) {require(\dirname(\dirname((__DIR__))).'/config.php');}
    $sAddonFile   = \str_replace('\\','/',__FILE__);
    $sAddonPath   = (\dirname($sAddonFile)).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }

try {
//
/*
    $sAddonName = \basename(__DIR__);
    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;
//  Only for Development as pretty mysql dump
    $sLocalDebug  = true;
    $sSecureToken = false;
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
*///
/* -------------------------------------------------------- */
    $bLocalDebug  = (is_readable($sAddonPath.'.setDebug'));
    $bSecureToken = (!is_readable($sAddonPath.'.setToken'));
    $bFrontendCss = (!is_readable($sAddonPath.'.setFrontend'));
    $sPHP_EOL     = ($bLocalDebug ? "\n" : '');
    $sqlEOL       = ($bLocalDebug ? "\n" : "");

/* ------------------------------------------------------------------ */
// print with or without header
    $admin_header = false;
// Workout if the developer wants to show the info banner
    $print_info_banner = false; // true/false
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require($sModulesPath.'admin.php');
/* -------------------------------------------------------- */
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */
    $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
    $ModuleUrl      = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl      = $oReg->AppUrl.$sAddonRel;
    $sDomain        = $oApp->getDirNamespace(__DIR__);
    $oTrans->enableAddon($sDomain);
/* -------------------------------------------------------- */

    $sPagesPath = $oReg->AppPath.$oReg->PagesDir;
    $sPostsPath = $sPagesPath.'posts/';
    $sNewsLinkSubdir = 'posts/';

    $sPostIdKey = $iPostId = $oRequest->getParam('post_id',\FILTER_VALIDATE_INT);
    $saveType   = $oRequest->getParam('save-type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
/*
    $iPostId =  \bin\SecureTokens::checkIDKEY('post_id');
    $sPostIdKey = \bin\SecureTokens::getIDKEY($iPostId);
*/
    $sGetOldSecureToken = \bin\SecureTokens::checkFTAN();
    $aFtan = \bin\SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
//
    $sSectionIdPrefix = (\defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$section_id : '' );
    $sBacklink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    $sBackPostLink = WB_URL.'/modules/'.$sAddonName.'/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery.'&post_id=';
    $sBacklink = ($oRequest->getParam('save-close') ? $sBacklink.'#'.$sSectionIdPrefix.$section_id : $sBackPostLink );
    $sAddonBackUrl = $sBacklink;

    if (!$sGetOldSecureToken){
        $aMessage = \sprintf($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        $sAddonBackUrl = $sBacklink;
        throw new \Exception ($aMessage);
    }
//
// Validate all fields
    if (empty($admin->get_post('title'))) {
       $aMessage = \sprintf('%s (empty title) ',$oTrans->MESSAGE_GENERIC_FILL_IN_ALL);
       throw new \Exception ($aMessage);
    }else {
      $title      = $admin->StripCodeFromText($admin->get_post('title'));
      $short      = $admin->StripCodeFromText($admin->get_post('content_short'));
      $long       = $admin->StripCodeFromText($admin->get_post('content_long'));
      $commenting = $admin->StripCodeFromText($admin->get_post('commenting'));
      $moderated  = \intval($admin->get_post('moderated'));
      $active     = \intval($admin->get_post('active'));
//      $old_link   = $admin->StripCodeFromText($admin->get_post('link'));
      $group_id   = \intval($admin->get_post('group'));
    }
//
    $short = $admin->ReplaceAbsoluteMediaUrl($short);
    $long  = $admin->ReplaceAbsoluteMediaUrl($long);
// Get post old link URL
    $sql  = 'SELECT `link` FROM `'.TABLE_PREFIX.'mod_news_posts` '
          . 'WHERE `post_id`='.$iPostId;
    if (is_null($oldLink = $database->get_one($sql))){$oldLink = '';}
// Include WB functions file
    if (!\function_exists ('jscalendar_to_timestamp')) {require(WB_PATH."/include/jscalendar/jscalendar-functions.php");}
//    require (WB_PATH.'/framework/functions.php');
/*  do not use in production mode, only for development
    if ($saveType == 'insert'){
        $sSql  = 'SELECT `AUTO_INCREMENT` '
              . 'FROM `information_schema`.`TABLES` '
              . 'WHERE `TABLE_NAME` = \''.TABLE_PREFIX.'mod_news_posts\' '
              .   'AND `TABLE_SCHEMA` = \''.DB_NAME.'\' ';
        if (!($iPostId = $database->get_one($sSql))){
           $aMessage = \sprintf("ErrNo => %d \n %s \n %s",$database->get_errno(), $sSql, $database->get_error());
           throw new \Exception ($aMessage);
        }
    }
*/
// Work-out what the link should be
    $newLink     = '/posts/'.PreCheck::sanitizeFilename($title).$oReg->PageSpacer.$iPostId;
    $now = \time();

// get publisedwhen and publisheduntil
    $publishedwhen = jscalendar_to_timestamp($admin->get_post('publishdate'));
    if (($publishedwhen == '') || ($publishedwhen < 1)) { $publishedwhen = 0; }
    $publisheduntil = jscalendar_to_timestamp($admin->get_post('enddate'), $publishedwhen);
    if (($publisheduntil == '') || ($publisheduntil < 1)) { $publisheduntil = 0; }
    $order = new order($oReg->TablePrefix.'mod_news_posts', 'position', 'post_id', 'section_id');
    $sqlBodySet  = ''
             . '`group_id`='.(int)$group_id.', '.$sPHP_EOL
             . '`active`='.\intval($active).', '.$sPHP_EOL
             . '`title`=\''.$database->escapeString($title).'\', '.$sPHP_EOL
             . '`link`=\''.$database->escapeString($newLink).'\', '.$sPHP_EOL
             . '`content_short`=\''.$database->escapeString($short).'\', '.$sPHP_EOL
             . '`content_long`=\''.$database->escapeString($long).'\', '.$sPHP_EOL
             . '`commenting`=\''.$database->escapeString($commenting).'\', '.$sPHP_EOL
             . '`moderated`='.\intval($moderated).', '.$sPHP_EOL
             . '`published_when`='.(int)$publishedwhen.', '.$sPHP_EOL
             . '`published_until`='.(int)$publisheduntil.', '.$sPHP_EOL
             . '`modified_when`='.(int)$now.', '.$sPHP_EOL
             . '`modified_by`='.(int)$admin->getUserId().' '.$sPHP_EOL;
    if (($saveType == 'insert')){
// Get new order
        $position = $order->get_new($section_id);
        $sqlType    = 'INSERT INTO `'.$oReg->TablePrefix.'mod_news_posts` SET '.$sPHP_EOL
                . '`section_id`='.(int)($section_id).', '.$sPHP_EOL
                . '`page_id`='.(int)($page_id).', '.$sPHP_EOL
                . '`position`='.(int)($position).', '.$sPHP_EOL
                . '`created_when`='.(int)$now.', '.$sPHP_EOL
                . '`created_by`='.(int)$admin->getUserId().', '.$sPHP_EOL
                . '`posted_by` ='.(int)$admin->getUserId().', '.$sPHP_EOL
                . '`posted_when` ='.(int)$now.', '.$sPHP_EOL;
        $sSqlWhere  = '';
    } else {
// Update row
        $sqlType    = 'UPDATE `'.$oReg->TablePrefix.'mod_news_posts` SET '.$sPHP_EOL;
        $sSqlWhere  = 'WHERE `post_id`='.(int)$iPostId;
    }
    $sSql = $sqlType.$sqlBodySet.$sSqlWhere;
    if ($database->query($sSql)){
        $iPostId = $sPostIdKey = (($saveType == 'insert') ? $database->getLastInsertId() : $iPostId);
//        $sPostIdKey =  \bin\SecureTokens::getIDKEY($iPostId);
      // Check if there is a db error, otherwise say successful
        if ($database->is_error()) {
           $aMessage = \sprintf('%s',$database->get_error());
           throw new \Exception ($aMessage);
        } else {

      // create new accessfile
            $aOptional = [
                //'oReg' => $oReg,
                'page_id'=>(int)$page_id,
                'section_id'=>(int)$section_id,
                'post_id'=>(int)$iPostId,
                'group_id'=>(int)$group_id,
                'title'=>$title,
                'oldLink'=>$oldLink,
                'publishedwhen'=>$publishedwhen,
                'sPagesPath'=>$sPagesPath,
                'sPostsPath'=>$sPostsPath,
                'sNewsLinkSubdir'=>$sNewsLinkSubdir,
                '$sAddonPath' => $sAddonPath,
                'sAddonName'=>$sAddonName,
                'sAddonRel'=>$sAddonRel,
            ];

            if ($newLink = createNewsAccessFile($aOptional)){
// update with corrected access filename
                $sqlBodySet  = ''
                     . '`link`=\''.$database->escapeString($newLink).'\' '.$sPHP_EOL;
                $sqlType    = 'UPDATE `'.$oReg->TablePrefix.'mod_news_posts` SET '.$sPHP_EOL;
                $sSqlWhere  = 'WHERE `post_id`='.(int)$iPostId;
                $sSql = $sqlType.$sqlBodySet.$sSqlWhere;
                if (!($database->query($sSql))){
                    $aMessage = \sprintf("ErrNo => %d \n %s \n %s",$database->get_errno(), $sSql, $database->get_error());
                    throw new \Exception ($aMessage);
                }
            }
        }
    } else {
       $aMessage = \sprintf("ErrNo => %d \n %s \n %s",$database->get_errno(), $sSql, $database->get_error());
       throw new \Exception ($aMessage);
    }

} catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%03d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl.$sPostIdKey);
    exit;
}

    $admin->print_header();
    $order->get_new($section_id);
    $sMessage = sprintf($oTrans->MOD_NEWS_SUCCESS_POST, $title.'-'.$iPostId);

    $admin->print_success(sprintf('[%03d] %s',__LINE__,$sMessage), $sAddonBackUrl.$sPostIdKey);  //

// Print admin footer
    $admin->print_footer();
