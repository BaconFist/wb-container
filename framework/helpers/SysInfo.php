<?php

/*
 * Copyright (C) 2016 Manuela v.d.Decken <manuela@isteam.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Description of SysInfo
 *
 * @category     Core
 * @package      Core_SystemInfo
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 3.0
 * @version      0.0.1
 * @revision     $Revision: 68 $
 * @lastmodified $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 * @since        File available since 18.07.2016
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */

namespace bin\helpers;

class SysInfo
{
    const DIR_INCLUDE = false;
    const DIR_EXCLUDE = true;

    protected $bWbDbType    = true;
    protected $oDb          = null;
    protected $sTablePrefix = '';
    protected $getOne       = '';

    public function __construct()
    {
        if (class_exists('\database', false)) {
            $this->oDb = \database::getInstance();
            $this->sTablePrefix = $this->oDb->TablePrefix;
            $this->getOne      = 'get_one';
        } else {
            $this->oDb = $GLOBALS['database'];
            $this->sTablePrefix = TABLE_PREFIX;
            $this->getOne       = 'get_one';
        }
    }

    public function getInterface()
    {
        return PHP_SAPI;
    }

    public function isCgi()
    {
        return (\stripos(PHP_SAPI, 'cgi') !== false);
    }

   public function getWbVersion($bShowRev = false)
    {
        if (!\defined('VERSION_LOADED')) {require ADMIN_PATH.'/interface/version.php';}
        return VERSION
             . (\defined('WB_SP') ? ' '.WB_SP :'')
             . ($bShowRev ? '-r'.REVISION : '');
    }

    public function getPhpVersion()
    {
        return $this->stripNumber(PHP_VERSION);
    }

    /**
     * SysInfo::getOsVersion()
     *
     * @param bool $bFull
     * 'a': This is the default. Contains all modes in the sequence "s n r v m".
     * 's': Operating system name. eg. FreeBSD.
     * 'n': Host name. eg. localhost.example.com.
     * 'r': Release name. eg. 5.1.2-RELEASE.
     * 'v': Version information. Varies a lot between operating systems.
     * 'm': Machine type. eg. i386.
     * @return string
     */
    public function getOsVersion($bFull = false)
    {
        $sRetval = \php_uname();
        $x = array();
        if (!$bFull) {
            $xs = \php_uname('s');
            $xn = \php_uname('n');
            $xr = \php_uname('r');
            $xv = \php_uname('v');
            $xm = \php_uname('m');
            if (\stristr($xv, 'ubuntu')!==false) {
                $xs = \preg_replace('/^[^\~]*\~([^\s]+).*$/', '$1', $xv);
            }
            $sRetval = $xs;
        }
        return $sRetval;
    }

    public function getSqlServer()
    {
//         5.5.5-10.1.14-MariaDB
        $sRetval = 'unknown';
        $sql = 'SELECT VERSION( )';
        if (($sValue = $this->oDb->{$this->getOne}($sql))) {
            $sRetval = $sValue;
        }
        $sql = 'SELECT LOWER(@@global.sql_mode) AS strictinfo';
        if (($sValue = $this->oDb->{$this->getOne}($sql))) {
            $sRetval .= (\stristr($sValue, 'strict') !== false) ? ' [STRICT]' : '';
        }
        return $sRetval;
    }

    public function checkFolders(array $aFoldersList = null, $bMode = self::DIR_INCLUDE)
    {
        // get install folder of application
        $sInstallFolder = \str_replace('\\', '/', \dirname(dirname(__DIR__))).'/';
        // Callback sanitize path
        $cleanPath = function(& $sValue) { $sValue = \rtrim(\str_replace('\\', '/', $sValue), '/').'/'; };
        // sanitize list of given folders
        if (!$aFoldersList) { $aFoldersList = []; }
        // sanitize folders in list
        \array_walk($aFoldersList, $cleanPath);
        // save old working dir
        $sOldWorkDir = \getcwd();
        // change working dir
        \chdir($sInstallFolder);
        $aFoundFolders = \glob('*', \GLOB_ONLYDIR); // \GLOB_MARK|
        // sanitize folders in list
        \array_walk($aFoundFolders, $cleanPath);
        // restore old working dir
        \chdir($sOldWorkDir);
        if ($bMode != self::DIR_INCLUDE) {
        // leave only from $aFoldersList to test
            $aFoldersToTest = \array_diff($aFoundFolders, $aFoldersList);
        } else {
        // remove all folders in $aFoldersList for test
            $aFoldersToTest = \array_intersect($aFoundFolders, $aFoldersList);
        }
        // exchange  key<=>value
        $aFoldersToTest = \array_flip($aFoldersToTest);
        // set all values to false
        \array_walk($aFoldersToTest, function(& $value) { $value = false; });
        // set value to true if folder is writeable
        \array_walk(
            $aFoldersToTest,
            function(& $value, $key) use ($sInstallFolder) { $value = \is_writable($sInstallFolder.$key); }
        );
        return $aFoldersToTest;
    }

/* ************************************************************************** */
/*                                                                            */
/* ************************************************************************** */
    protected function stripNumber($sValue)
    {
        return \preg_replace('/^([0-9.]+).*$/', '$1', $sValue);
    }
}

