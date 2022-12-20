<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        addons
 * @package         form
 * @subpackage      modify
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
/* -------------------------------------------------------- */

//overwrite php.ini on Apache servers for valid SESSION ID Separator
    $sQuerySep = \ini_get('arg_separator.output');

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
/* ----------set to deprecated----------------------------- */
// load module language file
    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
/* -------------------------------------------------------- */
/* -- only needed in overview scripts (modify.php),
      to unset Addon $_SESSION created in oder addons scripts -- */
    if (isset($_SESSION[$sAddonName])){
        unset ($_SESSION[$sAddonName]);
    }
/* -------------------------------------------------------- */
    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
/* -------------------------------------------------------- */
    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
    //$sDateFormat = PreCheck::getoStrftime($sDateFormat);
/* -------------------------------------------------------- */
    $sDomain      = $oReg->App->getDirNamespace(__DIR__);
    $oTrans->enableAddon($sDomain);
    $sCallingScript = $oRequest->getServerVar('SCRIPT_NAME');
    $ModuleUrl    = $oReg->AppUrl.$ModuleRel;
    $sAddonUrl    = $oReg->AppUrl.$sAddonRel;
    $sTargetFieldsPath  = $sAddonPath.'/data/fields/';
    $sTargetLayoutPath  = $sAddonPath.'/data/layouts/';
/* -------------------------------------------------------- */

    $SectionIdPrefix = '#'.(defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).$section['section_id'];
    $backModuleUrl = $oReg->AcpUrl.'pages/sections.php?page_id='.$page_id;
//http://wb283.wdsnet.local/admin/pages/sections.php?page_id=13

//Delete all form fields with no title TODO will be changed to new Methode INSERT or UPDATE
    $sql  = '
    DELETE FROM `'.$oReg->TablePrefix.'mod_form_fields`
    WHERE page_id = '.(int)$page_id.'
      AND section_id = '.(int)$section_id.'
      AND title = \'\'
    ';
    if (!$oDb->query($sql)) {
// error msg
    }
    $bCanBackup = ($oApp->ami_group_member('1') || $oApp->get_permission('settings')); // true false
    $bCanDelete = ($oApp->ami_group_member('1') || $oApp->get_permission('settings')); // true false
    $bCanModifyOption = ($oApp->get_permission('modules_settings') );

//    $sSectionIdKey = SecureTokens::getIDKEY($section_id);
    $sSectionIdKey = $section_id;

// later in upgrade.php or install-struct
    $table_name = $oReg->TablePrefix.'mod_form_settings';
    $field_name = 'perpage_submissions';
    $description = "INT NOT NULL DEFAULT '10' AFTER `max_submissions`";
    if (!$oDb->field_exists($table_name,$field_name)) {
        $oDb->field_add($table_name, $field_name, $description);
    }

    $sTableName = $oReg->TablePrefix.'mod_form_settings';
    $sSql = '
    SELECT
    `stored_submissions`,`delete_submissions`
    FROM `'.$sTableName.'`
    WHERE `section_id` = '.(int)$section_id.'
    ';

    if ($oTriggerSub = $oDb->query($sSql)) {
        if ($aTriggerSubmissions = $oTriggerSub->fetchRow(MYSQLI_ASSOC)){
          $iActuellTimeStamp = time();

          $iDeleteTimestamp = $iActuellTimeStamp-86400 * (int)$aTriggerSubmissions['delete_submissions'];

          if (($aTriggerSubmissions['delete_submissions'] > 0)) {
              $iDeleted = '';
              $sSql = '
              DELETE
              FROM `'.$oReg->TablePrefix.'mod_form_submissions`
              WHERE `submitted_when` < '.(int)$iDeleteTimestamp.'
                AND `section_id` = '.(int)$section_id.'
              ';
              if (($oRes = $oDb->query($sSql))) {
                  $iDeleted = $oRes->oDbHandle->affected_rows;
                  echo \nl2br(\sprintf("<div class='w3-card w3-container'> %s </div> \n",sprintf($MOD_FORM['SUBS_DELETED'],$iDeleted,$aTriggerSubmissions['delete_submissions'])));
              }
          }
        }
    }
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sSql));

    $sFtan = SecureTokens::getFTAN();
    $oTrans->TEXT_BACKUP = $oTrans->TEXT_FIELD_IMPORT;
    $sql  = 'SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields` '
          . 'WHERE `section_id` = '.(int)$section_id.' '
          . '';
    if (!$backupType = $oDb->get_one($sql)==0){
        $oTrans->TEXT_BACKUP = $oTrans->TEXT_FIELD_EXPORT;
    }
