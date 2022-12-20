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
 * @requirements    PHP 5.6.0 and higher
 * @version         $Id: er_levels.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/interface/er_levels.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 * Error Reporting Level's list file
 * This file is used to generate a list of PHP
 * Error Reporting Level's for the user to select
 *
 */

if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}

// Define that this file is loaded
if (!\defined('ERROR_REPORTING_LEVELS_LOADED')) {
    \define('ERROR_REPORTING_LEVELS_LOADED', true);
}

// Create array
$ER_LEVELS = [];
$ER_LEVELS['0']    = $TEXT['DISABLED'];
$ER_LEVELS[E_ALL]  = 'Production';
$ER_LEVELS['-1']   = 'Development';

$TWIG_VERSIONS['1'] = 'Version 1.36.0 (2018-07-25)';
$TWIG_VERSIONS['2'] = 'Version 2.6.0-DEV (2018-07-31)';
