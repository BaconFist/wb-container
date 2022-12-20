<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
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
 * Description of PhplibFtan
 *
 * @package      Vendor_Phplib
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: Phplib_FtanTrait.php 22 2018-09-09 15:17:39Z Luisehahne $
 * @since        File available since 11.11.2017
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace App\traits;

/**
 * short description of trait
 */
trait PhplibFtan
{

    public function set_ftan($aFtan){
        $aResult = [
            'FTAN_NAME'  => $aFtan['name'],
            'FTAN_VALUE' => $aFtan['value']
        ];
        if (isset($aFtan['remain'])) { $aResult['FTAN_REMAIN'] = $aFtan['remain']; }
        if (isset($aFtan['previous'])) { $aResult['FTAN_PREVIOUS'] = $aFtan['previous']; }
        $this->set_var($aResult);
    }

   /**
    * Determines how much debugging output Template will produce.
    * This is a bitwise mask of available debug levels:
    * 0 = no debugging
    * 1 = debug variable assignments
    * 2 = debug calls to get variable
    * 4 = debug internals (outputs all function calls with parameters).
    * 8 = debug (outputs all set_block variables calls with parameters).
    *
    * Note: setting $this->debug = true will enable debugging of variable
    * assignments only which is the same behaviour as versions up to release 7.2d.
    *
    * @var       int
    * @access    public
    */
    public function setDebug($iDebug){
        if (in_array($iDebug,['1','2','4','8'])){
            $this->debug = $iDebug;
        }
    }

   /**
    * A hash of strings forming a translation table which translates variable names
    * hidden names of block files containing the variable content.
    * $aHideVarkeys[varname] = "varname";
    *
    * @var       array
    * @access    private
    * @see
    */
    public function setHideVarArray(array $aList){
        $this->aHideVarkeys = $aList;
    }

}