?>
  <div id="FrmModal<?php echo $section_id;?>" class="modalFrmDialog w3-modal w3-animate-opacity">
    <div class="w3-modal-content w3-card-4 ">
      <header class="w3-container w3-header-blue-wb">
        <span class="w3-button w3-circle w3-xlarge w3-red w3-display-topright w3-close" style="top: -10px; right: -10px;width: 50px;">X</span>
        <p class="w3-large"><?php echo $oTrans->FORM_MESSAGE_DELETE_LAYOUT;?></p>
      </header>
      <div class="w3-center w3-container w3-padding-32 ">
          <p id="message<?php echo $section_id;?>" class="w3-panel w3-large w3-text-red w3-margin"><?php echo $oTrans->FORM_MESSAGE_CONFIRM_DELETE_LAYOUT;?> Section </p>
          <form action="<?php echo $sAddonUrl; ?>modify_backup.php" method="post">
              <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
              <input id="p<?php echo $section_id; ?>" type="hidden" name="section_id" value="<?php echo $sSectionIdKey; ?>"/>
              <input type="hidden" name="<?php echo $sFtan['name'];?>" value="<?php echo $sFtan['value'];?>"/>
              <button name="delete_all" type="submit" class="w3-btn w3-blue-wb w3-round w3-hover-green w3--medium "><?php echo $oTrans->FORM_MESSAGE_DELETE_FIELDS;?></button>
              <button type="reset" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width w3-close" ><?php echo $oTrans->TEXT_CLOSE;?></button>
          </form>
      </div>
      <footer class="w3-container w3-header-blue-wb">
        <p style="line-height: 1;">&nbsp;</p>
      </footer>
    </div>
  </div>
<article class="w3-container w3-margin-bottom">
<h4 class="w3-margin-0" style="line-height: 0;">&nbsp;</h4>
<table class="mod_form form-btn-table" style="width: 100%;">
    <tbody>
        <tr>
            <td style="width: 33.336%;">
                <?php $sFieldIdKey = SecureTokens::getIDKEY('0');
                ?>
                <form action="<?php echo $sAddonUrl; ?>modify_field.php" method="post" class="mod_form" >
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
                    <input type="hidden" name="section_id" value="<?php echo $sSectionIdKey; ?>"/>
                    <input type="hidden" name="field_id" value="<?php echo $sFieldIdKey;?>"/>
                    <input type="hidden" name="<?php echo $sFtan['name'];?>" value="<?php echo $sFtan['value'];?>"/>
                    <input type="submit" value="<?php echo $oTrans->TEXT_ADD_FIELD; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" style="width: 100%;" />
                </form>
            </td>
