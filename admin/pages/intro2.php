<?php
/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
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
 * Description of admin/pages/intro2.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: intro2.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

// Create new admin object
if (!\defined('SYSTEM_RUN')){require(\dirname(\dirname((__DIR__))).'/config.php');}
$admin = new \admin('Pages', 'pages_intro',false);
if (!$admin->checkFTAN())
{
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL );
}

// Get posted content
if (!isset($_POST['content'])) {
    $admin->print_error($MESSAGE['PAGES_NOT_SAVED']);
    exit(0);
} else {
    $content = $admin->strip_slashes($_POST['content']);
}

// Include the WB functions file
//if (!\function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

$admin->print_header();
// Write new content
$filename = WB_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;
if (!\file_put_contents( $filename, $content )){
    $admin->print_error($MESSAGE['PAGES_NOT_SAVED']);
} else {
    change_mode($filename);
    $admin->print_success($MESSAGE['PAGES_INTRO_SAVED']);
}
if (!\is_writable($filename)) {
    $admin->print_error($MESSAGE['PAGES_INTRO_NOT_WRITABLE']);
}

// Print admin footer
$admin->print_footer();
