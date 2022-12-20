<?php
/**
 *
 * @category        modules
 * @package         form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: view_submission.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/view_submission.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 * @description
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
//use vendor\phplib\Template;


if (!defined('WB_PATH') ){require( dirname(dirname((__DIR__))).'/config.php');}

    $print_info_banner = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
    //$sDateFormat = PreCheck::dateFormatToStrftime($sDateFormat);

// load module language file
    $sAddonName = basename(__DIR__);
    require(WB_PATH .'/modules/'.$sAddonName.'/languages/EN.php');
    if(file_exists(WB_PATH .'/modules/'.$sAddonName.'/languages/'.LANGUAGE .'.php')) {
        require(WB_PATH .'/modules/'.$sAddonName.'/languages/'.LANGUAGE .'.php');
    }
/* */

//    include(WB_PATH.'/framework/functions.php');

// Get page pagination deprecated
//$requestMethod = '_'.strtoupper($_SERVER['REQUEST_METHOD']);
//    $page = intval(isset(${$requestMethod}['page'])) ? ${$requestMethod}['page'] : 1;
      $page = ($aRequestVars['page'] ?? 1);
// Get id
    $submission_id = intval($admin->getIdFromRequest('submission_id'));
    if (!$submission_id) {
        $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#submissions');
    }

// Get submission details
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_submissions` '
          . 'WHERE `submission_id` = '.$submission_id.' ';
    if($query_content = $database->query($sql)) {
        $submission = $query_content->fetchRow(MYSQLI_ASSOC);
    }

// Get the user details of whoever did this submission
$sql  = 'SELECT `username`,`display_name`, `email` FROM `'.TABLE_PREFIX.'users` '
      . 'WHERE `user_id` = '.$submission['submitted_by'];
    if ($get_user = $database->query($sql)) {
        if ($get_user->numRows() != 0) {
            $user = $get_user->fetchRow(MYSQLI_ASSOC);
        } else {
            $user['display_name'] = $TEXT['GUEST'];
            $user['username'] = $TEXT['UNKNOWN'];
            $user['email'] = '';
        }
    }
    $sBody = $submission['body'];
    if ($user['email']==''){
        $regex = "/[a-z0-9\-_]?[a-z0-9.\-_]+[a-z0-9\-_]?@[a-z0-9.-]+\.[a-z]{2,}/i";
        \preg_match ($regex, $sBody, $output);
// workout if output is empty
        $user['email'] = ($output['0'] ?? $TEXT['UNKNOWN']);
    }
    $sSectionIdPrefix = 'submissions';
    $sSubmittedWhen = PreCheck::getStrftime($sDateFormat, $submission['submitted_when']+TIMEZONE)
               . ', '.PreCheck::getStrftime($sTimeFormat, $submission['submitted_when']+TIMEZONE);

// TODO remove htm lto a template
?>
<div class="w3-container" style="margin: 0 1em;">

    <table class="frm-submission w3-table-all">
        <tbody>
            <tr>
                <th><span class="w3-bold"><?php echo $TEXT['SUBMISSION_ID']; ?></span></th>
                <td><?php echo $submission['submission_id']; ?></td>
            </tr>
            <tr>
                <th><span class="w3-bold"><?php echo $TEXT['SUBMITTED']; ?></span></th>
                <td><?php echo $sSubmittedWhen; ?></td>
            </tr>
            <tr>
                <th><span class="w3-bold"><?php echo $TEXT['USER'].' '; ?></span></th>
                <td><?php echo $user['display_name'].' '; ?></td>
            </tr>
            <tr>
                <th><span class="w3-bold"><?php echo $TEXT['EMAIL'].' '; ?></span></th>
                <td><?php echo $user['email'].' '; ?></td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr />
                </td>
            </tr>
            <tr class="w3-sand">
                <td colspan="2">
                    <?php echo nl2br($submission['body']); ?>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="margin: 1em 0;">
        <input class="w3-btn w3-blue-wb w3-hover-green w3-medium w3-padding-4" type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page=<?php echo $page?>&amp;page_id=<?php echo $page_id.'#'.$sSectionIdPrefix; ?>';" style="width: 150px; margin: 5px;" />
        <input class="w3-btn w3-blue-wb w3-hover-red w3-medium w3-padding-4" type="button" value="<?php echo $TEXT['DELETE']; ?>" onclick="confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/form/delete_submission.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&submission_id=<?php echo $admin->getIDKEY($submission_id).'#'.$sSectionIdPrefix; ?>');" style="width: 150px; margin: 5px;" />
    </div>

</div>
<?php

// Print admin footer
$admin->print_footer();
