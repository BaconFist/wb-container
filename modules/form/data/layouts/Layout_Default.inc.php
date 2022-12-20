<?php
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
/* -------------------------------------------------------- */

if (isset($bDivStyle) && $bDivStyle)
{
$sLayoutTitle = 'Layout_Default_Table';
$sLayoutDescription = 'The ancient layout, with new Captcha call as placeholder. To use only with div fields';
// begin div style captcha and dsgvo Link --------------------------------------------
    $field_loop = '
    <div>
        <div class="frm-field_title">{TITLE}{REQUIRED}</div>
        <div>{FIELD}</div>
    </div>
    ';

    $extra  = '
    <div>{CALL_DSGVO_LINK}</div>
    <div class="frm-field_title">{TEXT_VERIFICATION}{REQUIRED}</div>
    <div>{CALL_CAPTCHA}</div>
';
    $sInsertCaptcha = '
     <div>{CALL_CAPTCHA}</div>
';
    $sInsertDSGVO = '
     <div>{CALL_DSGVO_LINK}</div>
';
// end div style captcha and dsgvo Link ----------------------------------------------
}
else
{
$sLayoutTitle = 'HTML Less Simple Layout';
$sLayoutDescription = 'Simple contact form without HTML elements';
// begin simple insert for captcha and dsgvo link ------------------------------------
    $field_loop = '
{FIELD}
    ';
    $extra  = '
{CALL_DSGVO_LINK}
{CALL_CAPTCHA}
';

    $sInsertCaptcha = '{CALL_CAPTCHA}
';

    $sInsertDSGVO = '
{CALL_DSGVO_LINK}
';
// end simple insert for captcha and dsgvo link --------------------------------------
}
// begin default settings for header and footer --------------------------------------
    $header     = '
    <div class="frm frm-field_table">
    ';

    $footer = '
     <div class="w3-margin"></div>
     <div>
        <input class="frm-btn" type="submit" name="submit" value="{SUBMIT_FORM}" />
     </div>
</div>
<div class="w3-margin"></div>
';
// end default settings for header and footer ----------------------------------------


