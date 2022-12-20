<?php

/*
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
 * SecureTokens.php
 *
 * @category      Core
 * @package       Core_Security
 * @copyright     Manuela v.d.Decken <manuela@isteam.de>
 * @author        Manuela v.d.Decken <manuela@isteam.de>
 * @license       http://www.gnu.org/licenses/gpl.html   GPL License
 * @version       0.2.1
 * @revision      $Id: SecureTokens.php 69 2020-11-22 14:38:39Z Manuela $
 * @since         File available since 12.09.2015
 * @description
 * This class adapts the src\Security\SecureTokens class for reasons of compatibility
 *
 * Settings for this class
 * TYPE    KONSTANTE                    REGISTY-VAR                       DEFAULTWERT
 * boolean SEC_TOKEN_FINGERPRINT        ($oReg->SecTokenFingerprint)      [default=true]
 * integer SEC_TOKEN_IPV4_NETMASK       ($oReg->SecTokenIpv4Netmask)      0-255 [default=24]
 * integer SEC_TOKEN_IPV6_PREFIX_LENGTH ($oReg->SecTokenIpv6PrefixLength) 0-128 [default=64]
 * integer SEC_TOKEN_LIFE_TIME          ($oReg->SecTokenLifeTime)         1800 | 2700 | 3600[default] | 7200
*/

namespace bin;

use src\Security\{CsfrTokens as CsfrTo, Randomizer};
use src\Interfaces\Registry;

class SecureTokens
{
/**
 * possible settings for TokenLifeTime in seconds
 * @description seconds for 30min / 45min / 60min / 75min / 90min / 105min / 120min
 */
/** minimum lifetime in seconds */
    const LIFETIME_MIN  = 1800; // 30min
/** maximum lifetime in seconds */
    const LIFETIME_MAX  = 7200; // 120min (2h)
/** stepwidth between min and max */
    const LIFETIME_STEP =  900; // 15min
/** lifetime in seconds to use in DEBUG mode if negative value is given (-1) */
    const DEBUG_LIFETIME = 300; // 5
/**  */
    const TOKEN_ENCODE_BASE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
/** Length of an IdKey */
    const ID_KEY_LENGTH     = 16;

    final protected function __construct() {}

    final public static function getInstance(
        Registry $oRegistry = null,
        Randomizer $oRandomizer = null
    ): CsfrTokens
    {
        return CsfrTo::getInstance($oRegistry, $oRandomizer);
    }

    private function __clone() {}

/**
 * destructor
 */
    final public function __destruct() {}

/**
 * returns the current FTAN
 * @return array  name and value of FTAN
 */
    final public static function getFTAN()
    {
        return CsfrTo::getToken();
    }

/**
 * checks received form-transactionnumbers against session-stored one
 * @return bool:    true if numbers matches against stored ones
 *
 * requirements: an active session must be available
 * this check will prevent from multiple sending a form. history.back() also will never work
 */
    final public static function checkFTAN(): bool
    {
        return CsfrTo::checkToken();
    }
/**
 * store value in session and returns an accesskey to it
 * @param mixed $mValue can be numeric, string or array
 * @return string
 */
    final public static function getIDKEY($mValue)
    {
        return CsfrTo::createIdKey($mValue);
    }
/**
 * Checks whether an IdKey is syntactically correct
 * @param  string|int $mValue
 * @return bool
 */
    final public static function isValidIdkey($mValue): bool
    {
        return CsfrTo::isValidIdkey($mValue);
    }
/*
 * search for key in session and returns the original value
 * @param string $sToken: name of the requested token
 * @return mixed: the original value (string, numeric, array) | null
 * @description: each IDKEY can be checked only once. Unused Keys stay in list until they expire
 */
    final public static function checkIDKEY($sToken)
    {
        return CsfrTo::decodeIdKey($sToken);
    }

/**
 * make a valid LifeTime value from given integer on the rules of class SecureTokens
 * @param integer  $iLifeTime
 * @return integer
 */
    final public static function sanitizeLifeTime($iLifeTime)
    {
        return CsfrTo::sanitizeTokenLifeTime($iLifeTime, false);
    }

/**
 * returns all TokenLifeTime values
 * @return array
 */
    final public static function getTokenLifeTime()
    {
        return CsfrTo::getTokenLifeTime();
    }
/**
 * get a simple Token without effect to the tokens table
 * @param int $iTokenLength 3-4  (default: 4)
 * @return string returns an independent Token between 3 and 4 Chars
 */
    final public static function getFreeToken($iTokenLength = 4)
    {
        return Csfr::getFreeToken($iTokenLength);
    }

/**
 * creates a runtime-unique token with n digits length
 * @staticvar array $aTokens
 * @param int $iDigits
 * @return string
 */
    final public static function getUniqueFreeToken($iDigits = 4)
    {
        return CsfrTo::getUniqueFreeToken($iDigits);
    }  // end of getUniqueToken

} // end of class SecureTokens