<?php       if ($bCanBackup){
              $sSql = '
              SELECT COUNT(*) FROM `'.$oReg->TablePrefix.'mod_form_fields`
              WHERE `section_id`='.(int)$section_id;
              $bFieldExport = ($oDb->get_one($sSql));
?>
            <td style="width: 33.336%;">
                <form action="<?php echo $sAddonUrl; ?>modify_backup.php" method="post" class="mod_form" >
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
                    <input type="hidden" name="section_id" value="<?php echo $sSectionIdKey; ?>"/>
                    <input type="hidden" name="<?php echo $sFtan['name'];?>" value="<?php echo $sFtan['value'];?>"/>
<?php
if ($bFieldExport == 0) { ?>
                    <input type="submit" value="<?php echo $oTrans->TEXT_FIELD_IMPORT; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" style="width: 100%;" />
<?Php } else { ?>
                    <input type="submit" value="<?php echo $oTrans->TEXT_FIELD_EXPORT; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" style="width: 100%;" />
<?php } ?>
                </form>
            </td>
<?php       }
            if ($bCanModifyOption) {
?>
            <td style="width: 33.336%;">
                <form action="<?php echo $sAddonUrl; ?>modify_settings.php" method="post" class="mod_form" >
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
                    <input type="hidden" name="section_id" value="<?php echo $sSectionIdKey; ?>"/>
                    <input type="hidden" name="<?php echo $sFtan['name'];?>" value="<?php echo $sFtan['value'];?>"/>
                    <input type="submit" value="<?php echo $oTrans->TEXT_SETTINGS; ?>" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" style="width: 100%;" />
                </form>
            </td>
<?php       } ?>
<?php if (@DEBUG && $admin->ami_group_member('1')) { ?>
            <td style="width: 33.336%;">
                <form action="<?php echo $sAddonUrl; ?>/modules/form/reorgPosition.php" method="post" class="mod_form" >
                    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>
                    <input type="hidden" name="section_id" value="<?php echo $sSectionIdKey; ?>"/>
                    <input type="hidden" name="<?php echo $sFtan['name'];?> value="<?php echo $sFtan['value'];?>/>
                    <input type="submit" value="Reorg Position" class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" style="width: 100%;" />
                </form>
            </td>
<?php } ?>
        </tr>
    </tbody>
</table>
</article>
<article class="w3-container w3-margin-bottom">
<h3 id="tablecontent<?php echo $section_id;?>" class="tablecontent" style="line-height: 0;">&nbsp;</h3>
<?php
// Loop through existing fields
$sql  = 'SELECT * FROM `'.$oReg->TablePrefix.'mod_form_fields` '
      . 'WHERE `section_id` = '.(int)$section_id.' '
      . 'ORDER BY `position` ASC';
