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
 * @subpackage      modify_field
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
use bin\helpers\{PreCheck,ParentList};
use App\Utils\{IpAddress,Helpers};
use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $oReg     = Wbadaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oRequest = $oReg->getRequester();
    $oTrans   = $oReg->getTranslate();
    $oApp     = $oReg->getApplication();
/* may be not needed */
//    if ((($oApp instanceof frontend))==false) {
    if (empty($oApp->page)) {
        $oApp0 = ($GLOBALS['wb'] ?? null);
        $oApp1 = ((isset($GLOBALS['admin']) && $oApp0==null) ? $GLOBALS['admin'] : $oApp0);
        $oReg->setApplication($oApp1);
        $oApp  = ($oReg->getApplication());
    }

    $isAuth   = $oApp->is_authenticated();
//    require(WB_PATH.'/framework/functions.php');

    $sAddonPath = \str_replace(['\\','//'], '/', __DIR__).'/';
    $sAddonName = \basename($sAddonPath);
//TODO
    $aRequestVars = [];
    $bMailDebug = true;
    // Only for development for pretty mysql dump
    $sLocalDebug  =  is_readable($sAddonPath.'.setDebug');
    // Only for development prevent secure token check,
    $sSecureToken = !is_readable($sAddonPath.'.setToken');
    $sPHP_EOL     = ($sLocalDebug ? "\n" : '');

    $aRequestNames = $oRequest->getParamNames();
    foreach ($aRequestNames as $item){
        $aRequestVars[$item] = $oRequest->getParam($item);
    }
// TODO dw2020 Sanitize Requests
    if (count($aRequestVars)){

    }

    $sDateFormat = ($oReg->DateFormat ?? 'system_default');
    $sDateFormat = ($sDateFormat == 'system_default') ? $oReg->DefaultDateFormat : $oReg->DateFormat;
    $sTimeFormat = ($oReg->TimeFormat ?? 'system_default');
    $sTimeFormat = ($sTimeFormat == 'system_default') ? $oReg->DefaultTimeFormat : $oReg->TimeFormat;
    $sDateFormat = PreCheck::dateFormatToStrftime($sDateFormat);

