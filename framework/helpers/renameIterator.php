<?php /*
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
 * Description of Unbenannt 1
 *
 * @package      core
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: $
 * @since        File available since 12.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');
namespace bin\helpers;
//use this

class renameIterator implements Iterator {
    protected $source = [], $dest = [], $currentPos = 0;
    public function __construct(array $source, array $dest) {
        $sKeys = array_keys($source);
        sort($sKeys);
        $dKeys = array_keys($dest);
        sort($dKeys);
        if ($sKeys === $dKeys) {
            $this->source = $source;
            $this->dest = $dest;
        } else {
            throw new LogicException('Inbound-Array check for Source and Destination fails due to key-incompability.');
        }
    }
    public function next() {
        $this->currentPos++;
    }
    public function rewind() {
        $this->currentPos = 0;
    }
    public function key() {
        return $this->currentPos;
    }
    public function valid() {
        return isset($this->source[$this->currentPos], $this->dest[$this->currentPos]);
    }
    public function current() {
        $source = $this->source[$this->currentPos];
        $dest = $this->dest[$this->currentPos];
        if (realpath($source) && realpath(pathinfo($dest, PATHINFO_DIRNAME)) && is_file($source) && is_writeable($dest)) {
            if (is_file($dest)) {
                unlink($dest);
                $deletion = ', target file has been deleted before.';
            }
            if (rename($source, $dest)) {
                return $source.' renamed to '.$dest.(isset($deletion)?$deletion:'');
            } else {
                return $source.' not renamed due to unknown system-based violation.';
            }
        } else {
            return $source.' not renamed due to access violation';
        }
    }
}
/*
$sourcefiles = []; // repräsentiert in dem fall die quellnamen
$targetfiles = []; // repräsentiert in dem fall die zielnamen
// existierende Ziel-Dateien werden gelöscht !
$jobIterator = new renameIterator($sourcefiles, $targetfiles);
foreach ($jobIterator as $stateMessage) {
    echo $stateMessage."\n";
}
*/
// end of file
