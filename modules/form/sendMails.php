<?php



/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

/* -------------------------------------------------------------------------- */
// send E-Mails function mail(
// 1                   $sFromAddress,
// 2                   $toAddress,
// 3                   $sSubject,
// 4                   $sMessage,
// 5                   $sFromname='',
// 6                   $toName='',
// 7                   $sReplyToAddress='',
// 8                   $sReplyToName='',
// 9                   $sMessagePath='',
//10                   $aAttachment=null
/*
        $aParameters = [
            'setFromAdress' => $sFromAddress,
            'toAddress' => $toAddress,
            'Subject' => $email_subject,
            'msgHTML' => $sMessage,
            'setFromName' => $sFromname,
            'AddAddress' => $toName,
            'addReplyToAdress' => $success_email_to,
            'addReplyToName' => $email_toname,
            'msgHTML' => $sMessagePath,
        ];
*/
/* -------------------------------------------------------------------------- */
                        $success = false;
//                      send form to admin, can replyto to given e-mail adress
                        if (!empty($email_from)) {
                            if (!empty($mail_replyto)) {
                                $success = $oApp->mail(
                                    //$success_email_to,
                                    $email_to,
                                    $email_to,
                                    $email_subject,
                                    $sEmailBody,
                                    $email_fromname,
                                    $email_toname,
                                    $mail_replyto,  // replyto
                                    $mail_replyName,
                                    '',
                                    $aAttachment,
                                    sprintf("%d %s",__LINE__,'sendMail to admin')
                                );
                            } else {
                                $success = $oApp->mail(
                                    $email_to,
                                    $email_to,
                                    $email_subject,
                                    $sEmailBody,
                                    $email_fromname,
                                    $email_toname,
//                                    '',
//                                    '',
//                                    $success_email_to, // replyto
//                                    $success_email_fromname,
                                    $email_to,
                                    $email_toname,
                                    '',
                                    $aAttachment,
                                    sprintf("%d %s",__LINE__,'sendMail to admin')
                                );
                            }
                        }
/* -------------------------------------------------------------------------- */
//
/* -------------------------------------------------------------------------- */
// send only to user if is_authenticated and not blocked in form settings
                        if ($success && $aMailValues['is_authenticated'] && !$aSettings['prevent_user_confirmation']){
                            $success = false;
                            if (!empty($success_email_to)){
                                if(!empty($success_email_from)){
/* */
                                $aEmail = $emailUser();
                                if (is_array($aEmail) && (SERVER_EMAIL==$email_to)){
                                    $email_to = $aEmail['email'];
                                    $email_toname = $aEmail['display_name'];
                                }
                                // send confirmation to authenticated user -mail
                                $success = $oApp->mail(
                                    $success_email_from,
                                    $success_email_to,
                                    $success_email_subject,
                                    ($success_email_text)."\n".($email_body).$oTrans->MOD_FORM_SUCCESS_EMAIL_TEXT_GENERATED,
                                    $success_email_fromname,
                                    $success_email_toName,
                                    '', // no replyto
                                    '',
//                                        $email_to,
//                                        $email_toname,
                                        '',
                                    $aAttachment,
                                    sprintf("%d %s",__LINE__,'ConfirmMail to Recipient')
                                    );
                                }
                            }
                        }

/* -------------------------------------------------------------------------- */
//
/* -------------------------------------------------------------------------- */

