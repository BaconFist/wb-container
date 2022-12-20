<?php
/**
 *
 * @category        module
 * @package         Form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: EN.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/languages/EN.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 * @description
 */
/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
/* -------------------------------------------------------- */

//Modul Description
$module_description = 'This module allows you to create customized online forms, such as a feedback form. Thank-you to Rudolph Lartey who help enhance this module, providing code for extra field types, etc.';

//Variablen for backend Texte
$MOD_FORM['SETTINGS'] = 'Form settings';
$MOD_FORM['SAVE_SETTINGS'] = 'Save form settings';
$MOD_FORM['CONFIRM'] = 'Confirmation';
$MOD_FORM['SUBMIT_FORM'] = 'Submit';
$MOD_FORM['EMAIL_SUBJECT'] = 'You have received a message about {{WEBSITE_TITLE}}';
$MOD_FORM['SUCCESS_EMAIL_SUBJECT'] = 'You have sent a forumlar via {{WEBSITE_TITLE}}';
$MOD_FORM['REPLACE_EMAIL_SUBJECT'] = 'Replace subject line with ';

$MOD_FORM['SUCCESS_EMAIL_TEXT']    = 'Thank you for sending your message to {{WEBSITE_TITLE}}! '.PHP_EOL;
$MOD_FORM['SUCCESS_EMAIL_TEXT']   .= 'We will contact you as soon as possible';

$MOD_FORM['SUCCESS_EMAIL_TEXT_GENERATED'] = "\n"
."**************************************************************************************\n"
."This is an automatically generated e-mail. The sender address of this e-mail\n"
."is furnished only for dispatch, not to receive messages!\n"
."If you have received this e-mail by mistake, please contact us and delete this message\n"
.""
."**************************************************************************************\n";

$MOD_FORM['FROM'] = 'Sender';
$MOD_FORM['TO']   = 'Recipient';

$MOD_FORM['EXCESS_SUBMISSIONS'] = 'Sorry, this form has exceeded the maximum hourly submissions. Please retry later.';
$MOD_FORM['INCORRECT_CAPTCHA']  = 'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email the <a href="mailto:{{WEBMASTER_EMAIL}}">webmaster</a>';

$MOD_FORM['PRINT']     = 'SPAM protection! It is not possible to send a confirmation to a non-verified e-mail address! ';
$MOD_FORM['SPAM_PROTECT']   = 'SPAM-protection!!';
$MOD_FORM['SUBS_DELETED']   = 'Submissions older than %d days deleted';
$MOD_FORM['DELETE_SUBS_AFTER']   = '%d entries older than %d day(s) successfully deleted';
$MOD_FORM['DAY_CONUTER']   = 'Calculate number of days, only needed once';
$MOD_FORM['MINUTES']  = 'Minute(s)';
$MOD_FORM['DAYS']  = 'Day(s)';
$TEXT['MAX_SUBMISSIONS_PER_HOUR']= 'Max. entries';

$MOD_FORM['DAYS'] = 'Day(s) ';
$MOD_FORM['PRINT']     = 'It is not possible to send a confirmation to a non-verified e-mail addresses! ';
$MOD_FORM['PRINT']    .= 'Please print this page, if a copy is desired for your records.';

$MOD_FORM['RECIPIENT'] = 'E-mail confirmations will only be sent to registered users!';
$MOD_FORM['LOAD_LAYOUT'] = 'Load Default Layout';
$MOD_FORM['IMPORT_LAYOUT'] = 'Layout title and description are only added as xml file! To load the layout selected in the selection box, confirm the Import button. Without a selection, the currently loaded layout is exported as an xml file!';
$MOD_FORM['CAPTCHA_PLACEHOLDER'] = 'New placeholder for embedding captcha in the layout';
$MOD_FORM['DSGVO_PLACEHOLDER'] = 'New placeholder for embedding data protection in the layout';
$MOD_FORM['REQUIRED_FIELDS'] = 'You must enter details for the following fields';
$MOD_FORM['ERROR']           = 'E-Mail could not send!!';
$MOD_FORM['SPAM']  = 'Caution! Replying to an unchecked form submit address can be perceived as spamming and carry the risk of receiving a cease-and-desist warning! ';
$MOD_FORM['DSGVO'] = 'Missing confirmation and agreement to the Data Protection Directive';

$MOD_FORM['DSGVO_ENABLED'] = 'Consent to the Privacy Policy %s';
$MOD_FORM['DSGVO_DISABLED'] = 'Lack of consent to the Privacy Policye %s';
$MOD_FORM['DSGVO_NOT_INUSE'] = 'Privacy policy consent is disabled %s';

$MOD_FORM['EDIT_TPL'] = 'Edit Successful page';
$MOD_FORM['REPLY_TO'] = 'Reply Address';

$MESSAGE['INCORRECT_CAPTCHA']  = 'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email the <a href="mailto:{{WEBMASTER_EMAIL}}">webmaster</a>';
$MESSAGE['FIELD_DELETED'] = 'Field <b>[%s]</b> deleted successfully';

