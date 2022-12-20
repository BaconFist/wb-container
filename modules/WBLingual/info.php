<?php
/**
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
 *
 * Description of Lingual
 *
 * @package      Addon package
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      1.0.0-dev.0
 * @revision     $Id: info.php 300 2019-03-27 09:00:11Z Luisehahne $
 * @since        File available since 02.12.2017
 * @deprecated   no
 * @description  xxx
 *
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

$module_directory    = 'WBLingual';
$module_name         = 'WebsiteBaker Lingual v2.0.6';
$module_function     = 'snippet';
$module_version      = '2.0.6';
$module_status       = '';
$module_platform     = '2.12.2';
$module_author       = 'Luisehahne';
$module_license      = 'GNU General Public License';
$module_requirements = 'PHP 7.3.x';
$module_description  = 'This snippet switches between different languages';
