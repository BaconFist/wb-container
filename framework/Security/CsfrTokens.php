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
 * @package       Security
 * @copyright     Manuela v.d.Decken <manuela@isteam.de>
 * @author        Manuela v.d.Decken <manuela@isteam.de>
 * @license       http://www.gnu.org/licenses/gpl.html   GPL License
 * @version       0.1.2
 * @revision      $Id: CsfrTokens.php 69 2020-11-22 14:38:39Z Manuela $
 * @since         File available since 12.09.2015
 * @description
 * This class is a replacement for the former class SecureForm using the SecureTokensInterface
 *
 * Settings for this class
 * TYPE    KONSTANTE                    REGISTY-VAR                       DEFAULTWERT
 * boolean SEC_TOKEN_FINGERPRINT        ($oReg->SecTokenFingerprint)      [default=true]
 * integer SEC_TOKEN_IPV4_NETMASK       ($oReg->SecTokenIpv4Netmask)      0-255 [default=24]
 * integer SEC_TOKEN_IPV6_PREFIX_LENGTH ($oReg->SecTokenIpv6PrefixLength) 0-128 [default=64]
 * integer SEC_TOKEN_LIFE_TIME          ($oReg->SecTokenLifeTime)         1800 | 2700 | 3600[default] | 7200
*/

namespace src\Security;

use src\Interfaces\Registry;
use App\Utils\{IpAddress,Helpers};

class CsfrTokens
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
/** lifetime in seconds to use in DEBUG mode if negative value is given (< 0) */
    const DEBUG_LIFETIME = 300; // 5
/**  */
    const TOKEN_ENCODE_BASE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
/** Length of an IdKey */
    const ID_KEY_LENGTH     = 16;

    private $oRegistry   = null;
    private $oRequest    = null;
    private $oRandomizer = null;

/** array to hold all tokens from the session */
    private $aTokens = ['default' => ['value' => 0, 'expire' => 0, 'instance' => 0]];
/** the salt for this instance */
    private $sSalt             = '';
/** fingerprint of the current connection */
    private $sFingerprint      = '';
/** the FTAN token which is valid for this instance */
    private $aLastCreatedFtan  = null;
/** the time when tokens expired if they created in this instance */
    private $iExpireTime       = 0;
/** remove selected tokens only and update all others */
    private $bPreserveAllOtherTokens = false;
/** id of the current instance */
    private $sCurrentInstance  = null;
/** id of the instance to remove */
    private $sInstanceToDelete = null;
/** id of the instance to update expire time */
    private $sInstanceToUpdate = null;
/* --- settings for SecureTokens ------------------------------------------------------ */
/** use fingerprinting to encode */
    private $bUseFingerprint   = true;
/** maximum lifetime of a token in seconds */
    private $iTokenLifeTime    = 1800; // between LIFETIME_MIN and LIFETIME_MAX (default = 30min)
/** bit length of the IPv4 Netmask (0-32 // 0 = off  default = 24) */
    private $iNetmaskLengthV4  = 24;
/** bit length of the IPv6 Netmask (0-128 // 0 = off  default = 64) */
    private $iNetmaskLengthV6  = 64;

//    private static $oInstance = null;
/**
 * constructor
 * @param (void)
 */
    protected function __construct($oRegistry, $oRandomizer)
    {
    // initialize object
        $this->oRegistry        = $oRegistry;
        $this->oRequest         = $oRegistry->getRequester();
        $this->oRandomizer      = $oRandomizer;
    // load settings if available
        $this->getSettings();
    // generate salt for calculations in this instance
        $this->sSalt            = $oRandomizer->getHexString(16);
    // generate fingerprint for the current connection
        $this->sFingerprint     = $this->buildFingerprint();
    // define the expiretime for this instance
        $this->iExpireTime      = \time() + $this->iTokenLifeTime;
    // calculate the instance id for this instance
        $this->sCurrentInstance = $this->encodeHash(\md5($this->iExpireTime.$this->sSalt));
    // load array of tokens from session
        $this->loadTokens();
    // at first of all remove expired tokens
        $this->removeExpiredTokens();
    }

    public static function getInstance(
        Registry $oRegistry = null,
        Randomizer $oRandomizer = null
    ): CsfrTokens
    {
        static $oInstance  = null;
        if ($oInstance == null) {
            $sClass = __CLASS__;
            $oInstance = new $sClass($oRegistry, $oRandomizer);
        // check token if one is given
            self::checkToken();
        // create new request token
            self::getToken();
        }
        return $oInstance;
    }

    private function __clone() {}

