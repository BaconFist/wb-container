<?php
/**
 *
 * @category        admin
 * @package         pages
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: delete.php 346 2019-05-07 13:42:36Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/delete.php $
 * @lastmodified    $Date: 2019-05-07 15:42:36 +0200 (Di, 07. Mai 2019) $
 *
 */

// Create new admin object and print admin header
    if (!defined('SYSTEM_RUN') ){ require( dirname(dirname((__DIR__))).'/config.php' ); }
    $admin = new \admin('Pages', 'pages_delete');

    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\'.basename(__DIR__));
    $sMessage = $oTrans->MESSAGE_PAGES_DELETED;
// Include the WB functions file
//    if (!\function_exists('delete_page')){require(WB_PATH.'/framework/functions.php');}

    if( (!($page_id = $admin->checkIDKEY('page_id', 0, $_SERVER['REQUEST_METHOD']))) )
    {
        $admin->print_error($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS, ADMIN_URL );
        exit();
    }

// Get perms
    if (!$admin->get_page_permission($page_id,'admin')) {
        $sErrorMsg = \sprintf('%s [%d] %s',basename(__FILE__),__LINE__,$oTrans->MESSAGE_PAGES_INSUFFICIENT_PERMISSIONS);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }

// Find out more about the page
    $query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'";
    $results = $database->query($query);
    if($database->is_error()) {
        $admin->print_error($database->get_error());
    }
    if ($results->numRows() == 0) {
        $admin->print_error($oTrans->MESSAGE_PAGES_NOT_FOUND);
    }

    $results_array = $results->fetchRow(MYSQLI_ASSOC);

    $visibility = $results_array['visibility'];

// Check if we should delete it or just set the visibility to 'deleted'
    if (PAGE_TRASH != 'disabled' && $visibility != 'deleted') {
        // Page trash is enabled and page has not yet been deleted
        // Function to change all child pages visibility to deleted
        function trash_subs($parent = 0) {
            global $database;
        // Query pages
            $sql = 'SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` '
                  .'WHERE `parent` = '.$parent.' '
                  .'ORDER BY `position` ASC';
            if ($oRes = $database->query($sql)) {
                // Check if there are any pages to show
                if ($oRes->numRows() > 0) {
                    // Loop through pages
                    while($page = $oRes->fetchRow(MYSQLI_ASSOC)) {
                        // Update the page visibility to 'deleted'
                        $sql = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
                              .'`visibility` = \'deleted\' '
                              .'WHERE `page_id` = '.$page['page_id'].' '
                              .'';
                        if (!$database->query($sql)){
                          $admin->print_error($database->get_error());
                        }
                        // Run this function again for all sub-pages
                        trash_subs($page['page_id']);
                    }
                }
            }
        }
    // Update the page visibility to 'deleted'
        $sql = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
                          .'`visibility` = \'deleted\' '
                          .'WHERE `page_id` = '.$page_id.' '
                          .'';
                    $sMessage = $oTrans->MESSAGE_PAGES_MARKED_DELETED;
                    if (!$database->query($sql)){
                      $admin->print_error($database->get_error());
                    }
    //
    // Run trash subs for this page
        trash_subs($page_id);
    } else {
        // Really dump the page
        // Delete page subs
        $sub_pages = get_subs($page_id, []);
        foreach($sub_pages AS $sub_page_id) {
            delete_page($sub_page_id);
        }
        // Delete page
        delete_page($page_id);
    }

// Check if there is a db error, otherwise say successful
    if ($database->is_error()) {
        $admin->print_error($database->get_error());
    } else {

        $admin->print_success($sMessage);

    }

// Print admin footer
    $admin->print_footer();