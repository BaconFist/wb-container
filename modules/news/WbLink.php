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
 * WbLink.php
 *
 * @category     Addons
 * @package      Addons_News
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @contribute   Dietmar Wöllbrink
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2070 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/modules/news/WbLink.php $
 * @lastmodified $Date: 2014-01-03 02:21:42 +0100 (Fr, 03. Jan 2014) $
 * @since        File available since 04.11.2013
 * @description  This class implements an interface for i.e. the wblink-outputfilter
 *
 * @inherited WbDatabase $oDb
 * @inherited WbAdaptor  $oReg
 *
 */
namespace addon\news;

class WbLink extends \WbLinkAbstract
{
/* *** BEGIN define the environment of your addon! ************************************ */
/* (this section is a MUST and it MUST have all 8 consts defined!!)                     */
/**
 * name of the needed table
 * @description do NOT use the TablePrefix in this name!
 */
    const TABLE_NAME           = 'mod_news_posts';

/**
 * name of the field with the PageId
 */
    const FIELDNAME_PAGE_ID    = 'page_id';

/**
 * name of the field with the SectionId
 */
    const FIELDNAME_SECTION_ID = 'section_id';

/**
 * name of the field with the ItemId
 */
    const FIELDNAME_ITEM_ID    = 'post_id';

/**
 * name of the field with the needed link
 */
    const FIELDNAME_LINK       = 'link';

/**
 * name of the field with the needed title
 */
    const FIELDNAME_TITLE      = 'title';

/**
 * name of the field with the timestamp
 * @description define an empty string if no 'timestamp'-field is available or it's not needed!
 */
    const FIELDNAME_TIMESTAMP  = 'published_when';

/** name of the field with the active-flag
 * @description define an empty string if no 'active'-field is available or it's not needed!
 */
    const FIELDNAME_ACTIVE     = 'active';

    const FIELDNAME_ORDER      = 'published_when';

    const ORDER_KEY            = 'DESC';


/* *** END define the environment of your addon! ************************************** */

/**
 * makeLinkFromTag
 * @param type $aReplacement
 * @return string
 * @description this method is used by the output filter
 */
    public function makeLinkFromTag(array $aReplacement)
    {
/* *** Define here the full path where your links are based on! *********************** */
        $sBaseDir = $this->oReg->AppPath.$this->oReg->PagesDir;
/* *** Do NOT change the following request! ******************************************* */
        $sBaseDir = \rtrim(\str_replace('\\', '/', $sBaseDir), '/').'/';
        return $this->_makeLinkFromTag($sBaseDir, $aReplacement);
    }
/**
 * generateOptionsList
 * @return &array definition of a SelectBox
 * @description build a mulitdimensional Array with complete Option definitions for use in a Select Box
 */
    public function generateOptionsList()
    {
        $aAddonItems = $this->_executeListGeneration();
        return $aAddonItems;
    }
} // end of class m_news_WbLink
