<?php
/**
 *
 * @category        modules
 * @package         form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2 and higher
 * @version         $Id: info.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/info.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @description
 */
/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
$module_directory = 'form';
$module_name = 'Form Modul v3.2.14';
$module_function = 'page';
$module_version = '3.2.14 ';
$module_platform = '2.13.0';
$module_author = 'Ryan Djurovich & Rudolph Lartey - additions John Maats - PCWacht, dev-team';
$module_license = 'GNU General Public License';
$module_description = 'This module allows you to create customised online forms, such as a feedback form. '.
'Thank-you to Rudolph Lartey who help enhance this module, providing code for extra field types, etc.';
