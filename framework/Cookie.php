<?php

/*
 * Copyright (C) 2018 Manuela v.d.Decken <manuela@isteam.de>
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
 * Description of Cookie
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: Cookie.php 22 2018-09-09 15:17:39Z Luisehahne $
 * @since        File available since 20.06.2018
 * @deprecated   no / since 0000/00/00
 * @description  Examples:
 *
 *  use bin\Cookie;
 * set new cookie ----------------
 *  (new Cookie('user_id'))
 *      ->setValue(1)
 *      ->setExpire(86400)
 *      ->setPath('/foo/')
 *      ->setDomain('example.com')
 *      ->setHttpOnly(true)
 *      ->send();
 * delete same cookie ------------
 *  (new Cookie('user_id'))
 *      ->setPath('/foo/')
 *      ->setDomain('example.com')
 *      ->setHttpOnly(true)
 *      ->remove();
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin;

// use

/**
 * set and remove cookies
 */
class Cookie
{
    private $sName;
    private $sValue    = '';
    private $iExpire   = 0;
    private $sPath     = '';
    private $sDomain   = '';
    private $bSecure   = false;
    private $bHttpOnly = false;

    public function __construct(
        $sName,
        $sValue    = '',
        $iExpire   = 0,
        $sPath     = '',
        $sDomain   = '',
        $bSecure   = false,
        $bHttpOnly = false
    ) {
        $this->sName     = $sName;
        $this->sValue    = $sValue;
        $this->iExpire   = $iExpire;
        $this->sPath     = $sPath;
        $this->sDomain   = $sDomain;
        $this->bSecure   = $bSecure;
        $this->bHttpOnly = $bHttpOnly;
    }

    public function setValue($sValue)
    {
        $this->sValue = $sValue;
        return $this;
    }

    public function setExpire($iExpire)
    {
        $this->iExpire = $iExpire;
        return $this;
    }

    public function setPath($sPath)
    {
        $this->sPath = $sPath;
        return $this;
    }

    public function setDomain($sDomain)
    {
        $this->sDomain = $sDomain;
        return $this;
    }

    public function setSecure($bSecure)
    {
        $this->bSecure = $bSecure;
        return $this;
    }

    public function setHttpOnly($bHttpOnly)
    {
        $this->bHttpOnly = $bHttpOnly;
        return $this;
    }

    /**
     * send this Cookie
     * @return bool
     */
    public function send()
    {
        return setcookie(
            $this->sName,
            $this->sValue,
            $this->iExpire,
            $this->sPath,
            $this->sDomain,
            $this->bSecure,
            $this->bHttpOnly
        );
    }
    /**
     * Removes this Cookie complete
     * @return bool
     */
    public function remove()
    {
        $this->sValue = '';
        $this->iExpire = time() - (86400 * 10); // 10 days back to eliminate timezones
        return $this->send();
    }
} // end of class Cookie
