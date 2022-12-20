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
 * @version         $Id: date_formats.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/interface/date_formats.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 * Date format list file
 * This file is used to generate a list of date formats for the user to select
 *
 */

use bin\{WbAdaptor};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}

// Define that this file is loaded
    if (!\defined('DATE_FORMATS_LOADED')) {
        \define('DATE_FORMATS_LOADED', true);
    }

// Create array
    $DATE_FORMATS = [];
// set vars for backend or frontend
    $oReg = (is_object($oReg) ? $oReg : WbAdaptor::getInstance());
    $oApp = $oReg->getApplication();
    $isBackend = $oApp instanceof admin;
    $sTimezone = (int)($userTimezone ?? $oReg->Timezone);
    $sDefaultTimezone = (int)($userTimezone ?? $oReg->DefaultTimezone);
/*
    if ($isBackend) {
        $sTimezone = $oReg->Timezone;
        $sDefaultTimezone = $oReg->DefaultTimezone;
    } else {
    }
*/
// Get the current time (in the users timezone if required)
    $iActualTimezone = ((isset($user_time) && $user_time == true) ? $sTimezone : $sDefaultTimezone);
    $iDateTime = \time()+ $iActualTimezone;
    $oReg = WbAdaptor::getInstance();
// Add values to list
    $DATE_FORMATS['l,|jS|F,|Y'] = PreCheck::getStrftime('l, jS F, Y',$iDateTime). ' (l, jS F, Y)'; //
    $DATE_FORMATS['jS|F,|Y']    = PreCheck::getStrftime('jS F, Y',$iDateTime). ' (jS F, Y)';
    $DATE_FORMATS['d|M|Y']      = PreCheck::getStrftime('d M Y',$iDateTime). ' (d M Y)';
    $DATE_FORMATS['M|d|Y']      = PreCheck::getStrftime('M d. Y',$iDateTime). ' (M d. Y)';
    $DATE_FORMATS['D|M|d,|Y']   = PreCheck::getStrftime('D, M d. Y',$iDateTime). ' (D, M d. Y)';
    $DATE_FORMATS['d-m-Y']      = PreCheck::getStrftime('d-m-Y',$iDateTime).' (d-m-Y)';
    $DATE_FORMATS['m-d-Y']      = PreCheck::getStrftime('m-d-Y',$iDateTime).' (m-d-Y)';
    $DATE_FORMATS['d.m.Y']      = PreCheck::getStrftime('d.m.Y',$iDateTime).' (d.m.Y)';
    $DATE_FORMATS['m.d.Y']      = PreCheck::getStrftime('m.d.Y',$iDateTime).' (m.d.Y)';
    $DATE_FORMATS['d/m/Y']      = PreCheck::getStrftime('d/m/Y',$iDateTime).' (d/m/Y)';
    $DATE_FORMATS['m/d/Y']      = PreCheck::getStrftime('m/d/Y',$iDateTime).' (m/d/Y)';
    $DATE_FORMATS['j.n.Y']      = PreCheck::getStrftime('j.n.Y',$iDateTime).' (j.n.Y)';

// Add "System Default" to list (if we need to)
if (isset($user_time) && $user_time == true)
{
    if(isset($TEXT['SYSTEM_DEFAULT']))
    {
        $DATE_FORMATS['system_default'] = \gmdate($oReg->DefaultDateFormat, $iDateTime).' ('.$TEXT['SYSTEM_DEFAULT'].')';
    } else {
        $DATE_FORMATS['system_default'] = \gmdate($oReg->DefaultDateFormat, $iDateTime).' (System Default)';
    }
}

// Reverse array so "System Default" is at the top
$DATE_FORMATS = \array_reverse($DATE_FORMATS, true);

