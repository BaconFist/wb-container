<?php
/**
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
 *
 * Description of Lingual
 *
 * @package      Addon package
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      1.0.0-dev.0
 * @revision     $Id: install.php 300 2019-03-27 09:00:11Z Luisehahne $
 * @since        File available since 02.12.2017
 * @deprecated   no
 * @description  xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $msg = [];
    $sErrorMsg = null;
    $sAddonPath = \str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
    $sAddonName = basename($sAddonPath);
    $globalStarted = preg_match('/upgrade\-script\.php$/', $_SERVER["SCRIPT_NAME"]);
    $sWbVersion = ($globalStarted && defined('VERSION') ? VERSION : WB_VERSION);
    $sModulePlatform = PreCheck::getAddonVariable($sAddonName,'platform');
    if (version_compare($sWbVersion, $sModulePlatform, '<')){
        $msg[] = $sErrorMsg = sprintf('It is not possible to install from WebsiteBaker Versions before %s',$sModulePlatform);
        if ($globalStarted){
            echo $sErrorMsg;
        }else{
            throw new \Exception ($sErrorMsg);
        }
    } else {
//        require (WB_PATH.'/framework/functions.php');
        if (\is_writable(WB_PATH.'/modules/mod_multilingual')) {rm_full_dir (WB_PATH.'/modules/mod_multilingual');}
    }