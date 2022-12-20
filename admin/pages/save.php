<?php
/**
 *
 * @category        admin
 * @package         pages
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: save.php 271 2019-03-21 17:30:01Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/save.php $
 * @lastmodified    $Date: 2019-03-21 18:30:01 +0100 (Do, 21. Mrz 2019) $
 *
 */


// Create new admin object
if (!\defined('SYSTEM_RUN')){ require(\dirname(\dirname((__DIR__))).'/config.php' ); }

// suppress to print the header, so no new FTAN will be set
$admin = new \admin('Pages', 'pages_modify', false);

// Get page & section id
if (!isset($_POST['page_id']) || !\is_numeric($_POST['page_id'])) {
    \header("Location: index.php");
    exit(0);
} else {
    $page_id = intval($_POST['page_id']);
}

if (!isset($_POST['section_id']) || !\is_numeric($_POST['section_id'])) {
    \header("Location: index.php");
    exit(0);
} else {
    $section_id = \intval($_POST['section_id']);
}
// $js_back = "javascript: history.go(-1);";
$sec_anchor = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR)  ? SEC_ANCHOR : 'Sec' ).(int)$section_id;
$js_back = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.$sec_anchor;
$bBackLink = isset($_POST['pagetree']);
if ( $bBackLink ) {
  $js_back = ADMIN_URL.'/pages/index.php';
}

if (!$admin->checkFTAN())
{
    $admin->print_header();
    $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, '.'.PAGE_EXTENSION)).'';
    $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
    $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL );
}
// After check print the header
$admin->print_header();

// Get perms
$sql = 'SELECT `admin_groups`,`admin_users` '
     . 'FROM `'.TABLE_PREFIX.'pages` '
     . 'WHERE `page_id` = '.$page_id;
$results = $database->query($sql);
$results_array = $results->fetchRow();
if (!$admin->ami_group_member($results_array['admin_users']) &&
   !$admin->is_group_match($admin->get_groups_id(), $results_array['admin_groups']))
{
    $sInfo = \strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, '.'.PAGE_EXTENSION));
    $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
    $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
    $admin->print_error($sDEBUG.$sErrorMsg, ADMIN_URL);
}
// Get page module
$sql = 'SELECT `module` FROM `'.TABLE_PREFIX.'sections` '
     . 'WHERE `page_id`='.$page_id.' AND `section_id`='.$section_id;
$module = $database->get_one($sql);
if (!$module)
{
    $admin->print_error( $database->is_error() ? $database->get_error() : $MESSAGE['PAGES_NOT_FOUND']);
}
/*
// Update the pages table
$now = time();
$sql = 'UPDATE `'.TABLE_PREFIX.'pages` '
     . 'SET `modified_when`='.$now.', '
     .     '`modified_by`='.$admin->getUserId().' '
     . 'WHERE `page_id`='.$page_id;
$database->query($sql);
*/
// Include the modules saving script if it exists
if (\file_exists(WB_PATH.'/modules/'.$module.'/save.php'))
{
    include(WB_PATH.'/modules/'.$module.'/save.php');
}
// Check if there is a db error, otherwise say successful
if ($database->is_error())
{
    $admin->print_error($database->get_error(), $js_back );
} else {
    $admin->print_success($MESSAGE['PAGES_SAVED'], $js_back );
}

// Print admin footer
$admin->print_footer();
