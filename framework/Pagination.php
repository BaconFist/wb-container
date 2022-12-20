<?php
/*
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
 * Unbenannt 3
 *
 * @category     Addons
 * @package      Addons_wrapper
 * @subpackage
 * @copyright    Dietmar Wöllbrink
 * @author       Manuela v.d.Decken >
 * @author       Dietmar Wöllbrink >
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      3.0.1
 * @lastmodified $Date: 2019-01-30 06:08:53 +0100 (Mi, 30. Jan 2019) $
 * @since        File available since 2015-12-17
 * @deprecated   This interface is deprecated since 2015-12-17
 * @description  xyz
 * @created      2016-12-3
 */
namespace bin;

class Pagination {

    protected $oReg    = null;
    protected $oDb     = null;
    protected $oTrans  = null;
    protected $oApp    = null;

    protected $error   = null;
    protected $aConfig = [];
/*
    protected $oTpl      = null;
    protected $Twig      = null;
    protected $loader    = null;
    protected $aTwigData = array();
*/
    public function __construct($aOptions) { $this->init($aOptions);}

    public function __destruct()
    {
        ini_restore('memory_limit');
    }

    public function __isset($name)
    {
        return isset($this->aConfig[$name]);
    }

     public function __set($name, $value)
     {
//         throw new \Exception('Tried to set a readonly or nonexisting property ['.$name.']!!');
         return $this->aConfig[$name] = $value;
     }

    public function __get($name)
    {
        $retval = null;
        if (!$this->__isset($name)) {
            throw new \Exception('Tried to get nonexisting property ['.$name.']');
        }
            $retval = $this->aConfig[$name];
        return $retval;
    }

/*********************************************************************************************/

/*********************************************************************************************/

    public function set($name, $value = '')
    {
        $this->aConfig[$name] = $value;
    }

    public function get($name)
    {
        if (!$this->aConfig[$name]){throw new \Exception('Tried to get nonexisting property ['.$name.']');}
        return $this->aConfig[$name];
    }

    public function removeExtension ($sFilename){
        return preg_replace('#^.*?([^/]*?)\.[^\.]*$#i', '\1', $sFilename);
    }

    public function isError() {
        return sizeof($this->error);
    }

    protected function setError($Message) {
        $this->error[] = $Message;
    }

    protected function getError() {
        return $this->error;
    }

    public function convertToArray ($sList)
    {
        $retVal = $sList;
        if (!is_array($sList)){
            $retVal = preg_split('/[\s,=+\;\:\.\|]+/', $sList, -1, PREG_SPLIT_NO_EMPTY);
        }
        return $retVal;
    }

    protected function init($aOptions)
    {
        $this->oReg   = ($GLOBALS['oReg']?:null);
        $this->oDb    = ($GLOBALS['database']?:null);
        $this->oTrans = ($GLOBALS['MESSAGE']?:null);
        foreach ($aOptions AS $name=>$value){
            switch ($name):
                case 'ItemPerPage':
                case 'CurrentPage':
                case 'TotalValues':
                    $this->aConfig[$name] = $value;
                    break;
                case 'Style':
                    $this->aConfig[$name] = $value.'.css';
                    break;
                default:
                    $this->setError('Tried to set a not allowed property ['.$name.']!!');
            endswitch;
        }
/*
        $this->aConfig['bStart'] = TotalValues > $max;
        $this->aConfig['bCrump'] = TotalValues > $max;
*/
    }

} // end of class


// end of file
