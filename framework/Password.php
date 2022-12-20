<?php
/*
 * Password.php
 *
 * Copyright 2018 Manuela v.d.Decken <manuela@ISTMZL01>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA or see <http://www.gnu.org/licenses/>
 *
 */

namespace bin;

// use ???;

/** Description of class Password.php
 *
 * @package      Core_Password
 * @copyright    2018 Manuela v.d.Decken <manuela@ISTMZL01>
 * @author       Manuela v.d.Decken <manuela@ISTMZL01>
 * @license      GNU General Public License 2.0
 * @version      1.0
 * @revision     $Id: Password.php 22 2018-09-09 15:17:39Z Luisehahne $
 * @lastmodified $Date: 2018-09-09 17:17:39 +0200 (So, 09. Sep 2018) $
 * @since        File available since 2018-04-12
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */

class Password
{

    private static $oDb        = null;
    private static $sTable     = '';
    private static $sFieldId   = 0;
    private static $sFieldName = '';
    private static $sFieldPass = '';

/**
 * constructor
 * @param
 * @return void
 */
/**
 *
 * @param database $oDb
 * @param string $sTable
 * @param string $sFieldId
 * @param string $sFieldName
 * @param string $sFieldPass
 */
    public static function init(
        \database $oDb,
        $sTable,
        $sFieldId,
        $sFieldName,
        $sFieldPass
    )
    {
        self::$oDb        = $oDb;
        self::$sTable     = $oDb->TablePrefix.$sTable;
        self::$sFieldId   = $sFieldId;
        self::$sFieldName = $sFieldName;
        self::$sFieldPass = $sFieldPass;
    }

/**
 *
 * @param mixed $mUser  loginname | user_id
 * @param string $sPassword  any printable char
 * @throws \RuntimeException
 * @description compares given password against the stored hash.
 *              if the hashing algorithm is outdatet, it will be upgraded automaticaly
 */
    public static function verify($mUser, $sPassword)
    {
        if (\is_null($this->oDb)) {
            throw new \RuntimeException('class ['.__CLASS__.'] not initialized!');
        }
        try {
            $bRetval = false;
            $iUserId = self::getId($mUser);
            $sStoredHash = self::getHash($iUserId);
// --- this part is for updating period only -------------------------------- //
            if (                                                              //
                // if there is a valid, old md5 hash                          //
                \preg_match('/^[0-9a-f]{32}$/i', $sStoredHash) &&             //
                $sStoredHash === md5($sPassword)                              //
            ) {                                                               //
                // rehash the password                                        //
                $sHash = \password_hash($sPassword, \PASSWORD_DEFAULT);       //
                self::storeHash($iUserId, $sHash);                            //
            }                                                                 //
// -------------------------------------------------------------------------- //
            if (\password_verify($sPassword, $sStoredHash)) {
                // Passwort stimmt!
                if (\password_needs_rehash($sStoredHash, \PASSWORD_DEFAULT)) {
                    // Passwort neu hashen
                    $sHash = \password_hash($sPassword, \PASSWORD_DEFAULT);
                    self::storeHash($iUserId, $sHash);
                }
                $bRetval = true;
            }
        } catch(\InvalidArgumentException $e) {
            $bRetval = false;
        }
    }

/**
 *
 * @param mixed $mUser  loginname | user_id
 * @param string $sPasswordNew  any printable char
 * @param string $sPasswordOld  any printable char
 * @throws \RuntimeException
 * @description
 */
    public static function change($mUser, $sPasswordNew, $sPasswordOld = '')
    {
        if (\is_null($this->oDb)) {
            throw new \RuntimeException('class ['.__CLASS__.'] not initialized!');
        }
        try {
            $bRetval = false;
            $iUserId = self::getId($mUser);
            $sStoredHash = self::getHash($iUserId);
            if ($sStoredHash === '' && $sPasswordOld === '') {
                self::saveHash($iUserId, \password_hash($sPasswordNew, \PASSWORD_DEFAULT));
            } else {
                if (self::verify($iUserId, $sPasswordOld)) {
                    self::saveHash($iUserId, \password_hash($sPasswordNew, \PASSWORD_DEFAULT));
                }
            }
        } catch(\InvalidArgumentException $e) {
            $bRetval = false;
        }
        return $bRetval;
    }

/**
 * upgrade table structure for longer hashes
 * @return bool
 */
    public static function upgrade()
    {
        $sql = 'ALTER TABLE `'.self::$sTable.'` CHANGE `'.self::$sFieldPass.'` `'.self::$sFieldPass.'` '
             . 'VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\';';
        return ((bool) self::$oDb->query($sql));
    }
/* ------------------------------------------------------------------------------------ */
/**
 *
 * @param mixed $mUser
 * @return int
 * @throws \InvalidArgumentException
 */
    private static function getId($mUser)
    {
        $sql = 'SELECT `'.self::$sFieldId.'` '
             . 'FROM `'.self::$sTable.'` ';
        if (\is_int($mUser)) {
            $sql .= 'WHERE `'.self::$sFieldId.'`='.$mUser;
        } else {
            $sql .= 'WHERE `'.self::$sFieldName.'`=\''.self::$oDb->escapeString((string) $mUser).'\'';
        }
        if (!($iUserId = self::$oDb->get_one($sql))) {
            throw new \InvalidArgumentException('user not found');
        }
        return $iUserId;
    }

/**
 *
 * @param int $iUserId
 * @return string
 */
    private static function getHash($iUserId)
    {
        $sql = 'SELECT `'.self::$sFieldPass.'` '
             . 'FROM `'.self::$sTable.'` '
             . 'WHERE `'.self::$sFieldId.'`='.$iUserId;
        $sHash = \trim(self::$oDb->get_one($sql));
        return $sHash;
    }

/**
 *
 * @param int $iUserId
 * @param string $sHash
 * @throws \RuntimeException
 */
    private static function saveHash($iUserId, $sHash)
    {
        $sql = 'UPDATE `'.self::$sTable.'` '
             . 'SET `'.self::$sFieldPass.'`=\''.self::$oDb->escapeString($sHash).'\' '
             . 'WHERE `'.self::$sFieldId.'`='.$iUserId;
        if (!self::$oDb->query($sql)) {
            throw new \RuntimeException('save password failed!');
        }
    }

}
