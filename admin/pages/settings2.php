<?php
/**
 *
 * @category        admin
 * @package         pages
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: settings2.php 191 2019-01-29 17:14:41Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/settings2.php $
 * @lastmodified    $Date: 2019-01-29 18:14:41 +0100 (Di, 29. Jan 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
//use vendor\phplib\Template;

/* ****************************************************************** */
// Function to fix page trail of subs
    function fix_page_trail($parent,$root_parent){
        // Get objects and vars from outside this function
        global $admin, $template, $database, $TEXT, $MESSAGE;
        // Get page list from database
        // $database = new database();
        $sql = 'SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` '
             . 'WHERE `parent`='.(int)$parent;
        // Insert values into main page list
        if (($get_pages = $database->query($sql))) {
        // Insert values into main page list
            while(($page = $get_pages->fetchRow(MYSQLI_ASSOC))) {
                // Fix page trail
                $sql = 'UPDATE `'.TABLE_PREFIX.'pages` '
                     . 'SET `page_trail`=\''.get_page_trail($page['page_id']).'\' '
                     .     ($root_parent != 0 ? ',`root_parent`='.(int)$root_parent.' ' : '')
                     . 'WHERE `page_id`='.(int)$page['page_id'];
                $database->query($sql);
                // Run this query on subs
                fix_page_trail($page['page_id'],$root_parent);
            }
        }
    }
/* ****************************************************************** */
// inherit settings to subpages
// <Subpages should inherit settings.>
/*
 * inheritable settings:
 *   template
 *   language
 *   menu
 *   searching
 *   visibility
 *   - admin_groups
 *   - admin_users
 *   - viewing_groups
 *   - viewing_users
 */

    function doInheritSettings($database, $page_id, array $aSettings){
        // deactivate doInheritSettings
        if (\sizeof($aSettings)==0){return false;}
        $sqlSet = '';
        foreach ($aSettings as $sFieldname=>$sValue) {
            $sqlSet .= '`'.$sFieldname.'`=\''.$database->escapeString($sValue).'\', ';
        }
        $sqlSet = \rtrim($sqlSet, ' ,');
        if ($sqlSet) {
            $aListOfChildren = [];
            $aMatches = array($page_id);
            // search all children
            do {
                $sql = 'SELECT `page_id` FROM `'.TABLE_PREFIX.'pages` '
                     . 'WHERE `parent` IN('.\implode(',', $aMatches).')';
                if (($oChildren = $database->query($sql))) {
                    $aMatches = [];
                    while (($aChild = $oChildren->fetchRow(MYSQLI_ASSOC))) {
                        $aMatches[] = $aChild['page_id'];
                    }
                    $aListOfChildren = \array_merge($aListOfChildren, $aMatches);
                }
            } while (\sizeof($aMatches) > 0);
            $sqlSet = 'UPDATE `'.TABLE_PREFIX.'pages` SET '.$sqlSet.' '
                    . 'WHERE `page_id` IN('.\implode(',', $aListOfChildren).')';
            $database->query($sqlSet);
        }
    }
/* ****************************************************************** */

// Create new admin object and print admin header
if (!\defined('SYSTEM_RUN')) {require(((\dirname((__DIR__),2))).'/config.php');}
// suppress to print the header, so no new FTAN will be set
    $admin = new \admin('Pages', 'pages_settings',false);
// Get page id
    if (!isset($_POST['page_id']) || !\is_numeric($_POST['page_id']))
    {
        $admin->print_header();
        $admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
    } else {
        $page_id = (int)$_POST['page_id'];
    }
    $target_url = ADMIN_URL.'/pages/settings.php?page_id='.$page_id;
    $pagetree_url = ADMIN_URL.'/pages/index.php';
    $bBackLink = isset($_POST['pagetree']);
    $parent_section = '';
    $iParentLevel = 0;
    $bCaseSensitiv = true; // TODO set by settings
/*
if( (!($page_id = $admin->checkIDKEY('page_id', 0, $_SERVER['REQUEST_METHOD']))) )
{
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS']);
}
*/
    if (!$admin->checkFTAN())
    {
        $admin->print_header();
        $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], $target_url);
    }
// After FTAN check print the header
    $admin->print_header();
// Include the WB functions file
//    if (!\function_exists('create_access_file')) {require(WB_PATH.'/framework/functions.php');}

