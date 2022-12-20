<?php

/*
 * Copyright (C) 2020 Manuela v.d.Decken <manuela@isteam.de>
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
 * W3CssFilter
 *
 * @category     Addon_OutputFilter
 * @package      W3Css
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 28.08.2020
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */

declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace addon\output_filter\Filters\W3Css;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

class Filter
{

    protected $sW3Css       = "\n".'<link rel="stylesheet" href="%sinclude/assets/w3-css/w3.css">';
    protected $oReg         = null;
    protected $aSettings    = [];
    protected $aOptions     = [];
    protected $sContentSave = '';
    protected $aStyleParts  = [];
    protected $aScriptParts = [];

    /**
     * constructor imports default arguments
     * @param \database $oDb
     * @param array $aSettings
     * @param array $aOptions
     */
    public function __construct(WbAdaptor $oReg, array $aSettings, array $aOptions = [])
    {
        $this->oReg      = $oReg;
        $this->aSettings = $aSettings;
        $this->aOptions  = $aOptions;
        $this->doInit();
    }

    /**
     * initialize this filter
     */
    public function doInit()
    {
        // do nothing
    }

    /**
     * perform the filter on given content string
     * @param string $sContent
     * @return string
     */
    public function execute($sContent)
    {
        try {
            $this->sContentSave = $sContent;
            if ($this->aSettings[\basename(__DIR__) . '_force'] || $this->isW3CssInBody($sContent)) {
                $sHead = $this->getHead($sContent);
                $this->findAllStyles($sHead);
                $this->removeStylePartsFromDocument($sContent);
                $this->findAllScripts($sHead);
                $this->removeScriptPartsFromDocument($sContent);
                $this->insertStylesInHead($sContent);
                $this->insertScriptsInHead($sContent);
                $sContent = $this->removeRedundantNewlines($sContent);
            }
        } catch (\throwable $e) {
            trigger_error($e->getMessage(), \E_USER_WARNING);
            $sContent = $this->sContentSave;
        }
        return $sContent;
    }

    private function isW3CssInBody(& $sContent): bool
    {
        $sPattern = '/<body[^>]*>.*<.*class\s*=\s*\"([^\"]*w3-[^\"]*)\"/imsUu';
        return (\preg_match($sPattern, $sContent) === 1);
    }

    private function getHead(& $sContent)
    {
        $aMatches = [];
        $sPattern = '/^(?:.*<head[^>]*>)(.*)(?:<\/head>)/siUu';
        // 1 = content of head
        if (\preg_match($sPattern, $sContent, $aMatches) !== 1) {
            throw new RuntimeException(__METHOD__.'['.__LINE__.'] missmatched <head><body> structure');
        }
// remove comments for search
        $sHead = \preg_replace('/<!--[^\[].*[^\]]-->/siUu', '', $aMatches[1]);
        return $sHead;
    }

    private function findAllStyles(& $sHead)
    {
        $aMatches = [];
        $sPattern = '/(<!--\[[^\]]+\]>\s*<link[^>]+"stylesheet"[^>]*\/?>\s*<!\[[^\]]+\]-->)|'
                  . '(<link[^>]+"stylesheet"[^>]*\/?>)|'
                  . '(<!--\[[^\]]+\]>\s*<style[^>]*>.*<\/style>\s*<!\[[^\]]+\]-->)|'
                  . '(<style[^>]*>.*<\/style>)/siUu';
        if (false === \preg_match_all($sPattern, $sHead, $aMatches)) {
            throw new \RuntimeException(__METHOD__.'['.__LINE__.'] preg_match_all pattern failed');
        }
        $this->aStyleParts = \array_merge($this->aStyleParts, $aMatches[0]);
    }

    private function findAllScripts(& $sHead)
    {
        $aMatches = [];
        $sPattern = '/(<!--\[[^\]]+\]>\s*<script[^>]*>.*<\/script>\s*<!\[[^\]]+\]-->)|'
                  . '(<script[^>]*>.*<\/script>)|'
                  . '(<!--\[if[^\]]*\]>\s*<script[^>]*>.*<\/script>\s*<!\[endif\]-->)|'
                  . '(<script[^>]*>.*<\/script>)/siUu';
        if (false === \preg_match_all($sPattern, $sHead, $aMatches)) {
            throw new \RuntimeException(__METHOD__.'['.__LINE__.'] preg_match_all pattern failed');
        }
        $this->aScriptParts = \array_merge($this->aScriptParts, $aMatches[0]);

    }

    private function removeStylePartsFromDocument(& $sContent)
    {
        \array_unshift($this->aStyleParts, \sprintf($this->sW3Css, $this->oReg->AppUrl));
        $this->aStyleParts = \array_unique($this->aStyleParts);
        $sContent = \str_replace($this->aStyleParts, '', $sContent); //, $iCount);
    }

    private function removeScriptPartsFromDocument(& $sContent)
    {
        $this->aScriptParts = \array_unique($this->aScriptParts);
        $sContent = \str_replace($this->aScriptParts, '', $sContent); //, $iCount);
    }

    private function removeRedundantNewlines($sContent)
    {
        // Converts Windows / Mac line breaks to Linux line breaks throughout the document.
        $sTmp = \str_replace(["\r\n", "\r"], "\n", $sContent);
        // remove all superfluous line feeds and whitespaces from <head> only
        return \preg_replace_callback(
        '/^.*(<\!doc.*<body)/siUu',
        function ($aMatches) {
            return \preg_replace('/\n\s+/', "\n", $aMatches[1]);
            },
            $sTmp
        );
    }

    private function insertStylesInHead(& $sContent)
    {
        if ($this->aStyleParts) {
            $sInsertion = '<!-- reordered by W3Css -->'."\n".\implode("\n", $this->aStyleParts)."\n";
            $sPattern = '/(^.*)(<\/head>.*<body.*$)/siuU';
            $sContent = \preg_replace($sPattern, '$1'.$sInsertion.'$2', $sContent);
            if (is_null($sContent)) {
                throw new \RuntimeException(__METHOD__.'['.__LINE__.'] preg_replace pattern failed');
            }
        }
    }

    private function insertScriptsInHead(& $sContent)
    {
        if ($this->aScriptParts) {
            $sInsertion = "\n".implode("\n", $this->aScriptParts)."\n";
            $sPattern = '/(^.*)(<\/head>.*<body.*$)/siuU';
            $sContent = \preg_replace($sPattern, '$1'.$sInsertion.'$2', $sContent);
            if (is_null($sContent)) {
                throw new \RuntimeException(__METHOD__.'['.__LINE__.'] preg_replace pattern failed');
            }
        }
    }

}
