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
 * ModuleReorgAbstract.php
 *
 * @category     Core
 * @package      Core_ModuleInterface
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2070 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/framework/ModuleReorgAbstract.php $
 * @lastmodified $Date: 2014-01-03 02:21:42 +0100 (Fr, 03. Jan 2014) $
 * @since        File available since 19.10.2013
 * @description  This class provides the basics for modul depending reorganisation classes
 */
abstract class ModuleReorgAbstract {

/** registry object */
    protected $oReg = null;
/** database object */
    protected $oDb  = null;
/** collector of return values */
    protected $aReport = null;
/** set kind of return values */
    protected $bDetailedLog = false;

/** optional config parameter */
    protected $aConfig   = [];

/** show minimal log entries */
    const LOG_SHORT    = 0;
/** show extended log entries */
    const LOG_EXTENDED = 1;

/**
 * execute reorganisation
 * @return boolean
 */
    abstract public function execute();

/**
 * init reorganisation, e.g. to read optional addon config or set some optional properties
 * @return void
 */
    abstract public function init();

/**
 * create sql statement
 * @return string
 */
    abstract protected function makeSql($sAddonName='');

    abstract protected function createPagesDir($sAccessFilesDir);

/**
 * constructor
 * @param int $bDetailedLog  can be LOG_EXTENDED or LOG_SHORT
 */
    final public function __construct($bDetailedLog = self::LOG_SHORT) {
        $this->bDetailedLog = (bool)($bDetailedLog & self::LOG_EXTENDED);
        $this->oDb          = \database::getInstance();
        $this->oReg         = \bin\WbAdaptor::getInstance();
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
       return $this->aConfig[$name] = $value;
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
 * getReport
 * @return array
 * @description a report about the whoole reorganisation<br />
 */
    public function getReport()
    {
        return $this->aReport;
    }

} // end of class ModuleReorgAbstract
