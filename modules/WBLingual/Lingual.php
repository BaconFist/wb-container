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
 *
 * Description of Lingual
 *
 * @package      Addon package
 * @copyright    Dietmar Wöllbrink
 * @author       Dietmar Wöllbrink
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      1.0.0-dev.0
 * @revision     $Id: Lingual.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 02.12.2017
 * @deprecated   no
 * @description  xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace addon\WBLingual;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use Twig;

/* -------------------------------------------------------- */
/* --------------------------------------------------------
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
    if (\version_compare(WB_VERSION, '2.11.0', '<')){
        throw new \Exception ('It is not possible to upgrade from WebsiteBaker Versions before 2.11.0');
    }
*/
class Lingual
{

/** holds the active singleton instance */
    private static $oInstance = null;
/** @var object instance of the WbAdaptor object */
    protected $oReg   = null;
/** @var object instance of the application object */
    private static $oApp     = null;
/** @var object instance of the database object */
    private $oDb      = null;

/** @var array holds several default values  */
    private $aConfig     = [];
/** @var array set several values for Twig_Environment */
    private $aTwigEnv    = [];
/** @var array set several values for Twig_Loader */
    private $aTwigLoader = [];
/** @var string set icon extension */
    private $sExtension  = 'png';

/**
 * constructor used to import some application constants and objects
 */
    public function __construct(){
        $this->init();
    }

    private function init(){
        $oReg        = WbAdaptor::getInstance();
        $this->oReg  = $oReg;
        $this->oDb   = $oReg->getDatabase();
        self::$oApp  = $oReg->getApplication();
        $this->aConfig['aLang'] = $this->getLanguageInUse();
        $this->aConfig['Twig']  = $this->getTwigConfig();
        $this->aTwigLoader      = $this->Twig['twig-loader-file'];
        $this->aTwigEnv         = $this->Twig['twig-environment'];
        $this->getTemplatePath();
    }

    /**
     * CopyAddons::__set()
     *
     * @param mixed $name
     * @param mixed $value
     * @return
     */
    public function __set($name, $value)
    {
       return $this->config[$name] = $value;
    }

    /**
     * CopyAddons::__isset()
     *
     * @param mixed $name
     * @return
     */
    public function __isset($name)
    {
        return isset($this->aConfig[$name]);
    }

    public function __get($name)
    {
        if (!$this->__isset($name)) {
            throw new \Exception('Tried to get none existing property ['.__CLASS__.'::'.$name.']');
        }
        return $this->aConfig[$name];
    }

/********************************************************************************************/
//
/********************************************************************************************/

    public function set($name, $value = '')
    {
        $this->aConfig[$name] = $value;
    }

    public function get($name)
    {
        return $this->$name;
    }
/********************************************************************************************/
//
/********************************************************************************************/

/**
 * get a valid instance of this class
 * @return object
 */
    static public function getLingualRel() {
      $sLingualDir = \str_replace('\\','/',\str_replace(WB_PATH,'',__DIR__.'/update_keys.php'));
      return $sLingualDir;
    }

    /**
    * Lib::getLangMenu()
    *
    * @param mixed $config
    * @param mixed $oApp
    * @return
    */
    private function getLangMenuTwig ( )
    {
        $sRetval = '';
        $data['aTargetList'] = $this->getLangMenuData();
        if (\count($data['aTargetList'])>1){
            $loader  = new Twig\Loader\FilesystemLoader($this->AbsTemplateDir.$this->aTwigLoader['templates_dir']);
            $twig    = new \Twig\Environment($loader);
            $sRetval = $twig->render($this->aTwigLoader['default_template'], $data);
        }
        return $sRetval;
    }

    private function getTemplatePath()
    {
        $this->aConfig['AbsTemplateDir'] = $this->oReg->TemplatePath;
        if (is_readable($this->AbsTemplateDir.$this->aTwigLoader['templates_dir'].'/'.$this->aTwigLoader['default_template'])){
        /* do nothing */
        } else if (is_readable(str_replace('\\','/',__DIR__).'/'.$this->aTwigLoader['templates_dir'].'/')){
            $this->aConfig['AbsTemplateDir'] = str_replace('\\','/',__DIR__).'/';
        }
    }

