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
 * Helpers
 *
 * @category     name
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 21.11.2020
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace App\Utils;

// use source;

class Helpers
{

/**
 * make sure that an integer value is between min and max
 * or set to default, if value is out of range
 * @param int $iValue
 * @param int $iMin
 * @param int $iMax
 * @param int|null $iDefault  null = result inside the range,
 *                            int  = result default if out of range
 *                                   default can not be out of range
 * @return int
 */
    public static function sanitizeMinMax(int $iValue, int $iMin, int $iMax, $iDefault = null): int
    {
        if (\is_null($iDefault)) {
            $iRetval = ($iValue < $iMin ? $iMin : ($iValue > $iMax ? $iMax : $iValue));
        } else {
            $iDefault = ($iDefault < $iMin ? $iMin : ($iDefault > $iMax ? $iMax : $iDefault));
            $iRetval = (($iValue > $iMax || $iValue < $iMin) ? $iDefault : $iValue);
        }
        return $iRetval;
    }


}

