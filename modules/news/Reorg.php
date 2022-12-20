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
 * Reorg.php
 *
 * @category     Addon
 * @package      news_package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2070 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/modules/news/Reorg.php $
 * @lastmodified $Date: 2014-01-03 02:21:42 +0100 (Fr, 03 Jan 2014) $
 * @since        File available since 15.10.2013
 * @description  reorganisize all accessfiles of the addon 'news'
 */


class m_news_Reorg extends ModuleReorgAbstract{

/** root directory for accessfiles */
    protected $sAccessFilesRoot = '';
/** sub directory for accessfiles
 * @description  This is needed to correct db::x_mod_news_posts::link entries of former versions<br />
 *               It can be removed after <b>all</b> modules accessing news are modified to access
 *               without a hardcoded subdirectory name.
 */
    protected $sAccessFilesSubdir = 'posts/';

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
    // reset report
        $this->aReport = array( 'FilesDeleted'=>0,
                                'FilesCreated'=>0,
                                'Success'=>array(),
                                'Failed'=>array()
                              );
    // build AccessFilesRoot
        $this->sAccessFilesRoot = $this->oReg->AppPath.$this->oReg->PagesDir.$this->sAccessFilesSubdir;
    // delete old accessfiles
        $this->deleteAll();
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
                if(AccessFileHelper::isAccessFile($sItem))
                {
                // delete accessfiles only
                    if(is_writable($sItem) && @unlink($sItem))
                    {
                    // if file is successful deleted
                        if($this->bDetailedLog)
                        {
                            $this->aReport['Success'][] = 'File successful removed : '.str_replace($this->oReg->AppPath, '', $sItem);
                        }
                    // increment successful counter
                        $this->aReport['FilesDeleted']++;
                    }else
                    {
                    // if failed
                        $this->aReport['Failed'][] = 'Delete file failed : '.str_replace($this->oReg->AppPath, '', $sItem);
                    }
                } // endif
            } // endforeach
        }else
        {
            $this->aReport['Failed'][] = 'Directory scan failed : '.str_replace($this->oReg->AppPath, '', $this->sAccessFilesRoot);
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
        $sql = 'SELECT `page_id`, `post_id`, `section_id`, `link`, `title` '
             . 'FROM `'.$this->oDb->TablePrefix.'mod_news_posts` '
             . 'WHERE `link`!=\'\'';
        if(($oPosts = $this->oDb->query($sql)))
        {
            while(($aPost = $oPosts->fetchRow(MYSQLI_ASSOC)))
            {
            // sanitize link if there is an old value in database from former versions
                $aPost['link'] = preg_replace( '/^'.preg_quote($this->sAccessFilesSubdir, '/').'/',
                                               '',
                                               trim( str_replace('\\', '/', $aPost['link']), '/')
                                             );

            // compose name of accessfile
                $sAccFileName = $this->sAccessFilesRoot.$aPost['link'].$this->oReg->PageExtension;

                try
                {
                // create new object
                    $oAccFile = new AccessFile($this->sAccessFilesRoot, $aPost['link'], $aPost['page_id']);
                    $oAccFile->addVar('section_id',   $aPost['section_id'], AccessFile::VAR_INT);
                    $oAccFile->addVar('post_id',      $aPost['post_id'],    AccessFile::VAR_INT);
                    $oAccFile->addVar('post_section', $aPost['section_id'], AccessFile::VAR_INT);
                    $oAccFile->write();
                // destroy object if its file is written
                    unset($oAccFile);
                    if($this->bDetailedLog)
                    {
                        $this->aReport['Success'][] = 'File successful created : '.str_replace($this->oReg->AppPath, '', $sAccFileName);
                    }
                // increment successful counter
                    $this->aReport['FilesCreated']++;
                }catch(AccessFileException $e)
                {
                // if failed
                    $this->aReport['Failed'][] = ($this->bDetailedLog ? $e : $e->getMessage());
                }
            } // endwhile
        } // endif
    } // end of function rebuildAll()

} // end of class m_news_Reorg

