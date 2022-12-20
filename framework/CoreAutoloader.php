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
/*
 * Description of Autoloader
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: CoreAutoloader.php 22 2018-09-09 15:17:39Z Luisehahne $
 * @since        File available since 05.07.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin;

// use

/**
 * short description of class
 */
class CoreAutoloader
{

    private static $sInstallPath = null;
    private static $aPatterns = [];
    private static $aReplacements = [];
    private static $aNamespaces = [];
    private static $aComeFrom = [];

    private static $aTransNs = [];
/**
 * add new Namespace->Directory relation or overwrite existing
 * @param mixed $sNamespace
 * @param string $sDirectory (default: '')
 */
    public static function addNamespace($mNamespace, $sDirectory = '')
    {
        if (\is_null(self::$sInstallPath)) {
            throw new \RuntimeException('can not add namespaces before autoloader is registered!!');
        }
        if (\is_string($mNamespace)) {
            $mNamespace = [$mNamespace => $sDirectory];
        }
        if (\is_array($mNamespace)) {
            foreach ($mNamespace as $sNamespace => $sDirectory) {
                $sNamespace = \trim(\str_replace('\\', '/', $sNamespace), '/');
                $sDirectory = \trim(\str_replace('\\', '/', $sDirectory), '/');
                self::$aTransNs[$sNamespace] = $sDirectory;
            }
            \krsort(self::$aTransNs);
            self::$aPatterns = self::$aReplacements = [];
            foreach (self::$aTransNs as $sPattern => $sReplacement) {
                self::$aPatterns[]     = '@^('.$sPattern.'/)@su';
                self::$aReplacements[] = $sReplacement.'/';
            }
        }
    }

    public static function getNamespaces($iFlag = 0)
    {
        $aRetval = [];
        $aRetval =  (($iFlag==1) ? self::$aNamespaces : (($iFlag==2) ? self::$aComeFrom : self::$aTransNs));
        return $aRetval;
    }

/**
 *
 * @param string $sClassName  class name with possible namepace
 * @return void
 */
    public static function autoLoad($sClassName)
    {
        self::$aComeFrom[][$sClassName] = $_SERVER["SCRIPT_NAME"];
/*
        $sTmp = \basename($sClassName);
        if (\strtolower(\substr($sTmp, -5)) == 'trait') {
            //$sClassName = ''.$sTmp;
            $sClassName = 'bin\traits\\'.$sTmp;
        }
*/
        $sFilePath = self::seekClassFile($sClassName);
        if (is_readable($sFilePath)) {
            include $sFilePath;
        }
    }

/**
 *
 * @param string $sClassName full class name with possible namepace
 * @return string full Path to the class file
 */
    public static function seekClassFile($sClassName)
    {
        $sResult = '';
        $aMatches = \preg_split(
            '=/=',
            \str_replace('\\', '/',$sClassName.'.php'),
            -1,
            \PREG_SPLIT_NO_EMPTY
        );

        // insert default NS if no one is given
        if (\sizeof($aMatches) == 1) { \array_unshift($aMatches, 'bin'); }
        // extract default filename
        $sClassFileName = \array_pop($aMatches);
        // translate namespaces into the real dir entries
        $sClassDirName = self::$sInstallPath.\preg_replace(
            self::$aPatterns,
            self::$aReplacements,
            \implode('/', $aMatches).'/'
        );
        // first seek normal filename
        $sFilePath = $sClassDirName.$sClassFileName;
        if (\is_readable($sFilePath)) {
            $sResult = $sFilePath;
        } else {
            // second seek filename with prefix 'class.'
            $sFilePath = $sClassDirName.'class.'.$sClassFileName;
            if (\is_readable($sFilePath)) {
                $sResult = $sFilePath;
            }
        }
        self::$aNamespaces[][$sClassName] = \str_replace(self::$sInstallPath,'',$sFilePath);
        self::$aComeFrom[][$sClassName] = $_SERVER["SCRIPT_NAME"];



        return $sResult;
    }

/**
 * register this autoloader
 */
    public static function doRegister($sPathPrefix)
    {
        self::$sInstallPath = \rtrim(\str_replace('\\', '/', $sPathPrefix), '/').'/';
        if (\is_dir(self::$sInstallPath) && \is_readable(self::$sInstallPath)) {
            \krsort(self::$aTransNs);
            foreach (self::$aTransNs as $sPattern => $sReplacement) {
                self::$aPatterns[]     = '@^('.$sPattern.'/)@su';
                self::$aReplacements[] = $sReplacement.'/';
            }
            \spl_autoload_register([__CLASS__, 'autoLoad']);
        } else {
            throw new \RuntimeException('invalid PathPrefix given!!');
        }
    }

/**
 * unregister this autoloader
 */
    public static function unRegister()
    {
        \spl_autoload_unregister([__CLASS__, 'autoLoad']);
    }

}
