<?php
use App\Utils\{IpAddress,Helpers};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */


        if (\count($aRequired )){
?>
            <div class="form-block frm-warning ">
<?php
    if (!isset($oTrans->MESSAGE_MOD_FORM_REQUIRED_FIELDS)) {
?>
                <h3>You must enter details for the following fields</h3>
<?php  } else {?>
                <h3 style="font-size: 1.8em;"><?=$oTrans->MESSAGE_MOD_FORM_REQUIRED_FIELDS;?> <span class="nixhier w3-hide">(<?php echo $oTrans->TEXT_SECTION.' '.$section_id;?>)</span></h3>
<?php
            }
?>
            <ol>
<?php
            foreach($aRequired as $field_title) {
                if ($field_title!=''){
?>
                    <li><?= $field_title;?></li>
<?php
                }
            }
            if (isset($email_error)) {
?>
                <li><?= $email_error;?></li>
<?php
            }
            if (isset($captcha_error)) {
?>
                <li><?= $captcha_error;?></li>
<?php
            }
            if (isset($sDSGVO_Error)) {
?>
                <li><?= $sDSGVO_Error;?></li>
<?php
            }
            // Create blank "required" array
            $aRequired = [];
?>
            </ol>
            <p class="frm-warning form-error">
                <a id="frm-warning" class="frm-btn" href="<?= $sRecallUrl;?>"><?= $oTrans->TEXT_BACK;?></a>
            </p>
            </div>
<?php
        } else // end required
        { // email_error
// TODO call in a template
            if (isset($email_error)) {
?>
                <div class="frm-warning">
                <ol>
                <li><?= $email_error;?></li>
                </ol>
                <p class="frm-warning form-error ">
                    <a id="email-error" class="frm-btn" href="<?= $sRecallUrl;?>"><?= $oTrans->TEXT_BACK;?></a>
                </p>
                </div>
<?php
            } elseif(isset($captcha_error)) {
?>
                <div class="frm-warning">
                <br /><ol>'
                <li><?= $captcha_error;?></li>
                </ol>
                <p class="frm-warning form-error ">
                  <a id="captcha-error" class="frm-btn" href="<?= $sRecallUrl;?>"><?= $oTrans->TEXT_BACK;?></a>
                </p>
                </div>
<?php
           }  else {// no captcha error
/* -------------------------------------------------------------------------- */
// Check how many times form has been submitted in last hour
/* -------------------------------------------------------------------------- */
                $success = false;
                $last_hour = \time()-$aSettings['spam_submissions']*60; // minutes
                $sFormIpAddress = $oRequest->getServerVar('REMOTE_ADDR');
                $sNormFormIp = IpAddress::sanitizeIp($sFormIpAddress);
                $sql  = '
                SELECT `submission_id` FROM `'.TABLE_PREFIX.'mod_form_submissions`
                WHERE `submitted_when` >= '.(int)$last_hour.'
                  AND `form_ip` = \''.$sNormFormIp.'\'
                  AND `section_id` = '.(int)$section_id.'
                ';
                if ($oSubmissions = $oDb->query($sql)){
            // stop
                    if (($max_submissions > 0) && ($oSubmissions->numRows() >= $max_submissions)){
// Too many submissions so far this hour
                        echo $oTrans->MESSAGE_MOD_FORM_EXCESS_SUBMISSIONS;
                        $success = false;
                    } else {
                        // Adding the IP to the body and try to send the email
                        // $email_body .= "\n\nIP: ".$_SERVER['REMOTE_ADDR'];
                        if (isset($aRequestVars['fri'])&& \is_numeric($aRequestVars['fri'])) {
                            $iFormRequestId = \filter_var($aRequestVars['fri'],FILTER_VALIDATE_INT);
                        } else {
                          $iFormRequestId = \time();
                        }
                        if ($iFormRequestId) {
//                            $email_body .= "\n\nFormRequestID: ".$iFormRequestId;
                        }
                        $sEmailBody  = $email_body.($aSettings['info_dsgvo_in_mail'] ? '' :$sDataProtection);
                        $aAttachment = null;
                        $aDebug      = $aMailValues;
/*
                        [
                            'SERVER_EMAIL' => SERVER_EMAIL,
                            'email_from' => $email_from,
                            'email_to' => $email_to,
                            'email_subject' => $email_subject,
                            'email_body' => $sEmailBody,
                            'email_fromname' => $email_fromname,
                            'email_toname' => $email_toname,
                            'mail_replyto' => $mail_replyto,
                            'mail_replyName' => $mail_replyName,
                            ];
*/

/* -------------------------------------------------------------------------- */
    if (\is_readable($sAddonPath.'sendMails.php')){require $sAddonPath.'sendMails.php';}
/* -------------------------------------------------------------------------- */

                        if (($success==true)){
                            // Write submission to database only if form was send
                            if (isset($oApp) && ($oApp->is_authenticated() && ($oApp->getUserId() > 0))) {
                                $submitted_by = $oApp->getUserId();
                            } else {
                                $submitted_by = 0;
                            }
                            $email_text = '';
                            $now = \time();
                            $email_body = \htmlspecialchars($oApp->add_slashes($email_body));
                            if ($aSettings['stored_submissions'] > 0){
                                $email_text = $email_body;
                            }
                            $sql  = '
                            INSERT INTO `'.$oReg->TablePrefix.'mod_form_submissions` SET
                            `page_id`        = '.(int)$oApp->page_id.',
                            `section_id`     = '.(int)$section_id.',
                            `submitted_when` = '.(int)$now.',
                            `submitted_by`   = '.(int)$submitted_by.',
                            `body`           = \''.$oDb->escapeString($email_text).'\',
                            `form_ip`        = \''.$sNormFormIp.'\'
                             ';
                            if ($oRes = $oDb->query($sql)){
                                // Get the SubmissionId
                                $iSubmissionId = ($oDb->LastInsertId);
                                if (!$oDb->is_error()) {
                                    $success = true;
                                }
                                // check only if days to delete sunmission is 0 days
                                if (((int)$aSettings['delete_submissions'] === 0)){
                                    // Make sure submissions table isn't too full
                                    $sSql = '
                                    SELECT `submission_id`
                                    FROM `'.$oReg->TablePrefix.'mod_form_submissions`
                                    WHERE `section_id` = '.(int)$section_id.'
                                    ORDER BY `submitted_when`
                                    ';
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sSql));
                                    $query_submissions = $oDb->query($sSql);
                                    $num_submissions = $query_submissions->numRows();
                                    if (($max_submissions > 0) && ($num_submissions > $max_submissions)){
                                        // Remove excess submission
                                        $num_to_remove = $num_submissions-$max_submissions;
                                        while(($aDelete = $query_submissions->fetchRow(MYSQLI_ASSOC))){
                                            if ($num_to_remove > 0){
                                                $submission_id = $aDelete['submission_id'];
                                                $sDeleteSql = '
                                                DELETE
                                                FROM `'.$oReg->TablePrefix.'mod_form_submissions`
                                                WHERE `submission_id` = '.(int)$submission_id.'
                                                ';
                                                $oDb->query($sDeleteSql);
                                                $num_to_remove = $num_to_remove-1;
                                            }
                                        }
                                    } // $num_submissions to delete
                                } // do not delete submission if delete days greater than 0
                            }  // insert new submission
                        } // $success
                     }
                 } // end how many times form has been submitted in last hour
            } // end no captcha error
        }  // email_error