/**
 * destructor
 */
    final public function __destruct()
    {
        foreach ($this->aTokens as $sKey => $aToken) {
            if ($aToken['instance'] == $this->sInstanceToUpdate) {
                $this->aTokens[$sKey]['instance'] = $this->sCurrentInstance;
                $this->aTokens[$sKey]['expire']   = $this->iExpireTime;
            } elseif ($aToken['instance'] == $this->sInstanceToDelete) {
                unset($this->aTokens[$sKey]);
            }
        }
        $this->saveTokens();
    }

/**
 * returns the current Token
 * @return array  name and value of Token
 */
    final public static function getToken($mSpecialToken = null): array
    {
        $oThis = self::getInstance();
        if (\is_null($mSpecialToken)) {
            if (\is_null($oThis->aLastCreatedFtan)) {
                $sFtan = \md5($oThis->sSalt);
                $oThis->aLastCreatedFtan = $oThis->addToken(
                    \substr($sFtan, \rand(0,15), 16),
                    \substr($sFtan, \rand(0,15), 16)
                );
            }
            $aFtan = $oThis->aTokens[$oThis->aLastCreatedFtan];
            $aFtan['name']  = $oThis->aLastCreatedFtan;
            $aFtan['value'] = $oThis->encodeHash(\md5($aFtan['value'].$oThis->sFingerprint));
            $aRetval = ['name' => $aFtan['name'], 'value' => $aFtan['value']];
        } else {
            $aRetval = $this->getSpecialToken($mSpecialToken);
        }
        return $aRetval;
    }

/**
 * checks received form-transactionnumbers against session-stored one
 * @return bool:    true if numbers matches against stored ones
 *
 * requirements: an active session must be available
 * this check will prevent from multiple sending a form. history.back() also will never work
 */
    final public static function checkToken()
    {
        static $bFtanOk = null;
        $bRetval = false;
        if (!\is_null($bFtanOk)) {
            $bRetval = $bFtanOk;
        } else {
            $oThis = self::getInstance();
            // get the POST/GET arguments
            $aServer  = \filter_input_array(\INPUT_SERVER);
            if (\sizeof($aServer) != \sizeof($_SERVER)) {$aServer = $_SERVER;}
            switch (\strtolower($aServer['REQUEST_METHOD'])):
                case 'post':
                    $aArguments = \filter_input_array(\INPUT_POST);
                    break;
                case 'get':
                    $aArguments = \filter_input_array(\INPUT_GET);
                    break;
                default:
                    break;
            endswitch;
            $aArguments = ($aArguments ?? []);
            // encode the value of all matching tokens
            $aMatchingTokens = \array_map(
                function ($aToken) use ($oThis) {
                    return $oThis->encodeHash(\md5($aToken['value'].$oThis->sFingerprint));
                },
                \array_intersect_key($oThis->aTokens, $aArguments)
            );
            // extract all matching arguments from $aArguments
            $aMatchingArguments = \array_intersect_key($aArguments, $oThis->aTokens);
            // get all tokens with matching values from match lists
            $aHits = \array_intersect($aMatchingTokens, $aMatchingArguments);
            foreach ($aHits as $sTokenName => $sValue) {
                $bRetval = true;
                $oThis->removeToken($sTokenName);
            }
            $bFtanOk = $bRetval;
        }
        return $bRetval;
    }
/**
 * store value in session and returns an accesskey to it
 * @param mixed $mValue can be numeric, string or array
 * @return string
 */
    final public static function createIdKey($mValue)
    {
        $oThis = self::getInstance();
        if (\is_array($mValue) == true) {
            // serialize value, if it's an array
            $mValue = \serialize($mValue);
        }
        // crypt value with salt into md5-hash and return a 16-digit block from random start position
        $sTokenName = $oThis->addToken(
            \substr(\md5($oThis->sSalt.(string)$mValue), \rand(0,(31-self::ID_KEY_LENGTH)), self::ID_KEY_LENGTH),
            $mValue
        );
        return $sTokenName;
    }
/**
 * Checks whether an IdKey is syntactically correct
 * @param  string|int $mValue
 * @return bool
 */
    final public static function isValidIdkey($mValue): bool
    {
        $sPattern = '/[[:xdigit:]]{'.self::ID_KEY_LENGTH.'}/';
        return ((bool) \preg_match($sPattern, (string) $mValue));
    }
