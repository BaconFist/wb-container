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
 * Description of admin/pages/modify.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: modify.php 271 2019-03-21 17:30:01Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

//namespace ;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use vendor\phplib\Template;


    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname($sAddonFile).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleDir   = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.\basename($sModulesPath).'/'.$sAddonName;
    $sPattern = "/^(.*?\/)".$sModuleDir."\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}
    $sAddonUrl = $sModuleDir.$sAddonRel;

try{

// Create new admin object
    $admin = new \admin('Pages', 'pages_modify');
// Get page id
    $oReg = WbAdaptor::getInstance();
    $sBackLink = ADMIN_URL.'/pages/index.php';
    $sBackStartLink = ADMIN_URL.'/start/index.php';
    $sBackStartLink = $sBackLink;
    $oRequest = $oReg->getRequester();
    $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
    $sDomain = \basename(\dirname($sCallingScript)).'/'.\basename($sCallingScript);

    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aVars = $oRequest->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oRequest->getParam($sName);
    }

    $page_id = ($admin->getIdFromRequest('page_id'));
    if (($page_id === false)) {
        $aMessage  = sprintf("%s\n",$MESSAGE['PAGES_NOT_FOUND']);
//echo nl2br($aMessage);
        throw new \Exception ($aMessage);
    }
// Get perms
    if (!$admin->get_page_permission($page_id,'admin')) {
        $aMessage = \sprintf("%s %s\n",\basename(__DIR__).'/'.\basename(__FILE__),$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
//echo nl2br($aMessage);
//        throw new \Exception ($aMessage);
    }
//    $section_id = ($admin->getIdFromRequest('section_id'));
//    $sSectionIdKey = $section_id;
/*
    if (is_null($section_id)) {
        $aMessage  = sprintf("%s\n",$MESSAGE['PAGES_NOT_FOUND']);
        throw new \Exception ($aMessage);
    }
*/
// Get page details
    $results_array = $admin->get_page_details($page_id);
// Get display name of person who last modified the page
    $user=$admin->get_user_details($results_array['modified_by']);

// Convert the unix ts for modified_when to human a readable form
    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
     $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
    $modified_ts = 'Unknown';
    if (($results_array['modified_when'] != 0)){
        $sModifyWhen = $results_array['modified_when']+TIMEZONE;
        $modified_ts = PreCheck::dateFormatToStrftime($sDateFormat.' '.$sTimeFormat, $sModifyWhen);
    }

    $oLang = \Translate::getInstance();
    $oLang->enableAddon(ADMIN_DIRECTORY.'\\'.$sAddonName);
    $aLang = $oLang->getLangArray();

}catch (\Exception $ex) {
    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sBackStartLink);
    exit;
}

// $ftan_module = $GLOBALS['ftan_module'];
// Setup template object, parse vars to it, then parse it
// Create new template object
    $template = new Template(\dirname($admin->correct_theme_source('pages_modify.htt')));
// $template->debug = true;
    $template->set_file('page', 'pages_modify.htt');
    $template->set_block('page', 'main_block', 'main');
// $template->set_var('FTAN', $admin->getFTAN() );

    $template->set_var([
                'PAGE_ID' => $results_array['page_id'],
                //  'PAGE_IDKEY' => $admin->getIDKEY($results_array['page_id']),
                'PAGE_IDKEY' => ($results_array['page_id']),
                'PAGE_TITLE' => ($results_array['page_title']),
                'MENU_TITLE' => ($results_array['menu_title']),
                'ADMIN_URL' => ADMIN_URL,
                'WB_URL' => WB_URL,
                'THEME_URL' => THEME_URL
            ]);

    $template->set_var([
                'MODIFIED_BY' => $user['display_name'],
                'MODIFIED_BY_USERNAME' => $user['username'],
                'MODIFIED_WHEN' => $modified_ts,
                'LAST_MODIFIED' => $MESSAGE['PAGES_LAST_MODIFIED'],
            ]);

    $template->set_block('main_block', 'show_modify_block', 'show_modify');
    if($modified_ts == 'Unknown')
    {
        $template->set_block('show_modify', '');
        $template->set_var('CLASS_DISPLAY_MODIFIED', 'hide');
    } else {
        $template->set_var('CLASS_DISPLAY_MODIFIED', '');
        $template->parse('show_modify', 'show_modify_block', true);
    }

