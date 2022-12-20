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
/** Description of class Password.php
 *
 * @package      Core_Password
 * @copyright    2018 Manuela v.d.Decken <manuela@ISTMZL01>
 * @author       Manuela v.d.Decken <manuela@ISTMZL01>
 * @license      GNU General Public License 2.0
 * @version      1.0
 * @revision     $Id: Password.php 65 2020-11-19 05:30:20Z Manuela $
 * @lastmodified $Date: 2020-11-19 06:30:20 +0100 (Do, 19. Nov 2020) $
 * @since        File available since 2018-04-12
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */

namespace src\Security;

class Password
{

    private static $oDb        = null;
    private static $sTable     = '';
    private static $sFieldId   = '';
    private static $sFieldName = '';
    private static $sFieldPass = '';

/**
 * constructor
 */
    private function __construct() { ; }
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
        string $sTable,
        string $sFieldId,
        string $sFieldName,
        string $sFieldPass
    )
    {
        self::$oDb        = $oDb;
        self::$sTable     = $oDb->getTablePrefix().$sTable;
        self::$sFieldId   = \trim($sFieldId, '`');
        self::$sFieldName = \trim($sFieldName, '`');
        self::$sFieldPass = \trim($sFieldPass, '`');
    }

/**
 *
 * @param mixed $mUser  loginname | user_id
 * @param string $sPassword  any printable char
 * @return bool
 * @throws \RuntimeException
 * @description compares given password against the stored hash.
 *              if the hashing algorithm is outdatet, it will be upgraded automaticaly
 */
    public static function verify($mUser, string $sPassword)
    {
        if (\is_null(self::oDb)) {
            throw new \RuntimeException('class ['.__CLASS__.'] not initialized!');
        }
        try {
            $iUserId = self::getId($mUser);
            $sStoredHash = self::getHash($iUserId);
// --- this part is for updating period only -------------------------------- //
            if ( // if there is a valid, old md5 hash                         //
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
                    self::saveHash($iUserId, $sHash);
                }
                $bRetval = true;
            }
        } catch(\InvalidArgumentException $e) {
            \trigger_error($e->getMessage(), E_USER_WARNING);
        }
        return ($bRetval ?? false);
    }

/**
 *
 * @param mixed $mUser  loginname | user_id
 * @param string $sPasswordNew  any printable char
 * @param string $sPasswordOld  any printable char
 * @throws \RuntimeException
 * @description
 */
    public static function change($mUser, string $sPasswordNew, string $sPasswordOld = '')
    {
        if (\is_null(self::oDb)) {
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
 * in case of system upgrades
 * upgrade the table structure for longer hashes
 * @return bool
 */
    public static function upgradeTable()
    {
        if (\is_null(self::oDb)) {
            throw new \RuntimeException('class ['.__CLASS__.'] not initialized!');
        }
        $aDef = [
            'username' => [
                'COLUMN_NAME'=>'username',
                'COLUMN_TYPE'=>'varchar(255)',
                'CHARACTER_SET_NAME'=>'utf8mb4',
                'COLLATION_NAME'=>'utf8mb4_unicode_ci',
            ],
            'password' => [
                'COLUMN_NAME'=>'password',
                'COLUMN_TYPE'=>'varchar(255)',
                'CHARACTER_SET_NAME'=>'utf8mb4',
                'COLLATION_NAME'=>'utf8mb4_unicode_ci',
            ],
        ];
        $sSql = 'SELECT `COLUMN_NAME`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME` '
              . 'FROM INFORMATION_SCHEMA.COLUMNS '
              . 'WHERE TABLE_SCHEMA = \''.self::$oDb->getDbName().'\' AND '
              .       'TABLE_NAME = \''.self::$sTable.'\' AND '
              .       '`COLUMN_NAME` IN(\'username\',\'password\')';
        if (($oRs = self::$oDb->query($sSql))) {
            $aColumns = $oRs->fetchAll(\MYSQLI_ASSOC);
            foreach ($aColumns as $aColumn) {
                $aFields = $aDef[$aColumn['COLUMN_NAME']];
                if (! (
                    $aColumn['COLUMN_TYPE']        === $aFields['COLUMN_TYPE'] &&
                    $aColumn['CHARACTER_SET_NAME'] === $aFields['CHARACTER_SET_NAME'] &&
                    $aColumn['COLLATION_NAME']     === $aFields['COLLATION_NAME']
                )) {
                    $sql = 'ALTER TABLE `'.self::$sTable.'` CHANGE `'
                         . $aFields['COLUMN_NAME'].'` `'.$aFields['COLUMN_NAME'].'` '
                         . $aFields['COLUMN_TYPE'].' CHARACTER SET '
                         . $aFields['CHARACTER_SET_NAME'].' COLLATE '
                         . $aFields['COLLATION_NAME'].' NOT NULL DEFAULT \'\';';
                    self::$oDb->query($sql);
                }
            }
        }
        return ((bool) self::$oDb->query($sql));
    }
/* ------------------------------------------------------------------------------------ */
/**
 *
 * @param mixed $mUser
 * @return int
 * @throws \InvalidArgumentException
 */
    private static function getId($mUser): int
    {
        $sql = 'SELECT `'.self::$sFieldId.'` '
             . 'FROM `'.self::$sTable.'` ';
        if (\is_int($mUser)) {
            $sql .= 'WHERE `'.self::$sFieldId.'`='.(int) $mUser;
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
        if (! self::$oDb->query($sql)) {
            throw new \RuntimeException('save password failed!');
        }
    }

}
