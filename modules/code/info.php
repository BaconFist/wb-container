<?php
/**
 *
 * @category        modules
 * @package         code
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.6.0 and higher
 * @version         $Id: info.php 280 2019-03-22 01:03:06Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/code/info.php $
 * @lastmodified    $Date: 2019-03-22 02:03:06 +0100 (Fr, 22. Mrz 2019) $
 *
*/

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */

$module_directory   = 'code';
$module_name        = 'Code v3.0.8';
$module_function    = 'page';
$module_version     = '3.0.8';
$module_platform    = '2.13.0';
$module_author      = 'Ryan Djurovich';
$module_license     = 'GNU General Public License';
$module_description = 'This module allows you to execute PHP commands (limit access to users you trust!!)';

