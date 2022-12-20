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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * WbAdaptor.php
 *
 * @category     Core
 * @package      Core_package
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 69 $
 * @lastmodified $Date: 2020-11-22 15:38:39 +0100 (So, 22. Nov 2020) $
 * @since        File available since 18.01.2013
 * @deprecated   This class will be removed if Registry comes activated
 * @description  This adaptor is a temporary replacement for the future registry class
 */

namespace bin;

use src\Interfaces\{Registry,Requester};

class WbAdaptor implements Registry
{

/** active instance */
    private static $oInstance = null;
/** array hold settings */
    protected $aProperties = [];
/**  */
    protected $aObjects = ['Db' => null, 'Trans' => null, 'App' => null, 'Request' => null];
/** vars which             */
    protected $aReservedVars = ['Db', 'Trans', 'App', 'Request'];
/** constructor */
    protected function __construct()
    {
        $this->aProperties = ['System' => [], 'Request' => []];
    }

/**
 * Get active instance
 * @return WbAdaptor
 */
    public static function getInstance()
    {
        if (self::$oInstance === null) {
            $c = __CLASS__;
            self::$oInstance = new $c();
        } else {
        }
        return self::$oInstance;
    }

/**
 * set the global database object
 * @param WbDatabase $oDb
 */
    public function setRequester($oRequest)
    {
        $this->aObjects['Request'] = $oRequest;
        return true;
    }
    public function getRequester()
    {
        return $this->aObjects['Request'] ?? null;
    }
/**
 * set the global database object
 * @param WbDatabase $oDb
 */
    public function setDatabase(\database $oDb)
    {
        $this->aObjects['Db'] = $oDb;
        return true;
    }
    public function getDatabase(): \database
    {
        return $this->aObjects['Db'] ?? null;
    }
    /**
 * set the global translation object
 * @param Translate $oTrans
 */
    public function setTranslate(\Translate $oTrans)
    {
        $this->aObjects['Trans'] = $oTrans;
        return true;
    }
    public function getTranslate(): \Translate
    {
        return $this->aObjects['Trans'] ?? null;
    }

/**
 * set the global application object
 * @param wb $oApp must be an object which derivates from the class WB
 */
    public function setApplication(wb $oApp)
    {
        $this->aObjects['App'] = $oApp;
    }
    public function getApplication(): wb
    {
        return ($this->aObjects['App']);
    }
    /**
 * handle unknown properties
 * @param string name of the property
 * @param mixed value to set
 * @throws InvalidArgumentException
 */
    public function __set($name, $value)
    {
        if (\array_key_exists($name, $this->aProperties['System'])) {
            throw new \InvalidArgumentException('tried to set readonly or nonexisting property [ '.$name.' }!! ');
        } else {
            $this->aProperties['Request'][$name] = $value;
        }
    }
/**
 * Get value of a variable
 * @param string name of the variable
 * @return mixed
 */
    public function __get($sVarname)
    {
        $sRetval = null;
        $sRetval = $this->aProperties['Request'][$sVarname] ?? $sRetval;
        $sRetval = $this->aProperties['System'][$sVarname] ?? $sRetval;
        $sRetval = $this->aObjects[$sVarname] ?? $sRetval;
        return $sRetval;
    }
/**
 * Check if var is set
 * @param string name of the variable
 * @return bool
 */
    public function __isset($sVarname)
    {
        $sRetval = $this->aProperties['Request'][$sVarname] ?? null;
        $sRetval = $this->aProperties['System'][$sVarname] ?? $sRetval;
        $sRetval = $this->aObjects[$sVarname] ?? $sRetval;
        return (bool) $sRetval;
    }

/**
 * Import WB-Constants
 */
    public function getWbConstants()
    {
    // first reinitialize arrays
        $this->aProperties = [
            'System' => [],
            'Request' => []
        ];
    // get all defined constants
        $aConsts = getConstants('', 'user');
    // iterate all user defined constants
        foreach ($aConsts as $sKey=>$sVal) {
            if (\in_array($sKey, $this->aReservedVars)) { continue; }
        // skip possible existing database constants
            if (\preg_match('/^db_$/i', $sKey)) { continue; } // |^TABLE_PREFIX
        // change all path items to trailing slash scheme and assign the new naming syntax
            switch($sKey):
                case 'APP_NAME':
                    $this->aProperties['System']['AppSid'] = $sVal;
                    break;
                case 'DEBUG':
                    $this->aProperties['System']['Debug'] = \intval($sVal);
                    $this->aProperties['System']['DebugLevel'] = \intval($sVal);
                    break;
                case 'WB_URL':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AppUrl';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'WB_REL':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AppRel';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'WB_PATH':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AppPath';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'ADMIN_URL':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AcpUrl';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'ADMIN_REL':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AcpRel';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'ADMIN_PATH':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AcpPath';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'THEME_URL':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'ThemeUrl';
                    $this->aProperties['Request'][$sKey] = $sVal;
                    break;
                case 'THEME_REL':
                    $sVal = rtrim(str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'ThemeRel';
                    $this->aProperties['Request'][$sKey] = $sVal;
                    break;
                case 'THEME_PATH':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'ThemePath';
                    $this->aProperties['Request'][$sKey] = $sVal;
                    break;
                case 'TMP_PATH':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'TempPath';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'ADMIN_DIRECTORY':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'AcpDir';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'DOCUMENT_ROOT':
                    $sVal = \rtrim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'DocumentRoot';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'PAGES_DIRECTORY':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sVal = $sVal=='/' ? '' : $sVal;
                    $sKey = 'PagesDir';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'MEDIA_DIRECTORY':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/').'/';
                    $sKey = 'MediaDir';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'DEFAULT_TEMPLATE':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/');
                    $sKey = 'DefaultTemplate';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'TEMPLATE':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/');
                    $sKey = 'Template';
                    $this->aProperties['Request'][$sKey] = $sVal;
                    break;
                case 'DEFAULT_THEME':
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/');
                    $sKey = 'DefaultTheme';
                    $this->aProperties['System'][$sKey] = $sVal;
                    $this->aProperties['Request']['Theme'] = \trim($sVal, '/');
                    break;
                case 'OCTAL_FILE_MODE':
                    $sVal = ((\intval($sVal) & ~0111)|0600); // o-x/g-x/u-x/o+rw
                    $sKey = 'OctalFileMode';
                    $this->aProperties['System']['OctalFileMode'] = $sVal;
                    $this->aProperties['System']['FileModeOctal'] = $sVal;
                    break;
                case 'OCTAL_DIR_MODE':
                    $sVal = (\intval($sVal) |0711); // o+rwx/g+x/u+x
                    $sKey = 'OctalDirMode';
                    $this->aProperties['System']['OctalDirMode'] = $sVal;
                    $this->aProperties['System']['DirModeOctal'] = $sVal;
                    break;
                case 'WB_VERSION':
                    $sKey = 'AppVersion';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'WB_REVISION':
                    $sKey = 'AppRevision';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'WB_SP':
                    $sKey = 'AppServicePack';
                    $this->aProperties['System'][$sKey] = $sVal;
                    break;
                case 'PAGE_ICON_DIR':
                    $sKey = 'PageIconDir';
                    $sVal = \trim(\str_replace('\\', '/', $sVal), '/').'/';
                    $this->aProperties['Request'][$sKey] = $sVal;
                    break;
                case 'TEMPLATE_DIR':
                    break;
                default:
                    $aSysList = array(
                    // list of values which should be placed in ['System']
                        'DefaultCharset','DefaultDateFormat','DefaultLanguage','DefaultTimeFormat',
                        'DefaultTimezone','DevInfos'
                    );
                    // convert 'true' or 'false' strings into boolean
                    $sVal = ($sVal == 'true' ? true : ($sVal == 'false' ? false : $sVal));
                    // reformatting constant names
                    $sKey = \str_replace(' ', '', \ucwords(\str_replace('_', ' ', \strtolower($sKey))));
                    if (\in_array($sKey, $aSysList)) {
                        $this->aProperties['System'][$sKey] = $sVal;
                    } else {
                        $this->aProperties['Request'][$sKey] = $sVal;
                    }
                    break;
            endswitch;
        }
/* now set values which needs dependencies */
        if (!isset($this->aProperties['Request']['Template']) || $this->aProperties['Request']['Template'] == '') {
            $this->aProperties['Request']['Template'] = $this->DefaultTemplate;
        }
        $this->aProperties['System']['AppName'] = 'WebsiteBaker';
        if (isset($this->Template)) {
            $this->aProperties['Request']['TemplateDir']  = 'templates/'.$this->Template.'/';
            $this->aProperties['Request']['TemplateUrl']  = $this->AppUrl.'templates/'.$this->Template.'/';
            $this->aProperties['Request']['TemplatePath'] = $this->AppPath.'templates/'.$this->Template.'/';
        }
/* correct PageIconDir if necessary */
        $this->aProperties['Request']['PageIconDir'] = \str_replace('/*/', '/'.$this->Template.'/', $this->PageIconDir);

        $this->aProperties['System']['ModuleDir'] = 'modules/';
        $this->aProperties['System']['VarPath'] = $this->aProperties['System']['AppPath'].'var/';
        $this->aProperties['System']['VarUrl']  = $this->aProperties['System']['AppUrl'].'var/';
        $this->aProperties['System']['VarRel']  = $this->aProperties['System']['AppRel'].'var/';
/* cleanup arrays */
        $this->aProperties['Request'] = \array_diff_key(
            $this->aProperties['Request'],
            $this->aProperties['System']
        );
        \ksort($this->aProperties['System']);
        \ksort($this->aProperties['Request']);
    }

// temporary method for testing purposes only
    public function showAll(): \ArrayObject
    {
        return new \ArrayObject([
            'System'  => new \ArrayObject($this->aProperties['System'], \ArrayObject::ARRAY_AS_PROPS),
            'Request' => new \ArrayObject($this->aProperties['Request'], \ArrayObject::ARRAY_AS_PROPS),
        ], \ArrayObject::ARRAY_AS_PROPS);
    }

} // end of class WbAdaptor

