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
 * Description of admin/pages/restore.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: restore.php 346 2019-05-07 13:42:36Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

// use

// Get page id
    if (!isset($_GET['page_id']) || !\is_numeric($_GET['page_id'])) {
        \header("Location: index.php");
        exit(0);
    } else {
        $page_id = (int)$_GET['page_id'];
    }

    if (!\defined('SYSTEM_RUN') ){ require(\dirname(\dirname((__DIR__))).'/config.php' ); }
// Create new admin object and print admin header
    $admin = new \admin('Pages', 'pages_delete');

// Include the WB functions file
//    if (!\function_exists('delete_page')){require(WB_PATH.'/framework/functions.php');}

// Get perms
    $results = $database->query("SELECT `admin_groups`,`admin_users` FROM `".TABLE_PREFIX."pages` WHERE `page_id` = ".(int)$page_id);
    $results_array = $results->fetchRow(MYSQLI_ASSOC);

// Find out more about the page
    $query = "SELECT * FROM `".TABLE_PREFIX."pages` WHERE `page_id` = ".(int)$page_id;
    $results = $database->query($query);
    if($database->is_error()) {
        $admin->print_error($database->get_error());
    }
    if($results->numRows() == 0) {
        $admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
    }
    $results_array = $results->fetchRow(MYSQLI_ASSOC);
    $old_admin_groups = \explode(',', \str_replace('_', '', $results_array['admin_groups']));
    $old_admin_users  = \explode(',', \str_replace('_', '', $results_array['admin_users']));

    $in_old_group = FALSE;
    foreach($admin->get_groups_id() as $cur_gid){
        if (\in_array($cur_gid, $old_admin_groups)) {
        $in_old_group = TRUE;
        }
    }
    if ((!$in_old_group) AND !\is_numeric(\array_search($admin->getUserId(), $old_admin_users))) {
        $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }

    $visibility = $results_array['visibility'];

    if (PAGE_TRASH) {
        if ($visibility == 'deleted') {
            // Function to change all child pages visibility to deleted
            function restore_subs($parent = 0) {
                global $database;
                // Query pages
                $query_menu = $database->query("SELECT `page_id`,`visibility` FROM `".TABLE_PREFIX."pages` WHERE `parent` = ".(int)$parent." ORDER BY `position` ASC");
                // Check if there are any pages to show
                if($query_menu->numRows() > 0) {
                    // Loop through pages
                    while(($page = $query_menu->fetchRow(MYSQLI_ASSOC))) {
                        // Update the page visibility to 'public'
                        $database->query("UPDATE `".TABLE_PREFIX."pages` SET `visibility` = 'public' WHERE `page_id` = ".(int)$page['page_id']." LIMIT 1");
                        // Run this function again for all sub-pages
                        restore_subs($page['page_id']);
                    }
                }
            }
            // Update the page visibility to 'public'
            $database->query("UPDATE `".TABLE_PREFIX."pages` SET `visibility` = 'public' WHERE `page_id` = ".(int)$page_id);
            // Run trash subs for this page
            restore_subs($page_id);
        }
    }

// Check if there is a db error, otherwise say successful
    if($database->is_error()) {
        $admin->print_error($database->get_error());
    } else {
        $admin->print_success($MESSAGE['PAGES_RESTORED']);
    }

// Print admin footer
    $admin->print_footer();
