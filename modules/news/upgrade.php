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
 * @version         $Id: upgrade.php 365 2019-05-31 16:31:04Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/upgrade.php $
 * @lastmodified    $Date: 2019-05-31 18:31:04 +0200 (Fr, 31. Mai 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */
/* **** START UPGRADE ******************************************************* */

if(!function_exists('mod_news_Upgrade'))
{
    function mod_news_Upgrade($oReg)
    {
        $sErrorMsg = null;
        $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
        $sAddonName = basename($sAddonPath);
/*--------------------------------------------------------------------------------------------------*/
      $bExcecuteCommand = false;
      include \dirname($sAddonPath).'/SimpleCommandDispatcher.inc.php';

/**
 * There are files which are moved or no longer needed.
 * So we need to delete the old files and directories
 */
        $aFilesToDelete = [
            '/backend.js',
            '/save_post',
            '/install-struct.php',
            '/install-struct.sql',
            '/themes/default/DataTables/',
            '/presets/mod_news_layouts.inc.php',
            '/presets/mod_news_settings.inc.php',
            ];
        PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);
//
        $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
        $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
        $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
        if (version_compare($sWbVersion, $sModulePlatform, '<')){
            $msg[] = $sErrorMsg = sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform);
            if ($globalStarted){
                echo $sErrorMsg;
            }else{
                throw new \Exception ($sErrorMsg);
            }
        } else {
// Work-out all needed path and filenames
            $sPagesPath = WB_PATH.PAGES_DIRECTORY;
            $sAccessFileRootPath = $sPagesPath;
            $sPostsPath = $sPagesPath.'/posts';
            $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
            if (!is_readable($sInstallStruct)) {
                $msg[] = '<b>\'missing or not readable [install-struct] file\'</b> '.$FAIL;
                $iErr = true;
            }
            $sTableName = TABLE_PREFIX.'mod_news_groups';
//            if ($oReg->Db->index_remove($sTableName,'ident_news')){;}
            $sInstallStruct = $sAddonPath.'/install-struct.sql.php';
//            $oReg->Db->addReplacement('XTABLE_ENGINE','ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
//            $oReg->Db->addReplacement('XFIELD_COLLATION','COLLATE utf8mb4_unicode_ci');
            if (!$oReg->Db->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade'))//,'MyISAM','utf8mb4_unicode_ci'
            {
                $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
            }
            $aTable = ['mod_news_posts','mod_news_groups','mod_news_comments','mod_news_layouts','mod_news_settings'];

    // check if new fields must be added
            $doImportDate = true;
            if (!$oReg->Db->field_exists(TABLE_PREFIX.'mod_news_posts', 'created_when')) {
                if (!$oReg->Db->field_add(TABLE_PREFIX.'mod_news_posts', 'created_when',
                                        'INT NOT NULL DEFAULT \'0\' AFTER `commenting`')) {
                    if (!$globalStarted){
                        echo $oReg->Trans->MESSAGE_RECORD_MODIFIED_FAILED.'<br />';
                        return $msg;
                    }else {
                        $admin->print_error($oReg->Trans->MESSAGE_RECORD_MODIFIED_FAILED);
                    }
                }
                if (!$globalStarted) { echo 'datafield `'.TABLE_PREFIX.'mod_news_posts`.`created_when` added.<br />'; }
            } else {
                $doImportDate = false;
            }

            if (!$oReg->Db->field_exists(TABLE_PREFIX.'mod_news_posts', 'created_by')) {
                if (!$oReg->Db->field_add(TABLE_PREFIX.'mod_news_posts', 'created_by',
                                        'INT NOT NULL DEFAULT \'0\' AFTER `created_when`')) {
                    if (!$globalStarted){
                        $msg[] = $oReg->Trans->MESSAGE_RECORD_MODIFIED_FAILED.'';
                        return ;
                    } else {
                        $admin->print_error($oReg->Trans->MESSAGE_RECORD_MODIFIED_FAILED);
                    }
                }
                if (!$globalStarted) {$msg[] = 'datafield `'.TABLE_PREFIX.'mod_news_posts`.`created_by` added.'; }
            }
    // preset new fields `created_by` and `created_when` from existing values
            if($doImportDate) {
                $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_posts` '
                      . 'SET `created_by`=`posted_by`, `created_when`=`posted_when`';
                $oReg->Db->query($sql);
            }
// create /posts/ - directory if not exists
            if (!\file_exists($sPostsPath)) {
                if (\is_writable($sPagesPath)) {
                    make_dir(WB_PATH.PAGES_DIRECTORY.'/posts/');
                }else {
                    if(!$globalStarted){
                        $msg[] = ($oReg->Trans->MESSAGE_PAGES_CANNOT_CREATE_ACCESS_FILE);
                    }else {
                        $msg[] = $oReg->Trans->MESSAGE_PAGES_CANNOT_CREATE_ACCESS_FILE.'';
                        return $msg;
                    }
                }
                if (!$globalStarted) {$msg[] =  'directory "'.PAGES_DIRECTORY.'/posts/" created.'; }
            }

    // now iterate through all existing accessfiles,
    // write its creation date into database
            $oDir = new \DirectoryIterator($sPostsPath);
            $count = 0;
            foreach ($oDir as $fileinfo){
                $fileName = $fileinfo->getFilename();
                if((!$fileinfo->isDot()) &&
                   ($fileName != 'index.php') &&
                   (\substr_compare($fileName,PAGE_EXTENSION,(0-\strlen(PAGE_EXTENSION)),\strlen(PAGE_EXTENSION)) === 0)
                  )
                {
                // save creation date from old accessfile
                    if($doImportDate) {
                        $link = '/posts/'.\preg_replace('/'.\preg_quote(PAGE_EXTENSION).'$/i', '', $fileinfo->getFilename());
                        $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_posts` SET '
                              . '`created_when`='.$fileinfo->getMTime().' '
                              . 'WHERE `link`=\''.$oReg->Db->escapeString($link).'\' '
                              .   'AND `created_when`= 0';
                        $oReg->Db->query($sql);
                    }
                // delete old access file
                    if (\is_writeable($fileinfo->getPathname())){\unlink($fileinfo->getPathname());}
                    $count++;
                }
            }
            unset($oDir);
            if ($globalStarted && $count > 0) {
                $msg[] = 'save date of creation from '.$count.' old accessfiles and delete these files.';
            }
// ************************************************
    // Check the validity of 'create-file-timestamp' and balance against 'posted-timestamp'
            $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_posts` ';
            $sql .= 'SET `created_when`=`published_when` ';
            $sql .= 'WHERE `published_when`<`created_when`';
            $oReg->Db->query($sql);
            $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_posts` ';
            $sql .= 'SET `created_when`=`posted_when` ';
            $sql .= 'WHERE `published_when`=0 OR `published_when`>`posted_when`';
            $oReg->Db->query($sql);
// ************************************************
// remove layouts to db table, TODO optimize this part
            $sTableName = TABLE_PREFIX.'mod_news_layouts';
//            $sql = 'SHOW TABLE STATUS FROM `'.$oReg->Db->db_name.'` LIKE \''.$sTableName .'\'';
            $sql = 'SELECT COUNT(*) FROM `'.$sTableName.'`';
            if (($iNumRow = $oReg->Db->get_one($sql))==0){
                    $aDefaultLayouts = ['default_layout','div_layout','div_new_layout'];
                    $sPattern = '/^.*?([^\/]*?)\.[^\.]*\.[^\.]*$/is';
                    $aLayouts = \glob($sAddonPath.'/presets/*.inc.php');
                    foreach($aLayouts as $sLayoutFilename){
                        $sLayout = preg_replace($sPattern,'$1',$sLayoutFilename);
                        $sSql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_news_layouts` '
                              . 'WHERE `layout` = \''.$sLayout.'\' ';
                        if (!$oReg->Db->get_one($sSql) && is_readable($sLayoutFilename)){
                            require ($sLayoutFilename);
                            $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_layouts` SET '.PHP_EOL
                                  . '`layout` = \''.$oReg->Db->escapeString($sLayout).'\', '.PHP_EOL
                                  . '`header`=\''.$oReg->Db->escapeString($header).'\', '.PHP_EOL
                                  . '`post_loop`=\''.$oReg->Db->escapeString($post_loop).'\', '.PHP_EOL
                                  . '`footer`=\''.$oReg->Db->escapeString($footer).'\', '.PHP_EOL
                                  . '`post_header`=\''.$oReg->Db->escapeString($post_header).'\', '.PHP_EOL
                                  . '`post_footer`=\''.$oReg->Db->escapeString($post_footer).'\', '.PHP_EOL
                                  . '`comments_header`=\''.$oReg->Db->escapeString($comments_header).'\', '.PHP_EOL
                                  . '`comments_loop`=\''.$oReg->Db->escapeString($comments_loop).'\', '.PHP_EOL
                                  . '`comments_footer`=\''.$oReg->Db->escapeString($comments_footer).'\', '.PHP_EOL
                                  . '`comments_page`=\''.$oReg->Db->escapeString($comments_page).'\' '.PHP_EOL;
                            if (!$oReg->Db->query($sql)){
                              $msg[] = $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                            }
                        }
                    } // end foreach import layout files to db
                // prepare layout in news_setting to copy to layout table
                    $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_news_settings` SET '.PHP_EOL
                          . '`layout`= CONCAT(`layout`,`section_id`) '.PHP_EOL
                          . 'WHERE `layout` != \'\' '.PHP_EOL
                          .   'AND `layout` REGEXP \'[a-zA-Z]+$\' '.PHP_EOL;
                    if (!$oReg->Db->query($sql)){
                      $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                    } else {
                        if ($oRes = $oReg->Db->query('SELECT `layout` FROM `'.TABLE_PREFIX.'mod_news_settings` WHERE `layout` != \'\' ')){
                            while (!is_null($aRow = $oRes->fetchRow(MYSQLI_ASSOC))){
                                $sNewName = \addon\news\NewsLib::getUniqueName($oReg->Db, 'layout', $aRow['layout']);
                                $sql  = 'INSERT INTO `'.TABLE_PREFIX.'mod_news_layouts` '.PHP_EOL
                                      .        'SELECT '.PHP_EOL
                                      .        'NULL,'.PHP_EOL
                                      .        '\''.$oReg->Db->escapeString($sNewName).'\','.PHP_EOL
                                      .        '`header`,`post_loop`,`footer`,`post_header`,`post_footer`'.PHP_EOL
                                      .        ',`comments_header`,`comments_loop`,`comments_footer`,`comments_page`'.PHP_EOL
                                     . 'FROM `'.TABLE_PREFIX.'mod_news_settings` '.PHP_EOL
                                         .     'WHERE `layout`=\''.$oReg->Db->escapeString($aRow['layout']).'\''.PHP_EOL;
                                if (!$oReg->Db->query($sql)){
                                  $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                                }
                            } // end while
                        }
                    }
//echo nl2br(sprintf("%d insert presets and remove settings layouts to db table \n",__LINE__));
            }
             else {
                $msg[] = sprintf('%s already exists',$sTableName);
                $aDefaultLayouts = ['default_layout','div_layout','div_new_layout'];
                $sPattern = '/^.*?([^\/]*?)\.[^\.]*\.[^\.]*$/is';
                $aLayouts = \glob($sAddonPath.'/presets/*.inc.php');
                foreach($aLayouts as $sLayoutFilename){
                    $sLayout = preg_replace($sPattern,'$1',$sLayoutFilename);
                    if (is_readable($sLayoutFilename)){
                        require ($sLayoutFilename);
                        $sSql = 'UPDATE `'.TABLE_PREFIX.'mod_news_layouts` SET '
                              . '`layout` = \''.$oReg->Db->escapeString($sLayout).'\', '.PHP_EOL
                              . '`header`=\''.$oReg->Db->escapeString($header).'\', '.PHP_EOL
                              . '`post_loop`=\''.$oReg->Db->escapeString($post_loop).'\', '.PHP_EOL
                              . '`footer`=\''.$oReg->Db->escapeString($footer).'\', '.PHP_EOL
                              . '`post_header`=\''.$oReg->Db->escapeString($post_header).'\', '.PHP_EOL
                              . '`post_footer`=\''.$oReg->Db->escapeString($post_footer).'\', '.PHP_EOL
                              . '`comments_header`=\''.$oReg->Db->escapeString($comments_header).'\', '.PHP_EOL
                              . '`comments_loop`=\''.$oReg->Db->escapeString($comments_loop).'\', '.PHP_EOL
                              . '`comments_footer`=\''.$oReg->Db->escapeString($comments_footer).'\', '.PHP_EOL
                              . '`comments_page`=\''.$oReg->Db->escapeString($comments_page).'\' '.PHP_EOL
                              . 'WHERE `layout` = \''.$oReg->Db->escapeString($sLayout).'\' ';
                        if (!$oReg->Db->query($sSql)){
                          $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                        }
                    }
                }//foreach
//echo nl2br(sprintf("%d update presets layouts to db table \n",__LINE__));
            }
// set default layout and id to settings table
            $sSql = 'UPDATE '.TABLE_PREFIX.'mod_news_settings SET '
                  . '`layout` = \''.$oReg->Db->escapeString('default_layout').'\', '
                  . '`layout_id` = 1 '
                  . 'WHERE `layout` = \'\' '
                  .   'AND `layout_id` = 0 ';
            if (!$oReg->Db->query($sSql)){
                $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
            } else {
                $msg[] = (sprintf("[%05d] update sets default layouts to settings table \n",__LINE__));
                $sInstallStruct = $sAddonPath.'/delete-struct.sql.php';
//echo nl2br(sprintf("[%05d] load delete-struct %s \n",__LINE__,$sInstallStruct));
                if (!$oReg->Db->SqlImport($sInstallStruct, TABLE_PREFIX, 'upgrade' )){
                    $msg[] = sprintf('[%05d] %s',__LINE__,$oReg->Db->get_error());
                }
            }

// ************************************************
    // rebuild all access-files
            $count = 0;
            $backSteps = \preg_replace('@^'.\preg_quote(WB_PATH).'@', '', $sPostsPath);
            $backSteps = \str_repeat( '../', \substr_count($backSteps, '/'));
            $sql  = 'SELECT `page_id`,`post_id`,`section_id`,`link`, `title` ';
            $sql .= 'FROM `'.TABLE_PREFIX.'mod_news_posts`';
            $sql .= 'WHERE `link` != \'\'';
            if( ($oPosts = $oReg->Db->query($sql)) )
            {
                while(($aPost = $oPosts->fetchRow(MYSQLI_ASSOC)) )
                {
                      if ($aPost['page_id']){
                          $sNewLink = $aPost['link'].PAGE_EXTENSION;
                          $oAF = new \AccessFile($sAccessFileRootPath, $sNewLink, $aPost['page_id']);
                          $oAF->addVar('section_id', $aPost['section_id'], \AccessFile::VAR_INT);
                          $oAF->addVar('post_id', $aPost['post_id'], \AccessFile::VAR_INT);
                          $oAF->addVar('post_section', $aPost['section_id'], \AccessFile::VAR_INT);
                          $oAF->write();
                          unset($oAF);
                          $count++;
                      }
                } // end post while
            }
            $msg[] = sprintf('created %d new accessfiles.',$count);
/*--------------------------------------------------------------------------------------------------*/
        }
/*------------------------------------------------------------------------------*/
            $aFilesToDelete = [
                    '/_delete.php',
                    '/add_Post.old',
                    '/save_post.php',
                    '/view.php.new' ,
                    '/view.old',
                    '/oneforall/',
                ];
            PreCheck::deleteFiles($sAddonPath,$aFilesToDelete);

        return $msg;
    }
}

// ------------------------------------
    $callingScript = $_SERVER["SCRIPT_NAME"];
    $globalStarted = preg_match('/upgrade\-script\.php$/', $callingScript);
    $oRegister = \bin\WbAdaptor::getInstance();

    $aMsg = mod_news_Upgrade($oRegister);
//    if (count($aMsg)) {print nl2br(implode("\n", $aMsg));}
    if (!$globalStarted){
        foreach ($aMsg as $msg){
          if (empty(($msg))){continue;}
          print $msg.'<br />';
        }
    }
/* **** END UPGRADE ********************************************************* */