if ($oFields = $database->query($sql)) {
    $num_fields = $oFields->numRows();
    if ($num_fields) {
?>
        <div class="w3-bar w3-text-blue-wb" style="margin: 0 0 0.25em 0.30em;">
            <div class="w3-col m12 w3-container">
                <span class="w3-text-blue-wb w3-xlarge"><?php echo $oTrans->TEXT_MODIFY_DELETE_FIELD; ?></span>
<?php if ($bCanDelete) { ?>
                <button type="submit" id="<?php echo $section_id;?>_section" class="field_delete w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width w3-right" ><?php echo $oTrans->FORM_MESSAGE_DELETE_LAYOUT;?></button>
<?php } ?>
                </div>
        </div>
        <div class="jsadmin hide"></div>
        <div class="field-ScrollTable">
          <table class="mod_form w3-table-all form-field-table" >
            <thead class="frm-Scroll">
                <tr class="w3-header-blue-wb">
                    <th style="width: 3%;text-align: center;">&nbsp;</th>
                    <th style="width: 3%;text-align: center;">ID</th>
                    <th style="width:50%"><?php print $oTrans->TEXT_FIELD; ?></th>
                    <th style="width:20%"><?php print $oTrans->TEXT_TYPE; ?></th>
                    <th style="width:10%"><?php print $oTrans->TEXT_REQUIRED; ?></th>
                    <th style="width:10%"><?php echo $oTrans->TEXT_MULTISELECT; ?></th>
                    <th style="width: 9%;"><?php echo $oTrans->TEXT_ACTIONS; ?></th>
                    <th style="width:3%;text-align: center;">POS</th>
                </tr>
            </thead>
          </table>

          <table class="mod_form w3-table-all form-field-table" id="tableData_<?php echo $section_id;?>" >
            <thead class="frm-Scroll">
                <tr class="w3-header-blue-wb"><th colspan="10" style="line-height: 0;"></th></tr>
            </thead>
            <tbody class="scrolling">
<?php
        while(!is_null($aFields = $oFields->fetchRow(MYSQLI_ASSOC))) {
          $sFieldIdkey = $admin->getIDKEY($aFields['field_id']);
?>
                <tr class="sectionrow">
                    <td style="width: 2%;text-align: center;">
                        <a href="<?php echo $sAddonUrl; ?>modify_field.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $sSectionIdKey; ?>&amp;field_id=<?php echo $sFieldIdkey; ?>" title="<?php echo $oTrans->TEXT_MODIFY; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/modify_16.png" alt="^" />
                        </a>
                    </td>
                    <td style="width:2%;text-align: right;">
                        <a style=" font-weight: normal;" href="<?php echo $sAddonUrl; ?>modify_field.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $sSectionIdKey; ?>&amp;field_id=<?php echo $sFieldIdkey; ?>">
                            <?php echo $aFields['field_id']; ?>
                        </a>
                    </td>
                    <td style="width:50%;">
                        <a href="<?php echo $sAddonUrl; ?>modify_field.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $sSectionIdKey; ?>&amp;field_id=<?php echo $sFieldIdkey; ?>">
                            <?php echo $aFields['title']; ?>
                        </a>
                    </td>
                    <td style="width:12%;">
<?php
                    $key = $aFields['type'];
                    switch ($key):
                        case 'textfield':
                            $sTitle = $oTrans->TEXT_SHORT_TEXT;
                            break;
                        case 'textarea':
                            $sTitle = $oTrans->TEXT_LONG_TEXT;
                            break;
                        case 'heading':
                            $sTitle = $oTrans->TEXT_HEADING;
                            break;
                        case 'select':
                            $sTitle = $oTrans->TEXT_SELECT_BOX;
                            break;
                        case 'checkbox':
                            $sTitle = $oTrans->TEXT_CHECKBOX_GROUP;
                            break;
                        case 'radio':
                            $sTitle = $oTrans->TEXT_RADIO_BUTTON_GROUP;
                            break;
                        case 'email':
                            $sTitle = $oTrans->TEXT_EMAIL_ADDRESS;
                            break;
                        case 'subject':
                            $sTitle = $oTrans->TEXT_SUBJECT;
                            break;
                        default:
                        break;
                    endswitch;
                    echo $sTitle;
?>
                    </td>
                    <td style="width:20%;text-align: center;">
<?php
                if ($aFields['type'] != 'group_begin') {
                    if($aFields['required'] == 1) { echo $oTrans->TEXT_YES; } else { echo $oTrans->TEXT_NO; }
                }
?>
                    </td>
                    <td style="width: 10%;text-align: center;">
<?php
                if ($aFields['type'] == 'select') {
                    $aFields['extra'] = \explode(',',$aFields['extra']);
                    echo (($aFields['extra'][1] == 'multiple') ? $oTrans->TEXT_YES : $oTrans->TEXT_NO);
                }
?>
                    </td>
                    <td style="width:3%;text-align: center;">
<?php if($aFields['position'] != 1) { ?>
                        <a href="<?php echo $sAddonUrl; ?>move_up.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;field_id=<?php echo $sFieldIdkey; ?>&amp;move_id=<?php echo $aFields['field_id']; ?>&amp;position=<?php echo $aFields['position']; ?>&amp;module=<?php echo $sAddonName; ?>" title="<?php echo $oTrans->TEXT_MOVE_UP; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/up_16.png" alt="up" />
                        </a>
<?php } ?>
                    </td>
                    <td style="width:3%;text-align: center;">
<?php if($aFields['position'] != $num_fields) { ?>
                        <a href="<?php echo $sAddonUrl; ?>move_down.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;field_id=<?php echo $sFieldIdkey; ?>&amp;move_id=<?php echo $aFields['field_id']; ?>&amp;position=<?php echo $aFields['position']; ?>&amp;module=<?php echo $sAddonName; ?>" title="<?php echo $oTrans->TEXT_MOVE_DOWN; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/down_16.png" alt="down" />
                        </a>
<?php } ?>
                    </td>
                    <td style="width:3%;text-align: center;">
<?php
                $url = ($sAddonUrl.'delete_field.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;field_id='.$sFieldIdkey)
?>
                        <a class="confirm" onclick="confirm_link('<?php echo ($oTrans->TEXT_ARE_YOU_SURE); ?>','<?php echo $url; ?>');" title="<?php echo $oTrans->TEXT_DELETE; ?>">
                            <img src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="X" />
                        </a>
                    </td>
                    <td style="width:1.5%;text-align: right; padding-right: 5px;">
<?php
                    echo $aFields['position'];
if ( DEBUG ) {
}
?>
                    </td>
                </tr>
<?php
            // Alternate row color
        } // end while fields
?>
            </tbody>
          </table>
        </div>
</article>
<?php
        // include the required file for Javascript admin
        if (\file_exists($oReg->AppPath.'modules/jsadmin/jsadmin_backend_include.php')) {
            include($oReg->AppPath.'/modules/jsadmin/jsadmin_backend_include.php');
        }
    } else { ?>
        <div class=" w3-text-blue-wb w3-large w3-margin"><?php echo $oTrans->TEXT_FORM_NONE_FOUND;?></div>
<?php
    }
}

