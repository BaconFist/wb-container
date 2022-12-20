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
 */
namespace bin\helpers;

/**
 * AccessFileHelper.php
 *
 * @category     Core
 * @package      Core_Routing
 * @subpackage   Accessfiles
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2086 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/framework/AccessFileHelper.php $
 * @lastmodified $Date: 2014-01-16 04:41:48 +0100 (Do, 16. Jan 2014) $
 * @since        File available since 01.08.2013
 * @description  this class provides some helper methodes to handle access files
 */


class AccessFileHelper {

/**
 * do not delete start directory of deltree
 */
    const DEL_ROOT_PRESERVE = 0;
/**
 * delete start directory of deltree
 */
    const DEL_ROOT_DELETE   = 1;
/**
 * clear logs
 */
    const LOG_CLEAR = true;
/**
 * preserve logs
 */
    const LOG_PRESERVE = false;
/**
 * to store the last delTree log
 */
    static $aDelTreeLog = [];

/**
 * private constructor to deny instantiating
 */
    private function __construct() {
        ;
    }
/**
 * Test if file is an access file
 * @param       string $sFileName  qualified path/file
 * @return      boolean
 * @description test given file for typical accessfile signature
 */
    static public function isAccessFile($sFileName)
    {
        if (!\is_readable($sFileName)) { throw new \AccessFileInvalidFilePathException('invalid filename ['.$sFileName.']'); }
        $bRetval = false;
        if (($sFile = \file_get_contents($sFileName)) !== false) {
            $sPattern = '/^\s*?<\?php.*?\$i?page_?id\s*=\s*[0-9]+;.*?(?:require|include)'
                      . '(?:_once)?\s*\(\s*\'.*?index\.php\'\s?\);/siU';
            $bRetval = (bool) \preg_match($sPattern, $sFile);
            unset($sFile);
        }
        return $bRetval;
    }
/**
 * Delete all contents of basedir, but not the basedir itself
 * @param string  $sRootDir the content of which should be deleted
 * @param integer $iMode    the mode can be set to self::DEL_ROOT_PRESERVE(default) or self::DEL_ROOT_DELETE
 * @return boolean          false if a file or directory can't be deleted
 */
    static public function delTree($sRootDir, $iMode = self::DEL_ROOT_PRESERVE)
    {
        // check if root dir is inside the installation and is writeable
        $oReg = \bin\WbAdaptor::getInstance();
        self::$aDelTreeLog = [];
        $bResult = true;
        if (!\is_writeable($sRootDir)) {
            self::$aDelTreeLog[] = 'insufficient rights or directory not empty in : '.\str_replace($oReg->AppPath, '', $sRootDir);
            return false;
        }
        $oIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sRootDir), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($oIterator as $oPath) {
            $sPath = \rtrim(\str_replace('\\', '/', $oPath->__toString()), '/');
            if ($oPath->isDir() && !\preg_match('/\.$/s', $sPath)) {
                // proceed directories
                if (!\rmdir($sPath)) {
                    $bResult = false;
                    self::$aDelTreeLog[] = 'insufficient rights or directory not empty in : '.\str_replace($oReg->AppPath, '', $sPath);
                } else {
                    self::$aDelTreeLog[] = 'Directory successful removed : '.\str_replace($oReg->AppPath, '', $sPath);
                }
            } elseif ($oPath->isFile()) {
                // proceed files
                if (!\unlink($sPath)) {
                    $bResult = false;
                    self::$aDelTreeLog[] = 'insufficient rights or directory not empty in : '.\str_replace($oReg->AppPath, '', $sPath);
                } else {
                    self::$aDelTreeLog[] = 'File successful deleted : '.\str_replace($oReg->AppPath, '', $sPath);
                }
            }
        }
        if (($iMode & self::DEL_ROOT_DELETE)) {
            if ($bResult) { // if there was no error before
                if (\rmdir($sRootDir)) {
                    self::$aDelTreeLog[] = 'Directory successful removed : '.\str_replace($oReg->AppPath, '', $sRootDir);
                    return $bResult;
                }
            }
            self::$aDelTreeLog[] = 'insufficient rights or directory not empty in : '.\str_replace($oReg->AppPath, '', $sRootDir);
        }
        return $bResult;
    }
/**
 * returns the log of the last delTree request
 * @param  bool  $bClearLog   TRUE clears the log after request, FALSE preserve the log
 * @return array
 */
    static public function getDelTreeLog($bClearLog = self::LOG_CLEAR)
    {
        $aRetval = self::$aDelTreeLog;
        if($bClearLog) { self::$aDelTreeLog = array(); }
        return $aRetval;
    }

/* *** following methods still are in developing right now! Do not use it! ************ */

/**
 * isPathInsideApplication
 * @param string $sRootPath   full base path<br />
 *        example1-a: '/www/htdocs/subdir/'
 *        example2-a: 'c://www/htdocs/subdir/'
 *        example3-a: 'http://user:pass@isteam.de:123/www/htdocs/subdir/'
 * @param string $sFullPath  full path to test<br />
 *        example1-b: '/www/htdocs/subdir/targetdir/../testfile.php'
 *        example2-b: 'c://www/htdocs/subdir/targetdir/../testfile.php'
 *        example3-b: 'http://user:pass@isteam.de:123/www/htdocs/subdir/targetdir/../testfile.php'
 * @return type
 */
    static public function isRootPartOfPath($sRootPath, $sFullPath)
    {
        $sRootPath = self::getRealPath($sRootPath);
        $sFullPath = self::getRealPath($sFullPath);
        $sRetval = (\preg_match('/^'.\preg_quote($sRootPath, '/').'/', $sFullPath) ? $sFullPath : null );
        return $sRetval;
    }

    static public function getRealPath($sPath)
    {
        $iCount = 0;
        $aResultPath = [];
        $sPath = \str_replace(array('\\', '/./'), array('/', '/'), $sPath);
        if (\preg_match('/^\/?\.\//', $sPath)) {
            $sPath = \str_replace('\\', '/', \getcwd()).'/'.\preg_replace('/^\/?\.\//', '', $sPath);
        }

        $sPattern = '/((?:^[a-z]?\:\/+)|(?:^.*?\:\/\/.*?\/)|(?:^\/+))[^\/].*$/is';
    // extract host/root - part from path save and then remove it from path
        $sPrefix = \preg_replace($sPattern, '\1', $sPath);
        $sPath = \preg_replace('/^'.\preg_quote($sPrefix, '/').'/', '', $sPath);
    // replace any tripple or more dots by doubble dots and split the path at '/' into an array.
    // save number of replaced .. in $iCount and add somme buffer elements
        $aTestPath = \array_merge(($iCount ? \array_fill(0, $iCount * 2, '###') : []),
                                 \explode('/', \preg_replace(array('/\.{2,}/s', '/./'), array('..', '/'), $sPath, -1, $iCount))
                     );
        foreach ($aTestPath as $sPart) {
            switch ($sPart) {
                case '.': // skip part
                    continue 2;
                    break;
                case '..': // step back
                    \array_pop($aResultPath);
                    break;
                default: // take part
                    $aResultPath[] = $sPart;
                    break;
            }
        }
        while($aResultPath[0] == '###') {
            \array_shift($aResultPath);
        }
        $sResultPath = $sPrefix.\implode('/', $aResultPath);
        return $sResultPath;
    }


} // end of class AccessFileHelper
