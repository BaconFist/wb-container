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
 * Description of admin/pages/index.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: index.php 375 2019-06-21 14:34:41Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use vendor\phplib\Template;

if (!\defined( 'SYSTEM_RUN' ) ){ require(\dirname(\dirname((__DIR__))).'/config.php' ); }
$admin = new \admin('Pages', 'pages');
//$admin->clearIDKEY();
// Include the WB functions file
//if (!function_exists('make_dir')){require(WB_PATH.'/framework/functions.php');}

    $sAbsAddonPath = str_replace(['\\','//'],'/',__DIR__).'';
    if (\is_readable($sAbsAddonPath.'/languages/EN.php')) {require($sAbsAddonPath.'/languages/EN.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAbsAddonPath.'/languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.LANGUAGE.'.php');}

// fixes A URI contains impermissible characters or quotes around the URI are not closed.
//    $MESSAGE['PAGES_DELETE_CONFIRM'] = ($MESSAGE['PAGES_DELETE_CONFIRM']);
    $oReg   = WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $sDomain = basename(dirname(__DIR__)).'\\'.basename(__DIR__);
    $oTrans->enableAddon($sDomain);
    $database = $oReg->getDatabase();
    $oApp = $oReg->getApplication();

/**
 * set_node()
 *
 * @return
 */
    function set_node ($parent,& $par)
    {
        $retval = '';
        if($par['num_subs'] )
        {
            $retval .= "\n".'<ul id="p'.$parent.'"';
            if ($parent != 0)
            {
                $retval .= ' class="page_list draggable"';
                if (isset ($_COOKIE['p'.$parent]) && $_COOKIE['p'.$parent] == '1')
                {
                     $retval .= ' style="display:block"';
                }
            } else {
                $retval .= ' class="draggable"';
            }
            $retval .= '>';
        }
        $retval .= ''."\n";
        return $retval;
    } // end of function set_node

/**
 * make_list()
 *
 * @return
 */
    function make_list($parent = 0, $editable_pages = 0) {
        // Get objects and vars from outside this function
        global $admin, $template, $database, $TEXT, $MESSAGE, $HEADING, $par;
        print set_node ($parent,$par);
        // Get page list from database
        $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
             .'WHERE `parent` = '.$parent.' '
             . ((PAGE_TRASH != 'inline') ?  'AND `visibility` != \'deleted\' ' : ' ' )
             . 'ORDER BY `position` ASC';
        $get_pages = $database->query($sql);
        // Insert values into main page list
        if($get_pages->numRows() > 0)
        {
            while($page = $get_pages->fetchAssoc())
            {
                // Get user perms
                $admin_groups = explode(',', str_replace('_', '', $page['admin_groups']));
                $admin_users  = explode(',', str_replace('_', '', $page['admin_users']));
                $in_group = FALSE;
                foreach($admin->get_groups_id() as $cur_gid)
                {
                    if (in_array($cur_gid, $admin_groups))
                    {
                        $in_group = TRUE;
                    }
                }
                if(($in_group) || is_numeric(array_search($admin->getUserId(), $admin_users)))
                {
                    if($page['visibility'] == 'deleted')
                    {
                        if (PAGE_TRASH == 'inline')
                        {
                            $can_modify = true;
                            $editable_pages = $editable_pages+1;
                        } else {
                            $can_modify = false;
                        }
                    } elseif($page['visibility'] != 'deleted')
                    {
                        $can_modify = true;
                        $editable_pages = $editable_pages+1;
                    }
                } else {
                    if($page['visibility'] == 'private')
                    {
                        continue;
                    } else {
                        $can_modify = false;
                    }
                }
                // Work out if we should show a plus or not
                $sql = 'SELECT `page_id`,`admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.$page['page_id'].' ';
                $sql .= (PAGE_TRASH != 'inline') ?  'AND `visibility` != \'deleted\' ' : ' ';
                // $sql .= ' ORDER BY `position` ASC';
                $get_page_subs = $database->query($sql);
                $num_subs = $get_page_subs->numRows();
                $par['num_subs'] = $num_subs;
                if ($get_page_subs->numRows() > 0) {
                    $display_plus = true;
                } else {
                    $display_plus = false;
                }
                // Work out how many pages there are for this parent
                $num_pages = $get_pages->numRows();
                $iLevel = $page['level'];
                $iPaddingLeft = 10;
                switch ($iLevel):
                    case '0':
                        $iPaddingLeft = 10;
                        break;
                    case '1':
                        $iPaddingLeft = $iLevel * 28;
                        break;
                    case '2':
                        $iPaddingLeft = $iLevel * 25;
                        break;
                    case '3':
                        $iPaddingLeft = $iLevel * 24;
                        break;
                    case '4':
                        $iPaddingLeft = $iLevel * 23;
                        break;
                    case '5':
                        $iPaddingLeft = $iLevel * 22;
                        break;
                    default:
                        $iPaddingLeft = $iLevel * 21;
                endswitch;
?>
                <li class="p<?php echo $page['parent']; ?>">
<?php
                  require 'dataRowsTable.php';
                if ( $page['parent'] == 0)
                {
                    $page_tmp_id = $page['page_id'];
                }
                // Get subs
                $editable_pages =  make_list($page['page_id'], $editable_pages);
                print ''."\n";
?>
                </li>
<?php
            }
        }
        $output = ($par['num_subs'] )? '</ul>'."\n" : '';
        $par['num_subs'] = (empty($output) ) ?  1 : $par['num_subs'];
        print $output;
        return $editable_pages;
    }// end of function make_list