/*
 * search for key in session and returns the original value
 * @param string $sToken: name of the requested token
 * @return mixed: the original value (string, numeric, array) | null
 * @description: each IDKEY can be checked only once. Unused Keys stay in list until they expire
 */
    final public static function decodeIdKey($sToken)
    {
        $oThis = self::getInstance();
        $mReturnValue = null; // set returnvalue to default
//        if (\is_string($sToken) && \preg_match('/^[[:xdigit:]]{16}$/', $sToken)) {
        if (\is_string($sToken) && self::isValidIdkey($sToken)) {
        // key must be a 16-digit hexvalue
            if (\array_key_exists($sToken, $oThis->aTokens)) {
            // check if key is stored in IDKEYs-list
                $mReturnValue = $oThis->aTokens[$sToken]['value']; // get stored value
                $oThis->removeToken($sToken);   // remove from list to prevent multiuse
                if (\preg_match('/.*(?<!\{).*(\d:\{.*;\}).*(?!\}).*/', $mReturnValue)) {
                // if value is a serialized array, then deserialize it
                    $mReturnValue = \unserialize($mReturnValue);
                }
            }
        }
        return $mReturnValue;
    }
    final public static function _decodeIdKey(string $sToken)
    {
        static $sLastToken = '';
        $mReturnValue = null; // set returnvalue to default
        if ($sLastToken !== $sToken) {
            $sLastToken = $sToken;
            $oThis = self::getInstance();
            if (\is_string($sToken) && self::isValidIdkey($sToken)) {
            // key must be a 16-digit hexvalue
                if (\array_key_exists($sToken, $oThis->aTokens)) {
                // check if key is stored in IDKEYs-list
                    $mReturnValue = $oThis->aTokens[$sToken]['value']; // get stored value
                    $oThis->removeToken($sToken); // remove from list to prevent multiuse
                }
            }
        }
        return $mReturnValue;
    }
/**
 * make a valid LifeTime value from given integer on the rules of class SecureTokens
 * @param integer  $iLifeTime
 * @return integer
 */
    final public static function sanitizeTokenLifeTime($iLifeTime, bool $bAdjustToStep = true)
    {
        $iLifeTime = \intval($iLifeTime);
        for ($i = self::LIFETIME_MIN; $i <= self::LIFETIME_MAX; $i += self::LIFETIME_STEP) {
            $aLifeTimes[] = $i;
        }
        $iRetval = \array_pop($aLifeTimes);
        foreach ($aLifeTimes as $iValue) {
            if ($iLifeTime <= $iValue) {
                $iRetval = $iValue;
                break;
            }
        }
        return $iRetval;
    }

/**
 * returns all TokenLifeTime values
 * @return array
 */
    final public static function getTokenLifeTime()
    {
        return [
            'min'   => self::LIFETIME_MIN,
            'max'   => self::LIFETIME_MAX,
            'step'  => self::LIFETIME_STEP,
            'value' => self::getInstance()->iTokenLifeTime
        ];
    }
/**
 * get a simple Token without effect to the tokens table
 * @param int $iTokenLength 3-4  (default: 4)
 * @return string returns an independent Token between 3 and 4 Chars
 */
    final public static function getFreeToken($iTokenLength = 4)
    {
        $iDigits = ($iTokenLength > 4 ? 4 : ($iTokenLength < 3 ? 3 : $iTokenLength));
        $iToBaseLen = \strlen(self::TOKEN_ENCODE_BASE);
        $iNumber = \rand($iToBaseLen**($iDigits-1), ($iToBaseLen**$iDigits)-1);
        return self::getInstance()->encodeInteger($iNumber);
    }

/**
 * creates a runtime-unique token with n digits length
 * @staticvar array $aTokens
 * @param int $iDigits
 * @return string
 */
    final public static function getUniqueFreeToken($iDigits = 4)
    {
        static $aTokens = [];
        $iRepeats = (int) \ceil($iDigits / 4);
        do {
            $sTmp = '';
            while ($iRepeats--) {
                $sTmp .= self::getFreeToken(4);
            }
            $sTmp = \substr($sTmp, 0, $iDigits);
        } while (\in_array($sTmp, $aTokens));
        return $sTmp;
    }  // end of getUniqueToken

/* ************************************************************************************ */
/* *** from here private methods only                                               *** */
/* ************************************************************************************ */
/**
 * load all tokens from session
 */
    private function loadTokens()
    {
        if (isset($_SESSION['TOKENS'])) {
            $this->aTokens = \unserialize($_SESSION['TOKENS']);
        } else {
            $this->saveTokens();
        }
    }

/**
 * save all tokens into session
 */
    private function saveTokens()
    {
        $_SESSION['TOKENS'] = \serialize($this->aTokens);
    }

