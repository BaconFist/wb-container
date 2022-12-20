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
 * Description of Translate
 *
 * @category     Core
 * @package      Core_Security
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 3.0
 * @version      0.0.2
 * @revision     $Revision: 67 $
 * @lastmodified $Date: 2020-11-21 18:59:51 +0100 (Sa, 21. Nov 2020) $
 * @since        File available since 23.05.2016
 * @deprecated   no / since 0000/00/00
 * @description  handling of security tokens to prevent cross side scripting on manipulating requests
 */

//namespace bin;

class Translate
{
/** holds the active singleton instance */
    private static $oInstance      = null;
/** translations of the core */
    protected $aTranslations       = [];
/** translations of current module */
    protected $aAddonTranslations  = [];
/** path to the cache files */
    protected $sCachePath          = '';
/** switch the cache on/off */
    protected $bUseCache           = false;
/** list of languages to load */
    protected $aLanguages          = ['EN'];
/** stack of loaded addons */
    protected $aAddonStack         = [];
// -----------------------------------------------------------------------------
/** prevent class from public instancing and get an object to hold extensions */
    protected function  __construct() {}
// -----------------------------------------------------------------------------
/** prevent from cloning existing instance */
    private function __clone() {}
// -----------------------------------------------------------------------------
/**
 * get a valid instance of this class
 * @return object
 */
    static public function getInstance() {
        if( is_null(self::$oInstance) ) {
            $c = __CLASS__;
            self::$oInstance = new $c;
        }
        return self::$oInstance;
    }
// -----------------------------------------------------------------------------
/**
 * initialize class and load core translations
 * @param string $sCachePath
 */
    public function initialize($mLanguages, $sCachePath = '')
    {
        if ($this->sCachePath == '') {
            if ($sCachePath == '') {
                $sCachePath = \dirname(__DIR__).'/temp/cache/Translate';
            }
            $sCachePath = (\str_replace('\\', '/', $sCachePath)).'/';
            $this->bUseCache = (\is_writeable($sCachePath));
            $this->sCachePath = $sCachePath;
            if (!\is_array($mLanguages)) {
                $mLanguages = \preg_split('/\s*?[,;|\s]\s*?/', $mLanguages);
            }
            $this->aLanguages = $mLanguages;
            $this->aTranslations = [];
            $this->aTranslations = $this->readFiles('core');
        }
    }
// -----------------------------------------------------------------------------
/**
 * load translations of an addon
 * @param string $sDomain   i.e 'modules\\news' or 'admin\\pages'
 */
    public function addAddon($sDomain)
    {
        if ($sDomain) {
            $this->aAddonTranslations = [];
            $this->aAddonTranslations = $this->readFiles($sDomain);
        }
    }
// -----------------------------------------------------------------------------
/**
 * ALIAS for addAddon()
 * @param string $sDomain   i.e 'modules\\news' or 'admin\\pages'
 */
    public function enableAddon($sDomain)
    {
        $this->addAddon($sDomain);
        \array_push($this->aAddonStack, $sDomain);
    }
// -----------------------------------------------------------------------------
    public function getAddonStack()
    {
        return $this->aAddonStack;
    }
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
/**
 * remove translations of an addon
 */
    public function disableAddon()
    {
        if (isset($this->aAddonTranslations)) {
            $this->aAddonTranslations = [];
            if (($sDomain = \array_pop($this->aAddonStack)) != null) {
                $this->addAddon($sDomain);
                if (count($this->aAddonStack)){$this->addAddon(end($this->aAddonStack));}
            }
        }
    }
// -----------------------------------------------------------------------------
/**
 * clear all translation cache files
 */
   public function clearCache()
    {
        $sMask = $this->sCachePath.'*';
        $aFiles = \glob($sMask, GLOB_NOSORT);
        foreach ($aFiles as $sFile) {
            if (\is_writable($sFile)) {
              \unlink($sFile);
            }
        }
    }// -----------------------------------------------------------------------------
/**
 * Return complete table of translations
 * @return array containing all loaded translations
 * @deprecated for backward compatibility to PHPLIB only. Will be removed shortly
 */
    public function getLangArray()
    {
        $aRetval = \array_merge($this->aTranslations, $this->aAddonTranslations);
        return $aRetval;
    }
// -----------------------------------------------------------------------------
/* *** method group used by Twig ******************************************** */
/**
 * check if a entry exists
 * @param string $sKeyword
 * @return boolean
 */
    public function __isset($sKeyword)
    {
        return (
            isset($this->aAddonTranslations[$sKeyword]) ||
            isset($this->aTranslations[$sKeyword])
        );
    }
// -----------------------------------------------------------------------------
/**
 * return value of an existing entry
 * @param string $sKeyword
 * @return string
 */
    public function __get($sKeyword)
    {
        try {
            if (\is_null($sKeyword)) { throw new \InvalidArgumentException('Illegal value null in keyword!'); }
            if ($sKeyword == '') { throw new \InvalidArgumentException('Empty keyword given!'); }
            if (preg_match('/[^a-z_0-9]/siu', $sKeyword)) { throw new \InvalidArgumentException('Illegal chars in keyword!'); }
            if (isset($this->aAddonTranslations[$sKeyword])) {
                $sRetval = $this->aAddonTranslations[$sKeyword];
            } else if (isset($this->aTranslations[$sKeyword])) {
                $sRetval = $this->aTranslations[$sKeyword];
            } else {
                $sRetval = '#'.$sKeyword.' missing#';
            }
        } catch(\throwable $e) {
            $sRetval = $e->getMessage();
        }
        return $sRetval;
    }
// -----------------------------------------------------------------------------
/**
 * try to set a new entry
 * @param string $sKeyword
 * @param string $value
 */
    public function __set($sKeyword, $value)
    {
        if (isset($this->aAddonTranslations[$sKeyword])) {
            $sRetval = $this->aAddonTranslations[$sKeyword];
        } elseif (isset($this->aTranslations[$sKeyword])) {
            $sRetval = $this->aTranslations[$sKeyword];
        } else {
            throw new RuntimeException('illegal action ['.__CLASS__.'::__set(\''.$sKeyword.'\', \''.$value.'\')]!! ');
        }
    }
// -----------------------------------------------------------------------------
/**
 * read translation files of given domain
 * @param string $sDomain
 * @return array of translations
 */
    protected function readFiles($sDomain)
    {
        $aTranslations = [];
        $sSourcePath = \str_replace('\\', '/', \dirname(__DIR__).'/'.($sDomain == 'core' ? '' : $sDomain.'/'));
        $sCacheFile = $this->sCachePath.\md5($sDomain.\implode('', $this->aLanguages));
        if (!\is_readable($sCacheFile)) {
            $sLoadedLanguages = '';
            $sCurrentLang = '';
            foreach ($this->aLanguages as $sLanguage) {
                // Avoid that a language is proceeded several times in a row
                if ($sCurrentLang == $sLanguage) { continue; }
                $sCurrentLang = $sLanguage;
                $sLoadedLanguages .= $sLanguage.', ';
                $sFile = $sSourcePath.'languages/'.$sLanguage.'.php';
                if (\is_readable($sFile)) {
                    $aTranslations = \array_merge($aTranslations, $this->importArrays($sFile));
                }
                \reset($aTranslations);
            }
            // create cache file
            $sOutputVar = '';
            $sOutput = '<?php'.PHP_EOL.'// *** autogenerated cachefile '.PHP_EOL
                     . '// *** Domain:    '.$sDomain.PHP_EOL
                     . '// *** Languages: '.rtrim($sLoadedLanguages, ' ,').PHP_EOL
                     . '// ***************************'.PHP_EOL;
            foreach ($aTranslations as $key => $value){
                $sOutputVar .= '$aTranslations[\''.$key.'\']=\''.\str_replace("'", "\'", $value).'\';'.PHP_EOL;
            }
            $sOutputVar .= PHP_EOL;
            // save cache file
            if ($this->bUseCache && \is_writeable($this->sCachePath)) {
                \file_put_contents($sCacheFile, $sOutput.$sOutputVar); //, LOCK_EX);
            }
        } else {
            // include chache file
            include($sCacheFile);
        }
        return $aTranslations;
    }
// -----------------------------------------------------------------------------
/**
 * Import language definitions into array
 * @param string load language from filename
 * @return array contains all found translations
 */
    protected function importArrays($sLanguageFile)
    {
        // get all available loaded vars of this method
        $aOldVarlist = [];
        $aOldVarlist = \get_defined_vars();
        // include the file
        include $sLanguageFile;
        $aCurrVarlist = \array_diff_key(\get_defined_vars(), $aOldVarlist);
        $aLangSections = [];
        $aLanguageTable = [];
        foreach ($aCurrVarlist as $key=>$value) {
        // extract the names of arrays from language file
            if (\is_array($value)) {
                $aLangSections[] = $key;
//                $aLangSections[$key] = $value;
            }
        }
        foreach ($aLangSections as $sSection) {
        // walk through all arrays
            foreach (${$sSection} as $key => $value) {
            // and import all found translations
                if (!\is_array($value)) {
                // skip all multiarray definitions from compatibility mode
                    $aLanguageTable[$sSection.'_'.$key] = $value;
                }
            }
        }
        return $aLanguageTable;
    }
// -----------------------------------------------------------------------------
} // end of class Translation()
