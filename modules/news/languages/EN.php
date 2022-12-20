<?php
/**
 *
 * @category        modules
 * @package         news
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2019, Website BakerOrg. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: EN.php 370 2019-06-11 17:55:53Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/news/languages/EN.php $
 * @lastmodified    $Date: 2019-06-11 19:55:53 +0200 (Di, 11. Jun 2019) $
 *
 */

//Modul Description
$module_description = 'This page type is designed for making a news page.';

//Variables for the backend
$HEADING['GENERAL_SETTINGS'] = 'General Settings';
$HEADING['GENERAL_LAYOUTS'] = 'Change or add Frontend and Comment Template';
$HEADING['GENERAL_COMMENTS'] = 'Comment Settings';
$HEADING['LAYOUT_COMMENTS'] = 'Comment Template';
$HEADING['CHOOSE_LAYOUTS'] = 'Select template';

$MOD_NEWS['SETTINGS'] = 'News Settings';

//Variables for the frontend
$MOD_NEWS['TEXT_READ_MORE'] = 'Read More';
$MOD_NEWS['TEXT_POSTED_BY'] = 'Posted by';
$MOD_NEWS['TEXT_ON'] = 'on';
$MOD_NEWS['TEXT_ORDER_ASC']    = 'Sorted in Ascending Order';
$MOD_NEWS['TEXT_ORDER_DESC']   = 'Sorted in Descending Order';
$MOD_NEWS['TEXT_ORDER']        = 'Drag and Drop re-ordering is only active when Order by Field "Position" and "Sorted in Ascending Order" are selected in News - Settings';
$MOD_NEWS['TEXT_ORDER_TO']     = 'Sort Order';
$MOD_NEWS['TEXT_ORDER_FROM']   = 'Order by Field';
$MOD_NEWS['TEXT_POSITION']     = 'Position';
$MOD_NEWS['TEXT_PUBLISHED_WHEN']  = 'Publishing';
$MOD_NEWS['TEXT_LAST_CHANGED'] = 'Last changed';
$MOD_NEWS['TEXT_title'] = 'Title';
$MOD_NEWS['TEXT_AT'] = 'at';
$MOD_NEWS['TEXT_BACK'] = 'Back to Overview';
$MOD_NEWS['TEXT_COMMENTS'] = 'Comments';
$MOD_NEWS['TEXT_COMMENT'] = 'Comment';
$MOD_NEWS['TEXT_COMMENTING'] = 'Commenting';
$MOD_NEWS['TEXT_ADD_COMMENT'] = 'Add Comment';
$MOD_NEWS['TEXT_ADD_POST'] = 'Add Post';
$MOD_NEWS['TEXT_ADD_GROUP'] = 'Add Group';
$MOD_NEWS['TEXT_DELETE_POST'] = 'Delete contribution %s?';
$MOD_NEWS['TEXT_DELETE_GROUP'] = 'Delete group %s?';

$MOD_NEWS['TEXT_BY'] = 'By';
$MOD_NEWS['PAGE_NOT_FOUND'] = 'Page not found';
$MOD_NEWS['NO_COMMENT_FOUND'] = 'No comment found';
$MOD_NEWS['NO_POSTS_FOUND'] = 'No post found';
$MOD_NEWS['NO_GROUP_FOUND'] = 'No group found';
$MOD_NEWS['SUCCESS_POST'] = 'Post "%s" saved successfully!';
$MOD_NEWS['SUCCESS_GROUP'] = 'Group "%s" saved successfully!';
$MOD_NEWS['SUCCESS_COMMENT'] = 'Comment saved successfully!';
$MOD_NEWS['DELETED_POST'] = 'Post %s was successfully deleted!';
$MOD_NEWS['NO_DELETED_POST'] = 'Post file %s could not be deleted!';
$MOD_NEWS['DELETED_GROUP'] = 'Gruppe %s was successfully deleted!';
$MOD_NEWS['DELETED_COMMENT'] = 'Kommentar was successfully deleted!';
$MOD_NEWS['LOAD_LAYOUT'] = 'Load Default Layout';
$MOD_NEWS['LOAD'] = 'Load';
$MOD_NEWS['MODERATED_COMMENT'] = 'Commenting Moderated';
$MOD_NEWS['REQUIRED_FIELDS'] = 'You must enter details for the following fields';
$MOD_NEWS['TEXT_MODIFY_POST'] = 'Modify or Delete Post';
$MOD_NEWS['TEXT_MODIFY_GROUP'] = 'Modify or Delete Group';
$MOD_NEWS['DSGVO'] = 'Missing confirmation and agreement to the Data Protection Directive';

$MESSAGE['INCORRECT_CAPTCHA']  = 'The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email the <a href="mailto:{{WEBMASTER_EMAIL}}">webmaster</a>';

$TEXT['UNKNOWN'] = 'Guest';
$TEXT['DSGVO'] = 'Data Protection Directive';
$TEXT['DSGVO_LINK'] = 'Data Protection Url';
$TEXT['MODIFIED'] = 'modified';
$TEXT['LAYOUT'] = 'Template';


$NEWS_MESSAGE = array (
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
    'IMAGE' => 'Output the <img>-tag for the image only.',
    'IMAGE_IFRAME' => 'Output only an <img>-tag.',
    'INPUT' => 'Output only the input-field, you can add style="...;" or class="..." Attribute',
    'TEXT' => 'Text Output',
    'ADD_LAYOUT' => 'Enter name or empty field creates a new unique name from "%2$s" to "%1$s".',
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
    'DSGVO' => 'I confirme, that i have read and agree to the <a href="%s" target="_blank" rel="noopener">Data Protection Directive</a>  by submitting the form',
    );
