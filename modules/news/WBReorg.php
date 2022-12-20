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
 */

/**
 * WBReorg.php
 *
 * @category     Addon
 * @package      addon_package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision:  $
 * @link         $HeadURL: $
 * @lastmodified $Date:  $
 * @since        File available since 15.10.2013
 * @description  reorganisize all accessfiles of the addon 'news'
 */

namespace addon\news;
//                \trigger_error(sprintf('Inside calling %s ',$sFilePath), E_USER_NOTICE);

class WBReorg extends \ModuleReorgAbstract{
/**
 * name of the needed table
 * @description do NOT use the TablePrefix in this name!
 */
    const TABLE_NAME           = 'mod_news_posts';

/** sub directory for accessfiles
 * @description  This is needed to correct db::x_mod_table::link entries of former versions<br />
 *               let it empty without a hardcoded subdirectory name like our news addon with a
 *               hardcodet subdirectory. The root directory always will be the pages_directory.
 *               with trailing slash if not empty
 */
    const ACCESSFILES_SUBDIR   = 'posts/';


/** root directory for accessfiles */
    protected $sAccessFilesRoot = '';

/*
    protected function createPagesDir($sAccessFilesDir){}
*/
    protected function createPagesDir($sAccessFilesDir){
        $bRetval = \file_exists($sAccessFilesDir);
        if (!$bRetval){
            $iOldUmask = \umask(0);
            $bRetval = \mkdir($sAccessFilesDir, $this->oReg->OctalDirMode,true);
            \umask($iOldUmask);
        }
//        if ($bRetval){\trigger_error(sprintf('[%d] Sanitize FileName %s',__LINE__, $sAccessFilesDir), E_USER_NOTICE);}
        return $bRetval;
    }

/**
 * init reorganisation, e.g. to read optional addon config or set some optional properties
 * @return void
 */
    public function init(){
        $this->aConfig['AddonRel'] = \str_replace($this->oReg->AppPath,'',\str_replace('\\','/',__DIR__));
        $this->aConfig['AddonName'] = \basename(__DIR__);
        $this->aConfig['AddonAbsPath'] = $this->oReg->AppPath.$this->AddonRel;
    }

/**
 * execute reorganisation
 * @return boolean
 */
    public function execute()
    {
/**
 * @description Structure of report array.<br />
 *              (int) number of 'FilesDeleted'<br />
 *              (int) number of 'FilesCreated'<br />
 *              (array) 'Success'<br />
 *              (array) 'Failed'
 */

        $this->init();

    // reset report
        $this->aReport = array( 'FilesDeleted'=>0,
                                'FilesCreated'=>0,
                                'Success'=>array(),
                                'Failed'=>array()
                              );

    // build AccessFilesRoot
        $this->sAccessFilesRoot = $this->oReg->AppPath.$this->oReg->PagesDir.self::ACCESSFILES_SUBDIR;
        // create rootfolder if not exists
        if (!empty($this->sAccessFilesRoot) && !file_exists($this->sAccessFilesRoot)){$this->createPagesDir($this->sAccessFilesRoot);}
    // delete old accessfiles only if no subdir
        if (!empty(self::ACCESSFILES_SUBDIR)){$this->deleteAll();}
    // recreate new accessfiles
        $this->rebuildAll();
    // return true if all is successful done
        return (sizeof($this->aReport['Failed']) == 0);
    }
/**
 * deleteAll
 * @throws AccessFileException
 * @description delete all accessfiles and its children in $sAccessFilesRoot
 */
    protected function deleteAll()
    {
    // scan start directory for access files
        $aMatches = glob($this->sAccessFilesRoot . '*'.$this->oReg->PageExtension);
        if(is_array($aMatches))
        {
            foreach($aMatches as $sItem)
            {
            // sanitize itempath
                $sItem = str_replace('\\', '/', $sItem);
                if(\bin\helpers\AccessFileHelper::isAccessFile($sItem))
                {
                // delete accessfiles only
                    if(\is_writable($sItem) && @\unlink($sItem))
                    {
                    // if file is successful deleted
                        if($this->bDetailedLog)
                        {
                            $this->aReport['Success'][] = 'File successful removed : '.\str_replace($this->oReg->AppPath, '', $sItem);
                        }
                    // increment successful counter
                        $this->aReport['FilesDeleted']++;
                    }else
                    {
                    // if failed
                        $this->aReport['Failed'][] = 'Delete file failed : '.\str_replace($this->oReg->AppPath, '', $sItem);
                    }
                } // endif
            } // endforeach
        }else
        {
            $this->aReport['Failed'][] = 'Directory scan failed : '.\str_replace($this->oReg->AppPath, '', $this->sAccessFilesRoot);
        }
    } // end of function deleteAll()
/**
 * rebuildAll
 * @return integer  number of successful deleted files
 * @throws AccessFileException
 * @description rebuild all accessfiles from database
 */
    protected function rebuildAll()
    {
        $sAddonName = \basename(__DIR__);
        $sql = $this->makeSql($sAddonName);
        if(($oPosts = $this->oDb->query($sql)))
        {
            while(($aRecord = $oPosts->fetchRow(MYSQLI_ASSOC)))
            {
            //  if link is stored without parent directories, so you have to add, otherwise empty
                $sAddonPath = '';
            // sanitize link if there is an old value in database from former versions
                $aRecord['link'] = \preg_replace( '/^'.\preg_quote(self::ACCESSFILES_SUBDIR, '/').'/',
                                   '',
                                   \trim(\str_replace('\\', '/', $sAddonPath.$aRecord['link']), '/')
                                   );

            // compose name of accessfile
                $sAccFileName = $this->sAccessFilesRoot.$aRecord['link'].$this->oReg->PageExtension;

                try
                {
                // create new object
                    if ($aRecord['page_id']){
                        $oAccFile = new \AccessFile($this->sAccessFilesRoot, $aRecord['link'], $aRecord['page_id']);
                        $oAccFile->addVar('section_id',   $aRecord['section_id'], \AccessFile::VAR_INT);
                        $oAccFile->addVar('post_id',      $aRecord['post_id'],    \AccessFile::VAR_INT);
                        $oAccFile->addVar('post_section', $aRecord['section_id'], \AccessFile::VAR_INT);
                        $oAccFile->write();
                    // destroy object if its file is written
                        unset($oAccFile);
                        if($this->bDetailedLog)
                        {
                            $this->aReport['Success'][] = 'Post File created successfully : '.\str_replace($this->oReg->AppPath, '', $sAccFileName);
                        }
                    // increment successful counter
                        $this->aReport['FilesCreated']++;
                    }
                }catch(\AccessFileException $e)
                {
                // if failed
                    $this->aReport['Failed'][] = ($this->bDetailedLog ? $e : $e->getMessage());
                }
            } // endwhile
        } // endif
    } // end of function rebuildAll()

    protected function makeSql($sAddonName='')
    {
//                \trigger_error(sprintf('Function rebuildAll calling %s ',$sAddonName), E_USER_NOTICE);
        return 'SELECT `page_id`, `post_id`, `section_id`, `link`, `title` '
             . 'FROM `'.$this->oDb->TablePrefix.self::TABLE_NAME.'` '
             . 'WHERE `link`!=\'\'';
    }
} // end of class m_news_Reorg