$MOD_FORM['CONFIRM'] = 'Confirmation';
$MOD_FORM['WARNING'] = 'Important note';
$MOD_FORM['EMAIL_RECIPIENT'] = 'E-mail recipient';
$MOD_FORM['DIVIDER_SEPERATOR'] = 'You have the option to enter a separator between label and content in the form as well as a line break, separator can be any character such as a colon. For the line break, enter \n. Then the label is above the content, otherwise it is in a row. This only applies to the e-mail content!';
$MOD_FORM['PLACEHOLDER'] = 'Insert title in input fields as placeholder';
$MOD_FORM['REQUIRED'] = 'Check required input before submitting';
$MOD_FORM['FIELD_EXPORT'] = 'Export form fields for this section. Select a file from the selection above. Without selection, the fields are exported from the database. Overwriting the xml file is not possible. If the file names match, a copy is always created!';
$MOD_FORM['CSS_REQUIRED'] = 'Do not load Frontend.css! (Don\'t forget to add stylesheets to your frontend template styles or in a frontendUser.css)';

$TEXT['GUEST']       = 'Guest';
$TEXT['UNKNOWN']     = 'unknown';
$TEXT['PRINT_PAGE']  = 'Print page';
$TEXT['REQUIRED_JS'] = 'Required Javascript';
$TEXT['SUBMISSIONS_PERPAGE'] = 'Display of entries';
$TEXT['PERPAGE'] = ' per page';

$TEXT['ADMIN'] = 'Admin';
$MENU['USERS'] = 'Users';
$TEXT['BACKUP'] = 'Frontend Layouts Export/Import';
$TEXT['BACKUP_FILEDS'] = 'Fields Export/Import';
$TEXT['EXPORT'] = 'Export';
$TEXT['IMPORT'] = 'Import';
$TEXT['FIELD_EXPORT'] = 'Export Fields';
$TEXT['FIELD_IMPORT'] = 'Import Fields';
$TEXT['FORM_NONE_FOUND'] = 'No Entries found';
$TEXT['MODIFY_FIELD'] = 'Modify Field';
$TEXT['ADD_FIELD'] = 'Add Field';
$TEXT['MODIFY_DELETE_FIELD'] = 'Modify or delete Field';
$TEXT['ADD_GROUP'] = 'Add Group';
$TEXT['MODIFY_DELETE_GROUP'] = 'Modify or delete Field';
$TEXT['DSGVO'] = 'Data Protection Directive';
$TEXT['DSGVO_LINK'] = 'Data Protection Url';
$TEXT['EMPTY'] = '&#160;';
$TEXT['EXTRA'] = 'Extra Fields';
$TEXT['USE_CAPTCHA_AUTH'] = 'No captcha when user logs in';
$TEXT['INFO_DSGVO_IN_MAIL'] = 'No GDPR reference in e-mail confirmation.';
$TEXT['GO_TO']  = 'Go to';
$TEXT['GO_TOP'] = 'Goto Top';
$TEXT['PREVENT_USER_CONFIRMATION'] = 'No e-mail confirmation to form sender';
$TEXT['USER_CONFIRMATION'] = 'Sender Confirmation Form';
$TEXT['SUBMESSAGE_FILE'] = 'Edit %s ';
$TEXT['SUBJECT'] = 'Subject';
$TEXT['BACK_TO_FORM'] = 'Back to Form';
$TEXT['MESSAGE'] = 'Message';
$TEXT['EMAIL_RECIPIENT'] = 'Form message to recipient';
$TEXT['EMAIL_SENDER'] = 'Form confirmation to sender';
$TEXT['DIVIDER'] = 'Separator';
$TEXT['PLACEHOLDER'] = 'Placeholder';
$TEXT['FORM_REQUIRED'] = 'Required';
$TEXT['XML_FILES'] = 'XML File';
$TEXT['NEW_XML_FILE'] = 'Filename';
$TEXT['FORM_FRONTEND_CSS'] = 'Frontend Styles';

