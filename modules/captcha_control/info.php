<?php
/**
 *
 * @category        modules
 * @package         captcha_control
 * @author          WebsiteBaker Project
 * @copyright       2009-2019, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.11.0
 * @requirements    PHP 5.6.0 and higher
 * @version         $Id: info.php 257 2019-03-17 20:00:55Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/captcha_control/info.php $
 * @lastmodified    $Date: 2019-03-17 21:00:55 +0100 (So, 17. Mrz 2019) $
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */
$module_directory   = 'captcha_control';
$module_name        = 'Captcha Spam-Protect v2.2.2';
$module_function    = 'tool';
$module_version     = '2.2.2';
$module_platform    = '2.13.0';
$module_author      = 'Thomas Hornik (thorn),Luisehahne';
$module_license     = 'GNU General Public License';
$module_description = 'Admin-Tool to control CAPTCHA and ASP';
