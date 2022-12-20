<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 7.2.x and higher
 * @version         $Id: info.php 283 2019-03-22 01:52:39Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/info.php $
 * @lastmodified    $Date: 2019-03-22 02:52:39 +0100 (Fr, 22. Mrz 2019) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}/* -------------------------------------------------------- */

$module_directory   = 'droplets';
$module_name        = 'Droplets v3.3.6';
$module_type        = 'addon';
$module_function    = 'tool';
$module_version     = '3.3.6';
$module_platform    = '2.13.0';
$module_author      = 'Ruud and pcwacht, Luisehahne';
$module_license     = 'GNU General Public License';
$module_description = 'This tool allows you to manage your local Droplets.';

/*
CHANGELOG
2018-12-16
Droplets v3.1.9

Recoded Droplet SearchBox
*/