// Generate pages list
    if ($admin->get_permission('pages_view') == true) {
?>

      <!-- -->
<article class="pages-block w3-margin">

        <h2 ><?php echo $HEADING['MODIFY_DELETE_PAGE']; ?></h2>
        <div class="jsadmin hide"></div>
        <div class="pages_tree">
        <div class="pages_list block-outer" >
        <table class="pages_list">
        <thead>
        <tr class="pages_list_header">
            <th class="header_list_menu_title">
                <?php echo $TEXT['VISIBILITY'] .' / ' .$TEXT['MENU_TITLE']; ?>:
            </th>
            <th class="header_list_page_title">
                <?php echo $TEXT['PAGE_TITLE']; ?>:
            </th>
            <th class="header_list_page_id">
                PID:
            </th>
            <th class="header_list_actions">
                <?php echo $TEXT['ACTIONS']; ?>:
            </th>
            <th > </th>
        </tr>
        </thead>
        </table>
<?php
    // Work-out if we should check for existing page_code
        $field_set = $database->field_exists(TABLE_PREFIX.'pages', 'page_code');

        $par = [];
        $par['num_subs'] = 1;
        $editable_pages = make_list(0, 0);
    } else {
        $editable_pages = 0;
    }
// eggsurplus: add child pages for a specific page
 ?></div>
<!--
</article>
-->
<script src="<?php print ADMIN_URL; ?>/pages/eggsurplus.js"></script>

<?php

    if(intval($editable_pages) == 0 ) {
?>
        <div class="empty_list">
            <?php echo $TEXT['NONE_FOUND']; ?>
        </div>
<?php
    }
// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(dirname($admin->correct_theme_source('pages.htt')));
// $template->debug = true;
    $template->set_file('page', 'pages.htt');
    $template->set_block('page', 'main_block', 'main');
// Insert values into the add page form
    $template->set_var('FTAN', $admin->getFTAN());

// Group list 1

    $query = "SELECT * FROM `".TABLE_PREFIX."groups`";
    $get_groups = $database->query($query);
    $template->set_block('main_block', 'group_list_block', 'group_list');
    // Insert admin group and current group first
    $admin_group_name = $get_groups->fetchRow(MYSQLI_ASSOC);
    $template->set_var([
                          'ID' => 1,
                          'TOGGLE' => '1',
                          'DISABLED' => ' disabled="disabled"',
                          'LINK_COLOR' => '000000',
                          'CURSOR' => 'default',
                          'NAME' => $admin_group_name['name'],
                          'CHECKED' => ' checked="checked"'
                          ]
                      );
    $template->parse('group_list', 'group_list_block', true);

    while($group = $get_groups->fetchRow(MYSQLI_ASSOC)) {
        // check if the user is a member of this group
        $flag_disabled = '';
        $flag_checked =  '';
        $flag_cursor =   'pointer';
        $flag_color =    '';
        if (in_array($group["group_id"], $admin->get_groups_id())) {
            $flag_disabled = ''; //' disabled';
            $flag_checked =  ' checked="checked"';
            $flag_cursor =   'default';
            $flag_color =    '000000';
        }

        // Check if the group is allowed to edit pages
        $system_permissions = explode(',', $group['system_permissions']);
        if(is_numeric(array_search('pages_modify', $system_permissions))) {
            $template->set_var([
                                  'ID' => $group['group_id'],
                                  'TOGGLE' => $group['group_id'],
                                  'CHECKED' => $flag_checked,
                                  'DISABLED' => $flag_disabled,
                                  'LINK_COLOR' => $flag_color,
                                  'CURSOR' => $flag_checked,
                                  'NAME' => $group['name'],
                                  ]
                                    );
            $template->parse('group_list', 'group_list_block', true);
        }
    }
