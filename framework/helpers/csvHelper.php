<?php

namespace bin\helpers;

/**
 * csvHelper
 *
 * @package WebsieBaker 283
 * @copyright 2016
 * @version $Id: csvHelper.php 335 2019-04-13 07:07:50Z Luisehahne $
 * @access public
 */
class csvHelper {

    const DELIMITER = ';';
    const HASHEADLINE = true; // true | false

    protected $sError = '';
    protected $iErrNo = 0;

    protected $iHeadFields  = 0;

    public function __construct() {}

    public static function getInstance()
    {
        static $oInstance = null;
        $sClass = __CLASS__;
        return $oInstance ?: $oInstance = new $sClass;
    }

    protected function getHeadLine($aCsvLines, $hasHeadline=self::HASHEADLINE){
      $aRetVal =  [0, 1];// default
      if (\sizeof($aCsvLines) && $hasHeadline) {
          $aRetVal = \str_getcsv(\array_shift($aCsvLines),self::DELIMITER);
      }
      return $aRetVal;
    }

// number of elements for each array isn't equal.
    protected function combineToArray($aHeadline, $aValue=[]){
        $aRetVal = false;
        // check if array can combine
        if ((count($aHeadline) > 0) &&
                  (count($aValue) > 0) &&
                        (count($aHeadline) == count($aValue))
        ) {
        $aRetVal = \array_combine($aHeadline, $aValue);
        }
        return $aRetVal;
    }

    protected function readCsvFile($sCsvFilename){
        $aCsvLines = [];
        if (\is_readable($sCsvFilename)) {
            $aLines = \file($sCsvFilename, \FILE_IGNORE_NEW_LINES|\FILE_SKIP_EMPTY_LINES);
            foreach ($aLines as $iLine => $sItem) {
                if (!\preg_match('/^[\-\/]+.*/', $sItem)) {
                  $aCsvLines[] = $sItem;
                }
            }
        }
        return $aCsvLines;
    }

    public function ImportCsvFile($sCsvFilename){
        \clearstatcache();
        $aCsvLines = [];
        if (\is_readable($sCsvFilename)) {
            $aCsvLines = $this->readCsvFile($sCsvFilename);
            $aHeadline = $this->getHeadLine($aCsvLines);
            // remove column header
            \array_shift($aCsvLines);
            \array_walk(
                      $aCsvLines,
                      function(& $a) use ($aCsvLines, $aHeadline) {
                          $a = \str_getcsv($a, ';');
                          $a = $this->combineToArray($aHeadline, $a);
                      });
        }
        return $aCsvLines;
    }
} // end of class csvHelper
/********************************************************************************************/
//
/********************************************************************************************/

class CSVException extends \Exception {};

