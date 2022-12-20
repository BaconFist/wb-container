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
 * Description of Lingual
 *
 * @package      Addon package
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @license      GNU General Public License 2.0
 * @version      1.0.0-dev.0
 * @revision     $Id:  $
 * @since        File available since 02.12.2019
 * @deprecated   no
 * @description  xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace addon\news;

class NewsLib {



// -----------------------------------------------------------------------------
/** prevent class from public instancing and get an object to hold extensions */
    private function  __construct() {
    }
// -----------------------------------------------------------------------------
/** prevent from cloning existing instance */
    private function __clone() {}
// -----------------------------------------------------------------------------

    public static function getInstance()
    {
        static $oInstance = null;
        $sClass = __CLASS__;
        return $oInstance ?: $oInstance = new $sClass;
    }
    public static function getUniqueName($oDb, $sField, $sValue)
    {
        $sRetval = $sValue;
        $sBaseName = \preg_replace('/^(.*?)(\_[0-9]+)?$/', '$1', $sValue);
        $sql = 'SELECT `'.$sField.'` FROM `'.TABLE_PREFIX.'mod_news_layouts` '
             . 'WHERE `'.$sField.'` RLIKE \'^'.$sBaseName.'(\_[0-9]+)?$\' '
             . 'ORDER BY `'.$sField.'` DESC';
        if (($sMaxName = $oDb->get_one($sql))) {
            $iCount = \intval(\preg_replace('/^'.$sBaseName.'\_([0-9]+)$/', '$1', $sMaxName));
            $sRetval = $sBaseName.\sprintf('_%03d', ++$iCount);
        }
        return $sRetval;
    }

}// end of class