?>
<div class="w3-container">

<div class="w3-bar w3-text-blue-wb w3-xlarge w3-margin">
    <div class="w3-col m12 w3-container">
            <span class="w3-text-blue-wb"><?php echo $oTrans->TEXT_SUBMISSIONS; ?></span>
        </div>
</div>

<?php
$old_section_id = $section_id;
// Query submissions table
/*
$sql  = 'SELECT * FROM `'.$oReg->TablePrefix.'mod_form_submissions`  ';
$sql .= 'WHERE `section_id` = '.(int)$section_id.' ';
$sql .= 'ORDER BY `submitted_when` ASC ';
*/
$sql  = '
SELECT
s.*, u.`display_name`, u.`email`,s.`submitted_when`
FROM `'.$oReg->TablePrefix.'mod_form_submissions` s
LEFT OUTER JOIN `'.$oReg->TablePrefix.'users` u
ON u.`user_id` = s.`submitted_by`
WHERE s.`section_id` = '.(int)$section_id.'
  AND s.`body` != \'\'
ORDER BY s.`submitted_when` DESC
';

if ($oSubmissions = $database->query($sql)) {
?>
<!-- submissions -->
    <div class="sub-ScrollTable">
        <table class="mod_form w3-table-all form-field-table" >
            <thead class="frm-Scroll">
                <tr class="w3-header-blue-wb">
                    <th style="width: 3%; text-align: center; ">&nbsp;</th>
                    <th style="width: 2%; text-align: center; "> ID </th>
                    <th style="width: 14%;"><?php echo $oTrans->TEXT_SUBMITTED ?></th>
                    <th style="width: 20%;"><?php echo $oTrans->TEXT_USER; ?></th>
                    <th style="width: 11%;"><?php echo $oTrans->TEXT_EMAIL.' '.$oTrans->MOD_FORM_FROM ?></th>
                    <th style="width: 5%;text-align: center; ">&nbsp;</th>
                    <th style="width: 5%;text-align: center; ">&nbsp;</th>
                    <th style="width: 3%;text-align: center; ">&nbsp;</th>
                    <th style="width: 3%;text-align: center; ">&nbsp;</th>
                    <th style="width: 3%;text-align: center; ">&nbsp;</th>
                </tr>
            </thead>
        </table>

        <table class="mod_form w3-table-all form-field-table">
            <thead class="frm-Scroll">
                <tr class="w3-header-blue-wb"><th colspan="10" style="line-height: 0;"></th></tr>
            </thead>
            <tbody class="scrolling">
<?php
    if ($oSubmissions->numRows() > 0) {
        // List submissions
       $emailUser = (function ($userid=0)
       {
            $oReg = WbAdaptor::getInstance();
            $oDb  = $oReg->getDatabase();
            $retval = '';
            if ($userid!='0') {
                $sql  = 'SELECT `email` FROM `'.$oReg->TablePrefix.'users` '
                      . 'WHERE `user_id`=\' '.$userid.'\' ';
                $retval = $oDb->get_one($sql);
            }
            return $retval;
        });
        while($submission = $oSubmissions->fetchRow(MYSQLI_ASSOC)) {

            $submission['display_name'] = (($submission['display_name']!=null) ? $submission['display_name'] : $oTrans->TEXT_GUEST);
            $sBody = $submission['body'];
            $submission['email'] = $emailUser($submission['submitted_by']);
            if ($submission['email']==''){
                $regex = "/[a-z0-9\-_]?[a-z0-9.\-_]+[a-z0-9\-_]?@[a-z0-9.-]+\.[a-z]{2,}/i";
                \preg_match ($regex, $sBody, $output);
// workout if output is empty
                $submission['email'] = (isset($output['0']) ? $output['0'] : '');
            }
            $sSubmissionIdkey = $admin->getIDKEY($submission['submission_id']);
            $sSubmittedWhen = PreCheck::getStrftime($sDateFormat, $submission['submitted_when']+TIMEZONE)
                       . ', '.PreCheck::getStrftime($sTimeFormat, $submission['submitted_when']+TIMEZONE);
?>
            <tr class="">
                <td style="text-align: center; width: 2%;">
                    <a href="<?php echo $sAddonUrl?>view_submission.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;submission_id=<?php echo $sSubmissionIdkey; ?>" title="<?php echo $oTrans->TEXT_OPEN; ?>">
                        <img src="<?php echo THEME_URL; ?>/images/folder_16.png" alt="<?php echo $oTrans->TEXT_OPEN; ?>" />
                    </a>
                </td>
                <td style="padding-right: 15px;text-align: right; width: 2%; font-weight: normal;"><?php echo $submission['submission_id']; ?></td>
                <td style=" width: 14%;"><?php echo $sSubmittedWhen; ?></td>
                <td style=" width: 20%;"><?php echo $submission['display_name']; ?></td>
                <td style=" width: 11%;" ><?php echo $submission['email']; ?></td>
                <td style="text-align: center; width: 5%;">&nbsp;</td>
                <td style=" width: 5%;"  >&nbsp;</td>
                <td style="text-align: center; width: 3%;cursor: pointer;">
<?php
                $url = ($sAddonUrl.'delete_submission.php?page_id='.$page_id.'&amp;section_id='.$section_id.'&amp;submission_id='.$sSubmissionIdkey)
?>
                    <a class="confirm" onclick="confirm_link('<?php echo ($oTrans->TEXT_ARE_YOU_SURE); ?>','<?php echo $url; ?>');" title="<?php echo $oTrans->TEXT_DELETE; ?>">
                        <img src="<?php echo THEME_URL; ?>/images/delete_16.png" alt="X" />
                    </a>
                </td>
<?php
if ( DEBUG ) { ?>
                <td style=" width: 3%;" ><?php echo $sSubmissionIdkey; ?></td>
                <td style=" width: 3%;" >&nbsp;</td>
<?php } else  { ?>
                <td style=" width: 3%;" >&nbsp;</td>
                <td style=" width: 3%;" >&nbsp;</td>
<?php }  ?>
            </tr>
<?php
        }
    } else { ?>
<tr class="w3-section">
    <td colspan="10" class=" w3-text-blue-wb w3-large w3-margin"><?php echo $oTrans->TEXT_FORM_NONE_FOUND ?></td>
</tr>
<?php } ?>
        </tbody>
        </table>
<div class="w3-margin-bottom"></div>
    </div>
<?php
} else {
    echo $database->get_error().'<br />';
    echo $sql;
}
?>
</div>
    <script src="<?php echo WB_URL;?>/include/plugins/default/sweetalert2/09.17.2/dist/sweetalert2.all.min.js"></script>
