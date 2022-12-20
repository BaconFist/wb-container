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
 * @version         $Id: settings.php 375 2019-06-21 14:34:41Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/settings.php $
 * @lastmodified    $Date: 2019-06-21 16:34:41 +0200 (Fr, 21. Jun 2019) $
 *
 */
declare(strict_types=1);

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;
use addon\WBLingual\Lingual;

// Create new admin object
if (!\defined('SYSTEM_RUN')) {require((\dirname((__DIR__),2)).'/config.php');}
    $admin = new \admin('Pages', 'pages_settings');
// Include the WB functions file

    $mLang = \Translate::getInstance();
    $mLang->enableAddon(ADMIN_DIRECTORY.'\\'.\basename(__DIR__));
    $aLang = $mLang->getLangArray();

// Get page id
    $page_id = $admin->getIdFromRequest('page_id');
    if (!$page_id || !\is_numeric($page_id))
    {
//        $admin->print_header();
        $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_NOT_FOUND']);
        $admin->print_error($sErrorMsg);
    } else {
//        $page_id = \intval($_GET['page_id']);
    }

//if (!function_exists( 'create_access_file' ) ) { require(WB_PATH.'/framework/functions.php'); }
    if (!\function_exists( 'entities_to_7bit')) {require(WB_PATH.'/framework/functions-utf8.php');}

//  Work-out if we should check for existing page_code
    $sMultiLingualPath = Lingual::getLingualRel();
    $bIsMultilingual   = \file_exists(WB_PATH.$sMultiLingualPath);

    $cfg = array(
        'page_newstyle' => (\defined('PAGE_NEWSTYLE') ? PAGE_NEWSTYLE : '1'),
        'page_oldstyle' => (\defined('PAGE_OLDSTYLE') ? PAGE_OLDSTYLE : '0'),
    );
    foreach($cfg as $key=>$value) {
        db_update_key_value('settings', $key, $value);
    }
    $bPageNewstyle = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'page_newstyle\' ');
    $bPageOldstyle = $database->get_one('SELECT `value` FROM `'.TABLE_PREFIX.'settings` WHERE `name` = \'page_oldstyle\' ');

    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
         . 'WHERE `page_id` = '.(int)$page_id;
    $oPage = $database->query($sql);
    $aPages = $oPage->fetchRow(MYSQLI_ASSOC);
    if($database->is_error()) {
//        $admin->print_header();
        $admin->print_error($database->get_error());
    }
//  Get page details
    if ($oPage->numRows() == 0) {
//        $admin->print_header();
        $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_NOT_FOUND']);
        $admin->print_error($sErrorMsg);
    }
    $old_admin_groups = \explode(',', $aPages['admin_groups']);
    $old_admin_users  = \explode(',', $aPages['admin_users']);
    $in_old_group = FALSE;
    foreach($admin->get_groups_id() as $cur_gid)
    {
        if (\in_array($cur_gid, $old_admin_groups))
        {
            $in_old_group = TRUE;
        }
    }
    if((!$in_old_group) && !\is_numeric(\array_search($admin->getUserId(), $old_admin_users)))
    {
        $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sErrorMsg, ADMIN_URL);
    }
    $aCurrentPage['page_id'] = \basename($aPages['page_id']);
    $aCurrentPage['seo_title'] = \basename($aPages['link']);
//  Get display name of person who last modified the page
    $user=$admin->get_user_details($aPages['modified_by']);
//  Convert the unix ts for modified_when to human a readable form
    $modified_ts = 'Unknown';
    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
    if ($aPages['modified_when'] != 0)
    {
        $sModifyWhen = $aPages['modified_when']+TIMEZONE;
        $modified_ts = PreCheck::dateFormatToStrftime($sDateFormat. ' '.$sTimeFormat, $sModifyWhen);
    }
