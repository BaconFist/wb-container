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
 * Description of version.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: version.php 379 2019-07-03 12:13:47Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no / since 0000/00/00
 * @description  simply stares the version number of installed files
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace version;

// use

/* --------------------------------------------------------- */
if (!\defined('VERSION_LOADED')) {
// Must include code to stop this file being accessed directly
     if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/*--------------------------------------------------------- */
    $sInfo = '
        VERSION  = "2.13.2"
        REVISION = "133"
        SP       = ""
        PATCH    = "133"
    ';
    $aInfo = [];
    $aInfo['WBInfo'] = \parse_ini_string($sInfo);
    foreach ($aInfo['WBInfo'] as $item=>$value) {
        if (!\defined($item)) {\define($item, $value);}
    }
    \define('VERSION_LOADED', true);
}
    unset($aInfo);
    unset($sInfo);