// load module language file
    if (\is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (\is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (\is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}

    if (!isset($oTrans) || !($oTrans instanceof \Translate)) { $oTrans = \Translate::getInstance(); }
    $oTrans->enableAddon('modules\\'.$sAddonName);
    $form_name = 'form'.$section_id;

    $aModForm = [];
    $aWebsiteTitle = (\defined('WEBSITE_TITLE') && !empty(WEBSITE_TITLE) ? WEBSITE_TITLE : $oRequest->getServerVar('SERVER_NAME'));
    $aReplacement  = ['WEBSITE_TITLE' => $aWebsiteTitle, 'SERVER_EMAIL'=>SERVER_EMAIL];

    $MOD_FORM_EMAIL_SUBJECT         = replace_vars($oTrans->MOD_FORM_EMAIL_SUBJECT, $aReplacement);
    $MOD_FORM_SUCCESS_EMAIL_TEXT    = replace_vars($oTrans->MOD_FORM_SUCCESS_EMAIL_TEXT, $aReplacement);
    $MOD_FORM_SUCCESS_EMAIL_SUBJECT = replace_vars($oTrans->MOD_FORM_SUCCESS_EMAIL_SUBJECT, $aReplacement);

    $aModForm['EMAIL_SUBJECT'] = $MOD_FORM_EMAIL_SUBJECT;
    $aModForm['SUCCESS_EMAIL_TEXT'] = $MOD_FORM_SUCCESS_EMAIL_TEXT;
    $aModForm['SUCCESS_EMAIL_SUBJECT'] = $MOD_FORM_SUCCESS_EMAIL_SUBJECT;

/*
    $removebreaks = function ($value) {
        return \trim(\preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i', null, $value));
    };
    $checkbreaks = function ($value) {
        return $value === $removebreaks($value);
    };
*/
    $aSuccess   = [];
    $aErrorMsg  = [];

    $emailAdmin = (function () use ( $oDb, $oApp ){
        $retval = $oApp->get_email();
        if ($oApp->getUserId() != '1') {
            $sql  = 'SELECT `email` FROM `'.TABLE_PREFIX.'users` '
                  . 'WHERE `user_id`=\'1\' ';
            $retval = $oDb->get_one($sql);
        }
        return $retval;
    });

    $emailUser = (function ($id=1) use ( $oDb, $oApp ){
        $retval = $oApp->get_email();
        if ($oApp->getUserId() != 1) { }
            $sql  = 'SELECT `email`,`display_name` FROM `'.TABLE_PREFIX.'users` '
                  . 'WHERE `user_id`='.(int)$id.' ';
            if ($oRes = $oDb->query($sql)){
                $retval = ($oRes->fetchRow(MYSQLI_ASSOC) ?? null);
            } elseif ($oDb->is_error() || !is_array(($retval))){
                \trigger_error(sprintf("[%d] Invalid query %s",__LINE__, $oDb->get_error()), E_USER_NOTICE);
            }
        return $retval;
    });

    if ($bMailDebug){;}
/* ---------------------------------------------------------------------------------- */
    if (!\function_exists("new_submission_id") ) {
        function new_submission_id() {
            $submission_id = '';
            $salt = "abchefghjkmnpqrstuvwxyz0123456789";
            \srand((double)microtime()*1000000);
            $i = 0;
            while ($i <= 7) {
                $num = \rand() % 33;
                $tmp = \substr($salt, $num, 1);
                $submission_id = $submission_id . $tmp;
                $i++;
            }
            return $submission_id;
        }
    }
/* ---------------------------------------------------------------------------------- */
/*
    $sScriptUrl = WB_URL.PAGES_DIRECTORY.$oApp->page['link'].PAGE_EXTENSION ;
    $sShortUrl  = WB_URL.$oApp->page['link'].'/' ;
    $sRecallUrl = (\is_readable(WB_PATH.DIRECTORY_SEPARATOR.'short.php') ? $sShortUrl : $sScriptUrl);
*/
    $sRecallUrl = $oApp->getPageLink((int)$oApp->page_id);
/* ---------------------------------------------------------------------------------- */
// Work-out if the form has been submitted or not
    $bSubmittedFlag = (!isset($aRequestVars['section_id']) || (isset($aRequestVars['section_id']) && ($aRequestVars['section_id'] != $section_id)));
    if ($bSubmittedFlag){
        \is_callable('display_captcha_real') ? '' : require (WB_PATH.'/include/captcha/captcha.php');
        // Set new submission ID in session
        $_SESSION[$form_name]['form_submission_id'] = $oApp->getUniqueToken(7);//new_submission_id();
        $out = '';
        $header = '';
        $field_loop = '';
        $sExtra = '';
        $footer = '';
        $sFooter = '';
        $use_xhtml_strict = false;
        $sDataProtection = 'not loaded';
        // Get settings
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_settings` '
              . 'WHERE section_id = '.(int)$section_id.' ';
        if ($oSetting = $oDb->query($sql)){
            // $query_settings  $fetch_settings
            $sDataProtection = $oDb->get_error();
            if ($oSetting->numRows() > 0){
                $aSettings = $oSetting->fetchRow(MYSQLI_ASSOC);
                $header = \str_replace('{WB_URL}',WB_URL, $aSettings['header']);
                $field_loop = $aSettings['field_loop'];
                $sExtra = $aSettings['extra'];
                $page_id = $aSettings['page_id'];
                $sDivider = htmlspecialchars_decode($aSettings['divider'] ?? ' : ');
                if ($aSettings['use_data_protection']) {
                    $target_section_id = $aSettings['data_protection_link'];
                    $sDataLink = ParentList::build_access_file($target_section_id);
                    \ob_start();
?>
    <article class="check-container caption data_protection" style="margin-top: 0.7em;">
        <h2 style="display: none;">&nbsp;</h2>
        <input class="checkbtn frm-border" id="data_protection" name="data_protection" value="1" type="checkbox" />
        <label for="data_protection" class="description ">
              <span class="checkbtn"></span>
              <span class="frm-label frm-radio-label">
              <?php echo (empty($sDataLink) ? \sprintf($FORM_MESSAGE['NO_DSGVO']) : \sprintf($FORM_MESSAGE['DSGVO'], $sDataLink)); ?>
              </span>
        </label>
    </article>
<?php
                    $sDataProtection = \ob_get_clean().PHP_EOL;
                }
                $sFooter = \str_replace('{WB_URL}',WB_URL, $aSettings['footer']);
                $use_captcha = $aSettings['use_captcha'];
                $use_xhtml_strict = false;
            }
        }
    // do not use sec_anchor, can destroy some layouts
        // Get list of fields
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_fields` '
              . 'WHERE section_id = '.(int)$section_id.' '
              . 'ORDER BY position ASC ';
        if ($query_fields = $oDb->query($sql)) {
            if ($query_fields->numRows() > 0) {
//                $sScriptUrl = $oRequest->getServerVar('SCRIPT_NAME');
//                $sActionUrl = WB_URL.$sScriptUrl;
              $iFormRequestId = isset($aRequestVars['fri']) ? intval($aRequestVars['fri']) : 0;
?>
            <section class="form-block">
                <h2 style="display: none;">&nbsp;</h2>
                <form style="float: none;" class="frm-formular" <?php echo (((\strlen($form_name) > 0) AND (false == $use_xhtml_strict)) ? "id=\"".$form_name."\"" : ""); ?> action="<?php echo $sRecallUrl.'';?>" method="post">
                    <input type="hidden" name="section_id" value="<?php echo $section_id;?>" />
                    <input type="hidden" name="submission_id" value="<?php echo $_SESSION[$form_name]['form_submission_id']; ?>" />
<?php
              if ($iFormRequestId) {
?>
                    <input type="hidden" name="fri" value="<?php echo $iFormRequestId;?>" />
<?php
                }
                if (ENABLED_ASP) { // first add some honeypot-fields
?>
                    <input type="hidden" name="submitted_when" value="<?php $t=\time(); echo $t; $_SESSION[$form_name]['submitted_when']=$t; ?>" />
                    <fieldset class="frm-fieldset">
                        <p class="nixhier">
                            email address:
                            <label for="email<?php echo $section_id;?>">Leave this field email-address blank:</label>
                            <input id="email<?php echo $section_id;?>" name="email" size="56" value="" /><br />
                            Homepage:
                            <label for="homepage<?php echo $section_id;?>">Leave this field homepage blank:</label>
                            <input id="homepage<?php echo $section_id;?>" name="homepage" size="55" value="" /><br />
                            URL:
                            <label for="url<?php echo $section_id;?>">Leave this field url blank:</label>
                            <input id="url<?php echo $section_id;?>" name="url" size="61" value="" /><br />
                            Comment:
                            <label for="comment<?php echo $section_id;?>">Leave this field comment blank:</label>
                            <textarea id="comment<?php echo $section_id;?>" name="comment" cols="50" rows="10"></textarea><br />
                        </p>
<?php           }
/* ----------------------------------------------------------------------------- */
          require __DIR__.'/'.'printForm.php';
/* ----------------------------------------------------------------------------- */
?>
                    </fieldset>
                </form>
          </section>
<?php
            }
        }
/* --------------------------------------- */
// check for errors and required fields
/* --------------------------------------- */
    } elseif (isset($aRequestVars['section_id']) && ($aRequestVars['section_id']==$section_id))
    {

    // Check that submission ID matches
    if (isset($_SESSION[$form_name]['form_submission_id'])
        && isset($aRequestVars['submission_id'])
        && ($_SESSION[$form_name]['form_submission_id'] == $aRequestVars['submission_id'])
    ) {
        $mail_replyto = '';
        $mail_replyName = '';
        $aMailValues = [];

        $aMailValues = [
            'is_authenticated' => false,
            'mail_replyto'     => '',
            'mail_replyName'   => '',
        ];

        if ($isAuth && $oApp->get_email()) {
          $mail_replyto = $oApp->get_email();
          $mail_replyName = \htmlspecialchars($oDb->escapeString($oApp->get_display_name()));
          $_SESSION[$form_name]['DISPLAY_NAME'] = $mail_replyName;
          $aMailValues = [
              'is_authenticated' => true,
              'mail_replyto'     => $mail_replyto,
              'mail_replyName'   => $mail_replyName,
          ];
        }

// Set new submission ID in session
//        $_SESSION[$form_name]['form_submission_id'] = $oApp->getUniqueToken(7);//new_submission_id();
// form faked? Check the honeypot-fields.
        if (ENABLED_ASP && (
            (!isset($aRequestVars['submitted_when']) || !isset($_SESSION[$form_name]['submitted_when'])) ||
            ($aRequestVars['submitted_when'] != $_SESSION[$form_name]['submitted_when']) ||
            (!isset($aRequestVars['email']) || $aRequestVars['email']) ||
            (!isset($aRequestVars['homepage']) || $aRequestVars['homepage']) ||
            (!isset($aRequestVars['comment']) || $aRequestVars['comment']) ||
            (!isset($aRequestVars['url']) || $aRequestVars['url'])// || !($bAspTest ?? false) // debug
        )) {
            // spam
//            $oApp->send_header($sRecallUrl); // TODO link to a page select in settings
              $sMessage = nl2br(sprintf("[%d] %s\n",__LINE__,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS));
              $sLink = '<a class="back-link" href="'.$sRecallUrl.'" >'.$oTrans->TEXT_BACK_TO_FORM.'</a>';
              $oApp->ShowMaintainScreen('error',$sMessage,$sLink);
              exit();
        }
// First start message settings
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_settings` '
              . 'WHERE `section_id` = '.(int)$section_id.'';
        if ($oSetting = $oDb->query($sql) ){
            if ($oSetting->numRows() > 0){
                if (($aSettings = $oSetting->fetchRow(\MYSQLI_ASSOC))){
                    $ServerEmail = $oDb->escapeString(SERVER_EMAIL);
                // who should manage the formular get from setting or webmaster
                    $email_to = (!empty($aSettings['email_to']) ? $aSettings['email_to'] : ($isAuth ? $emailAdmin() : $ServerEmail));
                    // where the formular comes from,  mail server settings
                    $email_from = $ServerEmail;
                    // get auth display name or guest
                    $email_toname   = ($_SESSION[$form_name]['DISPLAY_NAME'] ?? $oTrans->TEXT_GUEST);
                    $mail_replyName = $email_toname;
                    $email_fromname = $aSettings['email_fromname'];
                    if (\substr($email_fromname, 0, 5) == 'field') {
                        // Set the email_fromname to field to what the user entered in the specified field
                        $email_fromname = \htmlspecialchars($oDb->escapeString($aRequestVars[$email_fromname]));
                    }
                }

//set replyto adress
                if (empty(trim($mail_replyto))) {
                    $success_email_to = (!empty($aSettings['success_email_to'] ) ? $aSettings['success_email_to'] : '');
                    if (\substr($success_email_to, 0, 5) == 'field') {
                        // Set the success_email to field to what the user entered in the specified field
                        $success_email_to = \htmlspecialchars($oDb->escapeString($aRequestVars[$success_email_to]));
                        $mail_replyto = ($isAuth ? $mail_replyto : $success_email_to);
                    }
                }
                $email_subject = (!empty($aSettings['email_subject']) ? $aSettings['email_subject'] : $MOD_FORM_EMAIL_SUBJECT);
                $success_page = $aSettings['success_page'];
                $success_email_to = $mail_replyto;
                $success_email_toName = $mail_replyName;
                $success_email_from = $oDb->escapeString(SERVER_EMAIL);
                $success_email_fromname = $aSettings['success_email_fromname'];
                $success_email_text = $aSettings['success_email_text'];
                $success_email_text = (($success_email_text != '') ? $success_email_text : $MOD_FORM_SUCCESS_EMAIL_TEXT);
                $success_email_subject = (($aSettings['success_email_subject'] != '') ? $aSettings['success_email_subject'] : $MOD_FORM_SUCCESS_EMAIL_SUBJECT);
                $max_submissions = $aSettings['max_submissions'];
                $stored_submissions = $aSettings['stored_submissions'];
                $use_captcha = $aSettings['use_captcha'];
                $use_data_protection = $aSettings['use_data_protection'];
                $sDivider = htmlspecialchars_decode($aSettings['divider'] ?? ' : ');
            } else {
                exit($oTrans->TEXT_UNDER_CONSTRUCTION);
            }
        }

        $email_body = "\n";
        // Create blank "required" array
        $aRequired = [];
        // Captcha
        $bCaptchaChecked = false;
        if ((($use_captcha && !$isAuth) || ($use_captcha && $isAuth && !$aSettings['use_captcha_auth'])))
        {
            // captcha result exists and check if valide or not
            $namespace = (isset($aRequestVars['captcha'.$section_id]) ? 'captcha'.$section_id : 'captcha');
            if ((isset($aRequestVars[$namespace]) && !empty($aRequestVars[$namespace])))
            {
                if ((isset($_SESSION[$namespace]) && ($aRequestVars[$namespace] != $_SESSION[$namespace]))) {
                    $aReplace = ['WEBMASTER_EMAIL' => $emailAdmin()];
                    $captcha_error = replace_vars($oTrans->MESSAGE_INCORRECT_CAPTCHA, $aReplace);
                    $aRequired[]= '';
                }
            } else {
                $aReplace = ['WEBMASTER_EMAIL' => $emailAdmin()];
                $captcha_error = replace_vars($oTrans->MESSAGE_INCORRECT_CAPTCHA,$aReplace );
                $aRequired[]= '';
            }
        } // end use captcha

// use $data_protection
        $sUserDateFormat = str_replace('|',' ',$oReg->DateFormat.', '.$oReg->TimeFormat);
        $date = \date($sUserDateFormat);
        $sDataProtection = \sprintf($oTrans->MOD_FORM_DSGVO_NOT_INUSE, $date);
        if (($use_data_protection)){
            if (!isset($aRequestVars['data_protection'])) {
                $sDataProtection = \sprintf($oTrans->MOD_FORM_DSGVO_DISABLED, $date);
                $sDSGVO_Error = $oTrans->MOD_FORM_DSGVO;
                $aRequired[]= '';
            } elseif((int)($aRequestVars['data_protection'])==1) {
                $iPostVar = $oApp->StripCodeFromText($aRequestVars['data_protection']);
                $sDataProtection = \sprintf($iPostVar ? ($oTrans->MOD_FORM_DSGVO_ENABLED) : ($oTrans->MOD_FORM_DSGVO_DIABLED), $date);
            }
        }

        // Loop through fields and add to message body
        // Get list of fields
        $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_fields` '
              . 'WHERE `section_id` = '.(int)$section_id.' '
              . 'ORDER BY position ASC';
        if ($oField = $oDb->query($sql))
        {
            while(($aFields = $oField->fetchRow(MYSQLI_ASSOC)))
            {
                if (!$aFields['active']){continue;}
                // Add to message body $field
                if ($aFields['type'] != '') {
                    if (!empty($aRequestVars['field'.$aFields['field_id']]))
                    {
                        $sPostVar = '';
                        $aPostVar['field'.$aFields['field_id']] = [];
                       // do not allow code in user input!
                        if (\is_array($aRequestVars['field'.$aFields['field_id']])) {
                            foreach ($aRequestVars['field'.$aFields['field_id']] as $key=>$val) {
                                $aPostVar['field'.$aFields['field_id']][$key] =  $oApp->strip_slashes($oApp->StripCodeFromText($val));
                            }
                            $_SESSION[$form_name]['field'.$aFields['field_id']] = $aPostVar['field'.$aFields['field_id']];
                        } else {
                            $sPostVar = $oApp->strip_slashes($oApp->StripCodeFromText($oApp->get_post('field'.$aFields['field_id'])));
                            $_SESSION[$form_name]['field'.$aFields['field_id']] = $sPostVar;
                        }
                        if ($aFields['type'] == 'email' && $oApp->validate_email($sPostVar) == false) {
                            $email_error = $MESSAGE['USERS_INVALID_EMAIL'];
                            $aRequired[]= '';
                        }
                        if ($aFields['type'] == 'heading') {
                            $email_body .= $sPostVar."\n";
                        } elseif (($sPostVar!='')) {
                            $aMatches = [];
                            $cr = "";
                            if (preg_match("/[^\r\n]+/iu",$sDivider,$aMatches)){$cr = "\n";}
                            $sDivider = (($aMatches[0]) ?? $sDivider);
                            $sDivider = str_replace(['\\n','\n'],"\n",$sDivider);
                            $email_body .= $aFields['title'].$sDivider." ".$sPostVar."\n"; // ": "
                        } elseif ((\count($aPostVar['field'.$aFields['field_id']]) > 0)) {
                            $email_body .= $aFields['title']."-- \n";
                            foreach ($aPostVar['field'.$aFields['field_id']] as $key=>$val) {
                                $email_body .= $val."\n";
                            }
                            $email_body .= "\n";
                        }
                    } elseif($aFields['required'] == 1) {
                        $aRequired[] = $aFields['title'];
                    }
                }
            } //  while
        } //  query
// only for dump to test if all parameters are set
                $aMailValues = \array_merge (
                        $aMailValues,
                        [
                          'success_email_from' => $success_email_from,
                          'success_email_fromname' => $success_email_fromname,
                          'success_email_to' => $success_email_to,
                          'success_email_toName' => $success_email_toName,
                          'success_email_subject' => $success_email_subject,
                          'success_email_text' => $success_email_text."\n".$email_body.$oTrans->MOD_FORM_SUCCESS_EMAIL_TEXT_GENERATED.$sDataProtection.PHP_EOL,
                          'SERVER_EMAIL' => SERVER_EMAIL,
                          'email_to' => $email_to,
                          'email_toname' => $email_toname,
                          'email_from' => $email_from,
                          'success_page' => $success_page,
                          'email_fromname' => $email_fromname,
                          'email_subject' => $email_subject,
                          'email_body' => $email_body,
                          'mail_replyto' => $mail_replyto,
                          'mail_replyName' => $mail_replyName,
                          'data_protection' => ($aSettings['info_dsgvo_in_mail'] ? '' :$sDataProtection),
                        ]
                );
/* -------------------------------------------------------------------------- */
// required TODO call template  $sdataProtection = ($aSettings['info_dsgvo_in_mail'] ? '' : $sDataProtection);
/* -------------------------------------------------------------------------- */
// BEGIN Check if the user forgot to enter values into all the required fields
    if (\is_readable($sAddonPath.'checkForErrors.php')){require $sAddonPath.'checkForErrors.php';}
    } else {
        if (isset($_SESSION[$form_name])){
          //unset ($_SESSION[$form_name]);
        }
?>
    <div class="w3-panel w3-leftbar w3-sand w3-large w3-serif">
      <p><i>"<?= $FORM_MESSAGE['PAGE_RELOADED'];?>"</i></p>
    </div>
    <input class="w3-padding" onclick="window.location.href='<?= $sRecallUrl;?>';" type="submit" name="submit" value="<?= $oTrans->TEXT_BACK_TO_FORM;?>" />
<?php
    }
//  deprecated
//    $sSuccessLink = WB_URL.'/index.php';
    $sSuccessLink = $sRecallUrl;
    if (isset($success) && $success == true){
// test here ok
        if ($aSettings['success_page']) {
            $success_page = (($aSettings['success_page']!=0) ? $aSettings['success_page'] : $page_id);
            $sql  = 'SELECT `link` FROM `'.TABLE_PREFIX.'pages` '
                  . 'WHERE `page_id` = '.(int)$success_page;
            if (($link = $oDb->get_one($sql)) ) {
               $sSuccessLink = WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
            }
        }
// redirect to another side?
        if (($aSettings['success_page']<=0)) {
//        if (!$aSettings['success_page']) {
//         Now check if the email was sent successfully
            $submitted_by = $oApp->getUserId();
            $submission['submitted_by'] = $submitted_by;
            $submission['submitted_when'] = \time();
            $submission['submission_id'] = $iSubmissionId;
//          Get submission details
            $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'mod_form_submissions` '
                  . 'WHERE `submission_id` = '.$iSubmissionId.' ';
            if ($query_content = $oDb->query($sql)) {
                $submission = $query_content->fetchRow(MYSQLI_ASSOC);
            } else {
              $aErrorMsg[] = \sprintf('[] %s',__LINE__,$oDb->get_error());
            }
            $Message = '';
            $user    = [];
            $user['display_name'] = $oTrans->TEXT_GUEST;
            $user['username'] = $oTrans->TEXT_UNKNOWN;
            $NixHier = 'frm-nixhier';
        //  Get the user details of whoever did this submission
            $sql  = 'SELECT `username`,`display_name` FROM `'.TABLE_PREFIX.'users` '
                  . 'WHERE `user_id` = '.$submission['submitted_by'];
            if ($get_user = $oDb->query($sql))
            {
                if ($get_user->numRows() != 0) {
                    $user = $get_user->fetchRow(MYSQLI_ASSOC);
                    $_SESSION[$form_name]['DISPLAY_NAME'] = $user['display_name'];
                } else {
                    $Message = $oTrans->MOD_FORM_PRINT;
                    $NixHier = '';
                }
            }
            $aSubSuccess = [];

//          set template file and assign module and template block
            $sTemplateDir  = $oReg->AppPath.'modules/'.\basename(__DIR__).'/templates/';
            $sTemplateName = (($oReg->DefaultTemplate !== 'DefaultTemplate') && \is_dir($sTemplateDir.$oReg->DefaultTemplate) ? $oReg->DefaultTemplate : 'default');
            $sTemplatePath = $sTemplateDir.$sTemplateName;
            $sTemplatePath = (\is_readable($sTemplatePath.'/submessage.htt') ? $sTemplatePath : $sTemplateDir.'/default/');
            $oTpl = new Template($sTemplatePath);
            $oTpl->set_file('page', 'submessage.htt');
            $oTpl->setDebug(0);
            $oTpl->set_block('page', 'main_block', 'main');
            $aPaths = [
                    'ADMIN_URL' => ADMIN_URL,
                    'THEME_URL' => THEME_URL,
                    'MODULE_URL' => WB_URL.'/modules/'.\basename(__DIR__).'',
                    'WB_URL' => WB_URL
                ];

            $oTpl->set_var($aPaths);
            $success_email_text = \preg_replace('/[\n\r]/', '',\nl2br(($success_email_text)));
            $sSubmittedWhen = PreCheck::dateFormatToStrftime($sDateFormat, $submission['submitted_when']+TIMEZONE)
                       . ', '.PreCheck::dateFormatToStrftime($sTimeFormat, $submission['submitted_when']+TIMEZONE);

            $aData = [
                    'SUCCESS_EMAIL_TEXT' => $success_email_text,
                    'TEXT_SUBMISSION_ID' => $oTrans->TEXT_SUBMISSION_ID,
                    'TEMPLATE' => $sTemplateName,
                    'submission_submission_id' => $submission['submission_id'],
                    'submission_submitted_when' => $sSubmittedWhen,
            ];
            $oTpl->set_var($aData);
            $aLangs = [
                    'TEXT_SUBMITTED' => $oTrans->TEXT_SUBMITTED,
                    'NIX_HIER' => $NixHier,
                    'TEXT_USER' => $oTrans->TEXT_USER,
                    'TEXT_BACK' => $oTrans->TEXT_BACK,
                    'TEXT_BACK_TO_FORM' => $oTrans->TEXT_BACK_TO_FORM,
                    'TEXT_USERNAME' => $oTrans->TEXT_USERNAME,
                    'TEXT_PRINT_PAGE' => $oTrans->TEXT_PRINT_PAGE,
                    'TEXT_REQUIRED_JS' => $oTrans->TEXT_REQUIRED_JS,
                    'user_display_name' => $user['display_name'],
                    'user_username' => $user['username'],
                    'SUCCESS_PRINT' => $Message,
                    'SUCCESS_LINK' => $sSuccessLink,
                    'submission_body' => \nl2br($email_body),
//                    'submission_body' => \preg_replace('/[\n\r]/', '',\nl2br($email_body)),
                    ];
            $oTpl->set_var($aLangs);
            $oTpl->parse('main', 'main_block', false);
            $output = $oTpl->finish($oTpl->parse('output', 'page'));
            unset($oTpl);
            print $output;
        } else {
// ok here
        //  clearing session on success
            $sql  = 'SELECT `field_id` FROM `'.TABLE_PREFIX.'mod_form_fields` '
                  . 'WHERE `section_id` = '.$section_id.'';
            $query_fields = $oDb->query( $sql );
            while($field = $query_fields->fetchRow(MYSQLI_ASSOC)) {
                $field_id = $field['field_id'];
                if (isset($_SESSION[$form_name]['field'.$field_id])){unset($_SESSION[$form_name]['field'.$field_id]);}
            }
            $oApp->send_header($sSuccessLink);
            exit;
//            echo "<script>location.href='".$sSuccessLink."';</script>";
        }
    //  clearing session on success
        $sql  = 'SELECT `field_id` FROM `'.TABLE_PREFIX.'mod_form_fields` '
              . 'WHERE `section_id` = '.$section_id.'';
        $query_fields = $oDb->query( $sql );
        while($field = $query_fields->fetchRow(\MYSQLI_ASSOC)) {
            $field_id = $field['field_id'];
            if(isset($_SESSION[$form_name]['field'.$field_id])){unset($_SESSION[$form_name]['field'.$field_id]);}
        }
    } else {
        if (isset($success) && $success == false) {
?>
    <div class="w3-panel w3-leftbar w3-sand w3-large w3-serif">
      <p><i>"<?= $MOD_FORM['ERROR'];?>"</i></p>
    </div>
    <input id="form-error" class="w3-padding form-error" onclick="window.location.href='<?= $sRecallUrl;?>';" type="submit" name="submit" value="<?= $oTrans->TEXT_BACK_TO_FORM;?>" />
<?php
        }
    }
}
