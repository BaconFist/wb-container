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
 * Description of AppException
 *
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: AppException.php 22 2018-09-09 15:17:39Z Luisehahne $
 * @since        File available since 19.06.2018
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

/**
 * short description of class
 */
class AppException extends \Exception
{
    public function __toString() {
        $file = str_replace(dirname(dirname(__FILE__)), '', $this->getFile());
        if (defined('DEBUG')&& DEBUG) {
            $trace = $this->getTrace();
            $result = 'Exception: "'.$this->getMessage().'" @ ';
            if($trace[0]['class'] != '') {
              $result .= $trace[0]['class'].'->';
            }
            $result .= $trace[0]['function'].'(); in'.$file.'<br />'."\n";
            if($GLOBALS['database']->get_error()) {
                $result .= $GLOBALS['database']->get_error().': '.$GLOBALS['database']->get_error().'<br />'."\n";
            }
            $result .= '<pre>'."\n";
            $result .= print_r($trace, true)."\n";
            $result .= '</pre>'."\n";
        }else {
            $result = 'Exception: "'.$this->getMessage().'" >> Exception detected in: ['.$file.']<br />'."\n";
        }
        return $result;
    }
}
