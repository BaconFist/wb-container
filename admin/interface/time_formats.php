<?php
/**
 *
 * @category        admin
 * @package         interface
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: time_formats.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/interface/time_formats.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 * Time format list file
 * This file is used to generate a list of time formats for the user to select
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}

// Define that this file is loaded
    if (!\defined('TIME_FORMATS_LOADED')) {
        \define('TIME_FORMATS_LOADED', true);
    }

// Create array
    $TIME_FORMATS = [];
// set vars for backend or frontend
    $oReg = (is_object($oReg) ? $oReg : WbAdaptor::getInstance());
    $oApp = $oReg->getApplication();
    $isBackend = $oApp instanceof admin;

    $sTimezone = ($userTimezone ?? $oReg->Timezone);
    $sDefaultTimezone = ($userTimezone ?? $oReg->DefaultTimezone);
/*
    if ($isBackend) {
        $sTimezone = $oReg->Timezone;
        $sDefaultTimezone = $oReg->DefaultTimezone;
    } else {
    }
*/
// Get the current time (in the users timezone if required)
    $iActualTimezone = (int)((isset($user_time) && $user_time == true) ? $sTimezone : $sDefaultTimezone);
    $iDateTime = \time()+ $iActualTimezone;

// Add values to list
    $TIME_FORMATS['g:i:s|A'] = PreCheck::getStrftime('g:i:s A', $iDateTime);
    $TIME_FORMATS['g:i|A']   = PreCheck::getStrftime('g:i A',   $iDateTime);
    $TIME_FORMATS['g:i:s|a'] = PreCheck::getStrftime('g:i:s a', $iDateTime);
    $TIME_FORMATS['g:i|a']   = PreCheck::getStrftime('g:i a',   $iDateTime);
    $TIME_FORMATS['H:i:s']   = PreCheck::getStrftime('H:i:s',   $iDateTime);
    $TIME_FORMATS['H:i']     = PreCheck::getStrftime('H:i',     $iDateTime);

// Add "System Default" to list (if we need to)
    if(isset($user_time) && $user_time == true) {
        if(isset($TEXT['SYSTEM_DEFAULT'])) {
            $TIME_FORMATS['system_default'] = PreCheck::getStrftime($oReg->TimeFormat, $iDateTime).' ('.$TEXT['SYSTEM_DEFAULT'].')';
        } else {
            $TIME_FORMATS['system_default'] = PreCheck::getStrftime($oReg->TimeFormat, $iDateTime).' (System Default)';
        }
    }

// Reverse array so "System Default" is at the top
$TIME_FORMATS = \array_reverse($TIME_FORMATS, true);
