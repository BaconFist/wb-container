<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: info.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/info.php $
 * @lastmodified    $Date: 2019-06-11 19:55:53 +0200 (Di, 11. Jun 2019) $
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; }
/* -------------------------------------------------------- */

$module_directory   = 'news';
$module_name        = 'News v3.9.22';
$module_function    = 'page';
$module_version     = '3.9.22';
$module_platform    = '2.13.0';
$module_author      = 'Ryan Djurovich, Rob Smith, Werner v.d.Decken';
$module_license     = 'GNU General Public License';
$module_description = 'This page type is designed for making a news page.';

/* ---------------------------------------------------------------------------------------
CHANGELOG

2019-06-06
----------
fix default.css remove import url not existing DataTabler-min.css

2019-05-24
----------
Added table mod_news_layout to load presets from db
news upgrade first will check and import preset files and layouts from settings (insert with a new name)
with save, add and delete function,
check for existing layouts,
- saving a layout will overwrite same layout,
- adding layout rename layout bevor insert
can't delete layout in use or default (preset) layouts


*/