// Get values
    $page_title  = htmlspecialchars_decode($admin->StripCodeFromText($admin->get_post('page_title')));
    $menu_title  = htmlspecialchars_decode($admin->StripCodeFromText($admin->get_post('menu_title')));
    $seo_title   = $admin->StripCodeFromText($admin->get_post('seo_title'));
    $page_code   = \intval($admin->get_post('page_code')) ;
    $description = htmlspecialchars_decode($admin->StripCodeFromText($admin->get_post('description')));
    $keywords    = $admin->StripCodeFromText($admin->get_post('keywords'));
    $parent      = \intval($admin->get_post('parent')); // fix secunia 2010-91-3
    $visibility  = $admin->StripCodeFromText($admin->get_post('visibility'));
    if (!\in_array($visibility, array('public', 'private', 'registered', 'hidden', 'none'))) {$visibility = 'public';} // fix secunia 2010-93-3
    $template = \preg_replace('/[^a-z0-9_-]/i', "", $admin->get_post('template')); // fix secunia 2010-93-3
    $template = (($template == DEFAULT_TEMPLATE ) ? '' : $template);
    $target   = \preg_replace("/\W/", "", $admin->get_post('target'));
    $admin_groups = ($admin->get_post('admin_groups'));
    $viewing_groups = ($admin->get_post('viewing_groups'));
    $searching = \intval($admin->get_post('searching'));
    $language  = $admin->StripCodeFromText(\strtoupper($admin->get_post('language')));
    $language  = (\preg_match('/^[A-Z]{2}$/', $language) ? $language : DEFAULT_LANGUAGE);
    $menu      = \intval($admin->get_post('menu')); // fix secunia 2010-91-3
    $bPageOldstyle = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'page_oldstyle\' ');
//  ever set to new file format if oldstyle disabled
    if ($bPageOldstyle) {
        $bPageNewstyle  = (int)($admin->get_post('page_newstyle'));
        $bPagesNewStyle = (!\is_null($bPageNewstyle) ? \filter_var($bPageNewstyle, \FILTER_VALIDATE_BOOLEAN) : true);
    } else {
        $bPageNewstyle = true;
    }
// Validate data
    if ($menu_title == '' || \substr($menu_title,0,1)=='.'){
        $admin->print_error($MESSAGE['PAGES_BLANK_MENU_TITLE']);
    }
    if ( \trim($page_title)  == '' || \substr( $page_title, 0, 1) == '.') { $page_title = $menu_title; }
    if ( \trim($seo_title)   == '' || \substr( $seo_title, 0, 1)  == '.') { $seo_title  = $menu_title; }

// fetch old datas
    $sql = 'SELECT `level`,`root_parent`, `parent`,`page_trail`,`link`,`position`,`admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` '
         . 'WHERE `page_id`='.$page_id;
    $oPages = $database->query($sql);
    if (($aPages = $oPages->fetchRow(MYSQLI_ASSOC))){
        $iParentLevel = $aPages['level'];
        $old_parent   = $aPages['parent'];
        $old_link     = $aPages['link'];
        $old_position = $aPages['position'];
        $old_admin_groups = \explode(',', \str_replace('_', '', $aPages['admin_groups']));
        $old_admin_users  = \explode(',', \str_replace('_', '', $aPages['admin_users']));
    }
// Work-out if we should check for existing page_code
    $field_set = $database->field_exists(TABLE_PREFIX.'pages', 'page_code');
    $in_old_group = false;
    foreach($admin->get_groups_id() as $cur_gid){
        if (\in_array($cur_gid, $old_admin_groups)) { $in_old_group = TRUE; }
    }
    if ((!$in_old_group) && !\is_numeric(\array_search($admin->getUserId(), $old_admin_users))){
        $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }
// Setup admin groups
    $admin_groups[] = 1;
//if(!in_array(1, $admin->get_groups_id())) {
//    $admin_groups[] = implode(",",$admin->get_groups_id());
//}
    $admin_groups = \preg_replace("/[^\d,]/", "", \implode(',', $admin_groups));
// Setup viewing groups
    $viewing_groups[] = 1;
//if(!in_array(1, $admin->get_groups_id())) {
//    $viewing_groups[] = implode(",",$admin->get_groups_id());
//}
    $viewing_groups = \preg_replace("/[^\d,]/", "", \implode(',', $viewing_groups));
// If needed, get new order
    if ($parent != $old_parent){
        // Include ordering class
        $order = new \order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
        // Get new order
        $position = $order->get_new($parent);
        // Clean new order
        $order->clean($parent);
    } else {
        $position = $old_position;
    }

// Work out level and root parent
    $level = '0';
    $root_parent = '0';
    if ($parent != '0'){
        $level = level_count($parent)+1;
        $root_parent = root_parent($parent);
    }