//  Setup template object, parse vars to it, then parse it
//  Create new template object
    $template = new Template(\dirname($admin->correct_theme_source('pages_settings.htt')));
   /**
    * Determines how much debugging output Template will produce.
    * This is a bitwise mask of available debug levels:
    * 0 = no debugging
    * 1 = debug variable assignments
    * 2 = debug calls to get variable
    * 4 = debug internals (outputs all function calls with parameters).
    * 8 = debug (outputs all set_block variables calls with parameters).
    *
    * Note: setting $this->debug = true will enable debugging of variable
    *
    */
    $template->setDebug(0);
    $template->set_file('page', 'pages_settings.htt');
    $template->set_block('page', 'main_block', 'main');
    $template->set_var(array(
                'FTAN' => $admin->getFTAN(),
                'ADMIN_URL' => ADMIN_URL,
                'WB_URL' => WB_URL,
                'THEME_URL' => THEME_URL,
                'LANGUAGE' => LANGUAGE,
                )
    );
    $template->set_var($aLang);

    $template->set_var(array(
                'MODIFIED_BY'          => $user['display_name'],
                'MODIFIED_BY_USERNAME' => $user['username'],
                'MODIFIED_WHEN'        => $modified_ts,
                )
        );

    $isTree = false;
// find out if parent have childs  $i < $limit &&
    $sLink = '';
