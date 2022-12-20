<?php
/*
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
 */
/**
 * @category     template
 * @package      template_DefaultTheme
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      http://www.gnu.org/licenses/gpl.html
 * @revision     $Revision: 344 $
 * @lastmodified $Date: 2019-05-06 20:59:56 +0200 (Mo, 06. Mai 2019) $
 * @since        File available since 25.02.2017
 * @deprecated   no / since 0000/00/00
 * @description
 */
// -----------------------------------------------------------------------------

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use addon\WBLingual\Lingual;

/**
 * delete directory tree
 * @param string $sBasedir  the absolute path including a trailing slash
 * @param bool   $bRemoveBasedir  (default) true = remove base directory || false = preserve it
 * @throws Exception
 * @descripion deletes a tree recursively from given basedir
 */
    function rebuildAccessFiles_delTree($sBasedir, $bRemoveBasedir = true)
    {
        if (\is_dir($sBasedir)){
            $aSubDirs = \glob($sBasedir.'*', \GLOB_MARK|\GLOB_ONLYDIR);
            foreach ($aSubDirs as $sSubDir) {
                rebuildAccessFiles_delTree($sSubDir, true);
            }
            \array_map(
                function ($sFile) {
                    if (\is_file($sFile) && !\unlink($sFile)) { throw new \Exception('delTree: Unable to delete file'); }
                },
                \glob($sBasedir.'*')
            );
        }
        if ($bRemoveBasedir) {
            if (!\rmdir($sBasedir)) { throw new \Exception(\sprintf('delTree: Unable to remove directory %s',\basename($sBasedir))); }
        }

    }
// -----------------------------------------------------------------------------
/**
 * check if file is an access file
 * @param string $sFileName
 * @return bool
 * @throws Exception
 */
    function isAccessFile($sFileName)
    {
        $bRetval = false;
        if (\file_exists($sFileName)) {
            if (!\is_readable($sFileName)) { throw new \Exception('invalid filename ['.\basename($sFileName).']'); }
            if (($sFile = \file_get_contents($sFileName)) !== false) {
            // test content of this file
                $sPattern = '/^\s*?<\?php.*?\$i?page_?id\s*=\s*[0-9]+;.*?(?:require|include)'
                          . '(?:_once)?\s*\(\s*\'.*?index\.php\'\s?\);/siU';
                $bRetval = (bool) \preg_match($sPattern, $sFile);
                unset($sFile);
            }
        }
        return $bRetval;
    }
