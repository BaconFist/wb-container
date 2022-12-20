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
 * Description of Randomizer
 *
 * @package      Kipanga\Security
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      This program is subject to proprietary license terms.
 * @version      0.0.1 $Rev: 69 $
 * @revision     $Id: Randomizer.php 69 2020-11-22 14:38:39Z Manuela $
 * @since        File available since 07.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  can generate random strings of defined length and using several char bases
 */

declare(strict_types=1);
//declare(encoding='UTF-8');

namespace src\Security;

// use *

class Randomizer
{
// ---------------------------------------------------------------------------------------
// Defaults & Constants ------------------------------------------------------------------
    public const CURRENT        = 0;
    public const ALNUM_64       = 1;
    public const ALNUM_62       = 2;
    public const ALPHA_52       = 3;
    public const UPPER_ALNUM_36 = 4;
    public const LOWER_ALNUM_36 = 5;
    public const UPPER_ALPHA_26 = 6;
    public const LOWER_ALPHA_26 = 7;
    public const UPPER_HEX      = 8;
    public const LOWER_HEX      = 9;
/** several default char bases (use URL secure 7-bit ASCII chars only!)*/
    private $aCharBases = [
        self::CURRENT => '',
        self::ALNUM_64 => '0123456789-abcdefghijklmnopqrstuvwxyz_ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::ALNUM_62 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::ALPHA_52 => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::UPPER_ALNUM_36 => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LOWER_ALNUM_36 => '0123456789abcdefghijklmnopqrstuvwxyz',
        self::UPPER_ALPHA_26 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LOWER_ALPHA_26 => 'abcdefghijklmnopqrstuvwxyz',
        self::UPPER_HEX      => '0123456789ABCDEF',
        self::LOWER_HEX      => '0123456789abcdef',
    ];
// ---------------------------------------------------------------------------------------
// internal properties -------------------------------------------------------------------
/** length of the char base */
    private $iBaseLength    = 0;
/** max length of a key on given EncodeBase */
    private $iMaxPartLength = 3;
// ---------------------------------------------------------------------------------------
/**
 * Constructor set encoding type
 * @param int $iEncodeBase
 */
    public function __construct(int $iEncodeBase = self::ALNUM_64)
    {
        $iBaseIndex = $this->sanitizeMinMax($iEncodeBase, 1, \sizeof($this->aCharBases)-1);
        $this->aCharBases[0]  = $this->aCharBases[$iBaseIndex];
        $this->iBaseLength    = \strlen($this->aCharBases[0]);
        $this->iMaxPartLength = $this->getMaxPartLength();
    }
// ---------------------------------------------------------------------------------------
/**
 * Generates a random key
 * @param int $iKeyLength
 * @return string
 * @description  it's possible to create strings with 4 up to 16k characters in length.
 */
    public function getKeyString(int $iKeyLength): string
    {
        $iLength = $iKeyLength = $this->sanitizeMinMax($iKeyLength, 4, (2**14)-1);
        $sRetval = '';
        while ($iLength > 0) {
            $iCount   = $iLength >= $this->iMaxPartLength ? $this->iMaxPartLength : $iLength;
            $sRetval .= $this->encodeInteger($this->getRandomInt($iCount));
            $iLength -= $iCount;
        }
        return \substr($sRetval, 0, $iKeyLength);
    }
// ---------------------------------------------------------------------------------------
/**
 * create a random hex string of $iNrOfBytes bytes
 * @param int $iNrOfBytes (min 2 bytes max 512 bytes)
 * @return string
 */
    public function getHexBytes(int $iNrOfBytes, bool $bUpperCase = false): string
    {
        $iLength = $this->sanitizeMinMax($iNrOfBytes, 2, 512);
        $sRetval = \bin2hex(\random_bytes($iLength));
        return $bUpperCase ? \strtoupper($sRetval) : \strtolower($sRetval);
    }
// ---------------------------------------------------------------------------------------
/**
 * create a random hex string of $iLength bytes
 * @param int $iKeyLength (min 4 digits max 1024 digits)
 * @return string
 */
    public function getHexString(int $iKeyLength, bool $bUpperCase = false): string
    {
        $iLength = $this->sanitizeMinMax($iKeyLength, 4, 1024);
        $sRetval = \bin2hex(\random_bytes((($iLength & 1) ? $iLength + 1 : $iLength) / 2));
        $sRetval = \substr($sRetval, 0, $iLength);
        return ($bUpperCase ? \strtoupper($sRetval) : \strtolower($sRetval));
    }
// ---------------------------------------------------------------------------------------
/**
 * Get the character base for encoding by constants (CURRENT means the active charbase)
 * @param int $iEncodeBase
 * @return string
 */
    public function getEncodeBase(int $iEncodeBase = self::CURRENT): string
    {
        $iBaseIndex = $this->sanitizeMinMax($iEncodeBase, 0, \sizeof($this->aCharBases)-1);
        return $this->aCharBases[$iBaseIndex];
    }
/* ***************************************************************************************
 * *** from here private methods only                                                *** *
 * ************************************************************************************ */
/**
 * create a random integer with $iLength digits
 * @param int $iLength
 * @return int
 */
    private function getRandomInt(int $iLength = 6): int
    {
        // generate random token number
        $iNumber = \random_int($this->iToBaseLen**($iLength-1), ($this->iToBaseLen**$iLength)-1);
        return $iNumber;
    }
// ---------------------------------------------------------------------------------------
/**
 * encode an integer into TokenEncodeBase
 * @param int $iNumber  an integer (max 2^63−1)
 * @return string
 */
    private function encodeInteger(int $iNumber): string
    {
        $sRetval = '';
        while ($iNumber != 0) {
            $sRetval = $this->aCharBases[0][($iNumber % $this->iToBaseLen)].$sRetval;
            $iNumber = \intdiv($iNumber, $this->iToBaseLen);
        }
        return $sRetval;
    }
// ---------------------------------------------------------------------------------------
/**
 * calculate max string length depending from EncodeBase and OS (32/64 bit)
 * @return int
 */
    private function getMaxPartLength(): int
    {
        $iMaxInt = \PHP_INT_MAX -1;
        $iMaxPartLength = 0;
        while ($iMaxInt >= $this->iBaseLength) {
            $iMaxPartLength++;
            $iMaxInt = \intdiv($iMaxInt, $this->iBaseLength);
        }
        return $iMaxPartLength;
    }
// ---------------------------------------------------------------------------------------
/**
 * make sure that an integer value is between min and max
 * @param int $iValue
 * @param int $iMin
 * @param int $iMax
 * @return int
 */
    private function sanitizeMinMax(int $iValue, int $iMin, int $iMax): int
    {
        return ($iValue < $iMin ? $iMin : ($iValue > $iMax ? $iMax : $iValue));
    }
} // end of class Kipanga\Security\Randomizer
