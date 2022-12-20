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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of ExceptionHandler
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: ExceptionHandler.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 19.06.2018
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace bin\Exceptions;

//use bin\requester\HttpRequester;

/**
 * short description of class ExceptionHandler
 */
class ExceptionHandler
{
    protected $sProtokol = 'HTTP/1.1';
//    public function __construct()
//    {
////        $this->sProtokol = HttpRequester::getInstance()->getServerVar('SERVER_PROTOCOL');
//        \set_exception_handler([$this, 'handler']);
//    }

    public static function handler($ex) {
        // hide server internals from filename where the exception was thrown
        $sRoot = \dirname(\dirname(__DIR__));
        $sFile = \str_replace($sRoot, '', $ex->getFile());
        // select some exceptions for special handling
        if ($ex instanceof \IllegalFileException) {
            $sProtocol = \filter_input(\INPUT_SERVER, 'SERVER_PROTOCOL');
            $sResponse = $sProtocol.' 404 Not Found';
            \header($sResponse);
            echo $ex;
        } elseif ($ex instanceof \AppException) {
            if (defined('DEBUG')&& DEBUG) {
                echo 'Exception: "'.$ex->getMessage().'" @ ';
                $aTrace = $ex->getTrace();
                $iTrace = sizeof($aTrace);
                $i = 0;
                for($i;$i<$iTrace;$i++){
                    $aTrace[$i]['file'] = str_replace($sRoot, '',$aTrace[$i]['file']);
                    $aTrace[$i]['args']['0'] = str_replace($sRoot, '',$aTrace[$i]['args']['0']);
                    if(!empty($aTrace[$i]['class'])) {
                        echo $aTrace[$i]['class'].'->';
                    }
                    echo $aTrace[$i]['function'].'(); in'.$sFile.'<br />'."\n";
                    echo '<pre>'."\n";
                    print_r($aTrace[$i] )."\n";
                    echo '</pre>'."\n";
              }
            } else {
                echo 'Exception: "'.$ex->getMessage().'" >> Exception detected in: ['.$sFile.']<br />'."\n";
            }
        } else {
        // default exception handling
            $out  = 'There was an uncatched exception'."\n";
            $out .= $ex->getMessage()."\n";
            $out .= 'in line ('.$ex->getLine().') of ('.$sFile.'):'."\n";
            echo \nl2br($out);
        }
    }
}

