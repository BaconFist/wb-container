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
 * Description of RelUrlFilter
 *
 * @package      Addon_OutputFilter
 * @subpackage   RelUrl
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: Filter.php 17 2020-08-30 05:31:32Z Manuela $
 * @since        File available since 31.10.2017
 * @deprecated   no / since 0000/00/00
 * @description  transform all full qualified local URLs into relative URLs
 *               this filter touches FQURLs only!
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace addon\output_filter\Filters\CleanUp;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use addon\output_filter\Filters\FilterAbstract;

class Filter extends FilterAbstract
{

    protected $oReg         = null;
    protected $aSettings    = [];
    protected $aOptions     = [];
    protected $aCfg         = [];

    /**
     * constructor imports default arguments
     * @param \database $oDb
     * @param array $aSettings
     * @param array $aOptions
    public function __construct(WbAdaptor $oReg, array $aSettings, array $aOptions = [])
    {
        $this->oReg      = $oReg;
        $this->aSettings = $aSettings;
        $this->aOptions  = $aOptions;
        $this->doInit();
    }
     */

/**
 * initialize this filter
 */
    public function doInit(): void
    {
    // do nothing
        // dosn't preg_replace
        $this->aSettings['patterns'] = ['/\[@wblink/', '/\[@\[/'];
        $this->aSettings['replacements'] = ['[wblink', '[['];
    // these definitions will be read from settings table in next version!!
/*
        $this->aSettings['patterns'] = '/\\[@/';
        $this->aSettings['replacements'] = '[';
*/
    }

/**
 * perform the filter on given content string
 * @param string $sContent
 * @return string
 */
      public function execute($sContent): string
      {
          $sContentSave = $sContent;
          $sContent = \preg_replace(
              $this->aSettings['patterns'],
              $this->aSettings['replacements'],
              $sContent
          );
          return ($sContent ?? $_sContentSave);
      }
}// end of class