//    $sLink = sprintf('%s',$aPages['link']);
    $aLink   = explode('/',$aPages['link']);
    $iLenght = count($aLink);
    $iLevel  = $aPages['level'];
    $i       = 0;
    $limit   = 10;
    while ($i < $iLenght) {
      if (!empty($aLink[$i])){
           $data   = $aLink[$i];
           $sLink .= $data.'/';
       }
       ++$i;
    }
    $sql   = '
    SELECT COUNT(*) subs
    FROM `'.TABLE_PREFIX.'pages` `pages`
    WHERE FIND_IN_SET(\''.$aPages['page_id'].'\', `page_trail`)
    ';
    $isTree  = ($database->get_one($sql) > 1);
    $sHelpUrl = ADMIN_URL.'/pages/languages/help_'.(isset($oReg->Language) && !empty($oReg->Language) ? $oReg->Language : $oReg->DefaultLanguage).'.php';

    $template->set_var(array(
                'PAGE_ID'              => $aPages['page_id'],
                // 'PAGE_IDKEY' => $admin->getIDKEY($aCurrentPage['page_id']),
                'PAGE_IDKEY'           => $aPages['page_id'],
                'PAGE_TITLE'           => htmlspecialchars($aPages['page_title']),
                'MENU_TITLE'           => htmlspecialchars($aPages['menu_title']),
                'SEO_TITLE'            => ($aCurrentPage['seo_title']=='') ? $aPages['menu_title'] : $aCurrentPage['seo_title'],
                'NEW_STYLE'            => ($isTree ? 'new-style-tree' : 'new-style-show'),
                'HELP_URL'             => $sHelpUrl,
                'TEXT_HEADER'          => $mLang->TEXT_SEO_NEWSTYLE_FORMAT,
                'LANGUAGE'             => strtolower(LANGUAGE ?? DEFAULT_LANGUAGE),
                'CHECKED_STYLE'        => ($bPageNewstyle  ? ' checked="checked"' : ''),
                'DESCRIPTION'          => htmlspecialchars($aPages['description']),
                'KEYWORDS'             => ($aPages['keywords']),
                )
        );
    $template->set_block('main_block', 'show_page_oldstyle_block', 'show_page_oldstyle');
    if ($bPageOldstyle){
        $aJson = [
        'page_id'    => $aPages['page_id'],
        'page_trail' => $aPages['page_trail'],
        'link'       => $aPages['link'],
        'lang'       => $aPages['language']
        ];
        $PagesJSON =json_encode($aJson);
//        echo json_decode($PagesJSON);
        $template->set_var('JSON',$PagesJSON);
        $sFilename =  basename($aPages['link']);
//        $sHelpText = htmlspecialchars(sprintf($mLang->HELP_ACCESS_FILE,$sFilename));
//        $template->set_var('p_menu_access_file',  p($sHelpText,'Change Access-File Format'));
        $template->parse('show_page_oldstyle', 'show_page_oldstyle_block', true);
    } else {
        $template->parse('show_page_oldstyle', '');
    }
    $template->set_block('main_block', 'show_section_block', 'show_section');
//  Work-out if we should show the "manage sections" link
    $sql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'sections` WHERE `page_id`='.$page_id.' AND `module`=\'menu_link\'';
    $sections_available = (\intval($database->get_one($sql)) != 0);
    if ($sections_available)
    {
        $template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');
        $template->parse('show_section', '');
    } elseif(MANAGE_SECTIONS == 'enabled')
    {
        $template->set_var('TEXT_MANAGE_SECTIONS', $HEADING['MANAGE_SECTIONS']);
        $template->parse('show_section', 'show_section_block', true);
    } else {
        $template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');
        $template->parse('show_section', '');
    }
/*-- show visibility select box --------------------------------------------------------*/
//  Visibility
    $template->set_block('main_block', 'show_visibility_block', 'show_visibility');
    $template->set_var('SELECT_TOOLTIP', '');
    $template->set_var('TEXT_PUBLIC', $mLang->TEXT_PUBLIC);
    $template->set_var('TEXT_PRIVATE', $mLang->TEXT_PRIVATE);
    $template->set_var('TEXT_REGISTERED', $mLang->TEXT_REGISTERED);
    $template->set_var('TEXT_HIDDEN', $mLang->TEXT_HIDDEN);
    $template->set_var('TEXT_NONE', $mLang->TEXT_NONE);
    $template->set_var('TEXT_MARKED_PUBLIC', $mLang->TEXT_MARKED_PUBLIC);
    $template->set_var('TEXT_MARKED_PRIVATE', $mLang->TEXT_MARKED_PRIVATE);
    $template->set_var('TEXT_MARKED_REGISTERED', $mLang->TEXT_MARKED_REGISTERED);
    $template->set_var('TEXT_MARKED_HIDDEN', $mLang->TEXT_MARKED_HIDDEN);
    $template->set_var('TEXT_MARKED_NONE', $mLang->TEXT_MARKED_NONE);

    if ($aPages['visibility'] == 'public') {
        $template->set_var('PUBLIC_SELECTED', ' selected="selected"');
        $template->set_var('TEXT_MARKED_PUBLIC', $mLang->TEXT_MARK_PUBLIC);
        $template->set_var('SELECT_TOOLTIP', $mLang->TEXT_MARK_PUBLIC);
    } elseif ($aPages['visibility'] == 'private') {
        $template->set_var('PRIVATE_SELECTED', ' selected="selected"');
        $template->set_var('TEXT_MARKED_PRIVATE', $mLang->TEXT_MARK_PRIVATE);
        $template->set_var('SELECT_TOOLTIP', $mLang->TEXT_MARK_PRIVATE);
    } elseif ($aPages['visibility'] == 'registered') {
        $template->set_var('REGISTERED_SELECTED', ' selected="selected"');
        $template->set_var('TEXT_MARKED_REGISTERED', $mLang->TEXT_MARK_REGISTERED);
        $template->set_var('SELECT_TOOLTIP', $mLang->TEXT_MARK_REGISTERED);
    } elseif ($aPages['visibility'] == 'hidden') {
        $template->set_var('HIDDEN_SELECTED', ' selected="selected"');
        $template->set_var('TEXT_MARKED_HIDDEN', $mLang->TEXT_MARK_HIDDEN);
        $template->set_var('SELECT_TOOLTIP', $mLang->TEXT_MARK_HIDDEN);
    } elseif ($aPages['visibility'] == 'none') {
        $template->set_var('NO_VIS_SELECTED', ' selected="selected"');
        $template->set_var('TEXT_MARKED_NONE', $mLang->TEXT_MARK_NONE);
        $template->set_var('SELECT_TOOLTIP', $mLang->TEXT_MARK_NONE);
    }

    $template->set_block('main_block', 'show_deleted_block', 'show_deleted');
    if ($aPages['visibility'] == 'deleted') {
        $template->set_var('DELETED_SELECTED', ' selected="selected"');
        $template->parse('show_deleted', 'show_deleted_block', true);
        $template->set_block('show_visibility_block','');
    } else {
        $template->set_block('show_deleted_block','');
        $template->parse('show_visibility', 'show_visibility_block', true);
    }
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'groups`';
    $get_groups = $database->query($sql);
    $template->set_block('main_block', 'group_list_block', 'group_list');
//  Insert admin group and current group first
    $admin_group_name = $get_groups->fetchRow(MYSQLI_ASSOC);
    $template->set_var(array(
                                'ID' => 1,
                                'TOGGLE' => '',
                                'DISABLED' => ' disabled="disabled"',
                                'LINK_COLOR' => '000000',
                                'CURSOR' => 'default',
                                'NAME' => $admin_group_name['name'],
                                'CHECKED' => ' checked="checked"'
                                )
                            );
    $template->parse('group_list', 'group_list_block', true);
    while($group = $get_groups->fetchRow(MYSQLI_ASSOC)) {
        // check if the user is a member of this group
        $flag_disabled = '';
        $flag_checked =  '';
        $flag_cursor =   'pointer';
        $flag_color =    '';
        if (\in_array($group["group_id"], $admin->get_groups_id())) {
            $flag_disabled = ''; //' disabled';
            $flag_checked =  ''; //' checked';
            $flag_cursor =   'default';
            $flag_color =    '000000';
        }
// Group list 1 (admin_groups)
//    $admin_groups = explode(',', str_replace('_', '', $aPages['admin_groups']));
        // Check if the group is allowed to edit pages
//        $system_permissions = explode(',', $group['system_permissions']);
//        if(is_numeric(array_search('pages_modify', $system_permissions))) {
        if ($admin->get_permission('pages_modify')) {
            $template->set_var(array(
                              'ID' => $group['group_id'],
                              'TOGGLE' => $group['group_id'],
                              'DISABLED' => $flag_disabled,
                              'LINK_COLOR' => $flag_color,
                              'CURSOR' => $flag_cursor,
                              'NAME' => $group['name'],
                              'CHECKED' => $flag_checked
                              )
                      );
//            if(is_numeric(array_search($group['group_id'], $admin_groups))) {
            if ($admin->is_group_match($group['group_id'], $aPages['admin_groups'])) {
                $template->set_var('CHECKED', ' checked="checked"');
            } else {
                if (!$flag_checked) $template->set_var('CHECKED', '');
            }
            $template->parse('group_list', 'group_list_block', true);
        }
    }
// Group list 2 (viewing_groups)
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'groups`';
    $get_groups = $database->query($sql);
    $template->set_block('main_block', 'group_list_block2', 'group_list2');
    // Insert admin group and current group first
    $admin_group_name = $get_groups->fetchRow(MYSQLI_ASSOC);
    $template->set_var(array(
                      'ID' => 1,
                      'TOGGLE' => '',
                      'DISABLED' => ' disabled="disabled"',
                      'LINK_COLOR' => '000000',
                      'CURSOR' => 'default',
                      'NAME' => $admin_group_name['name'],
                      'CHECKED' => ' checked="checked"'
                      )
              );
    $template->parse('group_list2', 'group_list_block2', true);
    while($group = $get_groups->fetchRow(MYSQLI_ASSOC))
    {
        // check if the user is a member of this group
        $flag_disabled = '';
        $flag_checked =  '';
        $flag_cursor =   'pointer';
        $flag_color =    '';
        if (\in_array($group["group_id"], $admin->get_groups_id()))
        {
            $flag_disabled = ''; //' disabled';
            $flag_checked =  ''; //' checked';
            $flag_cursor =   'default';
            $flag_color =    '000000';
        }
        $template->set_var(array(
                          'ID' => $group['group_id'],
                          'TOGGLE' => $group['group_id'],
                          'DISABLED' => $flag_disabled,
                          'LINK_COLOR' => $flag_color,
                          'CURSOR' => $flag_cursor,
                          'NAME' => $group['name'],
                          'CHECKED' => $flag_checked
                          )
                        );
//        $viewing_groups = explode(',', str_replace('_', '', $aPages['viewing_groups']));
//        if(is_numeric(array_search($group['group_id'], $viewing_groups)))
        if ($admin->is_group_match($group['group_id'], $aPages['viewing_groups']))
        {
            $template->set_var('CHECKED', 'checked="checked"');
        } else {
            if (!$flag_checked) {$template->set_var('CHECKED', '');}
        }
        $template->parse('group_list2', 'group_list_block2', true);
    }
// Show private viewers
if($aPages['visibility'] == 'private' OR $aPages['visibility'] == 'registered')
{
    $template->set_var('DISPLAY_VIEWERS', '');
} else {
    $template->set_var('DISPLAY_VIEWERS', 'display:none;');
}
//-- insert page_code 20090904-->
$template->set_var('DISPLAY_CODE_PAGE_LIST', ' id="multi_lingual" style="display:none;"');
// Work-out if page languages feature is enabled
if (
    (\defined('PAGE_LANGUAGES') && PAGE_LANGUAGES) &&
      $bIsMultilingual
    ) {
    // workout field is set but module missing
    $TEXT['PAGE_CODE'] = empty($TEXT['PAGE_CODE']) ? 'Pagecode' : $TEXT['PAGE_CODE'];
    $template->set_var( array(
            'DISPLAY_CODE_PAGE_LIST' => ' id="multi_lingual"',
            'URL_PAGE_CODE' => WB_URL.$sMultiLingualPath,
            'TEXT_PAGE_CODE' => '<a href="'.WB_URL.$sMultiLingualPath.'?page_id='.$page_id.'">'.$TEXT['PAGE_CODE'].'</a>',
        )
    );
    // Page_code list
    function page_code_list($parent){
        global $admin,$database,$template,$aPages;
        $default_language = DEFAULT_LANGUAGE;
        $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.$parent.' AND `language` = \''.$default_language.'\' ORDER BY `position` ASC';
        if ($oPages = $database->query($sql)){
            while($page = $oPages->fetchRow(MYSQLI_ASSOC)) {
                if (($admin->page_is_visible($page)==false) && ($page['visibility'] <> 'none') ) { continue; }
                // If the current page cannot be parent, then its children neither
                $list_next_level = true;
                // Stop users from adding pages with a level of more than the set page level limit
                if ($page['level']+1 < PAGE_LEVEL_LIMIT)
                {
                    // Get user perms
                    $admin_groups = \explode(',', \str_replace('_', '', $page['admin_groups']));
                    $admin_users  = \explode(',', \str_replace('_', '', $page['admin_users']));
                    $in_group = FALSE;
                    foreach($admin->get_groups_id() as $cur_gid)
                    {
                        if (\in_array($cur_gid, $admin_groups))
                        {
                            $in_group = TRUE;
                        }
                    }
                    if (($in_group) OR \is_numeric(\array_search($admin->getUserId(), $admin_users)))
                    {
                        $can_modify = true;
                    } else {
                        $can_modify = false;
                    }
                    $title_prefix = '';
                    for($i = 1; $i <= $page['level']; $i++) { $title_prefix .= ' - - &nbsp;'; }
                    // $space = str_repeat('&nbsp;', 3);  $space.'&lt;'..'&gt;'
                    $template->set_var(array(
                                      'VALUE' => $page['page_code'],
                                      'PAGE_VALUE' => $title_prefix.$page['menu_title'],
                                      'PAGE_CODE' => $page['page_id']
                                      )
                                    );

                    if ($aPages['page_code'] == $page['page_code'])
                    {
                        $template->set_var('SELECTED', ' selected="selected"');
                    } elseif($can_modify != true)
                    {
                        $template->set_var('SELECTED', ' disabled="disabled" class="disabled"');
                    } else {
                        $template->set_var('SELECTED', '');
                    }
                    $template->parse('page_code_list', 'page_code_list_block', true);
                }
                if ($list_next_level)
                    page_code_list($page['page_id']);
            } // end while
        } else{
        \trigger_error(sprintf('[%d] %s ',__LINE__,$database->get_error()), E_USER_WARNING);
        }
    } // end page_code_list
    // Insert code_page values from page to modify
    $template->set_block('main_block', 'page_code_list_block', 'page_code_list');

    if ($admin->get_permission('pages_add_l0') == true OR $aPages['level'] == 0)
    {
        if ($aPages['parent'] == 0) { $selected = ' selected'; } else { $selected = ''; }
    }
    // get pagecode form this page_id
       page_code_list(0);
}
//-- page code -->
// Parent page list
/* $database = new database();  */
function parent_list($parent)
{
    global $admin, $database, $template, $aPages;
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `parent` = '.$parent.' ORDER BY `position` ASC';
    $get_pages = $database->query($sql);
    while($page = $get_pages->fetchRow(MYSQLI_ASSOC))
    {
        if($admin->page_is_visible($page)==false) { continue; }
        // If the current page cannot be parent, then its children neither
        $list_next_level = true;
        // Stop users from adding pages with a level of more than the set page level limit
        if( $page['level']+1 < PAGE_LEVEL_LIMIT +1 )
        {
            // Get user perms
            $admin_groups = \explode(',', \str_replace('_', '', $page['admin_groups']));
            $admin_users  = \explode(',', \str_replace('_', '', $page['admin_users']));
            $in_group = FALSE;
            foreach($admin->get_groups_id() as $cur_gid)
            {
                if (\in_array($cur_gid, $admin_groups))
                {
                    $in_group = TRUE;
                }
            }
            if (($in_group) OR \is_numeric(\array_search($admin->getUserId(), $admin_users)))
            {
                $can_modify = true;
            } else {
                $can_modify = false;
            }
            // Title -'s prefix
            $title_prefix = '';

            for($i = 1; $i <= $page['level']; $i++) { $title_prefix .= ' - - &nbsp;'; }
            $template->set_var(array(
                                'ID' => $page['page_id'],
                                'TITLE' => ($title_prefix.$page['menu_title']),
                                'PAGE-TITLE' => ($title_prefix.$page['page_title']),
                                'MENU-TITLE' => ($title_prefix.$page['menu_title']),
                                'FLAG_ICON' => 'none',
                                ));
            if($aPages['parent'] == $page['page_id'])
            {
                $template->set_var('SELECTED', ' selected="selected"');
            } elseif($aPages['page_id'] == $page['page_id'])
            {
                $template->set_var('SELECTED', ' disabled="disabled" class="disabled"');
                $list_next_level=false;
            } elseif($can_modify != true)
            {
                $template->set_var('SELECTED', ' disabled="disabled" class="disabled"');
            } else {
                $template->set_var('SELECTED', '');
            }
            $template->parse('page_list2', 'page_list_block2', true);
        }
        if ($list_next_level)
        {
          parent_list($page['page_id']);
        }
    }
}
$template->set_block('main_block', 'page_list_block2', 'page_list2');

if ($admin->get_permission('pages_add_l0') == true OR $aPages['level'] == 0) {
    if($aPages['parent'] == 0)
    {
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }
    $template->set_var(array(
                        'ID' => '0',
                        'TITLE' => $TEXT['NONE'],
                        'SELECTED' => $selected
                        ) );
    $template->parse('page_list2', 'page_list_block2', true);
}
parent_list(0);
if($modified_ts == 'Unknown')
{
    $template->set_var('DISPLAY_MODIFIED', 'hide');
} else {
    $template->set_var('DISPLAY_MODIFIED', '');
}
// Templates list
$template->set_block('main_block', 'template_list_block', 'template_list');

$sql = 'SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" AND `function` = \'template\' order by `name`';
if (($res_templates = $database->query($sql)) )
{
    while($rec_template = $res_templates->fetchRow(MYSQLI_ASSOC))
    {
        // Check if the user has perms to use this template
        if ($rec_template['directory'] == $aPages['template'] OR $admin->get_permission($rec_template['directory'], 'template') == true)
        {
            $template->set_var('VALUE', $rec_template['directory']);
            $template->set_var('NAME', $rec_template['name']);
            if($rec_template['directory'] == $aPages['template'])
            {
                $template->set_var('SELECTED', ' selected="selected"');
            } else {
                $template->set_var('SELECTED', '');
            }
            $template->parse('template_list', 'template_list_block', true);
        }
    }
}
// Menu list
if (MULTIPLE_MENUS == false)
{
    $template->set_var('DISPLAY_MENU_LIST', 'display:none;');
}
// Include template info file (if it exists)
if($aPages['template'] != '')
{
    $template_location = WB_PATH.'/templates/'.$aPages['template'].'/info.php';
} else {
    $template_location = WB_PATH.'/templates/'.DEFAULT_TEMPLATE.'/info.php';
}
if (\file_exists($template_location))
{
    require($template_location);
}
// Check if $menu is set
if (!isset($menu[1]) OR $menu[1] == '')
{
    // Make our own menu list
    $menu[1] = $TEXT['MAIN'];
}
// Add menu options to the list
$template->set_block('main_block', 'menu_list_block', 'menu_list');
foreach($menu AS $number => $name)
{
    $template->set_var('NAME', $name);
    $template->set_var('VALUE', $number);
    if($aPages['menu'] == $number)
    {
        $template->set_var('SELECTED', ' selected="selected"');
    } else {
        $template->set_var('SELECTED', '');
    }
    $template->parse('menu_list', 'menu_list_block', true);
}
// Insert language values
$template->set_block('main_block', 'language_list_block', 'language_list');
$sql = 'SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = \'language\' ORDER BY `name`';
if (($res_languages = $database->query($sql)) )
{
    while($rec_language = $res_languages->fetchRow(MYSQLI_ASSOC))
    {
        $l_codes[$rec_language['name']] = $rec_language['directory'];
        $l_names[$rec_language['name']] = entities_to_7bit($rec_language['name']); // sorting-problem workaround
    }
    \asort($l_names);
    foreach($l_names as $l_name=>$v)
    {
        $langAddonIcons = (empty($l_codes[$l_name])) ? 'none' : \strtolower($l_codes[$l_name]);
        // Insert code and name
        $template->set_var(array(
                                'VALUE' => $l_codes[$l_name],
                                'NAME' => $l_name,

/* deprecated
                                'FLAG_ADDON_ICON' => $langAddonIcons,
*/
                                ));
        // Check if it is selected
        if($aPages['language'] == $l_codes[$l_name])
        {
            $template->set_var('SELECTED', ' selected="selected"');
        } else {
            $template->set_var('SELECTED', '');
        }
        $template->parse('language_list', 'language_list_block', true);
    }
}
// Select disabled if searching is disabled
if ($aPages['searching'] == 0)
{
    $template->set_var('SEARCHING_DISABLED', ' selected="selected"');
}
// Select what the page target is
switch ($aPages['target'])
{
    case '_top':
        $template->set_var('TOP_SELECTED', ' selected="selected"');
        break;
    case '_self':
        $template->set_var('SELF_SELECTED', ' selected="selected"');
        break;
    case '_blank':
        $template->set_var('BLANK_SELECTED', ' selected="selected"');
        break;
}
$template->set_var(array(
                'DISPLAY_ADVANCED' => ' disabled="disabled"',
            ) );

$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');
// Print admin footer
$admin->print_footer();

    function p($sContent,$sTitle='')
    {
        $retVal  = 'onmouseover="return overlib('
        . '\''.($sContent).'\','
        . 'CAPTION,'.$sTitle.','
        . 'FGCOLOR,\'#ffffff\','
        . 'BGCOLOR,\'#557c9e\','
        . 'BORDER,1,'
//        . 'WIDTH,'
//        . 'HEIGHT,'
//        . 'STICKY,'
        . 'CAPTIONSIZE,\'13px\','
        . 'CLOSETEXT,\'X\','
        . 'CLOSESIZE,\'14px\','
        . 'CLOSECOLOR,\'#ffffff\','
        . 'TEXTSIZE,\'12px\','
        . 'VAUTO,'
        . 'HAUTO,'
 //       . 'MOUSEOFF,'
        . 'WRAP,'
        . 'CELLPAD,5'
        . ')" onmouseout="return nd()"';
        return $retVal;
    }



/**
* replace varnames with values in a string
*
* @param string $subject: stringvariable with vars placeholder
* @param array $replace: values to replace vars placeholder
* @return string
*/
function replaceVars($subject = '', $replace = null )
{
    if (\is_array($replace)==true)
    {
        foreach ($replace  as $key => $value) {
            $subject = \str_replace("{{".$key."}}", $value, $subject);
        }
    }
    return $subject;
}
