<?php

/*
 * Copyright (C) 2021 Manuela v.d.Decken <manuela@isteam.de>
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
 * DbRepair
 *
 * @category     name
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 05.04.2021
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types = 1);

namespace App;
// use source;

class DbRepair
{

    protected \database $oDb;
    protected string    $sTableName;
    protected array     $aRecords = [];
    protected string    $sError = '';
    protected bool      $bTrigger = false;

    public function __construct(\database $oDb)
    {
        $this->oDb = $oDb;
    }

    public function buildLinkFromTrail(string $sTableName = 'pages', bool $bTrigger=false)
    {
        $this->sError  = '';
        $this->bTrigger = $bTrigger;
        $this->sTableName = $this->oDb->TablePrefix.$sTableName;
        $this->loadPages(['page_id','level','page_trail','link']);
        foreach ($this->aRecords as $aRecord) {
            $aTrail = \explode(',', $aRecord['page_trail']);
            $sNewLink = '';
            foreach ($aTrail as $iPageId) {
                if ($this->checkTrail((int)$iPageId,$bTrigger)) {
                    $sNewLink .= ('/'.$this->getLastLinkPart($this->aRecords[(int)$iPageId]['link']));
                } else {
                    $sNewLink .= '/???';
                }
            }
            if ($sNewLink) {
                $sSql = 'UPDATE `'.$this->sTableName.'` '
                      . 'SET `link`=\''.$this->oDb->escapeString($sNewLink).'\' '
                      . 'WHERE `page_id`='.$aRecord['page_id'];
                $this->oDb->query($sSql);
            }
        }
        $this->aRecords = [];
    }

    protected function checkTrail(int $iPageId)
    {
        static $aIds = [];
        $bRetval = true;
        if (! \array_key_exists($iPageId, $this->aRecords)) {
            if (! \in_array($iPageId, $aIds)) {
                $aIds[] = $iPageId; // Suppresses the multiple display of the same error
                $sMsg = 'Dependency error in page tree!! PageId \''.$iPageId.'\' missing.'.PHP_EOL
                      . 'Affected pages:'.PHP_EOL;
                $sSql = 'SELECT `page_trail`, `link` FROM `'.$this->sTableName.'` '
                      . 'WHERE '.$iPageId.' IN(`page_trail`)';
                if (($oRecSet = $this->oDb->query($sSql))) {
                    $aRecords = $oRecSet->fetchAll(\MYSQLI_ASSOC);
                    foreach ($aRecords as $aItem) {
                        $sMsg .= '['.$aItem['page_trail'].'] > '.$aItem['link']."\n";
                    }
                }
                $this->sError = $sMsg;
                if ($this->bTrigger){trigger_error($sMsg, E_USER_WARNING);}
            }
            $bRetval = false;
        }
        return $bRetval;
    }

    protected function getLastLinkPart(string $sLink): string
    {
        $aParts = \explode('/', $sLink);
        return \array_pop($aParts);
    }

    protected function loadPages(array $aFieldNames)
    {
        $aFields = \array_diff($aFieldNames, ['page_id']);
        \array_unshift($aFields, 'page_id');
        $aRecords = [];
        $sSql = 'SELECT `'.\implode('`,`', $aFields).'` '
              . 'FROM `'.$this->sTableName.'` '
              . 'ORDER BY `page_id`';
        if (($oRecSet = $this->oDb->query($sSql))) {
            $aRecords = $oRecSet->fetchAll(\MYSQLI_ASSOC);
        }
        $this->aRecords = \array_column($aRecords, null, 'page_id');
        unset($aRecords);
    }

// Get error
    public function getError()
    {
        $oRetval = null;
        if (isset($this->sError)) {
            $oRetval = $this->sError;
        } else {
            $oRetval = null;
        }
        return $oRetval;
    }

}

