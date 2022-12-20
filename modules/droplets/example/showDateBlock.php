<?php
//:Insert Full Date and Clock
//:usage: [[showDateBlock?title=Allgemeine Termine&amp;pre=Heute ist&amp;desc=Terminänderungen bleiben vorbehalten]]
//:can be call without parameters
$oReg = \bin\WbAdaptor::getInstance();
$sSpace = '';
$sTitle = ($title ?? '');
$sPre   = ($pre ?? '');
$sDesc  = ($desc ?? '');
$iNow   = time()+$oReg->Timezone;
$ShowDate  = sprintf('%s',\bin\helpers\PreCheck::getStrftime($oReg->DateFormat,$iNow,$oReg->Language)); //
$ShowTime  = sprintf('%s',\bin\helpers\PreCheck::getStrftime($oReg->TimeFormat,$iNow,$oReg->Language)); //
$TimeZone  = $oReg->Timezone;
$bPeriod   = preg_match('/[^0-9:\-\/_].*(am|pm|AM|PM)/i',$ShowTime,$aMatches);
$sPeriod   = ($bPeriod ? trim($aMatches[0]) : "");
$sLanguage = strtolower($oReg->Language);
$content   = "";
    try {
    $sContent  = '<div id="showDate" class="w3-container w3-auto w3-center">'.PHP_EOL;
    if ($sTitle){$sContent .= '<h3>'.$sTitle .'</h3>'.PHP_EOL;}
    $sContent .= '<h4>'.$sPre.'&nbsp;';
    $sContent .= '<span id="date-stamp" data-lang="'.$sLanguage.'" data-timezone="'.$TimeZone.'">'.$ShowDate.' </span>';
    $sContent .= ' <span id="time-stamp" data-period="'.$sPeriod.'">'.$ShowTime.'</span>'.PHP_EOL;
    $sContent .= '</h4>'.PHP_EOL;
    if ($sDesc){$sContent .= '<h3>'.$sDesc.'</h3>'.PHP_EOL;}
    $sContent .= '</div>'.PHP_EOL;
    } catch (\Throwable $ex) {
        /* place to insert different error/logfile messages */
        $sContent = '$scontent = '.$ex->getMessage();
    }
    return $sContent;
