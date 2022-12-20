<?php

/*
 * Copyright (C) 2022 Manuela v.d. Decken <manuela@isteam.de>
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
 * Singleton
 *
 * @category     name
 * @package      Core package
 * @copyright    Manuela v.d. Decken <manuela@isteam.de>
 * @author       Manuela v.d. Decken <manuela@isteam.de>
 * @license      GNU General Public License 3
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 04.03.2022
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types=1);
// declare(encoding = 'UTF-8');

namespace App\traits;

// use source;

trait Singleton
{
    protected static $oInstance = null;

/**
 * get a valid instance of this class
 * @return object
 */
    public static function getInstance()
    {

      if (self::$oInstance === null) {
          self::$oInstance = new static;
      }
      return self::$oInstance;
    } // function

} // trait