// Work-out what the link should be
    if ($parent == '0'){
//        $link = '/'.page_filename( $seo_title,$bPageNewstyle);
        $link = '/'.PreCheck::sanitizeFilename($seo_title,$bCaseSensitiv,$bPageNewstyle);
        // rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php
        if ($link == '/index' || $link == '/intro'){
            $filename = WB_PATH.PAGES_DIRECTORY.'/'.$link.'_'.$page_id .PAGE_EXTENSION;
            $link .= '_' .$page_id;
        } else {
            $filename = WB_PATH.PAGES_DIRECTORY.'/'.$link.PAGE_EXTENSION;
        }
    } else {
        $parent_section = '';
        $parent_links = \array_reverse(get_parent_links($parent));
        foreach($parent_links as $parent_link) {
      //let the parent as is
//          $parent_section .= page_filename($parent_link, $bPageNewstyle).'/';
        $parent_section .= PreCheck::sanitizeFilename($parent_link,$bCaseSensitiv,$bPageNewstyle).'/';
        } //  end foreach
        if ( $parent_section == '/' ) { $parent_section = ''; }
//        $link = '/'.$parent_section.page_filename($seo_title,$bPageNewstyle);
        $link = '/'.$parent_section.PreCheck::sanitizeFilename($seo_title,$bCaseSensitiv,$bPageNewstyle);
        $filename = WB_PATH.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
    }
//echo nl2br(sprintf("<div class='w3-border w3-padding w3-margin-left'>[%04d] %s</div>\n",__LINE__,$link));
//  Check if a page with same page filename exists $oGetSamePage
    $sql = 'SELECT  COUNT(*) FROM `'.TABLE_PREFIX.'pages` '
         . 'WHERE `link` = \''.$database->escapeString($link).'\' '
         .       'AND `page_id` != '.(int)$page_id;
    if ($iNumRow=$database->get_one($sql)) {
        $admin->print_error( $MESSAGE['PAGES_PAGE_EXISTS'] );
    }
//  Update page with new order
    $sql = 'UPDATE `'.TABLE_PREFIX.'pages` '
         . 'SET `parent`='.(int)$parent.', `position`='.(int)$position.' '
         . 'WHERE `page_id`='.(int)$page_id;
    $database->query($sql);
//  Get page trail
    $page_trail = get_page_trail($page_id);
    $target_url = ADMIN_URL.'/pages/settings.php?page_id='.$page_id;
//  Update page settings in the pages table
    $sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
          . '`parent` = '.(int)$parent.', '
          . '`page_title` = \''.$database->escapeString($page_title).'\', '
          . '`menu_title` = \''.$database->escapeString($menu_title).'\', '
          . '`menu` = '.(int)$menu.', '
          . '`level` = '.(int)$level.', '
          . '`page_trail` = \''.$database->escapeString($page_trail).'\', '
          . '`root_parent` = '.(int)$root_parent.', '
          . '`link` = \''.$database->escapeString($link).'\', '
          . '`template` = \''.$database->escapeString($template).'\', '
          . '`target` = \''.$database->escapeString($target).'\', '
          . '`description` = \''.$database->escapeString($description).'\', '
          . '`keywords` = \''.$database->escapeString($keywords).'\', '
          . '`position` = '.(int)$position.', '
          . '`visibility` = \''.$database->escapeString($visibility).'\', '
          . '`searching` = '.(int)$searching.', '
          . '`language` = \''.$database->escapeString($language).'\', '
          . '`admin_groups` = \''.$database->escapeString($admin_groups).'\', '
          . '`viewing_groups` = \''.$database->escapeString($viewing_groups).'\' '
          .  (\defined('PAGE_LANGUAGES') && PAGE_LANGUAGES && $field_set  ? ', `page_code` = '.(int)$page_code.' ' : '')
          . 'WHERE `page_id` = '.(int)$page_id;
    if(!$database->query($sql)) {
        if($database->is_error())
        {
            $admin->print_error($database->get_error(), $target_url );
        }
    }
//  set new format back to true
    $bPageNewstyle  = '1';
    $cfg = array(
        'page_newstyle' => (\defined('PAGE_NEWSTYLE') && (PAGE_NEWSTYLE != $bPageNewstyle) ? $bPageNewstyle : $bPageNewstyle),
    );
    foreach($cfg as $key=>$value) {
        db_update_key_value('settings', $key, $value);
    }