/**
 * add new token to the list
 * @param string $sTokenName
 * @param string $sValue
 * @return string  name(index) of the token
 */
    private function addToken($sTokenName, $sValue)
    {
        // limit TokenName to 16 digits
        $sTokenName = \substr(\str_pad($sTokenName, 16, '0', \STR_PAD_LEFT), -16);
        // make sure, first digit is a alpha char [a-f]
        $sTokenName[0] = \dechex(10 + (\hexdec($sTokenName[0]) % 5));
        // loop as long the generated TokenName already exists in list
        while (isset($this->aTokens[$sTokenName])) {
            // split TokenName into 4 words
            $aWords = \str_split($sTokenName, 4);
            // get lowest word and increment it
            $iWord = \hexdec($aWords[3]) + 1;
            // reformat integer into a 4 digit hex string
            $aWords[3] = \sprintf('%04x', ($iWord > 0xffff ? 1 : $iWord));
            // rebuild the TokenName
            $sTokenName = \implode('', $aWords);
        }
        // store Token in list
        $this->aTokens[$sTokenName] = [
            'value'    => $sValue,
            'expire'   => $this->iExpireTime,
            'instance' => $this->sCurrentInstance
        ];
        return $sTokenName;
    }

/**
 *
 * @param integer  $iNumber
 * @return string
 */
    private function encodeInteger($iNumber)
    {
        $aToBase = \str_split(self::TOKEN_ENCODE_BASE);
        \shuffle($aToBase); // increase the randomisation
        $iToBaseLen = \sizeof($aToBase);
        $sRetval = '';
        while ($iNumber != 0) {
            $sRetval = $aToBase[($iNumber % $iToBaseLen)].$sRetval;
            $iNumber = (int)($iNumber / $iToBaseLen);
        }
        return $sRetval;
    }

/**
 * remove the token, called sTokenName from list
 * @param type $sTokenName
 */
    private function removeToken($sTokenName)
    {
        if (isset($this->aTokens[$sTokenName])) {
            if ($this->bPreserveAllOtherTokens) {
                if ($this->sInstanceToDelete) {
                    $this->sInstanceToUpdate = $this->sInstanceToDelete;
                    $this->sInstanceToDelete = null;
                } else {
                    $this->sInstanceToUpdate = $this->aTokens[$sTokenName]['instance'];
                }
            } else {
                $this->sInstanceToDelete = $this->aTokens[$sTokenName]['instance'];
            }
            unset($this->aTokens[$sTokenName]);
        }
    }

    private function getSpecialToken($mToken){
        if (is_string($mToken)){
            $mToken = explode('=',$mToken);
        }
        return \array_intersect_key($this->aTokens, $mToken );
    }

/**
 * remove all expired tokens from list
 */
    private function removeExpiredTokens()
    {
        $iTimestamp = time();
        foreach ($this->aTokens as $sTokenName => $aToken) {
            if ($aToken['expire'] <= $iTimestamp && $aToken['expire'] != 0){
                unset($this->aTokens[$sTokenName]);
            }
        }
    }

/**
 * generate a runtime depended hash
 * @return string  md5 hash
 */
    private function generateSalt()
    {
        list($fUsec, $fSec) = \explode(" ", microtime());
        $sSalt = (string)\rand(10000, 99999)
               . (string)((float)$fUsec + (float)$fSec)
               . (string)\rand(10000, 99999);
        return \md5($sSalt);
    }

/**
 * build a simple fingerprint
 * @return string
 */
    private function buildFingerprint()
    {
        if (!$this->bUseFingerprint) { return \md5('this_is_a_dummy_only'); }
        $sClientIp = '127.0.0.1';

        if (\array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
            $aTmp = \preg_split('/\s*,\s*/', $_SERVER['HTTP_X_FORWARDED_FOR'], -1, \PREG_SPLIT_NO_EMPTY);
            $sClientIp = \array_pop($aTmp);
        }else if (\array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $sClientIp = $_SERVER['REMOTE_ADDR'];
        }else if (\array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $sClientIp = $_SERVER['HTTP_CLIENT_IP'];
        }

        $aTmp = \array_chunk(\stat(__FILE__), 11);
        unset($aTmp[0][8]);
        return \md5(
            __FILE__ . \PHP_VERSION . \implode('', $aTmp[0])
            . (\array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : 'AGENT')
            . $this->calcClientIpHash($sClientIp)
        );
    }