// Work-out if we should show the "manage sections" link
    $sql = 'SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'sections` '
         . 'WHERE `page_id`='.(int)$page_id.' AND `module`=\'menu_link\'';
    $query_sections = $database->get_one($sql);
    $template->set_block('main_block', 'show_section_block', 'show_section');
    if ($query_sections) {
        $template->set_block('show_section', '');
        $template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');
    } elseif(MANAGE_SECTIONS == 'enabled') {
        $template->set_var('TEXT_MANAGE_SECTIONS', $HEADING['MANAGE_SECTIONS']);
        $template->parse('show_section', 'show_section_block', true);
    } else {
        $template->set_block('show_section', '');
        $template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');
    }
    $template->set_block('main_block', 'show_settings_block', 'show_settings');
    if ($admin->get_permission('pages_settings')) {
        $template->parse('show_settings', 'show_settings_block', true);
    } else {
        $template->set_block('show_settings', '');
    }

    $template->set_var($aLang);
// Parse and print header template
    $template->parse('main', 'main_block', false);
    $template->pparse('output', 'page');

// get template used for the displayed page (for displaying block details)
    if (SECTION_BLOCKS)
    {
        $sql = 'SELECT `template` FROM `'.$oReg->TablePrefix.'pages` '
             . 'WHERE `page_id`='.(int)$page_id;
        if (($sTemplate = $database->get_one($sql)) !== null) {
            $page_template = ($sTemplate == '') ? $oReg->DefaultTemplate : $sTemplate;
            // include template info file if exists
            if (\is_readable($oReg->AppPath.'templates/'.$page_template.'/info.php')) {
                include_once($oReg->AppPath.'templates/'.$page_template.'/info.php');
            }
        }
    }

// Get sections for this page
    $module_permissions = $_SESSION['MODULE_PERMISSIONS'];
// workout for edit only one section for faster pageloading
// Constant later set in wb_settings, in meantime defined in framework/initialize.php
    $sql = 'SELECT * FROM `'.$oReg->TablePrefix.'sections` ';
    $sql .= (\defined('EDIT_ONE_SECTION') && EDIT_ONE_SECTION && \is_numeric($sectionId))
            ? 'WHERE `section_id` = '.(int)$sectionId
            : 'WHERE `page_id` = '.(int)$page_id;
    $sql .= ' ORDER BY position ASC';
    $query_sections = $database->query($sql);
    if (($query_sections->numRows() > 0))
    {
        while (($section = $query_sections->fetchRow(MYSQLI_ASSOC)))
        {
            $iNow = \time();
            $bSectionInactive = !(($section['publ_start'] <= $iNow) && ($section['publ_end'] >= $iNow)) || !$section['active'];
            $section_id = $section['section_id'];
            $module = $section['module'];
            $block_name = '#'.(int) $section['block'];
            //Have permission?
            $aSearch = [$section_id => $module];
            if (\array_diff($aSearch, $module_permissions)) {
    //      if (!\is_numeric(\array_search($module, $module_permissions))) {
                $sSectionBlock     = '<div class="block-outer">'."\n";
                $sSectionInfoLine  = ($bSectionInactive ? 'inactive': 'active');
                if (isset($section['anchor'])) {
                    $SecAnchor     = (int)$section['anchor'];
                    $SetAttribute  = $section['attribute'];
                    $CheckedAnchor = ((int)$section['anchor'] ? ' checked="checked"' : '');
                }
//                $sSetActiveStatus = ($section['active'] ?  : )
                if (isset($section['active'])) {
                    $SetActive  = $section['active'];
                    $CheckedActive = ((int)$section['active'] ? ' checked="checked"' : '');
                }
                // Include the modules editing script if it exists
                if (!\is_readable($oReg->AppPath.'modules/'.$module.'/modify.php')){
// module loading failed
                    $content = $admin->format_message($oLang->MESSAGE_GENERIC_MODULE_VERSION_ERROR,'noaddon', '');
                } else {
                    if (isset($block[$section['block']]) && \trim(\strip_tags(($block[$section['block']]))) != '')
                    {
                        $block_name = \htmlentities(\strip_tags($block[$section['block']]));
                    } else {
                        if ($section['block'] == 1)
                        {
                            $block_name = $oLang->TEXT_MAIN;
                        } else {
                            $block_name = '#'.(int) $section['block'];
                        }
                    }
                        \ob_start() ;
                        require($oReg->AppPath.'modules/'.$module.'/modify.php');
                        $content = \ob_get_clean() ;
                } // module exists

                if ($content != '')
                {
                    echo $sSectionBlock;//block-outer
                    $oLang->enableAddon(ADMIN_DIRECTORY.'\\'.\basename(__DIR__));
                    $aLang = $oLang->getLangArray();
                    $sSectionIdPrefix = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).$section_id;
                    $data = [];
                    $tpl = new Template(\dirname($admin->correct_theme_source('SectionInfoLine.htt')),'keep');
                    $tpl->set_file(
                                $sSectionInfo =
                                ['page'  => 'SectionInfoLine.htt',
                                 'modal' => 'pages_expert_modal.htt']
                                );
                    $tpl->setDebug(0);
    //                $tpl->loadfile('page');
    //                $tpl->loadfile('modal');
                    $tpl->set_block('page', 'main_block', 'main');
                    $data['aTarget.SectionIdPrefix'] = \str_replace('#','',$sSectionIdPrefix);
                    $data['aTarget.SectionInfoLine'] = $sSectionInfoLine;
                    $data['aTarget.sectionBlock'] = $section['block'];
                    $data['aTarget.SectionId'] = $section_id;
                    $data['aTarget.pageId'] = $page_id;
                    $data['aTarget.FTAN'] = $admin->getFTAN();
                    $data['aTarget.BlockName'] = $block_name;
                    $data['aTarget.sectionUrl'] = ADMIN_URL.'/pages/';
                    $data['aTarget.sectionModule'] = $section['module'];
                    $data['aTarget.Addonname'] = $section['module'];
                    $data['aTarget.title'] = $section['title'];
                    $data['aTarget.anchor'] = $SecAnchor;
                    $data['aTarget.Checked_Anchor'] = $CheckedAnchor;
                    $data['aTarget.attribute'] = $SetAttribute;
                    $data['aTarget.active'] = $SetActive;
                    $data['aTarget.Checked_Active'] = $CheckedActive;
                    $data['aTarget.Content'] = '';
                    $tpl->set_block('modal', 'modal_expert_block', 'modal_expert');
                    $tpl->set_block('main_block', 'section_block', 'section_save');
                    if ($admin->get_permission('pages_settings') ) {
                        $data['lang.TEXT_SUBMIT'] = $oLang->TEXT_SAVE;
                        $tpl->parse('section_save', 'section_block',true);
                        $tpl->parse('modal_save', 'modal_expert_block',true);
                    } else {
                        $tpl->parse('section_save', '');
                        $tpl->parse('modal_save', '');
                    }
                    $tpl->set_block('modal_expert_block', 'show_anchor_block', 'show_anchor');
                    $tpl->set_block('modal_expert_block', 'show_panel_block', 'show_panel');
                    if ($oReg->SecAnchor !== "none") {
                        $data['lang.TEXT_SUBMIT'] = $oLang->TEXT_SAVE;
                        $data['TEXT_CLASS_PANEL'] = $oLang->TEXT_CLASS_PANEL_ACTIVE;
                        $tpl->parse('show_anchor', '');
                        $tpl->parse('show_panel', 'show_panel_block',true);
                    } else {
                        $data['TEXT_CLASS_PANEL'] = $oLang->TEXT_CLASS_PANEL_NONE;
                        $tpl->parse('show_anchor', 'show_anchor_block',true);
                        $tpl->parse('show_panel', 'show_panel_block',true);
                    }

                    $tpl->set_var($data);
                    $tpl->set_var($aLang);
                    $TextExtendedPageOptions = \sprintf($oLang->TEXT_EXTENDED_PAGE_OPTIONS,$section['page_id'],$section['section_id'],$section['module'],$block_name);
                    $tpl->set_var('TEXT_EXTENDED_PAGE_OPTIONS',$TextExtendedPageOptions);
                    $tpl->set_var('SECTIONS_TITLE', empty($section['title']) ? '' : $oLang->MESSAGE_PAGES_SECTIONS_TITLE);
                    $tpl->parse('main', 'main_block', false);
                    $tpl->parse('main', 'modal_expert_block', true);
                    $tpl->pparse('output', 'page');
                    unset($tpl);
                    $sAfterContent = '</div>'."\n" ;
                    $content = $content."\n".$sAfterContent;
                    echo $content;
                } // end SectionInfoLine and $content
            } //  end permission
        } // end while
    }
unset($tpl);
    $oLang->disableAddon(ADMIN_DIRECTORY.'\\'.\basename(__DIR__));

// Print admin footer
$admin->print_footer();