/* *** inherit settings to subpages ********************************* */
    if (isset($_POST['inherit'])) {
    //  make sure, $aPost is am array
        $aPost = (\is_array($_POST['inherit'])
                  // use the array itself
                  ? $_POST['inherit']
                  // split the string into an array
                  : \preg_split("/[\s,;\|]+/", $_POST['inherit'], -1, PREG_SPLIT_NO_EMPTY));
    //  define possible fields to inherit
        $aInherit = array('template','menu','language','searching','visibility');
    //  define additional fields to 'visibility'
        $aVisibilities = array('admin_groups','admin_users','viewing_groups','viewing_users');
    //  if 'all' is not selected
        if (!\in_array('all', $aPost)) {
        //  remove all not selected fields
            $aInherit = \array_intersect($aInherit, $aPost);
        }
    //  if 'visibility' is selected
        if (\in_array('visibility', $aInherit)) {
        //  add the additional fields
            $aInherit = \array_merge($aInherit, $aVisibilities);
        }
    //  flip array and set all values to ''
        $aInherit = \array_fill_keys($aInherit, '');
    //  iterate all existing fields
        foreach ($aInherit as $key=>$value) {
        //  fill with real values (i.e.  $aInherit['template'] = $template)
            $aInherit[$key] = isset(${$key}) ? ${$key} : '';
        }
    //  update database
        doInheritSettings($database, $page_id, array());// $aInherit
    }
/* ****************************************************************** */
//  Clean old order if needed
    if  ($parent != $old_parent)
    {
        $order->clean($old_parent);
    }
/*  BEGIN page "access file" code */
//  Create a new file in the /pages dir if title changed
    if (!\is_writable(WB_PATH.PAGES_DIRECTORY.'/')){
        $admin->print_error($MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE']);
    } else {
        $old_filename = WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION;
    //  First check if we need to create a new file
        if (($old_link != $link) || (!\file_exists($old_filename)))
        {
        //  Delete old file
            $old_filename = WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION;
            if (\file_exists($old_filename))
            {
                @\unlink($old_filename);
            }
        //  Create access file
            create_access_file($filename,$page_id,$level);
        //  Move a directory for this page
            if (\file_exists(WB_PATH.PAGES_DIRECTORY.$old_link.'/') && is_dir(WB_PATH.PAGES_DIRECTORY.$old_link.'/'))
            {
                @\rename(WB_PATH.PAGES_DIRECTORY.$old_link.'/', WB_PATH.PAGES_DIRECTORY.$link.'/');
            }
        //  Update any pages that had the old link with the new one
            $old_link_len = \strlen($old_link);
            $sql = 'SELECT `page_id`,`link`,`level` FROM `'.TABLE_PREFIX.'pages` '
                 . 'WHERE `link` LIKE \'%'.\addcslashes($old_link, '%_').'/%\' '
                 . 'ORDER BY `level` ASC';
            if (($query_subs = $database->query($sql))) {
                while($sub = $query_subs->fetchRow(MYSQLI_ASSOC))
                {
                //  Double-check to see if it contains old link
                    if (\substr($sub['link'], 0, $old_link_len) == $old_link)
                    {
                    //  Get new link
                        $replace_this = $old_link;
                        $old_sub_link_len = \strlen($sub['link']);
                        $new_sub_link = $link.'/'.\substr($sub['link'],$old_link_len+1,$old_sub_link_len);
                    //  Work out level
                        $new_sub_level = level_count($sub['page_id']);
                    //  Update level and link
                        $sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
                              . '`link` = \''.$database->escapeString($new_sub_link).'\', '
                              . '`level` = '.(int)$new_sub_level.' '
                              . 'WHERE `page_id` = '.(int)$sub['page_id'];
                        $database->query( $sql );
                    //  Re-write the access file for this page
                        $old_subpage_file = WB_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION;
                        if (\file_exists($old_subpage_file))
                        {
                            @\unlink($old_subpage_file);
                        }
                        create_access_file(WB_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION, $sub['page_id'], $new_sub_level);
                    }
                }
            }
        }
    }

//  Fix sub-pages page trail
    fix_page_trail($page_id,$root_parent);
/*  END page "access file" code */
//  Check if there is a db error, otherwise say successful
    if($database->is_error())
    {
        $admin->print_error($database->get_error(), $target_url );
    } elseif ( $bBackLink ) {
        $admin->print_success($MESSAGE['PAGES_SAVED_SETTINGS'], $pagetree_url );
    } else {
        $admin->print_success($MESSAGE['PAGES_SAVED_SETTINGS'], $target_url );
    }

// Print admin footer
$admin->print_footer();
