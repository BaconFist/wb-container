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
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2 and higher
 * @version         $Id: sections_save.php 271 2019-03-21 17:30:01Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/pages/sections_save.php $
 * @lastmodified    $Date: 2019-03-21 18:30:01 +0100 (Do, 21. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

// Include config file
if (!\defined('SYSTEM_RUN')) {require( (\dirname(\dirname((__DIR__)))).'/config.php');}


    $requestMethod = '_'.\strtoupper($_SERVER['REQUEST_METHOD']);
    $aRequestVars = (isset(${$requestMethod}) ? ${$requestMethod} : $_REQUEST);

    // Make sure people are allowed to access this page
    if (MANAGE_SECTIONS != 'enabled') {
        \header('Location: '.ADMIN_URL.'/pages/index.php');
        exit(0);
    }

/**/
// Create new admin object
// suppress to print the header, so no new FTAN will be set
    $admin = new admin('Pages', 'pages_modify',false);
    if (!(function_exists('jscalendar_to_timestamp')))(require(WB_PATH."/include/jscalendar/jscalendar-functions.php"));

// Get page id
    if(!isset($aRequestVars['page_id']) || !\is_numeric($aRequestVars['page_id'])) {
        $sInfo = __LINE__.') '.\strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
                $sErrorMsg = \sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
                $admin->print_error($sDEBUG.$$sErrorMsg, ADMIN_URL);
        exit(0);
    } else {
        $iPageId = $page_id = (int)$aRequestVars['page_id'];
    }

    $callingScript = $_SERVER['HTTP_REFERER'];
    $sBackLink = $callingScript.'?page_id='.$iPageId;
    $sBackLink = ADMIN_URL.'/pages/sections.php?page_id='.$iPageId;

//    if (!$admin->checkFTAN()){
      if (!\bin\SecureTokens::checkFTAN ()) {
        $admin->print_header();
        $sInfo = __LINE__.') '.\strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$MESSAGE['GENERIC_SECURITY_ACCESS'], $sBackLink);
    }