// Group list 2

    $query = "SELECT * FROM `".TABLE_PREFIX."groups`";

    $get_groups = $database->query($query);
    $template->set_block('main_block', 'group_list_block2', 'group_list2');
    // Insert admin group and current group first
    $admin_group_name = $get_groups->fetchRow(MYSQLI_ASSOC);
    $template->set_var([
                'ID' => 1,
                'TOGGLE' => '1',
                'DISABLED' => ' disabled="disabled"',
                'LINK_COLOR' => '000000',
                'CURSOR' => 'default',
                'NAME' => $admin_group_name['name'],
                'CHECKED' => ' checked="checked"'
                ]
              );
    $template->parse('group_list2', 'group_list_block2', true);

    while($group = $get_groups->fetchRow(MYSQLI_ASSOC)) {
        // check if the user is a member of this group
        $flag_disabled = '';
        $flag_checked =  '';
        $flag_cursor =   'pointer';
        $flag_color =    '';
        if (in_array($group["group_id"], $admin->get_groups_id())) {
            $flag_disabled = ''; //' disabled';
            $flag_checked =  ' checked="checked"';
            $flag_cursor =   'default';
            $flag_color =    '000000';
        }

        $template->set_var([
                            'ID' => $group['group_id'],
                            'TOGGLE' => $group['group_id'],
                            'CHECKED' => $flag_checked,
                            'DISABLED' => $flag_disabled,
                            'LINK_COLOR' => $flag_color,
                            'CURSOR' => $flag_cursor,
                            'NAME' => $group['name'],
                            ]
                        );
        $template->parse('group_list2', 'group_list_block2', true);
    }

// Parent page list
// $database = new database();
/**
 * parent_list()
 *
 * @return
 */
    function parent_list($parent)
    {
        global $admin, $database, $template, $field_set;
        $query = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
              . 'WHERE `parent` = '.$parent.' '
              .   'AND `visibility` !=\'deleted\' '
              . 'ORDER BY `position` ';
        $get_pages = $database->query($query);
        while($page = $get_pages->fetchRow(MYSQLI_ASSOC)) {
            if($admin->page_is_visible($page)==false) {continue;}
    /* deprecated
            // if parent = 0 set flag_icon
            $template->set_var('FLAG_ADD_ICON','none');
            if( $page['parent'] == 0 && $field_set) {
                $template->set_var('FLAG_ADD_ICON', strtolower($page['language']));
            }
    */
            // Stop users from adding pages with a level of more than the set page level limit
            if( $page['level'] <= PAGE_LEVEL_LIMIT + 1 ) {
                // Get user perms
                $admin_groups = explode(',', str_replace('_', '', $page['admin_groups']));
                $admin_users = explode(',', str_replace('_', '', $page['admin_users']));

                $in_group = FALSE;
                foreach($admin->get_groups_id() as $cur_gid) {
                    if (in_array($cur_gid, $admin_groups)) {
                        $in_group = TRUE;
                    }
                }
                if(($in_group) || is_numeric(array_search($admin->getUserId(), $admin_users))) {
                    $can_modify = true;
                } else {
                    $can_modify = false;
                }
                // Title -'s prefix
                $title_prefix = '';
                for($i = 1; $i <= $page['level']; $i++) { $title_prefix .= ' - - &nbsp;'; }
                    $template->set_var([
                                    'ID' => $page['page_id'],
                                    'TITLE' => ($title_prefix.$page['menu_title']),
                                    'MENU-TITLE' => ($title_prefix.$page['menu_title']),
                                    'PAGE-TITLE' => ($title_prefix.$page['page_title'])
                                    ]);
                    if($can_modify == true) {
                        $template->set_var('DISABLED', '');
                    } else {
                        $template->set_var('DISABLED', ' disabled="disabled" class="disabled"');
                    }
                    $template->parse('page_list2', 'page_list_block2', true);
            }
            parent_list($page['page_id']);
        }
    }// end of function parent_list
    $template->set_block('main_block', 'page_list_block2', 'page_list2');
    if($admin->get_permission('pages_add_l0') == true) {
        $template->set_var(
                            [
                                'ID' => '0',
                                'TITLE' => $TEXT['NONE'],
                                'SELECTED' => ' selected="selected"',
                                'DISABLED' => ''
                            ]
                    );
        $template->parse('page_list2', 'page_list_block2', true);
    }
    parent_list(0);
