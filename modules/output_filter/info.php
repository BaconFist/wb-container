<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of RelUrlFilter
 *
 * @package      Addon_OutputFilter
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id: info.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since        File available since 31.10.2017
 * @deprecated   no / since 0000/00/00
 * @description  transform all full qualified local URLs into relative URLs
 *               this filter touches FQURLs only!
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

$module_directory   = 'output_filter';
$module_name        = 'Output Filter Frontend v1.3.13';
$module_function    = 'tool';
$module_version     = '1.3.13';
$module_platform    = '2.13.0';
$module_author      = 'Christian Sommer(doc), Manuela v.d. Decken(DarkViper), Dietmar Wöllbrink(Luisehahne)';
$module_license     = 'GNU General Public License';
$module_description = 'This Add-On allows to filter the output directly before it is sent to the browser. Each individual filter can be activated/deactivated by the ACP.';
//                    . 'To add a new filter simply create a new filter file in \'modules/output_filter/filters\' '
//                    . 'and add it to the \'individual\' section of \'modules/output_filter/index.php\'. '
/*
CHANELOG
2018-12-16
Output Filter Frontend v1.2.6
Ckeditor no longer sets a absolute url after choosing a entry from an addon selectbox , after choosing an addon entry, link will be inserted by
[wblink{page_id}?addon=name&item={addon_id}]. Marking the link, the ckeditor jumps to the correct addon entry in select box
The frontend will be replace all "[wblink{page_id}?addon=name&item=n...]" with real links to addon accessfiles
All modules must offer the class 'WbLink'(implementing 'WbLinkAbstract'), to be taken into consideration.
Adding [wblink...] for add-ons, only working with new ckeditor wblink plugin and the new WbLink output_filter

*/