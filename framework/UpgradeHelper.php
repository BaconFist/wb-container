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

/**
 * UpgradeHelper.php
 *
 * @category     Core
 * @package      Core_Upgrade
 * @copyright    Werner v.d.Decken <wkl@isteam.de>
 * @author       Werner v.d.Decken <wkl@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2115 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/framework/UpgradeHelper.php $
 * @lastmodified $Date: 2014-12-01 11:22:18 +0100 (Mo, 01. Dez 2014) $
 * @since        File available since 17.03.2013
 * @description  some helper function for upgrade-script.php
 */
class UpgradeHelper {

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
    static $aDelTreeLog = array();

/**
 * Compare available tables against a list of tables
 * @param  array list of needed table names without TablePrefix
 * @return array list of missing tables
 * @description this method is the replaement for self::existsAllTables()
 */
    public static function getMissingTables(array $aTablesList)
    {
        $aTablesList = array_flip($aTablesList);
        $oDb = WbDatabase::getInstance();
        $sPattern = addcslashes ( $oDb->TablePrefix, '%_' );
        if (($oTables = $oDb->doQuery( 'SHOW TABLES LIKE "'.$sPattern.'%"'))) {
            while ($aTable = $oTables->fetchArray(MYSQLI_NUM)) {
                $sTable =  preg_replace('/^'.preg_quote($oDb->TablePrefix, '/').'/s', '', $aTable[0]);
                if (isset($aTablesList[$sTable])) {
                    unset($aTablesList[$sTable]);
                }
            }
        }
        return array_flip($aTablesList);
    }
/**
 * Alias for self::getMissingTables()
 * @param array list of needed table names without TablePrefix
 * @return array list of missing tables
 */
    public static function existsAllTables(array $aTablesList)
    {
        return self::getMissingTables($aTablesList);
    }
/**
 * Sanitize and repair Pagetree links
 * @return boolean|int number of updated records or false on error
 */
    public static function sanitizePagesTreeLinkStructure()
    {
        $oDb = WbDatabase::getInstance();
        $iCounter = 0;
        $aPages = array();
        try {
            $sql = 'SELECT `page_id`, `link`, `page_trail` '
                 . 'FROM `'.$oDb->TablePrefix.'pages` '
                 . 'ORDER BY `page_id`';
            $oPages = $oDb->doQuery($sql);
            // read 'page_id', 'link' and 'page_trail' from all pages
            while ($aPage = $oPages->fetchArray(MYSQLI_ASSOC)) {
                // extact filename only from complete link
                $aPages[$aPage['page_id']]['filename'] = preg_replace('/.*?\/([^\/]*$)/', '\1', $aPage['link']);
                $aPages[$aPage['page_id']]['page_trail'] = $aPage['page_trail'];
                $aPages[$aPage['page_id']]['link'] = $aPage['link'];
            }
            foreach ($aPages as $iKey=>$aRecord) {
            // iterate all pages
                $aTmp = array();
                $aIds = explode(',', $aRecord['page_trail']);
                // rebuild link from filenames using page_trail
                foreach($aIds as $iId) {
                    $aTmp[] = $aPages[$iId]['filename'];
                }
                $sLink = '/'.implode('/', $aTmp);
                if ($sLink != $aPages[$iKey]['link']) {
                // update page if old link is different to new generated link
                    $sql = 'UPDATE `'.$oDb->TablePrefix.'pages` '
                         . 'SET `link`=\''.$sLink.'\' '
                         . 'WHERE `page_id`='.$iKey;
                    $oDb->doQuery($sql);
                    $iCounter++;
                }
            }
        } catch(WbDatabaseException $e) {
            return false;
        }
        return $iCounter;
    }
/**
 *
 * @param string $sMsg Message to show
 */
    public static function dieWithMessage($sMsg)
    {
        $sMsg = 'Fatal error occured during initial startup.<br /><br />'.PHP_EOL.$sMsg
              . '<br />'.PHP_EOL.'Please correct these errors and then '
              . '<a href="http://'.$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"].'" '
              . 'title="restart">klick here to restart the upgrade-script</a>.<br />'.PHP_EOL;
        die($sMsg);
    }
/**
 *
 * @param string $sAppPath path to the current installation
 * @return boolean
 */
    public static function checkSetupFiles($sAppPath)
    {
        if (defined('DB_PASSWORD')) {
        // old config.php is active
            if (    !is_writable($sAppPath.'config.php')
                 || (file_exists($sAppPath.'config.php.new') && !is_writable($sAppPath.'config.php.new'))
                 || (file_exists($sAppPath.'setup.ini.php') && !is_writable($sAppPath.'setup.ini.php'))
                 || (!file_exists($sAppPath.'setup.ini.php') && !is_writable($sAppPath.'setup.ini.php.new'))
               )
            {
            // stop script if there's an error occured
                $sMsg = 'Following files must exist and be writable to run upgrade:<br />'.PHP_EOL
                      . '<ul>'.PHP_EOL
                      . '<li>config.php</li>'.PHP_EOL
                      . (file_exists($sAppPath.'config.php.new') ? '<li>config.php.new</li>'.PHP_EOL : '')
                      . (file_exists($sAppPath.'setup.ini.php') ? '<li>setup.ini.php</li>'.PHP_EOL : '')
                      . (!file_exists($sAppPath.'setup.ini.php') ? '<li>setup.ini.php.new</li>'.PHP_EOL : '')
                      . '</ul>'.PHP_EOL;
                self::dieWithMessage($sMsg);
            } else { // ok, let's change the files now!
                if (file_exists($sAppPath.'setup.ini.php')) {
                // if 'setup.ini.php' exists
                    if (!is_writeable($sAppPath.'setup.ini.php')) {
                    // but it's not writable
                        $sMsg = 'The file \'setup.ini.php\' already exists but is not writeable!';
                        self::dieWithMessage($sMsg);
                    }
                } else {
                // try to rename 'setup.ini.php.new' into 'setup.ini.php'
                    if (!rename($sAppPath.'setup.ini.php.new', $sAppPath.'setup.ini.php')) {
                        $sMsg = 'Can not rename \''.$sAppPath.'setup.ini.php.new\' into \''.$sAppPath.'setup.ini.php\' !!<br />'
                              . 'Create an empty file \''.$sAppPath.'setup.ini.php\' and make it writeable for the server!';
                        self::dieWithMessage($sMsg);
                    }
                }
            // now first read constants from old config.php
                $sContent = file_get_contents($sAppPath.'config.php');
                $sPattern = '/^\s*define\s*\(\s*([\'\"])(.*?)\1\s*,.*;\s*$/m';
                if (preg_match_all($sPattern, $sContent, $aMatches)) {
                // get all already defined consts
                    $aAllConsts = get_defined_constants(true);
                    $aSetupConsts = array();
                // collect the needed values from defined consts
                    foreach($aMatches[2] as $sConstName) {
                        $aSetupConsts[$sConstName] = (isset($aAllConsts['user'][$sConstName]) ? $aAllConsts['user'][$sConstName] : '');
                    }
                // try to sanitize available values
                    $aSetupConsts['DB_TYPE'] = ((isset($aSetupConsts['DB_TYPE']) && $aSetupConsts['DB_TYPE'] != '') ? $aSetupConsts['DB_TYPE'] : 'mysql');
                    $aSetupConsts['DB_PORT'] = ((isset($aSetupConsts['DB_PORT']) && $aSetupConsts['DB_PORT'] != '') ? $aSetupConsts['DB_PORT'] : '3306');
                    $aSetupConsts['DB_CHARSET'] = (isset($aSetupConsts['DB_CHARSET']) ? $aSetupConsts['DB_CHARSET'] : '');
                    $aSetupConsts['DB_CHARSET'] = preg_replace('/[^0-9a-z]*/i', '', $aSetupConsts['DB_CHARSET']);
                    $aSetupConsts['TABLE_PREFIX'] = (isset($aSetupConsts['TABLE_PREFIX']) ? $aSetupConsts['TABLE_PREFIX'] : '');
                    $aSetupConsts['ADMIN_DIRECTORY'] = trim(str_replace('\\', '/', preg_replace('/^'.preg_quote(WB_PATH, '/').'/', '', ADMIN_PATH)), '/').'/';
                    $aSetupConsts['WB_URL'] = rtrim(str_replace('\\', '/', WB_URL), '/').'/';
                // Try and write settings to config file
                    if (self::writeSetupIni($sAppPath, $aSetupConsts)) {
                        if (self::writeConfig($sAppPath)) {
                            return true;
                        } else {
                            $sMsg ='Error writing \''.$sAppPath.'config.php\'!';
                        }
                    } else {
                        $sMsg ='Error writing \''.$sAppPath.'setup.ini.php\'!';
                    }
                } else {
                    $sMsg = 'No valid content found in \''.$sAppPath.'config.php\'!';
                }
                self::dieWithMessage($sMsg);
            }
        } else {
            $sFileContent = file_get_contents($sAppPath.'config.php');
            if (preg_match('/\s*define\s*\(.*\)\s*;/i', $sFileContent)) {
            // if config.php does not contain any defines
                if (is_writable($sAppPath.'config.php')) {
                // overwrite config.php with default content for compatibility
                    if (self::writeConfig($sAppPath.'config.php')) {
                        return true;
                    } else  {
                        $sMsg ='Error writing \''.$sAppPath.'config.php\'!';
                    }
                } else {
                    $sMsg ='File is not writable \''.$sAppPath.'config.php\'!';
                }
                self::dieWithMessage($sMsg);
            }
        }
    }
/**
 *
 * @param string $sAppPath the path where setup.ini.php is located
 * @param array  $aSetupValues
 * @return boolean
 */
    public static function writeSetupIni($sAppPath, array $aSetupValues)
    {
        $sSetupContent =
            ";<?php die('sorry, illegal file access'); ?>#####\n"
           .";################################################\n"
           ."; WebsiteBaker configuration file\n"
           ."; auto generated ".date('Y-m-d h:i:s A e ')."\n"
           .";################################################\n"
           ."[Constants]\n"
           ."DEBUG   = false\n"
           ."AppUrl  = \"".$aSetupValues['WB_URL']."\"\n"
           ."AcpDir  = \"".$aSetupValues['ADMIN_DIRECTORY']."\"\n"
           .";##########\n"
           ."[DataBase]\n"
           ."type    = \"".$aSetupValues['DB_TYPE']."\"\n"
           ."user    = \"".$aSetupValues['DB_USERNAME']."\"\n"
           ."pass    = \"".$aSetupValues['DB_PASSWORD']."\"\n"
           ."host    = \"".$aSetupValues['DB_HOST']."\"\n"
           ."port    = \"".$aSetupValues['DB_PORT']."\"\n"
           ."name    = \"".$aSetupValues['DB_NAME']."\"\n"
           ."charset = \"".$aSetupValues['DB_CHARSET']."\"\n"
           ."table_prefix = \"".$aSetupValues['TABLE_PREFIX']."\"\n"
           .";\n"
           .";################################################\n";
        $sSetupFile = $sAppPath.'setup.ini.php';
        if (file_put_contents($sSetupFile, $sSetupContent)) {
            return true;
        }
        return false;
    }
/**
 *
 * @param string $sAppPath the path where config.php is located
 * @return boolean
 */
    public static function writeConfig($sAppPath)
    {
        $sConfigContent = "<?php\n"
            ."/* this file is for backward compatibility only */\n"
            ."/* never put any code in this file! */\n"
            ."include_once(dirname(__FILE__).'/framework/initialize.php');\n";
        $sConfigFile = $sAppPath.'config.php';
        if (file_put_contents($sConfigFile, $sConfigContent)) {
            return true;
        }
        return false;
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
        self::$aDelTreeLog = array();
        $bResult = true;
        if (!is_writeable($sRootDir)) {
            self::$aDelTreeLog[] = str_replace($oReg->AppPath, '', $sRootDir);
            return false;
        }
        $oIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sRootDir), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($oIterator as $oPath) {
            $sPath = rtrim(str_replace('\\', '/', $oPath->__toString()), '/');
            if ($oPath->isDir() && !preg_match('/\.$/s', $sPath)) {
                // proceed directories
                if (!@rmdir($sPath)) {
                    $bResult = false;
                    self::$aDelTreeLog[] = str_replace($oReg->AppPath, '', $sPath);
                }
            } elseif ($oPath->isFile()) {
                // proceed files
                if (!@unlink($sPath)) {
                    $bResult = false;
                    self::$aDelTreeLog[] = str_replace($oReg->AppPath, '', $sPath);
                }
            }
        }
        if (($iMode & self::DEL_ROOT_DELETE) && $bResult) {
        // delete RootDir if there was no error before
            if (!rmdir($sRootDir)) {
                $bResult = false;
                self::$aDelTreeLog[] = str_replace($oReg->AppPath, '', $sRootDir);
            }
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

/**
 * import info.ini as 'old style' variables
 * @param string $sIniDir the directory where the info.ini is located
 * @return array of settings or null on error
 */
    static public function convInfoIni2InfoPhp($sIniDir)
    {
        $aVarTypePrefixes = array(
            'template' => 'template', 'theme'  => 'template', 'tool'   => 'module',
            'page'     => 'module', 'snippet'  => 'module', 'WYSIWYG'  => 'module', 'none'  => 'module'
        );

        $aVarTypePrefixes = array(
            'template' => 'template',
            'theme'    => 'template',
            'tool'     => 'module',
            'page'     => 'module',
            'snippet'  => 'module',
            'wysiwyg'  => 'module',
            'WYSIWYG'  => 'module',
            'none'     => 'module',
       );

       $aNeededVars = array_flip(
            array(
                'directory','name','function','version','platform','platform_versions',
                'platform_minPhp','author','license','license_terms','description'
            )
        );
        $aRetval = array();
        $sIniDir = rtrim(str_replace('\\', '/', $sIniDir), '/').'/';
        if (is_readable($sIniDir.'info.ini')) {
            if (($ini = parse_ini_file($sIniDir.'info.ini', true))) {
                if (!array_key_exists($ini['info']['type'], $aVarTypePrefixes)) {
                    return null;
                }
                $aRetval['prefix'] = $aVarTypePrefixes[$ini['info']['type']];
                $aRetval['function'] = $ini['info']['type'];
                unset($aNeededVars['function']);
                foreach ($ini['info'] as $sVar => $sValue) {
                    $aRetval[$sVar] = $sValue;
                    unset($aNeededVars[$sVar]);
                }
                if (isset($ini['platform']['versions'])) {
                    $aRetval['platform'] = $ini['platform']['versions'];
                    unset($aNeededVars['platform']);
                }
                if (isset($ini['platform']['versions'])) {
                    $aRetval['platform_versions'] = $ini['platform']['versions'];
                    unset($aNeededVars['platform_versions']);
                }
                if (isset($ini['platform']['minPhp'])) {
                    $aRetval['platform_minPhp'] = $ini['platform']['minPhp'];
                    unset($aNeededVars['platform_minPhp']);
                }
                if  (sizeof($aNeededVars) > 0) {
                    $aRetval = null;
                }
            } else {
                $aRetval = null;
            }
        } else {
            $aRetval = null;
        }
        return $aRetval;
    } // end of function Ini2PhpInfo

} // end of class UpgradeHelper

