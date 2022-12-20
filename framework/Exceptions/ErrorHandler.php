<?php

/*
 * Copyright (C) 2018 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
/**
 * Description of ErrorHandler
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: ErrorHandler.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 28.06.2018
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin\Exceptions;

// use

/**
 * short description of class
 */
class ErrorHandler
{
    protected static $sAppPath = '';
    protected static $sVarDir  = 'var/';
    protected static $sLogDir  = 'log/';
    protected static $sLogFile = 'php_error.log.php';

/**
 *
 * @return string
 */
    public static function getLogFile()
    {
        return self::$sAppPath.self::$sVarDir.self::$sLogDir.self::$sLogFile;
    }
    public static function setAppPath($sAppPath)
    {
        self::$sAppPath = rtrim(str_replace('\\', '/', $sAppPath), '/').'/';
        $sErrorLogPath = self::$sVarDir.self::$sLogDir;
        $sErrorLogFile = $sErrorLogPath.self::$sLogFile;
        if (!\is_writeable(self::$sAppPath.$sErrorLogPath)) {
            if (\file_exists(self::$sAppPath.$sErrorLogPath)) {
                throw new \Exception('not writeable logfolder \''.\str_replace(\dirname(\dirname(__DIR__)), '',$sErrorLogPath));
            }
            if (!\mkdir(self::$sAppPath.$sErrorLogPath, 0777, true)) {
                throw new \Exception('unable to create logfolder \''.\str_replace(\dirname(\dirname(__DIR__)), '',$sErrorLogPath));
            }
            $iDirPermissions = (\fileperms(self::$sAppPath.self::$sVarDir) & 0777);
            $aVarDirs = \preg_split('/\//', self::$sVarDir, 0, \PREG_SPLIT_NO_EMPTY);
            $sErrorLogSubDirs = self::$sVarDir;
            foreach ($aVarDirs as $sDir) {
                $sErrorLogSubDirs .= $sDir.'/';
                if (is_dir($sErrorLogSubDirs) && !\chmod(self::$sAppPath.$sErrorLogSubDirs , $iDirPermissions)) {
                    throw new \Exception('unable set rights to logfolder \''.\str_replace(\dirname(\dirname(__DIR__)), '',$sErrorLogSubDirs).'\'');
                }
            }
        }

        if (!\file_exists(self::$sAppPath.$sErrorLogFile)) {
            $sTmp = '<?php header($_SERVER[\'SERVER_PROTOCOL\'].\' 404 Not Found\');echo \'404 Not Found\'; flush(); exit; ?>'
                  . 'created: ['.\date('r').']'.PHP_EOL;
            if (false === \file_put_contents(self::$sAppPath.$sErrorLogFile, $sTmp)) {
                throw new \Exception('unable to create logfile \''.self::$sVarDir.self::$sLogDir.self::$sLogFile.'\'');
            }
        }
    }

/**
 *
 */
    public static function handler($iErrorCode, $sErrorText, $sErrorFile, $iErrorLine)
    {
        $bRetval = false;
        $iErrorReporting = \error_reporting();
/**
 *
 * we need to detect if the error was suppressed with @,
 * so as to prevent certain actions from occurring in the handler.
 * Since the error is suppressed, there should be no output.
 * this is backwards compatible. since before PHP 8, error_reporting() returns 0
 * when error suppression is enabled, this will work exactly the same.
 * @link https://www.devbanana.me/php8/2021/08/04/changes-to-error-suppression-in-php-8.html
 */
        if (!(\error_reporting() & $iErrorCode)) {
            // Do nothing
//echo \nl2br(\sprintf("---- [%04d] Error Code %s \n",__LINE__,$iErrorCode));
            return false;
        }
        if ((\defined('ER_LEVEL') && (ER_LEVEL == -1)) && ($iErrorReporting==0)){
          $iErrorReporting = $iErrorCode;
        }
        if ($iErrorReporting && \ini_get('log_errors') != 0) {
            $sErrorLogFile = \ini_get ('error_log');
            if (\is_writeable($sErrorLogFile)) {
                $sErrorType =  E_NOTICE; // default E_NOTICE
                $aErrors = [
                    E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
                    E_USER_NOTICE       => 'E_USER_NOTICE',
                    E_USER_WARNING      => 'E_USER_WARNING',
                    E_DEPRECATED        => 'E_DEPRECATED',
                    E_ERROR             => 'E_ERROR',
                    E_NOTICE            => 'E_NOTICE',
                    E_PARSE             => 'E_PARSE',
                    E_WARNING           => 'E_WARNING',
                    E_CORE_WARNING      => 'E_CORE_WARNING',
                    E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
                    E_STRICT            => 'E_STRICT',
                    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                ];
                if (\array_key_exists($iErrorCode, $aErrors)) {
                    $sErrorType = $aErrors[$iErrorCode];
                    $bRetval = true;
                }
                $aBt= \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $x = \sizeof($aBt) -1;
                $iSize = (($x < 0) ? 1 : (($x <= 2) ? $x : 1));

                $sFile = \str_replace(\dirname(__DIR__,2), '', ($aBt[$iSize]['file']) ?? '');
                \date_default_timezone_set('UTC');
                $sEntry = \date('r').' '.'['.$sErrorType.'] '.\str_replace(\dirname(__DIR__,2), '', $sErrorFile).':['.$iErrorLine.'] '
                        . ' from '.$sFile.':['.($aBt[$iSize]['line'] ?? '').'] '
                        . (isset($aBt[$iSize]['class']) ? $aBt[$iSize]['class'].$aBt[$iSize]['type'] : '').$aBt[$iSize]['function'].' '
                        . '"'.$sErrorText.'"'."\n";
                \file_put_contents($sErrorLogFile, $sEntry, FILE_APPEND);
            }
        }
//        return $bRetval;
    } // end of function handler()

}
