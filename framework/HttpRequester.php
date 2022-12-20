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
 * Description of HttpRequester
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: HttpRequester.php 69 2020-11-22 14:38:39Z Manuela $
 * @since        File available since 04.10.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin\requester;

use src\Interfaces\Requester;

/**
 * short description of class
 */
class HttpRequester implements Requester
{
/** active instance */
    protected static $oInstance = null;
    private $aParameters = [];
    private $aServer     = [];
    private $aHeaders    = [];
    private $aCookies    = [];

/**
 * construct and initialize the class
 */
    public function __construct()
    {
        $this->aCookies = \filter_input_array(\INPUT_COOKIE);
        $aServer  = \filter_input_array(\INPUT_SERVER);
        if (\sizeof($aServer) != \sizeof($_SERVER)) {$aServer = $_SERVER;}
/*
        switch (\strtolower($aServer['REQUEST_METHOD'])):
            case 'post':
                $this->aParameters = \filter_input_array(\INPUT_POST);
                break;
            case 'get':
                $this->aParameters = \filter_input_array(\INPUT_GET);
                break;
            default:
                break;
        endswitch;
*/
        switch (\strtolower($aServer['REQUEST_METHOD'])):
            case 'post':
                $this->aParameters = \filter_input_array(\INPUT_POST);
                if (isset($_GET['route'])) {
                    $this->aParameters['_wb'] = $_GET['_wb'];
                }
                break;
            case 'get':
                $this->aParameters = \filter_input_array(\INPUT_GET);
                break;
            default:
                break;
        endswitch;
        if (\is_null($this->aParameters)) { $this->aParameters = []; }

        foreach ($aServer as $sKey => $sValue) {
            if (\substr_compare($sKey, 'HTTP_', 0, 5) === 0) {
                $this->aHeaders[$sKey] = $sValue;
            } else {
                $this->aServer[$sKey] = $sValue;
            }
        }
        $this->isSecure();
    }

    public static function getInstance()
    {
        if(self::$oInstance == null) {
            $c = __CLASS__;
            self::$oInstance = new $c();
        }
        return self::$oInstance;
    }

/**
 * returns a list of all parameters
 * @return array
 */
    public function getParamNames()
    {
        return \array_keys($this->aParameters);
    }
/**
 * check  if parameter exists
 * @param string $sParamName
 * @return bool
 */
    public function issetParam($sParamName)
    {
        return \array_key_exists($sParamName, $this->aParameters);
    }
/**
 * read a variable from commandline
 * @param string $sParamName
 * @param int $iFilterType
 * @param mixed $mOptions
 * @return mixed | null on error
 * @throws \InvalidArgumentException
 * @description the method is fully compatible to the PHP function filter_var()
 */
    public function getParam($sParamName, $iFilterType = \FILTER_DEFAULT, $mOptions = null)
    {
        $mRetval = null;
        try {
            if (!$this->issetParam($sParamName)) { throw new \Exception('error on getParam()'); }
            $mRetval = $this->aParameters[$sParamName];
            if (\FILTER_DEFAULT !== $iFilterType) {
                if (!\is_null($mOptions)) {
                    $mRetval = \filter_var($mRetval, $iFilterType, $mOptions);
                } else {
                    $mRetval = \filter_var($mRetval, $iFilterType);
                }
            }
        } catch (\Exception $ex) {
            $mRetval = null;
        }
        return $mRetval;
    }
/**
 *
 * @param string $sHeaderName
 * @return mixed | null on error
 */
    public function issetHeader($sHeaderName)
    {
        $sVarname = 'HTTP_'.preg_replace('/^http_/i', '', $sHeaderName);
        return \array_key_exists($sVarname, $this->aHeaders);
    }
/**
 * get header vars ($_SERVER['HTTP_'*])
 * @param string $sHeaderName
 * @return mixed | null on error
 */
    public function getHeader($sHeaderName)
    {
        $sRetval = null;
        $sVarname = 'HTTP_'.\preg_replace('/^http_/i', '', $sHeaderName);
        if ($this->issetHeader($sVarname)) {
            $sRetval = $this->aHeaders[$sVarname];
        }
        return $sRetval;
    }
/**
 *
 * @param string $sVarName
 * @return type
 */
    public function issetServerVar($sVarName)
    {
        return \array_key_exists($sVarName, $this->aServer);
    }
/**
 * get server vars excluding $_SERVER['HTTP_'*]
 * @param string $sVarName
 * @return mixed | null on error
 */
    public function getServerVar($sVarName)
    {
        $sRetval = null;
        if ($this->issetServerVar($sVarName)) {
            $sRetval = $this->aServer[$sVarName];
        }
         return $sRetval;
    }
/**
 * test if cookie 'name' exists
 * @param string $sName
 * @return bool
 */
    public function issetCookie($sName)
    {
        return \array_key_exists($sName, $this->aCookies);
    }
/**
 * return value of cookie 'name'
 * @param string $sName
 * @return mixed | null on not existing cookie
 */
    public function getCookie($sName)
    {
        $mRetval = null;
        if ($this->issetCookie($sName)) {
            $mRetval = $this->aCookies[$sName];
        }
        return $mRetval;
    }
/**
 * check for HTTPS request
 * @staticvar string $sRequestScheme
 * @return bool  true on HTTPS | false on HTTP-request
 */
    public function isSecure()
    {
        static $sRequestScheme = '';
        if ($sRequestScheme == '') {
            $iRetval = (int) filter_var(
                $this->getServerVar('HTTPS'),
                \FILTER_VALIDATE_BOOLEAN,
                ['options'=>['default'=>false]]
            );
            $iRetval += (int) (isset($this->aHeaders['HTTP_X_FORWARDED_PROTO']) &&
                        ('https' == strtolower($this->aHeaders['HTTP_X_FORWARDED_PROTO'])));
            $iRetval += (int) (isset($this->aServer['X_FORWARDED_PROTO']) &&
                        ('https' == strtolower($this->aServer['X_FORWARDED_PROTO'])));
            $iRetval += (int) filter_var(
                $this->getHeader('HTTP_X_FORWARDED_SSL'),
                \FILTER_VALIDATE_BOOLEAN,
                ['options'=>['default'=>false]]
            );
            $sRequestScheme = (($iRetval !== 0) ? 'https' : 'http');
            $_SERVER['HTTPS'] = ($sRequestScheme === 'https' ? 'on' : 'off');
            $this->aServer['HTTPS'] = ($sRequestScheme === 'https' ? 'on' : 'off');
            $_SERVER['REQUEST_SCHEME'] = $sRequestScheme;
            $this->aServer['REQUEST_SCHEME'] = $sRequestScheme;
        }
        return ($sRequestScheme === 'https');
    }
} // end class
