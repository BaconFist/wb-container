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
 * @version         $Id: settings-x.php 66 2018-09-17 15:48:07Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/settings-x.php $
 * @lastmodified    $Date: 2018-09-17 17:48:07 +0200 (Mo, 17. Sep 2018) $
 *
 */

// Create new admin object
    if (!\defined('WB_PATH')) { require \dirname(\dirname((__DIR__))).'/config.php'; }
    $admin = new \admin('Pages', 'pages_settings');
// Include the WB functions file
    include __DIR__.'/settings_helper.php';
// Get page id from  HTML request and sanitize it
    if (!($page_id = \intval(@$_GET['page_id']?:0))) {
        $admin->print_header();
        $admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
    }
// load requested page
    $aPage = null;
    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id;
    if (($oResult = $database->query($sql))) {
        $aPage = $oResult->fetchRow(MSQL_ASSOC);
    }
    if (!$aPage) {
// throw error if no valid page received
        $admin->print_header();
        $admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
    }
// check if current user has admin rights to that page
    if (!($admin->ami_group_member($aPage['admin_groups']) ||
         $admin->is_group_match($aPage['admin_users'], $admin->getUserId()))
    ) {
            $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
            $admin->print_error($sErrorMsg, ADMIN_URL);
    }
// check if user has owner rights for this page
    $bHasOwnerRight = ($admin->getUserId() == $aPage['page_owner'] || $admin->getUserId() == 1);
// restore SEO title from page-link
    $aPage['seo_title'] = \basename($aPage['link']);
// add user data array to page
    $aPage['modified_by'] = $admin->get_user_details($aPage['modified_by']);

// prepare template data -------------------------------------------------------
    $aTmplDataGlobal = [];
    $aTmplData = [];
    $aTmplDataGlobal['page'] = $aPage;
    $aTmplDataGlobal['user'] = $admin->get_user_details($admin->getUserId());
    $aTmplDataGlobal['user']['owner'] = $bHasOwnerRight;

// get sorted group lists ------------------------------------------------------
    $aAdmins  = \explode(',', $aPage['admin_groups']);
    $aViewers = \explode(',', $aPage['viewing_groups']);
    $aList = array();
    $sql = 'SELECT `group_id` `id`, `name` '
         . 'FROM `'.TABLE_PREFIX.'groups` '
         . 'ORDER BY `name`';
    $oRecords = $database->query($sql);
    while ($aRecord = $oRecords->fetchRow(MYSQLI_ASSOC)) {
    // if group is set as admin to this page
        $aRecord['admin'] = \in_array($aRecord['id'], $aAdmins);
    // if group ist set as viewer to this page
        $aRecord['viewer'] = \in_array($aRecord['id'], $aViewers);
        $aList[] = $aRecord;
    }
// sort groups by admins
    $aTmplData['GroupListAdmin']  = SettingsHelper::doMultiSort($aList, 'admin', 'name');
// sort groups by viewers
    $aTmplData['GroupListViewer'] = SettingsHelper::doMultiSort($aList, 'viewer', 'name');
// get sorted user lists -------------------------------------------------------
    $aAdmins  = \explode(',', $aPage['admin_users']);
    $aViewers = \explode(',', $aPage['viewing_users']);
    $aList = array();
    $sql = 'SELECT `user_id` `id`, `display_name` `name` '
         . 'FROM `'.TABLE_PREFIX.'users` '
         . 'ORDER BY `name`';
    $oRecords = $database->query($sql);
    while ($aRecord = $oRecords->fetchRow(MYSQLI_ASSOC)) {
    // if user is set as admin to this page
        $aRecord['admin']  = \in_array($aRecord['id'], $aAdmins);
    // if user is set as viewer to this page
        $aRecord['viewer'] = \in_array($aRecord['id'], $aViewers);
        $aList[] = $aRecord;
    }
// sort groups by admins
    $aTmplData['UserListAdmin'] = SettingsHelper::doMultiSort($aList, 'admin', 'name');
// sort groups by viewers
    $aTmplData['UserListViewer'] = SettingsHelper::doMultiSort($aList, 'viewer', 'name');
// clean up memory
    unset($aAdmins, $aViewers, $oRecords, $aRecord, $aList, $doMultiSort);
// add list of possible parent pages -------------------------------------------
    $aParentPages = SettingsHelper::getParentPagesList($aTmplDataGlobal['page']['page_id'], $iCurrentPage, $admin, $database);
// check for permission to add a level-0 page
    if ($admin->get_permission('pages_add_l0') || $results_array['level'] == 0) {
// add the option to choose level-0
        $aPage['id']       = 0;
        $aPage['title']    = $aLang['TEXT_NONE'];
        $aPage['language'] = '';
        $aPage['active']   = !$results_array['parent'];
        \array_unshift($aParentPages, $aPage);
    }
    $aTmplData['ParentPages'] = $aParentPages;
// add list of linking targets -------------------------------------------------
    $aTmplData['LinkTargets'] = array(
        array('target' => '_top', 'caption' => $TEXT['TOP_FRAME']),
        array('target' => '_self', 'caption' => $TEXT['SAME_WINDOW']),
        array('target' => '_blank', 'caption' => $TEXT['NEW_WINDOW'])
    );
// build list of available templates -------------------------------------------
    $aTemplatesList = array();
    $sql = 'SELECT `directory`, `name`, `version` FROM `'.TABLE_PREFIX.'addons` '
         . 'WHERE `function` = \'template\' '
         . 'ORDER BY `name`';
    if (($oAddons = $database->query($sql))) {
        while (($aAddon = $oAddon->fetchRow(MYSQLI_ASSOC))) {
            $aTemplatesList[] = $aAddon;
        }
    }
    if (!$aTemplatesList) {
        $aTemplatesList[] = array('directory'=>'', 'name'=>'System Default', 'version'=>'');
    }
    $aTmplData['Templates'] = $aTemplatesList;
// get available menues from active template -----------------------------------
    $sTpl = WB_PATH.'/templates/'
          .($aPage['template'] ?: DEFAULT_TEMPLATE)
          .'/info.php';
    $aTemplateInfo = getContentFromInfoFile(
        WB_PATH.'/templates/'.($aPage['template'] ?: DEFAULT_TEMPLATE).'/info.php'
    );
    if (!isset($aTemplateInfo['menu'])) {
        $aTemplateInfo['menu'] = array(1 => 'Main');
    }
    $aTmplData['Menues'] = $aTemplateInfo['menu'];
// get list of available languages ---------------------------------------------
    $sql = 'SELECT `directory`, `name`, `version` FROM `'.TABLE_PREFIX.'addons` '
         . 'WHERE `type` = \'language\' '
         . 'ORDER BY `name`';
    if (($oAddons = $database->query($sql))) {
        while (($aAddon = $oAddon->fetchRow(MYSQLI_ASSOC))) {
            $aLanguageList[] = $aAddon;
        }
    }
    $aTmplData['Languages'] = $aLanguageList;

// Print admin footer
$admin->print_footer();
