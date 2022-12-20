<?php
/**
 *
 * @category        backend
 * @package         wysiwyg
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: save.php 302 2019-03-27 10:25:40Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/wysiwyg/save.php $
 * @lastmodified    $Date: 2019-03-27 11:25:40 +0100 (Mi, 27. Mrz 2019) $
 *
*/

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')) {require((\dirname(\dirname((__DIR__)))).'/config.php');}
// Include the WB functions file
//require(WB_PATH.'/framework/functions.php');

try {

// suppress to print the header, so no new FTAN will be set
    $admin_header = false;
// Tells script to update when this page was last updated
    $update_when_modified = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');
    $sec_anchor = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).(int)$section_id;

    $bBackLink = isset($aRequestVars['pagetree']);
    $sAddonBackUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
    if (\defined('EDIT_ONE_SECTION') && EDIT_ONE_SECTION){
        $sAddonBackUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'&wysiwyg='.$section_id;
    } elseif ( $bBackLink ) {
      $sAddonBackUrl = ADMIN_URL.'/pages/index.php';
    } else {
        $sAddonBackUrl = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.$sec_anchor;
    }

    if (!$admin->checkFTAN()) {
        $admin->print_header();
        $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $sAddonBackUrl);
    }
// After check print the header
    $admin->print_header();


// Update the mod_wysiwygs table with the contents
    if (isset($aRequestVars['content'.$section_id])) {

        $sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_wysiwyg` '
        . 'WHERE `section_id` = '.(int)$section_id.'';
        if ($iNumRow = $database->get_one($sql)){
            $sql  = 'UPDATE `'.TABLE_PREFIX.'mod_wysiwyg` SET ';
            $sqlWHERE  = 'WHERE `section_id` = '.$section_id.' ';
        } else {
            $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_wysiwyg` SET '
                 . '`page_id`='.(int)$page_id.', '
                 . '`section_id`='.(int)$section_id.', ';
            $sqlWHERE  = '';
        }

        $content = $aRequestVars['content'.$section_id];
        $content = $admin->ReplaceAbsoluteMediaUrl($content);
        $text = \strip_tags($content);
        $sql  .= ''
               . '`content`=\''.$database->escapeString($content).'\', '
               . '`text`=\''.$database->escapeString($text).'\' '
               . $sqlWHERE;
        if (!$database->query($sql)) {
// Check if there is a database error, otherwise say successful
            throw new \Exception ($database->get_error());
        }
        $admin->print_success($MESSAGE['PAGES_SAVED'], $sAddonBackUrl );
    } else {
        throw new \Exception ($database->get_error());
    }
} catch (\Exception $ex) {

    $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
    $admin->print_error ($sErrMsg, $sAddonBackUrl);
    exit;
}

// Print admin footer
$admin->print_footer();
