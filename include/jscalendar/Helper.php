<?php

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
 * Description of Helper
 *
 * @package      DynarchJsCalendar
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: Helper.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @since        File available since 13.09.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace vendor\jscalendar;

// use

/**
 * short description of class
 */
class Helper
{
/**
 * transform date/time string into Unix timestamp
 * @param string $sTimeString a date/time string using one of the declared patterns
 * @param string (optional) $sFormat (not defined yet)
 * @return int  unix timestamp
 */
    public static function getTimestampFromString($sTimeString, $sFormat = '')
    {
        $iTimestamp = $iPatternFound = 0;
        if (trim($sTimeString)) { // procceed only if string not empty
            $aFormats = [
                // "dd.mm.[yy]yy[ [h]h:[m]m:[s]s]"
                ['/^\s*?(\d{1,2})\.(\d{1,2})\.(\d{4}|\d{2})(\s*.*)?\s*?$/si', '\3-\2-\1\4'],
                // "mm\/dd\/[yy]yy[ [h]h:[m]m:[s]s]"
                ['/^\s*?(\d{1,2})\/(\d{1,2})\/(\d{4}|\d{2})(\s*.*)?\s*?$/si', '\3-\1-\2\4'],
            ];
            foreach ($aFormats as $aPattern) {
                $sTmpTimeString = preg_replace($aPattern[0], $aPattern[1], $sTimeString, -1, $iPatternFound);
                if ($iPatternFound) {
                    $iTimestamp = strtotime($sTmpTimeString);
                    break;
                }
            }
        }
        return $iTimestamp;
    }
}
