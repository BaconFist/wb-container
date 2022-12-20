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
 * Description of admin/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = $sAddonPath.'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel     = '/'.$sModuleDir.'/'.$sAddonName;
    // \basename(__DIR__).'/'.\basename(__FILE__);
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {require($sAppPath.'config.php');}
// Check if the config file has been set-up
    if (!defined('TABLE_PREFIX')){
    /*
     * Remark:  HTTP/1.1 requires a qualified URI incl. the scheme, name
     * of the host and absolute path as the argument of location. Some, but
     * not all clients will accept relative URIs also.
     */
        $_SERVER['REQUEST_SCHEME'] = ($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $host       = $_SERVER['HTTP_HOST'];
        $sDocRoot   = ($_SERVER["PATH_TRANSLATED"] ?? $_SERVER["DOCUMENT_ROOT"]);
        $uri        = ((basename($sAppPath)==basename($sDocRoot))  ? '' : '/'.basename($sAppPath));//
        $file       = '/install/index.php';
        $target_url = $_SERVER['REQUEST_SCHEME'].'://'.$host.$uri.''.$file;
        $sResponse  = $_SERVER['SERVER_PROTOCOL'].' 307 Temporary Redirect';
        \header($sResponse);
        \header('Location: '.$target_url);
        exit;    // make sure that subsequent code will not be executed
    }
     else {
        header('Location: '.ADMIN_URL.'/start/index.php');
    }
