<?php
/**
 *
 * @category        admin
 * @package         pages
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: empty_trash.php 191 2019-01-29 17:14:41Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/empty_trash.php $
 * @lastmodified    $Date: 2019-01-29 18:14:41 +0100 (Di, 29. Jan 2019) $
 *
 */

require('../../config.php');
$admin = new admin('Pages', 'pages');

// Include the WB functions file
//require_once(WB_PATH.'/framework/functions.php');

// Get page list from database
$query = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
       . 'WHERE `visibility` = \'deleted\' '
       . 'ORDER BY level DESC';
$get_pages = $database->query($query);

// Insert values into main page list
if($get_pages->numRows() > 0)    {
    while($page = $get_pages->fetchAssoc()) {
        // Delete page subs
        $sub_pages = get_subs($page['page_id'], array());
        foreach($sub_pages AS $sub_page_id) {
            delete_page($sub_page_id);
        }
        // Delete page
        delete_page($page['page_id']);
    }
}

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
    $admin->print_error($database->get_error());
} else {
    $admin->print_success($TEXT['TRASH_EMPTIED']);
}

// Print admin
$admin->print_footer();