/*
if( (!($page_id = $admin->checkIDKEY('page_id', 0, $_SERVER['REQUEST_METHOD']))) )
{
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS']);
    exit();
}
*/
// Get perms
    $sql  = 'SELECT `admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` '
          . ' WHERE `page_id` = '.(int)$page_id. '';
    $results = $database->query($sql);
    $results_array = $results->fetchRow(MYSQLI_ASSOC);
    $old_admin_groups = \explode(',', $results_array['admin_groups']);
    $old_admin_users = \explode(',', $results_array['admin_users']);
    $in_old_group = false;
    foreach($admin->get_groups_id() as $cur_gid){
        if (\in_array($cur_gid, $old_admin_groups)) {
            $in_old_group = TRUE;
        }
    }

    if ((!$in_old_group) && !\is_numeric(\array_search($admin->getUserId(), $old_admin_users))) {
        $sInfo = __LINE__.') '.(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        $sErrorMsg = sprintf('%s [%d] %s',\basename(__FILE__),__LINE__,$MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
        $admin->print_error($sDEBUG.$sErrorMsg, ADMIN_URL);
    }

// Get page details
    $query = 'SELECT COUNT(`page_id`) `numRows` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.(int)$page_id.'';
    $numRows = $database->get_one($query);
    if ($database->is_error()) {
        $admin->print_header();
        $sInfo = __LINE__.') '.\strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'_DATABASE_ERROR::';
        $sDEBUG=(defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$database->get_error());
    }
    if ($numRows == 0) {
        $admin->print_header();
        $sInfo = __LINE__.') '.\strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$MESSAGE['PAGES_NOT_FOUND']);
    }
// After check print the header
    $admin->print_header();

    $results_array = $results->fetchRow(MYSQLI_ASSOC);
// Set module permissions
    $module_permissions = $_SESSION['MODULE_PERMISSIONS'];

    $aSql = [];
    $sec_anchor = '';
    $section_id = \intval($admin->get_post('section_id') );
    $sTitle  = $admin->StripCodeFromText( $admin->get_post('title_'.$section_id ));

    $iAnchor    = $oRequest->getParam('anchor'.$section_id, FILTER_VALIDATE_BOOLEAN);
    $iActive    = $oRequest->getParam('active'.$section_id, FILTER_VALIDATE_BOOLEAN);
    $sAttribute = $oRequest->getParam('attribute'.$section_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $bSaveTitle = isset($aRequestVars['inputSection']);
    if ($bSaveTitle ) {
        $aSql[]  = 'UPDATE `'.TABLE_PREFIX.'sections` SET '
                 . '`title`=\''.$database->escapeString($sTitle).'\', '
                 . '`anchor`='.(int)$iAnchor.', '
                 . '`active`='.(int)$iActive.', '
                 . '`attribute`=\''.$database->escapeString($sAttribute).'\' '
                 . 'WHERE `section_id`='.(int)$section_id;
        foreach( $aSql as $sSql ) {
              if(!$database->query($sSql)) {
// TODO Exception Message
            }
        }
        $sec_anchor = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).(int)$section_id;
        $sBackLink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.$sec_anchor;
    } else {
// Loop through sections
        $sql  = 'SELECT `section_id`,`module`,`position` FROM `'.TABLE_PREFIX.'sections` '
              . 'WHERE `page_id` = '.(int)$page_id.' '
              . 'ORDER BY `position` ';
        if($query_sections = $database->query($sql))
        {
            $num_sections = $query_sections->numRows();
            while($section = $query_sections->fetchRow(MYSQLI_ASSOC)) {
                if (!\is_numeric(\array_search($section['module'], $module_permissions))) {
                    // Update the section record with properties
                    $section_id = $section['section_id'];
                    $sql = ''; $publ_start = 0; $publ_end = 0;
                    $dst = \date("I")?" DST":""; // daylight saving time?
                    if (isset($_POST['block'.$section_id]) && $_POST['block'.$section_id] != '') {
                        $sql = "block = '".$admin->add_slashes($_POST['block'.$section_id])."'";
                    }
                    // update publ_start and publ_end, trying to make use of the strtotime()-features like "next week", "+1 month", ...
                    if (isset($_POST['start_date'.$section_id]) && isset($_POST['end_date'.$section_id])) {
                        if (\trim($_POST['start_date'.$section_id]) == '0' || \trim($_POST['start_date'.$section_id]) == '') {
                            $publ_start = 0;
                        } else {
                            $publ_start = jscalendar_to_timestamp($_POST['start_date'.$section_id]);
                        }
                        if (\trim($_POST['end_date'.$section_id]) == '0' || \trim($_POST['end_date'.$section_id]) == '') {
                            $publ_end = 2147483647;
                        } else {
                            $publ_end = jscalendar_to_timestamp($_POST['end_date'.$section_id], $publ_start);
                        }
                        if ($sql != ''){$sql .= ",";}
                        $sql .= " publ_start = '".$database->escapeString($publ_start)."'";
                        $sql .= ", publ_end = '".$database->escapeString($publ_end)."'";
                    }

                    $query = "UPDATE ".TABLE_PREFIX."sections SET $sql WHERE section_id = '$section_id'";
                    if($sql != '') {
                        $database->query($query);
                    }
                }
            }
        }
    }
// Check for error or print success message
    if ($database->is_error()) {
        $sInfo = __LINE__.') '.\strtoupper(\basename(__DIR__).'_'.\basename(__FILE__, ''.PAGE_EXTENSION)).'::';
        $sDEBUG=(\defined('DEBUG') && DEBUG ? $sInfo : '');
        $admin->print_error($sDEBUG.$database->get_error(), $sBackLink );
    } else {
        $admin->print_success($MESSAGE['PAGES_SECTIONS_PROPERTIES_SAVED'], $sBackLink );
    }

// Print admin footer
    $admin->print_footer();