    private function getTwigConfig(){
        $sFilename = __DIR__.'/default.ini';
        return (\is_readable($sFilename) ? $this->getTwigEnv($sFilename) : false );
    }

    private function getTwigEnv($sFilename)
    {
        $aRetval = \parse_ini_file($sFilename, true, INI_SCANNER_TYPED);
        return $aRetval;
    }

    public static function getClassInfo(){
        return nl2br(sprintf("class %s [%d] with instance of %s\n",__CLASS__,__LINE__,get_class (self::$oApp)));
    }
/********************************************************************************************/
//
/********************************************************************************************/

    /**
     * methode to update a var/value-pair into table
     * @param integer $iPageId which page shall be updated
     * @param string $sTable the pages table
     * @param integer $iEntry
     * @return bool
     */
    private function updatePageCode(int $iPageId, string $sTable, $iNewPageCode = null): \mysql
    {
        // if new Pagecode is missing then set the own page ID
        $entry = ( !isset($iNewPageCode) ? $iPageId : $iNewPageCode);
        $sql = '
        UPDATE `'.$this->oDb->sTablePrefix.$sTable.'`
        SET
        `page_code`='.$entry.',
        `modified_when` = '.\time().'
        WHERE `page_id` = '.$iPageId;
        return $this->oDb->query($sql);
    }