// -----------------------------------------------------------------------------
// direct access code
// -----------------------------------------------------------------------------
    if (!\defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}
    $sMessage = 'Rebuild Response!'."\n";
    $sMessage = '';
    $aJsonRespond = [];
    $aJsonRespond['message'] = 'Error rebuildAccessFiles';
    $aJsonRespond['success'] = false;
    try {
    // check autentification
        $admin = new admin('Pages', 'pages_settings',false);
        if (!$admin->is_authenticated()  || !$admin->ami_group_member('1')) {
            throw new \Exception(\sprintf('Access denied'));
        }
//        if (!\is_callable('make_dir')) {require (WB_PATH.'/framework/functions.php');}
        if (!\defined('VERSION')) {require (ADMIN_PATH.'/interface/version.php');}
// Work-out all needed path and filenames
        $sPagesPath = WB_PATH.PAGES_DIRECTORY;
        $sAccessFileRootPath = \rtrim($sPagesPath,'/').'/';
        if (!is_dir($sAccessFileRootPath)){make_dir($sAccessFileRootPath);}
        $iPageFilesCreated = 0;
    // Find all active Level 0 pages and delete the files and possible related directories
        $sql = 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` WHERE `level`= 0';
        if (($oPages = $database->query($sql))) {
            while (!is_null($aPage = $oPages->fetchRow(MYSQLI_ASSOC))) {
            // santize path
                $sAccessDir = \str_replace('\\', '/', WB_PATH.PAGES_DIRECTORY.$aPage['link']);
                $sAccessFile = $sAccessDir.PAGE_EXTENSION;
            // test if file is really an access file
                if (isAccessFile($sAccessFile)) {
                // delete the subdir and it's content if exists
                    if (\file_exists($sAccessDir.'/')) { rebuildAccessFiles_delTree($sAccessDir.'/'); }
                // delete the current accessfile
                    if (\is_file($sAccessFile) && !\unlink($sAccessFile)) { throw new \Exception(\sprintf('Unable to delete file %s',\basename($sAccessFile))); }
                }
            }
        } else {
            $sMessage .= \sprintf('Database Error::%s',$oDb->get_error());
        }
    // get all pages from database
        $sql = 'SELECT `page_id`, `link`, `level` FROM `'.TABLE_PREFIX.'pages` '
             . 'ORDER BY `link`';
        if (!($oPages = $database->query($sql))) { throw new \Exception(\sprintf('Database access failed %s', $database->get_error())); }
        while (!is_null($aPage = $oPages->fetchRow(MYSQLI_ASSOC))) {
                  $sNewLink = $aPage['link'].PAGE_EXTENSION;
                  $oAF = new \AccessFile($sAccessFileRootPath, $sNewLink, $aPage['page_id']);
                  $oAF->addVar('page_id', $aPage['page_id'], \AccessFile::VAR_INT);
                  if ($oAF->write()){$iPageFilesCreated++;}
                  unset($oAF);
        }// end while
        $Result = false;
//        require(WB_PATH.'/modules/SimpleRegister.php');
        $oPageLang = new Lingual();
        $Result = $oPageLang->updateDefaultPagesCode();
        $sMessage .= sprintf('Rebuild %d pages access files',$iPageFilesCreated)."\n";
        $sMessage .= ($Result ? sprintf('Update pages lingual codes')."\n" : '');
        $aJsonRespond['success'] = true;

    // echo the json_respond to the ajax function
/* ------------------------------------------------------------------------------------------- */

/* --- begin: crawl all available page-addons for additional access files -------------------- */
    $iClassTotal = 0;
    $bExecute = true;
    $aReport  = [];
    $oDb = \database::getInstance();
    $sql = 'SELECT `directory` '.'FROM `'.$oDb->TablePrefix.'addons` '.'WHERE `type`=\'module\' AND `function`=\'page\' '.''.''; // AND NOT `directory` LIKE \'%\_%\'
    // note: this query will skip directories including an underscore!
    $aAllOptions = []; // common result list
    if (($oAddons = $oDb->query($sql)) && $bExecute == true) {
        while (!is_null($aAddon = $oAddons->fetchRow(MYSQLI_ASSOC))) {
            $sClass = '\\addon\\'.strtolower($aAddon['directory']).'\\WBReorg';
            if (\class_exists($sClass)) {
//                \trigger_error(sprintf('Testing calling %s ',$sClass), E_USER_NOTICE);
                $oAddon = new $sClass();
                if ($oAddon instanceof \ModuleReorgAbstract) {
                     $oAddon->execute();
                     $aReport =  $oAddon->getReport();
                     $sMessage .= 'Rebuild '.$aReport['FilesCreated'].' '.$aAddon['directory'].' access files'."\n";
//                     $aJsonRespond['message'] .= 'Rebuild '.$sReport['FilesCreated'].' '.$aAddon['directory'].' access files'."\n";
                }
                unset($oAddon);
                ++$iClassTotal;
            }
        $aJsonRespond['success'] = true;
        } // end while
    } else {
        $sMessage .= \sprintf('Database Error::%s',$oDb->get_error());
    }
/* --------------------------------------------------------------------------------- */
    } catch (\Exception $e) {
        $sMessage .= 'Rebuild failed:: '.$e->getMessage().'!'."\n";
        $aJsonRespond['success'] = false;
    }

    $aJsonRespond['message'] = PreCheck::xnl2br($sMessage);
    exit(\json_encode($aJsonRespond));
