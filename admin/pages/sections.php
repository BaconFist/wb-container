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
 * Description of admin/pages/sections.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: sections.php 271 2019-03-21 17:30:01Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;

    $ds         = DIRECTORY_SEPARATOR;
    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.\basename($sModulesPath).'/'.$sAddonName;
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}
    $sLocalDebug  =  is_readable($sAddonPath.'/.setDebug');
    $sSecureToken = !is_readable($sAddonPath.'/.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

// Include config file
//if (!\defined('SYSTEM_RUN') ){ require( \dirname(\dirname((__DIR__))).'/config.php' );}
try {

    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;

    $sBackLink = ADMIN_URL.'/pages/index.php';
// Make sure people are allowed to access this page
    if (MANAGE_SECTIONS != 'enabled')
    {
        $aMessage = \sprintf("%s\n",$MESSAGE['SECTIONS_NO_ACTIVE']);
        throw new \Exception ($aMessage);
//        \header('Location: '.ADMIN_URL.'/pages/index.php');
//        exit(0);
    }
/* */
//    $bDebug = false; // to show position and section_id
    if (!\defined('PAGE_DEBUG')) {\define('PAGE_DEBUG',$sLocalDebug);}
// Include the WB functions file
//    if (!\function_exists( 'create_access_file')) {require($sAppPath.'/framework/functions.php');}
    $oLang = \Translate::getInstance();
    $oLang->enableAddon(ADMIN_DIRECTORY.'\\'.\basename(__DIR__));
    $aLang = $oLang->getLangArray();
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars = [];
// get POST or GET requests, never both at once
    $aVars = $oRequest->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oRequest->getParam($sName);
    }

// Create new admin object
    $admin = new \admin('Pages', 'pages_modify', false);
    $action = 'show';
/*
    $requestMethod = \strtoupper($_SERVER['REQUEST_METHOD']);
    $aRequestVars  = (isset(${'_'.$requestMethod}) ? ${'_'.$requestMethod} : $_REQUEST);
*/
// Get page id
//    $page_id = \intval(isset($aRequestVars['page_id']) ? $aRequestVars['page_id'] : 0);
// Get page id (on error page_id == 0))
    $page_id = $admin->getIdFromRequest('page_id');

    $action = ($page_id ? 'show' : $action);
// Get section id if there is one
//$section_id = intval((@$aRequestVars['section_id']) ?: 0);
//    $section_id = \intval(isset($aRequestVars['section_id']) ? (\bin\SecureTokens::checkIDKEY($aRequestVars['section_id'])) : 0);
// Get section id (on error section_id == 0))

    $section_id = $admin->getIdFromRequest('section_id');

    $action = ($section_id ? 'delete' : $action);

// Get module if there is one
    $module = ($aRequestVars['module'] ?? '');
    $action = ($module != '' ? 'add' : $action);

    $admin_header = true;
    $backlink = ADMIN_URL.'/pages/sections.php?page_id='.(int)$page_id;
    switch ($action):
        case 'delete' :
            if (!$section_id){
                if($admin_header) { $admin->print_header(); }
                $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'_idkey::';
                $sDEBUG=($sLocalDebug ? $sInfo : '');
                $admin->print_error($sDEBUG.$oLang->MESSAGE_GENERIC_SECURITY_ACCESS, $backlink);
            }
            $action = 'show';
            $sql  = 'SELECT `module` FROM `'.TABLE_PREFIX.'sections` '
                  . 'WHERE `section_id` ='.$section_id;
            if ((($modulname = $database->get_one($sql))) && ($section_id > 0)) {
                // Include the modules delete file if it exists
                if (\file_exists(WB_PATH.'/modules/'.$modulname.'/delete.php')){
                    require(WB_PATH.'/modules/'.$modulname.'/delete.php');
                }
                $sql  = 'DELETE FROM `'.TABLE_PREFIX.'sections` '
                      . 'WHERE `section_id` ='.(int)$section_id;
                if( !$database->query($sql) ) {
                    if($admin_header) { $admin->print_header(); }
                    $admin->print_error($database->get_error(),$backlink);
                } else {
                    $order = new \order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
                    $order->clean($page_id);
                    $message = \sprintf ($oLang->TEXT_SECTION_DELETE, \strtoupper($modulname),$section_id);
                    if($admin_header) { $admin->print_header(); }
                    $admin_header = false;
                    unset($_POST);
                    $admin->print_success($message, $backlink );
                }
            } else {
                if($admin_header) { $admin->print_header(); }
                $admin->print_error($module.' '.\strtolower($oLang->TEXT_NOT_FOUND), $backlink);
            }
            break;
        case 'add' :
            if (!$admin->checkFTAN())
            {
                $admin->print_header();
                $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
                $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
                $admin->print_error($sDEBUG.$oLang->MESSAGE_GENERIC_SECURITY_ACCESS, $backlink);
            }
            $action = 'show';
            $module = \preg_replace('/\W/', '', $module );  // fix secunia 2010-91-4
    //        require_once(WB_PATH.'/framework/class.order.php');
            // Get new order
            $order = new \order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
            $position = $order->get_new($page_id);
        // Insert module into DB
/* --------------------------------------------------------------------------------------- */
            $sql  = 'INSERT INTO `'.TABLE_PREFIX.'sections` SET '
                  . '`page_id` = '.(int)$page_id.', '
                  . '`module` = \''.$module.'\', '
                  . '`position` = '.(int)$position.', '
                  . '`attribute`=\'\', '
                  . '`block` = 1';
/* --------------------------------------------------------------------------------------- */
// you can test without adding a section in table
            $bAddSection =  true; // disable inserting section, only call add.php from module
            if (!$bAddSection) {
                $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'sections` '
                      . 'WHERE `module` = \''.$module.'\' ';
            }
            if ($oSection = $database->query($sql)) {
                // only for testing section management
                if (!$bAddSection) {
                    $aSection = $oSection->fetchAssoc(MYSQLI_ASSOC);
                    $section_id = $aSection['section_id'];
                    $page_id = $aSection['page_id'];
                } else {
                // Get the section id   get_one("SELECT LAST_INSERT_ID()")
                    $section_id = $database->getLastInsertId();
                }
                // Include the selected modules add file if it exists
                if (
                    \file_exists(WB_PATH.'/modules/'.$module.'/addon.php') &&
                    (\is_dir(WB_PATH.'/modules/'.$module.'/cmd'))
                ) { break; }
                if (\file_exists(WB_PATH.'/modules/'.$module.'/add.php'))
                {
                    require(WB_PATH.'/modules/'.$module.'/add.php');
                }
            } elseif ($database->is_error())  {
                if($admin_header) { $admin->print_header();}
                $admin->print_error($database->get_error());
            }
            break;
        default:
            break;
    endswitch;

    switch ($action):
        default:
            if($admin_header) { $admin->print_header(); }
            // Get perms
            $sql  = 'SELECT `admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` '
                  . 'WHERE `page_id` = '.$page_id;
            $oPages = $database->query($sql);
            $aPages = $oPages->fetchRow(MYSQLI_ASSOC);
            $in_old_group = $admin->is_group_match($aPages['admin_groups'],$admin->get_groups_id());

            if ((!$in_old_group) && !\is_numeric(\array_search($admin->getUserId(), $old_admin_users)))
            {
                $admin->print_header();
                $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
                $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
                $admin->print_error($sDEBUG.$oLang->MESSAGE_PAGES_INSUFFICIENT_PERMISSIONS);
            }
            // Get page details
            $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` '
                  . 'WHERE `page_id` = '.$page_id;
            $results = $database->query($sql);
            if($database->is_error())
            {
                // $admin->print_header();
                $admin->print_error($database->get_error());
            }
            if($results->numRows() == 0)
            {
                // $admin->print_header();
                $admin->print_error($oLang->MESSAGE_PAGES_NOT_FOUND);
            }
            $results_array = $results->fetchRow(MYSQLI_ASSOC);
            // Set module permissions
            $aAllowedModules = [];
            $sAllowedModules = '';
            $module_permissions = $_SESSION['MODULE_PERMISSIONS'];
            $sAllowedModules = \implode(', ',
                               \array_map(function($item) use ($database){
                                             return '\''.$database->escapeString($item) .'\'';
                                         }, $module_permissions));
            $sqlAddons  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` '
                        . 'WHERE `type` = \'module\' '
                        .   ($sAllowedModules?'AND `directory` NOT IN ('.$sAllowedModules.')' :'')
                        . 'ORDER BY `name`';
            if($oAddons = $database->query($sqlAddons))
            {
                while($aAddons = $oAddons->fetchRow(MYSQLI_ASSOC))
                {
                   $aAllowedModules[] = $aAddons['directory'];
                }
            }

            $sAllowedModules = '';
            $sAllowedModules = \implode(', ',
                               \array_map(function($item) use ($database){
                                             return '\''.$database->escapeString($item) .'\'';
                                         }, $aAllowedModules));
            // Unset block var
            unset($block);
            // Include template info file (if it exists)
            if($results_array['template'] != '')
            {
                $template_location = WB_PATH.'/templates/'.$results_array['template'].'/info.php';
            } else {
                $template_location = WB_PATH.'/templates/'.DEFAULT_TEMPLATE.'/info.php';
            }
            if (\file_exists($template_location))
            {
                require($template_location);
            }
            // Check if $menu is set
            if(!isset($block[1]) || $block[1] == '')
            {
                // Make our own menu list
                $block[1] = $TEXT['MAIN'];
            }
            // Get display name of person who last modified the page
            $user=$admin->get_user_details($results_array['modified_by']);
            // Convert the unix ts for modified_when to human a readable form
            $modified_ts = 'Unknown';
            if($results_array['modified_when'] != 0)
            {
                $sModifyWhen = $results_array['modified_when']+TIMEZONE;
                $modified_ts = PreCheck::dateFormatToStrftime($sDateFormat.' '.$sTimeFormat, $sModifyWhen);
            }
            /*-- load css files with jquery --*/
            // include jscalendar-setup
            $jscal_use_time = true; // whether to use a clock, too
            require(WB_PATH."/include/jscalendar/wb-setup.php");
            // Setup template object, parse vars to it, then parse it
            // Create new template object
            $tpl = new Template(dirname($admin->correct_theme_source('pages_sections.htt')));
    //        $tpl->debug = true;
            $tpl->set_file('page', 'pages_sections.htt');
            $tpl->set_block('page', 'main_block', 'main');
            $tpl->set_block('main_block', 'module_block', 'module_list');
            $tpl->set_block('main_block', 'show_section_block', 'show_section');
            $tpl->set_block('main_block', 'calendar_block', 'calendar_list');
            $tpl->set_block('show_section_block', 'can_delete_block', 'can_delete');
            $tpl->set_var('FTAN', $admin->getFTAN());
            // setting trash only if more than one section exists
            $sTmpLang  = '';
            $sql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'sections` '
                 . 'WHERE `page_id`='.\intval($page_id).' '
                 .   'AND `module` IN ('.$sAllowedModules.')';
            if ($bSectionCanDelete = ($database->get_one($sql)) ){}
            // Insert language text and messages
            $tpl->set_var($aLang);
            // set first defaults and messages
            $tpl->set_var(array(
                            'PAGE_ID' => $results_array['page_id'],
                           // 'PAGE_IDKEY' => $admin->getIDKEY($results_array['page_id']),
                            'PAGE_IDKEY' => $results_array['page_id'],
                            'TIMEZONE' => 'TIMEZONE',
                            'TEXT_ID' => 'ID',
                            'PAGE_TITLE' => ($results_array['page_title']),
                            'MENU_TITLE' => ($results_array['menu_title']),
                            'MODIFIED_BY' => $user['display_name'],
                            'MODIFIED_BY_USERNAME' => $user['username'],
                            'MODIFIED_WHEN' => $modified_ts,
                            'ADMIN_URL' => ADMIN_URL,
                            'WB_URL' => WB_URL,
                            'THEME_URL' => THEME_URL
                            )
                        );
            // Insert variables
            $tpl->set_var(array(
                            'PAGE_ID' => $results_array['page_id'],
                            // 'PAGE_IDKEY' => $admin->getIDKEY($results_array['page_id']),
                            'PAGE_IDKEY' => $results_array['page_id'],
                            'VAR_PAGE_TITLE' => $results_array['page_title'],
                            'SETTINGS_LINK' => ADMIN_URL.'/pages/settings.php?page_id='.$results_array['page_id'],
                            'MODIFY_LINK' => ADMIN_URL.'/pages/modify.php?page_id='.$results_array['page_id']
                            )
                        );
            $tpl->set_block('show_section_block', 'section_list_block', 'section_list');
            $tpl->set_block('show_section_block', 'section_title_block', 'section_title');
            $tpl->set_block('show_section_block', 'section_anchor_block', 'section_anchor');
            $sqlSections  = 'SELECT * FROM `'.TABLE_PREFIX.'sections` '
                          . 'WHERE `page_id` = '.(int)$page_id.' '
                          . 'ORDER BY `position`';
    //        $query_sections = $database->query($sql);
            if ($query_sections = $database->query($sqlSections))
            {
                $num_sections = $query_sections->numRows();
                $section = [];
                while ($section = $query_sections->fetchRow(MYSQLI_ASSOC))
                {
                  if (!\is_numeric(\array_search($section['module'], $module_permissions)))
                  {
                        // Get the modules real name
                        $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'addons` '
                             . 'WHERE `directory` = \''.$section['module'].'\'';
                        if (!$database->get_one($sql) || !\file_exists(WB_PATH.'/modules/'.$section['module']))
                        {
                            $edit_page = '<span class="module_disabled">'.$section['module'].'</span>';
                            $section['title'] = $oLang->MESSAGE_GENERIC_NOT_INSTALLED;
                        }else
                        {
                            $edit_page = '';
                        }
                        $sSectionIdKey = $admin->getIDKEY($section['section_id']);
                        $sec_anchor = (\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec');
                        $edit_page_0 = '<a id="sid'.$section['section_id'].'" href="'.ADMIN_URL.'/pages/modify.php?page_id='.$results_array['page_id'];
                        $edit_page_1  = ($sec_anchor!='') ? '#'.$sec_anchor.$section['section_id'].'">' : '">';
                        $edit_page_1 .= $section['module'].'</a>';

                        if (!empty($section['title'])) {
                            $sSectionTitle  =  ((\mb_strlen($section['title']) > 35) ? \mb_substr($section['title'], 0, 34).'…' : $section['title']);
                            $tpl->set_var('SECTION_TITLE', $sSectionTitle);
                            $tpl->set_var('SEC_TAG_TITLE', $section['title']);
                            $tpl->parse('section_title', 'section_title_block', false);
                        } else {
                            $tpl->parse('section_title', '');
                        }
                        if (isset($section['anchor'])) {
                            $tpl->set_var('SEC_ANCHOR', (int)$section['anchor']);
                            $tpl->set_var('CHECKED_ANCHOR', ($section['anchor'] ? ' checked="checked"' : ''));
                            $tpl->parse('section_anchor', 'section_anchor_block', false);
                        } else {
                            $tpl->parse('section_anchor', '');
                        }

                        if (SECTION_BLOCKS)
                        {
                            if ($edit_page == '')
                            {
                                if (\defined('EDIT_ONE_SECTION') && EDIT_ONE_SECTION)
                                {
                                    $edit_page = $edit_page_0.'&amp;wysiwyg='.$section['section_id'].$edit_page_1;
                                } else {
                                    $edit_page = $edit_page_0.$edit_page_1;
                                }
                            }
                            $input_attribute = 'input_normal';
                            $tpl->set_var(array(
                                    'STYLE_DISPLAY_SECTION_BLOCK' => ' style="visibility:visible;"',
                                    'NAME_SIZE' => 300,
                                    'INPUT_ATTRIBUTE' => $input_attribute,
                                    'VAR_SECTION_ID' => $section['section_id'],
                                    'VAR_SECTION_IDKEY' => $sSectionIdKey,
                                    // 'VAR_SECTION_IDKEY' => $section['section_id'],
                                    'VAR_POSITION' => $section['position'],
                                    'LINK_MODIFY_URL_VAR_MODUL_NAME' => $edit_page,
                                    'SET_NONE_DISPLAY_OPTION' => ''
                                    )
                                );
                            // Add block options to the section_list
                            $tpl->clear_var('section_list');

                            foreach($block as $number => $name)
                            {
                                $tpl->set_var('NAME', \htmlentities(\strip_tags($name)));
                                $tpl->set_var('VALUE', $number);
                                $tpl->set_var('SIZE', 1);
                                if($section['block'] == $number)
                                {
                                    $tpl->set_var('SELECTED', ' selected="selected"');
                                } else {
                                    $tpl->set_var('SELECTED', '');
                                }
                                $tpl->parse('section_list', 'section_list_block', true);
                            }
                        } else {
                            if ($edit_page == '')
                            {
                                $edit_page = $edit_page_0.'#wb_'.$edit_page_1;
                            }

                            $input_attribute = 'input_normal';
                            $tpl->set_var(array(
                                    'STYLE_DISPLAY_SECTION_BLOCK' => ' style="visibility:hidden;"',
                                    'NAME_SIZE' => 300,
                                    'INPUT_ATTRIBUTE' => $input_attribute,
                                    'VAR_SECTION_ID' => $section['section_id'],
                                    'VAR_SECTION_IDKEY' => $sSectionIdKey,
                                    // 'VAR_SECTION_IDKEY' => $section['section_id'],
                                    'VAR_POSITION' => $section['position'],
                                    'LINK_MODIFY_URL_VAR_MODUL_NAME' => $edit_page,
                                    'NAME' => \htmlentities(\strip_tags($block[1])),
                                    'VALUE' => 1,
                                    'SET_NONE_DISPLAY_OPTION' => ''
                                    )
                                );
                        }
                        // Insert icon and images
                        $tpl->set_var(array(
                                    'CLOCK_16_PNG' => 'clock_16.png',
                                    'CLOCK_DEL_16_PNG' => 'clock_del_16.png',
                                    'DELETE_16_PNG' => 'delete_16.png'
                                    )
                                );
                        // set calendar start values
                        if($section['publ_start']==0)
                        {
                            $tpl->set_var('VALUE_PUBL_START', '');
                        } else {
                            $tpl->set_var('VALUE_PUBL_START', \date($jscal_format, $section['publ_start']+TIMEZONE));
                        }
                        // set calendar start values
                        $publ_end = $section['publ_end'];
                        if (($publ_end==2147483647) || ($publ_end==0))
                        {
                            $tpl->set_var('VALUE_PUBL_END', '');
                        } else {
                            $tpl->set_var('VALUE_PUBL_END', \date($jscal_format, $section['publ_end']+TIMEZONE));
                        }
                        // Insert icons up and down
                        if ($section['position'] != 1 )
                        {
                            $tpl->set_var(
                                        'VAR_MOVE_UP_URL',
                                        '<a href="'.ADMIN_URL.'/pages/move_up.php?page_id='.$page_id.'&amp;section_id='.$section['section_id'].'">
                                        <img src="'.THEME_URL.'/images/up_16.png" alt="up" />
                                        </a>' );
                        } else {
                            $tpl->set_var(array(
                                        'VAR_MOVE_UP_URL' => ''
                                        )
                                    );
                        }
                        if($section['position'] != $num_sections ) {
                            $tpl->set_var(
                                        'VAR_MOVE_DOWN_URL',
                                        '<a href="'.ADMIN_URL.'/pages/move_down.php?page_id='.$page_id.'&amp;section_id='.$section['section_id'].'">
                                        <img src="'.THEME_URL.'/images/down_16.png" alt="down" />
                                        </a>' );
                        } else {
                            $tpl->set_var(array(
                                        'VAR_MOVE_DOWN_URL' => ''
                                        )
                                    );
                        }
                    } else {
                      continue;
                    }
                    $tpl->set_var([
//                                    'DISPLAY_SID_DEBUG' => ' style="visibility:visible;"',
                                    'TEXT_SID' => 'SID',
                                    'DEBUG_COLSPAN_SIZE' => 9,
                                    ]
                                );
                    if ($sLocalDebug)
                    {
                        $tpl->set_var([
                                        'DISPLAY_POS_DEBUG' => ' style="visibility:visible;"',
                                        'TEXT_PID' => 'PID',
                                        'TEXT_SID' => 'SID',
                                        'POSITION' => $section['position']
                                        ]
                                    );
                    } else {
                        $tpl->set_var([
                                        'DISPLAY_POS_DEBUG' => ' style="visibility:hidden;"',
                                        'TEXT_PID' => '',
                                        'POSITION' => ''
                                        ]
                                    );
                    }
                $sTmpId    = 0;
                $sTmpLang  = '';
                $bCanDelete = \is_writeable(WB_PATH.'/modules/'.$section['module']);
                $bCanDelete = \filter_var($bCanDelete, \FILTER_VALIDATE_BOOLEAN);
                if ($bCanDelete && $bSectionCanDelete != 1) {
                    $sTmpLang  = \str_replace(['{ModuleName}','{ID}'],[$section['module'],$section['section_id']],$oLang->TEXT_MODULE_DELETE);
                    $tpl->set_var(array(
                        'CONFIRM_DELETE' => ($sTmpLang),
                        'VAR_SECTION_IDKEY' => $sSectionIdKey,
                        )
                    );
                    $tpl->parse('can_delete', 'can_delete_block', false);
                } else {
                    $tpl->parse('can_delete', '', false);
                    $tpl->set_block('can_delete_block', '');
                    $bSectionCanDelete = false;
                    $sTmpLang  = '';
                    $sTmpId  = $section['section_id'];
                }
                $tpl->parse('show_section', 'show_section_block', true);
                }// end while
            } // section exists on this page

            // now add the calendars -- remember to to set the range to [1970, 2037] if the date is used as timestamp!
            // the loop is simply a copy from above.
            $sql  = 'SELECT `section_id`,`module` FROM `'.TABLE_PREFIX.'sections` ';
            $sql .= 'WHERE page_id = '.$page_id.' ';
            $sql .= 'ORDER BY `position` ASC';
            $query_sections = $database->query($sql);

            if($query_sections->numRows() > 0)
            {
                $num_sections = $query_sections->numRows();
                while($section = $query_sections->fetchRow())
                {
                    // Get the modules real name
                    $sql  = 'SELECT `name` FROM `'.TABLE_PREFIX.'addons` '
                          . 'WHERE `directory` = \''.$section['module'].'\'';
                    $module_name = $database->get_one($sql);

                    if (!\is_numeric(\array_search($section['module'], $module_permissions)))
                    {
                        $tpl->set_var(array(
                                    'jscal_ifformat' => $jscal_ifformat,
                                    'jscal_firstday' => $jscal_firstday,
                                    'jscal_today' => $jscal_today,
                                    'start_date' => 'start_date'.$section['section_id'],
                                    'end_date' => 'end_date'.$section['section_id'],
                                    'trigger_start' => 'trigger_start'.$section['section_id'],
                                    'trigger_end' => 'trigger_stop'.$section['section_id']
                                    )
                                );
                        if(isset($jscal_use_time) && $jscal_use_time==TRUE) {
                            $tpl->set_var(array(
                                    'showsTime' => "true",
                                    'timeFormat' => "24"
                                    )
                                );
                        }  else {
                            $tpl->set_var(array(
                                    'showsTime' => "false",
                                    'timeFormat' => "24"
                                    )
                                );
                        }
                    }
                    $tpl->parse('calendar_list', 'calendar_block', true);
                }
            }

        // Work-out if we should show the "Add Section" form
            $sql  = 'SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` '
                  . 'WHERE `page_id` = '.$page_id.' AND `module` = \'menu_link\'';
            $query_sections = $database->query($sql);
            if($query_sections->numRows() == 0)
            {
                // Modules list
                $sql  = 'SELECT `name`,`directory`,`type` FROM `'.TABLE_PREFIX.'addons` '
                      . 'WHERE `type` = \'module\' '
                      .   'AND `function` = \'page\' '
                      .   'AND `directory` != \'menu_link\' '
                      . 'ORDER BY `name`';
                $result = $database->query($sql);
            // if(DEBUG && $database->is_error()) { $admin->print_error($database->get_error()); }
                if($result->numRows() > 0)
                {
                    while ($module = $result->fetchRow(MYSQLI_ASSOC))
                    {
                        // Check if user is allowed to use this module   echo  $module['directory'],'<br />';
                        if (!\is_numeric(\array_search($module['directory'], $module_permissions)))
                        {
                            $tpl->set_var('VALUE', $module['directory']);
                            $tpl->set_var('NAME', $module['name']);
                            if($module['directory'] == 'wysiwyg')
                            {
                                $tpl->set_var('SELECTED', ' selected="selected"');
                            } else {
                                $tpl->set_var('SELECTED', '');
                            }
                            $tpl->parse('module_list', 'module_block', true);
                        } else {
                          continue;
                        }
                    }
                }
            }
            $tpl->set_block('main_block', 'show_settings_block', 'show_settings');
            if ($admin->get_permission('pages_settings')) {
                $tpl->parse('show_settings', 'show_settings_block', true);
            } else {
                $tpl->set_block('show_settings', '');
            }
            $tpl->parse('main', 'main_block', false);
            $tpl->pparse('output', 'page');
            break;
    endswitch;

}catch (\Exception $ex) {
    $admin->print_header();
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}
// Print admin footer // include the required file for Javascript admin
    $admin->print_footer(true);