    private function getLanguageInUse(string $sLangKey=''):? array
    {
      $aResult = [];
      $sqlSet = '
      SELECT DISTINCT
      `language`,`page_id`,`level`,`parent`,`root_parent`,`page_code`,`link`
      ,`menu_title`,`page_title`,`tooltip`
      ,`visibility`,`viewing_groups`,`viewing_users`,`position`
      FROM `'.$this->oDb->sTablePrefix.'pages`
      WHERE `level`= 0
        AND `visibility` NOT IN(\'none\', \'hidden\', \'deleted\')'
        .(($sLangKey != '') ? 'AND `language` = \''.$sLangKey.'\' ' : ' ').'
      ORDER BY `position`, `language`
      ';
      if (($oResult = $this->oDb->query($sqlSet))) {
          while (( $aPage = $oResult->fetchRow(MYSQLI_ASSOC))) {
              //if( !self::$oApp->isPageVisible($aPage['page_id'])) { continue; }
              $aResult[$aPage['language']] = $aPage;
          }
      }
      return $aResult;
    }

    /**
    *
    * search for pages with given page code and create a DB result object
    * @param integer Pagecode to search for
    * @return object result object or null on error
    */
    private function getPageCodeDbResult( int $iPageCode )
    {
        $sql = '
        SELECT
        `language`,`page_id`,`level`,`parent`,`root_parent`,`page_code`,`link`
        ,`menu_title`,`page_title`,`tooltip`
        ,`visibility`,`viewing_groups`,`viewing_users`,`position`
        FROM `'
        .$this->oDb->sTablePrefix.'pages`
        WHERE
        `page_code` = '.(int)$iPageCode.'
          AND `visibility` NOT IN (\'none\', \'deleted\')
       ORDER BY `parent`,`position`,`language`
       ';
       return $this->oDb->query($sql);
    }
//              .   'AND `visibility` NOT IN(\'none\', \'hidden\', \'deleted\') '

    /**
     *
     * @param integer $parent
     * @return database object with given SQL statement
     */
    private function getPageListDbResult(int $parent)
    {
        $sql = '
        SELECT `language`
        ,`page_id`,`parent`,`page_code`,`page_title`,`menu_title`,`tooltip`
        FROM `'.$this->oDb->sTablePrefix.'pages`
        WHERE `parent` = '.$parent. '
        ORDER BY `language`,`parent`,`position`
        ';
        return $this->oDb->query($sql);
    }

    private function getPageCodeValues(int $iPageCode):? array
    {
        $aRetval = [];
        if( ($oRes = $this->getPageCodeDbResult($iPageCode)) )
        {
            while($aPage = $oRes->fetchRow(MYSQLI_ASSOC))
            {
                if (!self::$oApp->page_is_visible($aPage)) {continue;}
                $aRetval[$aPage['language']] = $aPage;
            }
        }
        return $aRetval;
    }

    private function getPageList(int $parent, $this_page=0 )
    {
        static $entries = [];
        if( ($oLang = $this->getPageListDbResult($parent)) )
        {
            while($value = $oLang->fetchRow(MYSQLI_ASSOC))
            {
                if (( $value['page_id'] != $this_page ) )
                {
                    $entries [$value['page_id']]['language'] = $value['language'];
                    $entries [$value['page_id']]['menu_title'] = $value['menu_title'];
                    $this->getPageList($value['page_id'], $this_page );
                }
            }
        }
        return $entries;
    }

      public function getPageLangDetails():? array
      {
          $aLangData = [];
          $aPossiblePages = [];
// get root pages for all used languages
          $aAllowedRootLanguages = $this->getLanguageInUse();
          if (\count($aAllowedRootLanguages) > 1) {
// get all pages witch the same page_code
              $aPossiblePages = $this->getPageCodeValues((int)self::$oApp->page_code);
// remove all pages from list with not avaliable languages
              $aLangData = \array_intersect_key($aPossiblePages, $aAllowedRootLanguages);
          }
// add Allowed root pages to possible matches
        return \array_merge($aAllowedRootLanguages,$aLangData);
      }

    private function getLangMenuData():? array
    {
        $aTplData = [];
        $aAvailablePages = $this->getPageLangDetails();
        if (\count($aAvailablePages) > 1) {
            foreach ( $aAvailablePages as $aPage)
            {
                $sPageTitle     = $aAvailablePages[$aPage['language']]['page_title'];
                $sTooltip       = $aAvailablePages[$aPage['language']]['tooltip'];
                $sTargetPageUrl = $this->oReg->AppUrl.$this->oReg->PagesDir. \trim($aAvailablePages[$aPage['language']]['link'],'/'). $this->oReg->PageExtension;
                $bShortUrl      = is_readable($this->oReg->AppPath.'short.php');
                $sShortUrl      = $this->oReg->AppUrl.\trim($aAvailablePages[$aPage['language']]['link'],'/').'/';
                $aTplData[]     = [
                    'sIconUrl'         => $this->oReg->AppUrl . 'modules/'
                                        . \basename(dirname(__FILE__)) . '/',
                    'bCurrentLanguage' => (($aPage['language'] == $this->oReg->Language) ? true : false),
                    'sTargetPageUrl'   => ($bShortUrl ? $sShortUrl : $sTargetPageUrl),
                    'sPageTitle'       => $sPageTitle,
                    'sFilename'        => mb_strtolower($aAvailablePages[$aPage['language']]['language']),
                    'sImageType'       => $this->sExtension,
                    'sToolTip'         => (empty($sTooltip) ? $sPageTitle : $sTooltip),
                ];
            } // end foreach
        }
        return $aTplData;
    }

/********************************************************************************************/
//
/********************************************************************************************/

    private function detectIE()
    {
        return \preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT']);
    }

    public function setExtension($sExtension = 'auto')
    {
        if($sExtension == 'auto' || $sExtension == 'svg') {
            $this->sExtension = (($this->detectIE() == true) ? 'svg' : $sExtension);
        } else {
            $this->sExtension = $sExtension;
        }
        return;
    }

    public function getLangMenu()
    {
        return trim($this->getLangMenuTwig ());
    }

    public function updateDefaultPagesCode()
    {
        $retVal  = false;
        $aLangs  = $this->getLanguageInUse();
        $entries = $this->getPageList(0);
// fill page_code with page_id for default_language
        foreach($entries as $page_id=>$val)
        {
            if ($val['language'] == $this->oReg->DefaultLanguage) {
                if (($retVal = $this->updatePageCode((int)$page_id, 'pages', (int)$page_id ))==false){ break;}
            }
        }
        return $retVal;
    }

    public function getPagesDetail(){
        return $this->getLanguageInUse();

    }

} // end of class