/**
 * mask IPv4 as well IPv6 addresses with netmask and make a md5 hash from
 * @param string $sClientIp IP as string from $_SERVER['REMOTE_ADDR']
 * @return md5 value of masked ip
 * @description this method does not accept the IPv6/IPv4 mixed format
 *               like "2222:3333:4444:5555:6666:7777:192.168.1.200"
 */
    private function calcClientIpHash($sRawIp)
    {
        $sIpAddress = $sRawIp;
        if (IpAddress::isIpV4($sIpAddress)) {
            $sMaskedIp = IpAddress::getMaskedIpV4($sIpAddress, $this->iNetmaskLengthV4);
        } else {
            $sMaskedIp = IpAddress::getMaskedIpV6($sIpAddress, $this->iNetmaskLengthV6);
        }
        return \md5($sMaskedIp);
    }

/**
 * encode a hex string into a 64char based string
 * @param string $sMd5Hash
 * @return string
 * @description reduce the 32char length of a MD5 to 22 chars
 */
    private function encodeHash($sMd5Hash)
    {
         return \preg_replace('/[^a-zA-Z0-9]/', '_', \rtrim(\base64_encode(\pack('h*',$sMd5Hash)), '='));
//        return rtrim(base64_encode(pack('h*',$sMd5Hash)), '+-= ');
    }

// callback method, needed for PHP-5.3.x only
    private function checkFtanCallback($aToken)
    {
        return $this->encodeHash(\md5($aToken['value'].$this->sFingerprint));
    }

/**
 * read settings if available
 */
    private function getSettings()
    {
        $oReg = $this->oRegistry;

        $this->bUseFingerprint  = (bool) ($oReg->SecTokenFingerprint ?? $this->bUseFingerprint);
        $this->iNetmaskLengthV4 = Helpers::sanitizeMinMax(
                $oReg->SecTokenIpv4Netmask ?? $this->iNetmaskLengthV4,
                0,
                32,
                $this->iNetmaskLengthV4
        );
        $this->iNetmaskLengthV6 = Helpers::sanitizeMinMax(
                $oReg->SecTokenIpv6PrefixLength ?? $this->iNetmaskLengthV6,
                0,
                128,
                $this->iNetmaskLengthV6
        );
        if (\defined('DEBUG') && DEBUG != false) {
            $this->iTokenLifeTime = self::DEBUG_LIFETIME;
        } else {
            $this->iTokenLifeTime = self::sanitizeTokenLifeTime($oReg->SecTokenLifeTime ?? $this->iTokenLifeTime);
        }
/*
        $this->iTokenLifeTime   = $this->sanitizeLifeTime($this->iTokenLifeTime)

        $this->bUseFingerprint  = (bool) ($this->oRegistry->SecTokenFingerprint ?? $this->bUseFingerprint);
        $this->iNetmaskLengthV4 = (int) ($this->oRegistry->SecTokenIpv4Netmask ?? $this->iNetmaskLengthV4);
        $this->iNetmaskLengthV6 = $this->oRegistry->SecTokenIpv6PrefixLength ?? $this->iNetmaskLengthV6;
        $this->iTokenLifeTime   = $this->oRegistry->SecTokenLifeTime ?? $this->iTokenLifeTime;
        $this->iNetmaskLengthV4 = ($this->iNetmaskLengthV4 < 1 || $this->iNetmaskLengthV4 > 32)
                                  ? 0 :$this->iNetmaskLengthV4;
        $this->iNetmaskLengthV6 = ($this->iNetmaskLengthV6 < 1 || $this->iNetmaskLengthV6 > 128)
                                  ? 0 :$this->iNetmaskLengthV6;
        $this->iTokenLifeTime   = $this->iTokenLifeTime$this->iTokenLifeTime);
        if ($this->iTokenLifeTime <= self::LIFETIME_MIN && DEBUG) {
            $this->iTokenLifeTime = self::DEBUG_LIFETIME;
        }
*/




    }
/**
 * make sure that an integer value is between min and max
 * @param int $iValue
 * @param int $iMin
 * @param int $iMax
 * @param int|null $iDefault
 * @return int
 */
    private function sanitizeMinMax(int $iValue, int $iMin, int $iMax, $iDefault = null): int
    {
        if (\is_null($iDefault)) {
            $iRetval = ($iValue < $iMin ? $iMin : ($iValue > $iMax ? $iMax : $iValue));
        } else {
            $iRetval = ($iValue > $iMax || $iValue < $iMin) ? $iDefault : $iValue;
        }
        return $iRetval;
    }


} // end of class SecureTokens
