<?php

/*
 * Copyright (C) 2019 Manuela von der Decken <manuela@isteam.de>
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
 * WbGif
 * this class provides some helper function to handle gif images
 *
 * @category     Media
 * @package      Media_Helpers
 * @copyright    Manuela von der Decken <manuela@isteam.de>
 * @author       Manuela von der Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 07.10.2019
 * @deprecated   no / since 0000/00/00
*/

namespace bin\media;

class WbGif
{
/**
 * Test if the gif contains more then one image frames
 * WbGif::isAnimatet($sFilename)
 * @param string $sFilename
 * @return bool
 * @throws \RuntimeException
 */
    public static function isAnimatet(string $sFilename): bool
    {
        $iCount = 0;
        if (is_file($sFilename)){
            if (!\is_readable($sFilename)) {
                throw new \RuntimeException('no valid filename given');
            }
            if (!($fh = \fopen($sFilename, 'rb'))) {
                throw new \RuntimeException('unable to read gif file');
            }
            $sChunk = '';
            $aMatches = [];
            while(!\feof($fh) && $iCount < 2) {
                $sChunk = ($sChunk ? \substr($sChunk, -20) : "") . \fread($fh, 1024 * 100);
                // search for frameheaders
                $iCount += \preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $sChunk, $aMatches);
            }
            \fclose($fh);
        }
        return ($iCount > 1); // returns true if more then one frameheader found
    }

}//end of class
