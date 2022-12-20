<?php
/**
 *
 * @category        modules
 * @package         jsadmin
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.0
 * @requirements    PHP 5.6.x and higher
 * @version         $Id: info.php 288 2019-03-26 15:14:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/jsadmin/info.php $
 * @lastmodified    $Date: 2019-03-26 16:14:03 +0100 (Di, 26. Mrz 2019) $
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */

$module_directory = 'jsadmin';
$module_name = 'Javascript Admin v2.1.2';
$module_function = 'tool';
$module_version  = '2.1.2';
$module_platform = '2.13.0';
$module_author   = 'Stepan Riha, Swen Uth';
$module_license  = 'BSD License';
$module_description = 'This module adds Javascript functionality to the Website Baker Admin to improve some of the UI interactions. Uses the YahooUI library.';
