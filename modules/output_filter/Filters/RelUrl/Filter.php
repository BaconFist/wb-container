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

namespace addon\output_filter\Filters\RelUrl;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

class Filter
{
    protected $oReg         = null;
    protected $aSettings    = [];
    protected $aOptions     = [];
    protected $aCfg         = [];

    protected $sAppUrl       = WB_URL;
    protected $sAppPath      = WB_PATH;
    protected $sDomainUrl    = '';
    protected $sDocumentRoot = '';

/**
 * constructor imports default arguments
 * @param \database $oDb
 * @param array $aSettings
 * @param array $aOptions
 */
    public function __construct(WbAdaptor $oReg, array $aSettings, array $aOptions)
    {
        $this->oReg         = $oReg;
        $this->aSettings    = $aSettings;
        $this->aOptions     = $aOptions;
        $this->doInit();
    }

/**
 * initialize this filter
 */
    public function doInit()
    {
        $this->sAppUrl    = $this->oReg->AppUrl;
        $this->sAppPath   = $this->oReg->AppPath;
        \preg_match('/^(?P<domain>.*\:\/\/[^\/]*\/)(?P<instdir>.*)$/s', $this->sAppUrl, $aMatches);
        $this->sDomainUrl = $aMatches['domain'];
        $this->sDocumentRoot = \preg_replace(
            '/^(.*?)'.\preg_quote($aMatches['instdir'], '/').'$/s',
            '$1',
            $this->sAppPath
        );
    }

/**
 * perform the filter on given content string
 * @param string $sContent
 * @return string
 */
    public function execute($sContent)
    {
        $sDomainUrl = $this->sDomainUrl;
        $sDocumentRoot = $this->sDocumentRoot;
        $sRetval = \preg_replace_callback(
            // search for all HTML tags which can contain local URLs
            '/(?P<prefix><[^>]*? (?:href|src|action)\s*=\s*")(?P<url>[^\?\"]+?)(?P<suffix>[^>]*?>)/isU',
            function ($aMatches) use ($sDomainUrl, $sDocumentRoot) {
                // remember original URL
                $sRetval = $aMatches[0];
                // normalize found URL
                $aMatches['url'] = \str_replace('\\', '/', $aMatches['url']);
                // skip <link> by rel 'canonical' or 'alternate'
                if (!\preg_match('/\<link.*?rel\s*=\s*\"(canonical|alternate)\"/is', $aMatches[0])) {
                    // remove possible domain URL and leading / too
                    $sRelFile = \ltrim(\preg_replace('/^'.\preg_quote($sDomainUrl, '/').'/', '', $aMatches['url']), '/');
                    if (
                        !\preg_match('/^(http|ftp)s?\:\/\//s', $sRelFile) && ( // is not a foreign URL
                            \is_readable($sDocumentRoot.$sRelFile) ||    // the local file is readable
                            \preg_match('/(\/$|\/\?)/s', $sRelFile)            // is a local ShortUrl
                        )
                    ) {
                      // return new URL only if referenced file is readable or a URL modified by ShortUrl
                        $sRetval = $aMatches['prefix'].'/'.$sRelFile.$aMatches['suffix'];
                    }
                }
                return $sRetval;
            },
            $sContent
        );
        return $sRetval;
    }
}
