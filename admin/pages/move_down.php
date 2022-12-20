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
 * Description of admin/pages/move_down.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: move_down.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

if (!\defined('SYSTEM_RUN') ){require(\dirname(\dirname((__DIR__))).'/config.php' );}

// Get id
if(isset($_GET['page_id']) AND \is_numeric($_GET['page_id'])) {
    if(isset($_GET['section_id']) AND \is_numeric($_GET['section_id'])) {
        $page_id = $_GET['page_id'];
        $id = $_GET['section_id'];
        $id_field = 'section_id';
        $common_field = 'page_id';
        $table = TABLE_PREFIX.'sections';
    } else {
        $id = $_GET['page_id'];
        $id_field = 'page_id';
        $common_field = 'parent';
        $table = TABLE_PREFIX.'pages';
    }
} else {
    \header("Location: index.php");
    exit(0);
}

// Create new admin object and print admin header
$admin = new \admin('Pages', 'pages_settings');

// Include the ordering class
// Create new order object an reorder
$order = new \order($table, 'position', $id_field, $common_field);
if($id_field == 'page_id') {
    if($order->move_down($id)) {
        $admin->print_success($MESSAGE['PAGES_REORDERED']);
    } else {
        $admin->print_error($MESSAGE['PAGES_CANNOT_REORDER']);
    }
} else {
    if($order->move_down($id)) {
        $admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
    } else {
        $admin->print_error($TEXT['ERROR'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
    }
}

// Print admin footer
$admin->print_footer();
