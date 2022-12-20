<?php

/*
 * Copyright (C) 2020 Manuela von der Decken <manuela@isteam.de>
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
 * SessionGarbage
 *
 * @category     Core
 * @package      Helpers package
 * @copyright    Manuela von der Decken <manuela@isteam.de>
 * @author       Manuela von der Decken <manuela@isteam.de>
 * @license      GNU General Public License 2
 * @version      0.0.1 $Rev: 60 $
 * @revision     $Id: SessionGarbage.php 60 2020-11-08 12:29:05Z Manuela $
 * @since        File available since 07.11.2020
 * @deprecated   no / since 0000/00/00
 * @description  triggers session_gc() manually
 *               Condition: The session must already have started
 */
declare(strict_types=1);
//declare(encoding='UTF-8');

namespace bin\helpers;

// use source;

class SessionGarbage {

    private const INTERVAL = 1200; // seconds between executes (default: once a day= 84000)

    static public function execute()
    {
        $sSemaphore = dirname(dirname(__DIR__)).'/var/session_gc.sem';
        $sMsg = 'unable to write [ ../var/session_gc.sem ]';
        if (\file_exists($sSemaphore)) {
            if (\filemtime($sSemaphore) < (\time()-self::INTERVAL)) {
                \session_gc();
                if (! \touch($sSemaphore)) {
                    \trigger_error($sMsg, E_USER_WARNING);
                }
            }
        } else {
            if (! \touch($sSemaphore)) {
                \trigger_error($sMsg, E_USER_WARNING);
            }
        }
    }
}
