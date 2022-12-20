<?php

namespace dispatch;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};


class Dispatcher {

/** holds the active singleton instance */
    private static $oInstance = null;
/** @var object instance of the WbAdaptor object */
//    protected $oReg   = null;
/** @var object instance of the application object */
//    private static $oApp     = null;
/** @var object instance of the database object */
//    private $oDb      = null;
/** @var object instance of the translate object */
//    private $oTrans    = null;
/** @var array holds several default values  */
    private $aConfig     = [];


/**
 * constructor used to import some application constants and objects
 */
    public function __construct(array $aSettings=[]){
        $this->init($aSettings);
    }

    private function init(array $aSettings){
        $oReg = WbAdaptor::getInstance();
        $this->aConfig['oReg'] = $oReg;
        $this->aConfig['oRequest'] = $oReg->getRequester();
        $this->aConfig['oTrans'] = $oReg->getTranslate();
        $this->aConfig['oDb'] = $oReg->getDatabase();
        $this->addProperties($aSettings);
    }


/**
 * get a valid instance of this class
 * @param string $sIdentifier selector for several different instances
 * @return WbDatabase object
 */
    final public static function getInstance($sIdentifier = 'core')
    {
        if (!isset(self::$_oInstances[$sIdentifier])) {
            $c = __CLASS__;
            $oInstance = new $c;
            $oInstance->sInstanceIdentifier = $sIdentifier;
            self::$_oInstances[$sIdentifier] = $oInstance;
        }
        return self::$_oInstances[$sIdentifier];
    }

/**
 * disconnect and kills an existing instance
 * @param string $sIdentifier selector for instance to kill
 */
    final public static function killInstance($sIdentifier)
    {
        if($sIdentifier != 'core') {
            if (isset(self::$_oInstances[$sIdentifier])) {
                self::$_oInstances[$sIdentifier]->disconnect();
                unset(self::$_oInstances[$sIdentifier]);
            }
        }
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

/**
 * destructor
 */
    final public function __destruct()
    {
        $this->oTrans->disableAddon($this->sModuleName.'\\'.$this->sAddonName);
        $this->aConfig = [];
    }

/* ------------------------------------------------------------------------------------ */
    public function set($name, $value = '')
    {
        $this->aConfig[$name] = $value;
    }

    public function get($name)
    {
        return $this->$name;
    }
/* ------------------------------------------------------------------------------------ */
//
/* ------------------------------------------------------------------------------------ */
    private function addProperties(array $aSettings){
        foreach ($aSettings as $key=>$value){
            switch ($key):
                default:
                    $this->aConfig[$key] = $value;
                    break;
            endswitch;
        }
    }

    public function setProperties($mName=null, $value = ''){
        if (is_array($mName)){
          $this->addProperties($mName);
        } else {
          $this->set($mName, $value);
        }
    }

    public function getBackLinks($sFolder='')
    {
        $sSectionIdPrefix = (defined( 'SEC_ANCHOR' ) && ( SEC_ANCHOR != 'none' )  ? '#'.SEC_ANCHOR.$this->section_id : '' );
        $sBacklink        = $this->oReg->AcpUrl.'pages/modify.php?page_id='.(int)$this->page_id .$sSectionIdPrefix;
        $sPagelink        = $this->oReg->AcpUrl.'pages/index.php';
        $sAddonBackUrl    = $this->oReg->AppUrl.$sFolder;
        return compact(array_keys(get_defined_vars()));
    }

    private function getTemplatePaths($sAddonPath,$sAddonUrl){
        $sAddonThemeUrl     = $sAddonUrl.'themes/default/';
        $sAddonTemplateUrl  = $sAddonUrl.'themes/default/';
        $sAddonThemePath    = $sAddonPath.'templates/default/';
        $sAddonTemplatePath = $sAddonPath.'templates/default/';
        return compact(array_keys(get_defined_vars()));
    }

    private function getDeveloperVars($sAddonPath){
        // Only for development for pretty mysql dump
        $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
        // Only for development prevent secure token check,
        $sSecureToken = !is_readable($sAddonPath.'/.setToken');
        $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
        return compact(array_keys(get_defined_vars()));
    }

    public function getInitializePaths($sAddonDir='') : array
    {
        $sCallingScript = ($this->oReg->App->getCallingScript() ?? '');
        $sCallingDir    = \dirname($sCallingScript);
        $sAddonName     = basename($sCallingDir);
        $sAddonPath     = (empty($sAddonDir) ? $this->oReg->DocumentRoot.\ltrim($sCallingDir,'/') : $sAddonDir);
        $sAddonDir      = $sAddonPath;
        $sModulesPath   = \dirname($sAddonPath).'/';
        $sModuleName    = basename($sModulesPath);
        $sAddonRel      = ''.$sModuleName.'/'.$sAddonName;
        $sAddonUrl      = $this->oReg->AppUrl.$sAddonRel.'/';
        $sDumpPathname = $sModuleName.'/'.\basename($sCallingScript);
        $this->set('sAddonName', $sAddonName);
        $this->set('sModuleName', $sModuleName);
        extract($this->getTemplatePaths($sAddonPath,$sAddonUrl));
        $this->oTrans->enableAddon($sModuleName.'\\'.$sAddonName);
        // Only for development for pretty mysql dump
        extract($this->getDeveloperVars($sAddonPath));
        if (empty($sAddonDir)){unset($sAddonDir);}
        $aVariables   = compact(array_keys(get_defined_vars()));
        return ($aVariables);
    }

    public function getFieldId($FieldGroup){
        return $this->get($FieldGroup);
    }

    public function getSqlRecord($Table,$FieldGroup,$FieldId,$aDefault=[])
    {
        $retVal = null;
        $sql  = 'SELECT * FROM `'.$this->oReg->TablePrefix.$Table.'` '
              . 'WHERE `'.$FieldGroup.'`='.$FieldId.' ';
        if ($oRes = $this->oDb->query($sql)){
            if (($oRes->numRows()==1)){
                $aRecord = $oRes->fetchRow(MYSQLI_ASSOC);
            } else {
                $aRecord = $oRes->fetchAll(MYSQLI_ASSOC);
            }
            $retVal =(empty($aRecord) ? $aDefault : $aRecord);
        } else {
            $retVal = sprintf("%s\n",$this->oDb->get_error());
        }
        $this->set($FieldGroup,$FieldId);
        return $retVal;
    }

    public function countRowsSql($Table, $FieldGroup, $FieldId){
        $sSql = 'SELECT COUNT(*) FROM `'.$this->oReg->TablePrefix.$Table.'` '
              . 'WHERE `section_id` = '.$this->section_id.' '
              .   'AND `'.$FieldGroup.'`= \''.$this->oDb->escapeString($FieldId).'\' ';
        $this->set($FieldGroup,$FieldId);
        return ($this->oDb->get_one($sSql) ?? false);
    }

    public function replaceIntoSql($Table, $FieldGroup, $FieldId,$sqlBodySet)
    {
        if (!($iNumRow = $this->countRowsSql($Table, $FieldGroup, $FieldId))){
            $sqlType    = 'INSERT INTO `'.$this->oReg->TablePrefix.$Table.'` SET ';
            $sSqlWhere  = '';
            $sAction ='insert';
        } else {
            $sqlType    = 'UPDATE `'.$this->oReg->TablePrefix.$Table.'` SET ';
            $sSqlWhere  = 'WHERE `'.$FieldGroup.'`='.$FieldId.' ';
            $sAction ='update';
        }
        $sSql = $sqlType.$sqlBodySet.$sSqlWhere;
        if (($retVal = $this->oDb->query($sSql))){
            $iLastKey = (($sAction=='insert') ? $this->oDb->getLastInsertId() : $FieldId);
            $this->set($FieldGroup,$iLastKey);
            $oPos = new \order($this->oReg->TablePrefix.$Table, 'position', $FieldGroup, 'section_id');
            $oPos->clean($this->section_id);
        }
        return $retVal;
    }

    public function callAdminWrapperVars($bHeader=false, $bInfo = true, $bModify=false) : array
    {
        // print with or without header
        $admin_header=$bHeader; //
        // Workout if the developer wants to show the info banner
        $print_info_banner = $bInfo; // true/false
        // Tells script to update when this page was last updated
        $update_when_modified = $bModify;
        // Include WB admin wrapper script to sanitize page_id and section_id, print SectionInfoLine
        $aVariables = compact(array_keys(get_defined_vars()));
        return $aVariables;
        }


/**
 * @name Full Pathname
 *
 */
    public function getUniqueNameFromFile($sName)
    {
        $sBaseName = \preg_replace('/^(.*?)(\_[0-9]+)?$/', '$1', $this->removeExtension($sName));
        if (is_readable($sName)){
            $sMaxName = $this->removeExtension($sName);
            if (($sMaxName == $sBaseName)) {
                $iCount = \intval(\preg_replace('/^'.$sBaseName.'\_([0-9]+)$/', '$1', $sMaxName));
                $sName = $sBaseName.\sprintf('_%03d', ++$iCount);
            }
        }
        return $sName;
    }

// remove file extension
    public function removeExtension ($sFilename){
        return \preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sFilename);
    }

} // end of class
