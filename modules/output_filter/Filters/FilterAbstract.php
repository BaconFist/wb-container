<?php


//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace addon\output_filter\Filters;

use bin\WbAdaptor, addon\output_filter\OutputFilterException;

// use source;

abstract class FilterAbstract
{

    protected $oReg = null;
    protected $oDb = null;
    protected $aSettings = [];
    protected $aOptions = [];

/**
* constructor imports default arguments
* @param WbAdaptor $oReg
* @param array $aSettings
* @param array $aOptions
*/
    final public function __construct(WbAdaptor $oReg, array $aOptions = [])
    {
        $this->oReg = $oReg;
        $this->oDb = $oReg->getDatabase();
//        $this->aSettings = readSettings(($oReg->currAddonInstance ?? 0));
        $this->aOptions = $aOptions;
        $this->doInit();
    }
/**
* initialize the filter
*/
abstract protected function doInit(): void;
/**
* perform the filter on given content string
* @param string $sContent
* @return string
*/
    abstract public function execute(string $sContent): string;
/**
*
* @param int $iInstance default 0
* @return array all Settings of current namespace
* @throws OutputFilterException
* @deprecated from version 0.0.2
* from the next version, this method will only contain a call to the Registry class.
*/
    protected function readSettings(int $iInstance = 0): array
    {
        $sSql = 'SELECT * '
        . 'FROM `'.$this->oDb->getTablePrefix().'registry` '
        . 'WHERE `namespace`=\''.\get_class($this).'\' AND `instance`=\''.$sInstance.'\'';
        if (($oRs = $this->oDb->query($sSql))) {
            $aRetval = $oRes->fetch_all(\MYSQLI_ASSOC);
            foreach ($aRetval as & $aRecord) {
                if (! \settype($aRecord['value'], $aRecord['type'])) {
                    throw new OutputFilterException(
                    'Invalid datatype for '.$aRecord['namespace'].'|'.$aRecord['instance'].'|'.
                    $aRecord['item'].' given. Must be type of '.$aRecord['type']
                    );
                }
        // sanitize by regexpression not implemented yet.
            }
        }
        return $aRetval ?? [];
    }

} //end of Abstractclass

