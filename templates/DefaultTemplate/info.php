<?php
/*
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * [$filename]
 *
 * @category     Addons
 * @package      template
 * @subpackage   DefaultTemplate
 * @copyright    WebsiteBaker Project <board@websitebaker@org>
 * @author       WebsiteBaker Project <board@i@websitebaker@org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker@org>
 * @license      https://www.gnu.org/licenses/gpl.html   GPL License
 * @version      1.0.0
 * @lastmodified $Date: 2019-03-27 12:01:55 +0100 (Mi, 27. Mrz 2019) $
 * @since        File available since 2016-07-26
 * @description
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */

$template_directory    = 'DefaultTemplate';
$template_name         = 'WebsiteBaker Default Template v1.0.16';
$template_version      = '1.0.16';
$template_platform     = '2.13.0';
$template_function     = 'template';
$template_author       = 'WebsiteBaker Project';
$template_license      = '<a href="https://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_description  = 'Default template for Website Baker. This template is designed with one goal in mind: to completely control layout with CSS';

 $block[1]             = 'Main';
 $block[2]             = 'Teaser';
 $block[3]             = 'Sidebar';
 $block[4]             = 'Footer';

// Definition of menu elements
$menu[1]             ='Main-Navigation';
$menu[2]             ='Foot-Navigation';
$menu[3]             ='none';

// end of file