// Explode module permissions
    $module_permissions = $_SESSION['MODULE_PERMISSIONS'];
// Modules list
    $template->set_block('main_block', 'module_list_block', 'module_list');
    $result = $database->query("SELECT * FROM `".TABLE_PREFIX."addons` WHERE `type` = 'module' AND `function` = 'page' ORDER BY `name`");
    if($result->numRows() > 0) {
        while ($module = $result->fetchRow(MYSQLI_ASSOC)) {
            // Check if user is allowed to use this module
            if (!is_numeric(array_search($module['directory'], $module_permissions))) {
                $template->set_var('VALUE', $module['directory']);
                $template->set_var('NAME', $module['name']);
                if($module['directory'] == 'wysiwyg') {
                    $template->set_var('SELECTED', ' selected="selected"');
                } else {
                    $template->set_var('SELECTED', '');
                }
                $template->parse('module_list', 'module_list_block', true);
            }
        }
    }
// Insert urls
    $template->set_var([
                                'THEME_URL' => THEME_URL,
                                'WB_URL' => WB_URL,
                                'ADMIN_URL' => ADMIN_URL,
                                ]
                        );
// Insert language headings
    $template->set_var([
                          'HEADING_ADD_PAGE' => $HEADING['ADD_PAGE'],
                          'HEADING_MODIFY_INTRO_PAGE' => $HEADING['MODIFY_INTRO_PAGE']
                          ]
                        );
// Insert language text and messages
    $template->set_var([
                        'TEXT_TITLE' => $TEXT['TITLE'],
                        'TEXT_TYPE' => $TEXT['TYPE'],
                        'TEXT_PARENT' => $TEXT['PARENT'],
                        'TEXT_VISIBILITY' => $TEXT['VISIBILITY'],
                        'TEXT_PUBLIC' => $TEXT['PUBLIC'],
                        'TEXT_PRIVATE' => $TEXT['PRIVATE'],
                        'TEXT_REGISTERED' => $TEXT['REGISTERED'],
                        'TEXT_HIDDEN' => $TEXT['HIDDEN'],
                        'TEXT_NONE' => $TEXT['NONE'],
                        'COLOR' => 'public',
                        'TEXT_MARKED_PUBLIC' => $TEXT['MARKED_PUBLIC'],
                        'TEXT_MARKED_PRIVATE' => $TEXT['MARKED_PRIVATE'],
                        'TEXT_MARKED_REGISTERED' => $TEXT['MARKED_REGISTERED'],
                        'TEXT_MARKED_HIDDEN' => $TEXT['MARKED_HIDDEN'],
                        'TEXT_MARKED_NONE' => $TEXT['MARKED_NONE'],
                        'TEXT_NONE_FOUND' => $TEXT['NONE_FOUND'],
                        'TEXT_ADD' => $TEXT['ADD'],
                        'TEXT_RESET' => $TEXT['RESET'],
                        'TEXT_ADMINISTRATORS' => $TEXT['ADMINISTRATORS'],
                        'TEXT_PRIVATE_VIEWERS' => $TEXT['PRIVATE_VIEWERS'],
                        'TEXT_REGISTERED_VIEWERS' => $TEXT['REGISTERED_VIEWERS'],
                        'INTRO_LINK' => $MESSAGE['PAGES_INTRO_LINK'],
                        ]
                        );

    $template->set_block('main_block', 'add_block', 'add');
    $template->set_block('main_block', 'intro_block', 'intro');
// Insert permissions values
    if($admin->get_permission('pages_add') != true) {
        $template->set_var('DISPLAY_ADD', 'hide');
        $template->set_block('add', '', '');
    } elseif($admin->get_permission('pages_add_l0') != true && $editable_pages == 0) {
        $template->set_var('DISPLAY_ADD', 'hide');
        $template->set_block('add', '', '');
    } else {
        $template->parse('add', 'add_block', true);
    }
    if($admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled') {
        $template->set_var('DISPLAY_INTRO', 'hide');
        $template->set_block('intro', '', '');
    } else {
        $template->parse('intro', 'intro_block', true);
    }
// Parse template object
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');
/*
// include the required file for Javascript admin
if(file_exists(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php')){
    include(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php');
}
*/
// Print admin
    $admin->print_footer(true);
    exit;