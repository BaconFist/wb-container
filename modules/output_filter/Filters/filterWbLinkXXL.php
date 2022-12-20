<?php

/*
 * Copyright (C) 2020 Manuela v.d.Decken <manuela@isteam.de>
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
 * WbLinkXXL
 * designed as Extension for the WYSIWYG-Addon.
 * The use of the filter is only useful if a section contains 200 or
 * much more simple [wblink000]. It can be activated individually for
 * each section by placing the comment string
 * <!-- WbLinkXXL -->
 * at the very beginning of the content.
 *
 * @category     Addon
 * @package      OutputFilter
 * @subpackage   WbLinkXXL
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 25.06.2020
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

/*
 * replace all "[wblink{page_id}]" with real links
 * @param string  $content  the content with tags
 * @return string content with links
 */
    function doFilterWbLinkXXL($sContent)
    {
        $iChunkSize         = 250;

        $oReg = \bin\WbAdaptor::getInstance();
        $aSearchReplaceList = [];
        $aChunkList         = [];
        $aMatches           = [];
        $sPattern           = '/\[wblink([0-9]+)\]/si';
        if (\preg_match_all($sPattern, $sContent, $aMatches)) {
        // Find all simple [WbLinkxxx] in $sContent and collect their Page_ID.
        // In case of very long lists, the result is divided into several chunks.
            $aChunkList = \array_chunk($aMatches[1], $iChunkSize);
        }
        unset($aMatches); // never needed, free memory
        // Process all chunks in sequence
        foreach ($aChunkList as $aChunk) {
            // Use the Page_Id to get the corresponding 'links' from the
            // database and expand them to full qualified URLs.
            $sql = 'SELECT CONCAT(\'[wblink\',`page_id`,\']\') `id`, '
                 .          'CONCAT(\''.$oReg->AppUrl.$oReg->PagesDir.'\', '
                 .               'TRIM(BOTH \'/\' FROM `link`), '
                 .               '\''.$oReg->PageExtension.'\') `link` '
                 . 'FROM `'.$oReg->Db->TablePrefix.'pages` '
                 . 'WHERE `page_id` IN('.\implode(',',$aChunk).')';
            if (($oRecSet = $oReg->Db->query($sql))) {
                // get all results at once
                if (($aChunk = $oRecSet->fetchAll())) {
                    // convert the multidimensional result into a simple array
                    $aSearchReplaceList = \array_column($aChunk, 'link', 'id');
                    // now all matching WbLink-tags will be replaced by real fqurl links
                    $sContent = \str_replace(
                       \array_keys($aSearchReplaceList),
                       $aSearchReplaceList,
                       $sContent
                    );
                }
            }
        }
        return $sContent;
    }
