<?php
/**
 * Description of modules/admin.php
 *
 * @package      Core
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      2.0.1
 * @revision     $Id: admin.php 304 2019-03-27 10:41:04Z Luisehahne $
 * @since        File available since 04.10.2017
 * @deprecated   no
 * @description  xxx
 */
declare(strict_types = 1);

use bin\{SecureTokens,WbAdaptor};
use bin\helpers\{PreCheck,ParenList};
use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
try {

    $sModulesPath = str_replace('\\','/',(__DIR__)).'/';
    $sDumpPathname = \basename(__DIR__).'/'.\basename(__FILE__);

/**
 *
 * @param $admin_header to print with or without header, default print with header
 * @param $admin_header to check permission or not, default true
 * @description Create new admin object, you can set the next variable in your module
 *              it is recommed to set the variable before including the /modules/admin.php
 */
    $admin_header = ($admin_header ?? true);
    $admin_auth   = ($admin_auth ?? true);
    $section_name = ($section_name ?? 'Pages');
    $section_permission = ($section_permission ?? 'pages_modify');
//    \trigger_error(sprintf('[%d] <b>BEFORE instance admin wrapper</b> %s',__LINE__,$sDumpPathname), E_USER_NOTICE);
    $admin = new \admin($section_name, $section_permission,(bool)$admin_header, $admin_auth);
// get request method
    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $database = database::getInstance();
    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\pages');
    $module_dir    = \basename(\dirname($oReg->Request->getServerVar("SCRIPT_NAME")));
    $requestMethod = \strtoupper($oReg->Request->getServerVar('REQUEST_METHOD'));
    $aRequestVars  = [];
// get POST or GET requests, never both at once
    $aVars = $oReg->Request->getParamNames();
    foreach ($aVars as $sName) {
        $aRequestVars[$sName] = $oReg->Request->getParam($sName);
    }
    $page             = [];
    $old_admin_groups = [];
    $old_admin_users  = [];
    $sWrapperBackLink = $oReg->AcpUrl.'pages/index.php';

    $sDomain = $oReg->App->getDirNamespace(__DIR__);
// Get page id (on error page_id == 0))
    $page_id = ($admin->getIdFromRequest('page_id'));
//    Get section id (on error section_id == 0))
    $section_id = ($admin->getIdFromRequest('section_id'));

// Get perms
    if (($page_id === false)) {
        $aMessage = \sprintf('%s page_id = %d %s ',$sDumpPathname, $page_id,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    } elseif (($page_id > 0)) {
        $page = $admin->get_page_details($page_id, $oReg->AcpUrl.'pages/index.php' );
        $old_admin_groups = \explode(',', $page['admin_groups']);
        $old_admin_users  = \explode(',', $page['admin_users']);
        // Create back link
        $sWrapperBackLink = $oReg->AcpUrl.'pages/sections.php?page_id='.$page_id;
    }
    $in_group = $admin->ami_group_member($old_admin_groups);
    if ((!$in_group) && !\is_numeric(\array_search($admin->getUserId(), $old_admin_users))) {
        $aMessage = \sprintf('%s %s',$sDumpPathname,$oTrans->MESSAGE_PAGES_INSUFFICIENT_PERMISSIONS);
        throw new \Exception ($aMessage);
    }
// some additional security checks:
    if (($section_id === false)) {
        $aMessage = \sprintf('%s section_id = %d %s ',$sDumpPathname, $section_id,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        throw new \Exception ($aMessage);
    } elseif ($section_id > 0) {
        $section = $admin->get_section_details($section_id, $oReg->AcpUrl.'pages/index.php');
        if (!$admin->get_permission($section['module'], 'module')){
            $sWrapperBackLink = $oReg->AcpUrl.'pages/modify.php?page_id='.(int)$page_id;
            $aMessage = sprintf($oTrans->MESSAGE_PAGES_INSUFFICIENT_PERMISSIONS, $oReg->AcpUrl );
            throw new \Exception ($aMessage);
        }
    } elseif ($page_id === 0) {
        $sWrapperBackLink = $oReg->AcpUrl.'pages/index.php';
        $aMessage = sprintf($oTrans->MESSAGE_PAGES_INSUFFICIENT_PERMISSIONS, $oReg->AcpUrl );
        throw new \Exception ($aMessage);
    }
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = sprintf('%s=%s',$aFtan['name'],$aFtan['value']);

// Workout if the developer wants to show the info banner
    if (isset($print_info_banner) && $print_info_banner == true){
// Get page details already defined
        $aLang = $oTrans->getLangArray();
    // Get display name of person who last modified the page
        $user = $admin->get_user_details($page['modified_by']);
    // Convert the unix ts for modified_when to human a readable form
        $modified_ts = 'Unknown';
//  Convert the unix ts for modified_when to human a readable form
        $sDateFormat = ($oReg->DateFormat ?? 'system_default');
        $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
        $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
        $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
        if ($page['modified_when'] != 0) {
            $sModifyWhen = $page['modified_when']+TIMEZONE;
            $modified_ts = PreCheck::dateFormatToStrftime($sDateFormat. ' '.$sTimeFormat, $sModifyWhen);
        }
        $aTplData = [
                'PAGE_ID' => $page['page_id'],
                // 'PAGE_IDKEY' => $admin->getIDKEY($page['page_id']),
                'PAGE_IDKEY' => $page['page_id'],
                'PAGE_TITLE' => ($page['page_title']),
                'MENU_TITLE' => ($page['menu_title']),
                'ADMIN_URL' => ADMIN_URL,
                'WB_URL' => WB_URL,
                'THEME_URL' => THEME_URL,
                'MODIFIED_BY' => $user['display_name'],
                'MODIFIED_BY_USERNAME' => $user['username'],
                'MODIFIED_WHEN' => $modified_ts,
                'LAST_MODIFIED' => $oTrans->MESSAGE_PAGES_LAST_MODIFIED,
        ];
    // Setup template object, parse vars to it, then parse it
    // Create new Template object
        $template = new Template(\dirname($admin->correct_theme_source('pages_modify.htt')));
    // $template->debug = true;
        $template->set_file('page', 'pages_modify.htt');
        $template->set_block('page', 'main_block', 'main');
        $template->set_block('main_block', 'section_block', 'section_list');
        $template->set_block('section_block', 'block_block', 'block_list');
        $template->set_var($aLang);
        $template->set_var($aTplData);

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
        $sql  = 'SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id` = '.(int)$page_id.' ';
        $sql .= 'AND `module` = \'menu_link\'';
        $query_sections = $database->query($sql);
        $template->set_block('main_block', 'show_section_block', 'show_section');
        if($query_sections->numRows() > 0)
        {
            $template->set_block('show_section', '');
            $template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');
        } elseif((MANAGE_SECTIONS == 'enabled'))
        {
            $template->set_var('TEXT_MANAGE_SECTIONS', $oTrans->HEADING_MANAGE_SECTIONS);
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
/* */
// Insert language TEXT
        $template->set_var([
                    'TEXT_CURRENT_PAGE' => $oTrans->TEXT_CURRENT_PAGE,
                    'TEXT_CHANGE_SETTINGS' => $oTrans->TEXT_CHANGE_SETTINGS,
                    'HEADING_MODIFY_PAGE' => $oTrans->HEADING_MODIFY_PAGE
                    ]);

    // Parse and print header template
        $template->parse('main', 'main_block', false);
        $template->pparse('output', 'page');
        // unset($print_info_banner);
        unset($template);

        $sSectionBlock = '<div class="block-outer">'."\n";
        if (/*SECTION_BLOCKS && */isset($section) )
        {
            if (isset($block[$section['block']]) && trim(strip_tags(($block[$section['block']]))) != '')
            {
                $block_name = htmlentities(strip_tags($block[$section['block']]));
            } else {
                if ($section['block'] == 1)
                         {
                    $block_name = $oTrans->TEXT_MAIN;
                } else {
                    $block_name = '#' . (int) $section['block'];
                }
            }
            $now = time();
            $bSectionInactive = !(($now<=$section['publ_end'] || $section['publ_end']==0) && ($now>=$section['publ_start'] || $section['publ_start']==0) || !$section['active']);
//            $sSectionInfoLine  = ($bSectionInactive ? false: true);
            $sSectionInfoLine   = ($bSectionInactive ? 'inactive': 'active');
            $sSectionIdPrefix   = ($section['anchor']&&(defined('SEC_ANCHOR') && !empty(SEC_ANCHOR)  ? SEC_ANCHOR : 'Sec' ));
            $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
            $data = [];
            echo $sSectionBlock;
            $tpl = new Template(dirname($admin->correct_theme_source('SectionInfoLine.htt')),'keep');
            $tpl->setDebug(0);
            $tpl->set_file('page', 'SectionInfoLine.htt');
            $tpl->set_block('page', 'main_block', 'main');
            $tpl->set_block('main_block', 'section_block', 'section_save');
            $data['aTarget.SectionIdPrefix'] = $sSectionIdPrefix.$section_id;
            $data['aTarget.SectionInfoLine'] = $sSectionInfoLine;
            $data['aTarget.SectionIdPrefix'] = $sSectionIdPrefix.$section_id;
            $data['aTarget.sectionBlock'] = $section['block'];
            $data['aTarget.SectionId'] = $section_id;
            $data['aTarget.pageId'] = $page_id;
            $data['aTarget.FTAN_NAME']  = $aFtan['name'];//$admin->getFTAN();
            $data['aTarget.FTAN_VALUE'] = $aFtan['value'];//$admin->getFTAN();
            $data['aTarget.BlockName']  = $block_name;
            $data['aTarget.sectionUrl'] = ADMIN_URL.'/pages/';
            $data['aTarget.sectionModule'] = $section['module'];
            $data['aTarget.Addonname'] = $section['module'];
            $data['aTarget.title'] = $section['title'];
            $tpl->parse('section_save', '');
            if( preg_match( '/'.preg_quote(ADMIN_PATH,'/').'\/pages\/(settings|sections)\.php$/is', $sCallingScript)) {
                if ($admin->get_permission('pages_settings') ) {
                    $data['lang.TEXT_SUBMIT'] = $TEXT['SAVE'];
                    $tpl->parse('section_save', 'section_block');
                }
            }
            $tpl->set_var($data);
            $tpl->set_var($aLang);
            $tpl->set_var('SECTIONS_TITLE', empty($section['title']) ? '' : $oTrans->MESSAGE_PAGES_SECTIONS_TITLE);
            $tpl->set_block('section_save', '');
            $tpl->parse('main', 'main_block', false);
            $tpl->pparse('output', 'page');
            unset($tpl);
        }//SECTION_BLOCKS
    }//$print_info_banner

// Work-out if the developer wants us to update the timestamp for when the page was last modified
    if (isset($update_when_modified) && $update_when_modified == true) {
        $sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET '
              . '`modified_when` = '.time().','
              . '`modified_by` = '.$admin->getUserId().' '
              . 'WHERE `page_id` = '.$page_id;
        $database->query($sql);
    }

}catch (\Exception $ex) {
//    $admin->print_header(null,false);
    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sWrapperBackLink);
    exit;
}
