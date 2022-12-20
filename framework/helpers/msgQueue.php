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
namespace bin\helpers;

/**
 * msgQueue.php
 *
 * @category     Core
 * @package      Core_Helpers
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      http://www.gnu.org/licenses/gpl.html   GPL License
 * @version      0.0.1
 * @revision     $Revision: 2131 $
 * @link         $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/branches/2.8.x/wb/framework/msgQueue.php $
 * @lastmodified $Date: 2015-06-23 13:38:43 +0200 (Di, 23 Jun 2015) $
 * @since        File available since 09.05.2015
 * @description  provides a message queue to display system messages on each screens
 */
class msgQueue {

/** define type of retval */
    const RETVAL_ARRAY  = 0;
    const RETVAL_STRING = 1; // (default)
/** */
    const LOG      = 0;
    const ERROR    = 1; // (default)
    const SUCCESS  = 2;
    const OK       = 2;
    const WARN     = 4;
    const SHOW_ALL = 0;
    const ALL      = 0;
/**  */
    private static $_instance;
/**  */
    private $aLogs    = [];
/**
 * constructor
 */
    protected function __construct() {
        $this->aLogs = array(
            self::ERROR   => [],
            self::SUCCESS => [],
            self::WARN    => [],
            self::LOG     => []
        );
    }
/** disable cloning */
    private function __clone() { }
/**
 * get handle for active instance
 */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
/**
 * push new message in queue
 * @param string $sMessage
 * @param int $iStatus (default: self::FAIL)
 */
    public static function add($sMessage = '', $iStatus = self::ERROR)
    {
        $iStatus =
            ($iStatus === true ? self::OK : ($iStatus === false ? self::ERROR : $iStatus)) ?: self::ERROR;
        self::getInstance()->aLogs[self::LOG][] = array('status'=>self::WARN, 'msg'=>$sMessage);
        self::getInstance()->aLogs[$iStatus][]  = $sMessage;
    }
/**
 * clear error lists
 * @param int $iStatus defiones which list should be cleared (one or all)
 * @return void
 */
    public static function clear($iStatus = self::ALL)
    {
        if ($iStatus == self::ALL) {
            self::getInstance()->aLogs = array(
                self::ERROR   => [],
                self::SUCCESS => [],
                self::WARN    => [],
                self::LOG     => []
            );
        } else {
            if (isset(self::getInstance()->aLogs[$iStatus])) {
                self::getInstance()->aLogs[$iStatus] = [];
            }
        }
    }
/**
 *
 * @param int $iStatus (default: self::ALL)
 * @return bool
 */
    public static function isEmpty($iStatus = self::ALL)
    {
        if ($iStatus == self::ALL) {
            return (sizeof(self::getInstance()->aLogs[self::LOG]) == 0);
        } else {
            return (isset(self::getInstance()->aLogs[$iStatus]) == false);
        }
    }
/**
 * returns selected kind of messages
 * @param integer $iStatus  which messages
 * @param integer $iRetvalType  return as string or array(default)
 * @return mixed  string|array
 * @description  msgQueue::SHOW_ALL returns a multidimensional array as  $x[Type][Messages]
 *                all others return a string of messages concated by \n
 */
    public static function getMessages($iStatus = self::ERROR, $iRetvalType = self::RETVAL_ARRAY)
    {
        $aRetval = [];
        if ($iStatus == self::SHOW_ALL) {
            return self::getInstance()->aLogs[self::LOG];
        } else {
            if (isset(self::getInstance()->aLogs[$iStatus])) {
                foreach (self::getInstance()->aLogs[$iStatus] as $aItem) {
                    $aRetval[] = $aItem;
                }
            }
        }
        return ($iRetvalType == self::RETVAL_STRING ? implode("\n", $aRetval) : $aRetval);
    }
/**
 *
 * @param int $iType
 * @return mixed
 * @deprecated set deprecated since 2.8.4 and removed in next version
 */
    public static function getError($iType = self::RETVAL_STRING)
    {
//        trigger_error('Deprecated function call: '.__CLASS__.'::'.__METHOD__, E_USER_DEPRECATED);
        return self::getMessages(self::ERROR, $iType);
    }
/**
 *
 * @param int $iType
 * @return mixed
 * @deprecated set deprecated since 2.8.4 and removed in next version
 */
    public static function getSuccess($iType = self::RETVAL_STRING)
    {
//        trigger_error('Deprecated function call: '.__CLASS__.'::'.__METHOD__, E_USER_DEPRECATED);
        return self::getMessages(self::OK, $iType);
}
/**
 *
 * @return array
 * @deprecated set deprecated since 2.8.4 and removed in next version
 */
    public static function getLoglist()
    {
//        trigger_error('Deprecated function call: '.__CLASS__.'::'.__METHOD__, E_USER_DEPRECATED);
        return self::getMessages(self::LOG, self::RETVAL_ARRAY);
    }

} // end of class msgQueue
