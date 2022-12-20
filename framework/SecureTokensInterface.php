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
 * SecureTokensInterface.php
 *
 * @category     Core
 * @package      Core_package
 * @subpackage   Name of the subpackage if needed
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 69 $
 * @link         $HeadURL: svn://svn.kipanga.org/fwswm/intra/trunk/framework/SecureTokensInterface.php $
 * @lastmodified $Date: 2020-11-22 15:38:39 +0100 (So, 22. Nov 2020) $
 * @since        File available since 13.02.2016
 * @description  xyz
 */

namespace bin;

use src\Security\CsfrTokens;

class SecureTokensInterface {

    /** int first private property */
    private $oSecTok = null;

    /** constructor */
    public function __construct() {
//        $this->oSecTok = SecureTokens::getInstance();
    }

/**
 * Dummy method for backward compatibility
 * @return void
 * @deprecated from WB-2.8.3-SP5
 */
    final public function createFTAN()
    {
        trigger_error('Deprecated function call: '.__METHOD__, E_USER_DEPRECATED);
    } // do nothing

/**
 * Dummy method for backward compatibility
 * @return void
 * @deprecated from WB-2.8.3-SP5
 */
    final public function clearIDKEY()
    {
        trigger_error('Deprecated function call: '.__METHOD__, E_USER_DEPRECATED);
    } // do nothing

/**
 * returns the current FTAN
 * @param bool $mode: true or POST returns a complete prepared, hidden HTML-Input-Tag (default)
 *                     false or GET returns an GET argument 'key=value'
 * @return mixed:     array or string
 * @deprecated the param $mMode is set deprecated
 *              string retvals are set deprecated. From versions after 2.8.4 retval will be array only
 */
    final public function getFTAN($mMode = 'POST')
    {
        $aFtan = CsfrTokens::getToken();
        switch (strtoupper($mMode)):
            case 'POST':
                $mRetval = '<input type="hidden" name="'.$aFtan['name'].'" value="'
                         . $aFtan['value'].'" title="">';
                break;
            case 'GET':
                $mRetval = $aFtan['name'].'='.$aFtan['value'];
                break;
            default:
                $mRetval = ['name' => $aFtan['name'], 'value' => $aFtan['value']];
                break;
        endswitch;
        return $mRetval;
    }

/**
 * checks received form-transactionnumbers against session-stored one
 * @param string $mode: requestmethode POST(default) or GET
 * @param bool $bPreserve (default=false)
 * @return bool:    true if numbers matches against stored ones
 *
 * requirements: an active session must be available
 * this check will prevent from multiple sending a form. history.back() also will never work
 */
    final public function checkFTAN($mMode = 'POST', $bPreserve = false)
    {
        return CsfrTokens::checkToken();
    }
/**
 * store value in session and returns an accesskey to it
 * @param mixed $mValue can be numeric, string or array
 * @return string
 */
    final public function getIDKEY($mValue)
    {
        return CsfrTokens::createIdKey($mValue);
    }

/*
 * search for key in session and returns the original value
 * @param string $sFieldname: name of the POST/GET-Field containing the key or hex-key itself
 * @param mixed $mDefault: returnvalue if key not exist (default 0)
 * @param string $sRequest: requestmethode can be POST or GET or '' (default POST)
 * @param bool $bPreserve (default=false)
 * @return mixed: the original value (string, numeric, array) or DEFAULT if request fails
 * @description: each IDKEY can be checked only once. Unused Keys stay in list until they expire
 */
    final public function checkIDKEY($sFieldname, $mDefault = 0, $sRequest = 'POST', $bPreserve = false)
    {
        $mRetvalue = $mDefault; // set returnvalue to default
        switch (strtoupper($sRequest)) {
            case 'POST':
                $sTokenName = $_POST[$sFieldname] ?: $sFieldname;
                break;
            case 'GET':
                $sTokenName = $_GET[$sFieldname] ?: $sFieldname;
                break;
            default:
                $sTokenName = $sFieldname;
        }
        $mRetval = CsfrTokens::decodeIdKey($sTokenName);
        return ($mRetval ?? $mDefault);
    }

/**
 * make a valid LifeTime value from given integer on the rules of class SecureTokens
 * @param integer  $iLifeTime
 * @return integer
 */
    final public function sanitizeLifeTime($iLifeTime)
    {
        return CsfrTokens::sanitizeTokenLifeTime($iLifeTime);
    }

/**
 * returns all TokenLifeTime values
 * @return array
 */
    final public function getTokenLifeTime()
    {
        return CsfrTokens::getTokenLifeTime();
    }

} // end of class SecureTokensInterface