$FORM_MESSAGE = [
    'ARCHIVE_DELETED' => 'Zip(s) deleted successfully.',
    'ARCHIVE_NOT_DELETED' => 'Cannot delete the selected Zip(s).',
    'CONFIRM_FIELD_DELETING' => 'Are you sure you want to delete the selected field?',
    'DELETED' => 'Field deleted successfully.',
    'ALL_DELETED' => 'All Fields deleted successfully.',
    'DELETE_FIELDS' => 'Delete Fields',
    'MISSING_UNMARKED_ARCHIVE_FILES' => 'No Fields-File selected to import.',
    'GENERIC_MISSING_ARCHIVE_FILE' => 'No Zip-File selected to delete!',
    'GENERIC_MISSING_TITLE' => 'Insert a Field name.',
    'GENERIC_LOCAL_DOWNLOAD' => 'Download Zip',
    'GENERIC_LOCAL_UPLOAD' => 'Load and restore a locale Zip',
    'DELETE_LAYOUT' => 'Remove all Form Fields.',
    'CONFIRM_DELETE_LAYOUT' => 'Are you sure you want to delete all fields?',
    'CAPTCHA_STYLE' => 'Captcha Style Attribute',
    'CAPTCHA_ACTION' => 'Captcha Output',
    'ALL' => 'Output a div container with varying columns (default).',
    'IMAGE' => 'Output the &lt;img&gt;-tag for the image only.',
    'IMAGE_IFRAME' => 'Output only an <img>-tag.',
    'INPUT' => 'Output only the input-field, you can add style="...;" or class="..." Attribute',
    'TEXT' => 'Text Output',
    'SUBJECT' => 'Subject output',
    'FILE_TITLE_VALUE' => 'Missing the file name!',
    'LSETTINGS' => 'Layout Settings',
    'TEXT_SELECT_BOX' => 'Choose Layout',
    'LAYOUT_TITLE' => 'Layout file name',
    'LAYOUT_TITLE_NEW' => 'New Layout file name',
    'LAYOUT_DESCRIPTION' => 'Layout Description',
    'LAYOUT_SETTINGS' => 'Layout Settings',
    'LAYOUT' => 'Layout Output',
    'DOWNLOAD' => 'Download',
    'IMPORT_DELETED' => '%s has been successfully deleted',
    'IMPORT_SUCCESS' => '%s has been successfully imported',
    'EXPORT_SUCCESS' => '%s has been successfully exported',
    'FIELD_SUCCESS' => "The field <b>%s</b> was successfully saved\n",

    'ADDED_SUCCESS' => "%s was successfully created\n",
    'MODIFIED_SUCCESS' => "%s was successfully edited\n",
    'MODIFIED_FAILED' => "%s could not be updated\n",
    'GENERIC_FILL_TITLE' => "Missing description title\n",
    'GENERIC_FILL_TYPE' => "Please select the type of field\n",
    'EMAIL_TAKEN' => "The selected email type is already in use\nOnly one email address is allowed in the form\n",

    'DSGVO' => 'I confirme, that i have read and agree to the <a href="%s" target="_blank" rel="noopener">Data Protection Directive</a>  by submitting the form',
    'NO_DSGVO' => 'I confirme, that i have read and agree to the Data Protection Directive by submitting the form',
    'PAGE_RELOADED' => "<b>Expired session!!</b> After login or logout first reload page or press F5 ",
    ];

$FORM_HELP['IMPORT_FIELDS'] = 'Import form fields stored in an XML file to insert them into a blank form. When you select a file, you can save it to your local computer or delete it when it is not in use!';
$FORM_HELP['ADD_FIEDS'] = 'Import form fields and add to an empty form.';
$FORM_HELP['GDPR'] = '';

$MESSAGE['UNKNOW_UPLOAD_ERROR'] = "Unknown upload error";
$MESSAGE['UPLOAD_ERR_CANT_WRITE'] = "Failed to write file to disk";
$MESSAGE['UPLOAD_ERR_CANT_WRITE_FOLDER'] = "Failed to write file to %s";
$MESSAGE['UPLOAD_ERR_EXTENSION'] = "File upload stopped by extension";
$MESSAGE['UPLOAD_ERR_FORM_SIZE'] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
$MESSAGE['UPLOAD_ERR_INI_SIZE'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini of %s";
$MESSAGE['UPLOAD_ERR_NO_FILE'] = "No file was uploaded";
$MESSAGE['UPLOAD_ERR_FILE_EXISTS'] = '[%04d] Following File(s) already exists. %s '."\n".'Activate Checkbox (overwrite existing files)'."\n\n";
$MESSAGE['UPLOAD_ERR_NO_TMP_DIR'] = "Missing a temporary folder";
$MESSAGE['UPLOAD_ERR_OK'] = "File was successfully uploaded";
$MESSAGE['UPLOAD_ERR_PARTIAL'] = "The uploaded file was only partially uploaded";
$MESSAGE['UPLOAD_ERR_POST_MAX_SIZE'] = 'One or more of the uploaded file exceeds the post_max_size directive in php.ini of %s';

$MESSAGE['UPLOAD_ERR_PHPINI_SIZE'] =
'The uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini. This is an error that occurs on your WebsiteBaker installation when you upload a file that exceeds the limitations set by your webserver. Ask your provider to increase the limitations. Your filesize ist %s and  upload_max_filesize %s';

$errorTypes = [
    1 => 'The uploaded file %s exceeds the upload_max_filesize directive %s in php.ini.',
    2 => 'The uploaded file %s exceeds the MAX_FILE_SIZE directive %s that was specified in the HTML form.',
    3 => 'The uploaded file %s was only partially uploaded. %s',
//    4 => 'No file was uploaded. %s %s',
    6 => 'Missing a temporary folder. %s %s',
    7 => 'Failed to write file %s to disk. %s',
    8 => 'A PHP extension stopped the file %s upload. %s'
];

