<?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 7.4.x and higher
 * @version         $Id: EN.php 93 2018-09-20 18:09:30Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/output_filter/themes/default/languages/EN.php $
 * @lastmodified    $Date: 2018-09-20 20:09:30 +0200 (Do, 20. Sep 2018) $
 *
 */

// English module description
$module_description  = 'This module allows filtering of content before it is displayed in the frontend area. Supports filtering of email addresses in mailto links and text.';
$module_description .= '<span id="help-modfiles" style="visibility:hidden;"> <b>Attention!</b> RegisterModfiles is activated, some filters can therefore not be changed. To be able to set all filters individually, RegisterModfiles must be deactivated.</span>';
// Headings and text output
$MOD_MAIL_FILTER['HEADING']    = 'Options: Output Filtering';
$MOD_MAIL_FILTER['HOWTO']      = 'Output filtering can be configured via the following options.<b>Tip: </b>Mailto links can be encrypted with a Javascript routine!';
$MOD_MAIL_FILTER['W3CSS']      = 'With the following options the filter W3Css can be configured. <b>The external w3.css can be loaded permanently with this setting!</b>.';
$MOD_MAIL_FILTER['WARNING']            = 'Enter your own filters in the order in which they should be processed. This private list is optional and is not overwritten during the upgrade and is appended to the list of default filters. Note: In case of an empty private filter list, the default filters are retained.';
// Text from form elements
$MOD_MAIL_FILTER['SET_ACTIVE']          = 'Activate/deactivate filter';
$MOD_MAIL_FILTER['CLICK_HELP']          = 'Click on label for help';
$MOD_MAIL_FILTER['BASIC_CONF']          = 'Default settings';
$MOD_MAIL_FILTER['SYS_REL']             = 'Frontend output with relative urls';
$MOD_MAIL_FILTER['opf']                 = 'Output filter Dashboard';
$MOD_MAIL_FILTER['EMAIL_FILTER']        = 'Filter email addresses in the text';
$MOD_MAIL_FILTER['MAILTO_FILTER']       = 'Filter email addresses in mailto';
$MOD_MAIL_FILTER['ENABLED']             = 'Enabled';
$MOD_MAIL_FILTER['DISABLED']            = 'Disabled';
$MOD_MAIL_FILTER['LOAD_W3CSS']          = 'Load W3CSS permanently';
$MOD_MAIL_FILTER['REPLACEMENT_CONF']    = 'Email replacements';
$MOD_MAIL_FILTER['AT_REPLACEMENT']      = 'Replace "@" with';
$MOD_MAIL_FILTER['DOT_REPLACEMENT']     = 'Replace "." with';
$MOD_MAIL_FILTER['ACTIVE_MODFILES'] = "Attention! RegisterModfiles is activated, therefore some filters cannot be changed. To be able to set all filters individually, RegisterModfiles must be deactivated.";
$TEXT['SAVE_LIST']  = 'Save list';
$TEXT['ADD_LIST']   = 'Create List';
$TEXT['EMPTY_LIST'] = 'Empty list';
$output_filter_help = [
                'Droplets'=>'Executes Droplets',
                'Email'=>'Makes e-mail links more difficult to read and spy out.',
                'SnippetCss'=>'Loads external snippet/tool style files with <code>register_frontend_modfiles("css")</code> into the HEAD',
                'FrontendCss'=>'Loads external page module style files with <code>register_frontend_modfiles("css")</code> into the HEAD',
                'ScriptVars'=>'Sets Javascript variables for further processing in the HEAD',
                'FrontendJs'=>'Loads external page module script files into HEAD',
                'SnippetBodyJs'=>'Loads external snippet/tool script files with <code>register_frontend_modfiles_body("js")</code> before BODY end.<br />'
                                . 'It is not necessary to switch on the filter RegisterModFiles',
                'LoadOnFly'=>'Loads DomReady and LoadOnFLy script into HEAD for dynamic loading of external styles',
                'Jquery'=>'Enables the integration of jQuery<br />'
                        . '<ol start="1">'
                        . '<li>1) RegisterModFiles turned on old procedure</li>'
                        . '<ul style="padding-left: 1.525em;">'
                        . '<li>Loading jQuery into the HEAD with <code>register_frontend_modfiles("jquery")</code></li>'
                        . '<li>Loading jQuery before BODY end with <code>register_frontend_modfiles_body("jquery")</code></li>'
                        . '<li>Setting the jQuery checkbox is not required</li>'
                        . '</ul>'
                        . '<li>2) RegisterModFiles switched off new procedure</li>'
                        . '<ul style="padding-left: 1.525em;">'
                        . '<li>load jQuery into HEAD with </li>'
                        . '<li><code>register_frontend_modfiles("css")</code></li>'
                        . '<li>Setting the jQuery checkbox is required</li>'
                        . '</ul>'
                        . '</ol>'
                        . '',
                'JqueryUI'=>'Activates jQueryUi for jQuery',
                'SnippetJs'=>'Loads external snippet/tool script files into HEAD',
                'FrontendBodyJs'=>'Loads external page module script files with <code>register_frontend_modfiles_body("js")</code> before BODY end.<br />'
                                . 'It is not necessary to enable the RegisterModFiles',
                'OpF'=>'Output filter Dashboard',
                'RegisterModFiles'=>'<p>To support older templates and backward compatibility<br />'
                                  . 'the <code>register_frontend_modfiles...functions...</code> </p>'
                                  . '<ol>'
                                  . '<li style="font-weight:bold;">1) RegisterModFiles turned on old procedure</li>'
                                  . '<ul style="padding-left: 1.525em;">'
                                  . 'Setting the checkboxes is not required and will not affect the inclusion<br />'
                                  . '<li>Load the external styles with <code>register_frontend_modfiles("css")</code>.'
                                  . '<li>Loading the external scripts with <code>register_frontend_modfiles("js")</code>.'
                                  . '<li>Loading jQuery with <code>register_frontend_modfiles("jquery")</code>.'
                                  . '<li>additionally load <code>ScriptVar,domReady.js and LoadOnFly.js</code>.'
                                  . '<li>&nbsp;</li>'
                                  . '<li>'
                                  . 'Loading the front_body.js scripts before BODY end <br />'
                                  . '<code>register_frontend_modfiles_body("jquery")</code> and<br />'
                                  . '<code>register_frontend_modfiles_body("js")</code><br />'
                                  . '</li>'
                                  . '<li>&nbsp;</li>'
                                  . '</ul>'
                                  . '<li style="font-weight:bold;">2) RegisterModFiles switched off new procedure</li>'
                                  . '<ul style="padding-left: 1.525em;">'
                                  . '<li>Loads all external styles/scripts into the HEAD via <code>register_frontend_modfiles("css")</code></li>'
                                  . '<li>By setting the checkboxes you determine yourself what should be included</li>'
                                  . '</ul>'
                                  . '</ol>'
                ,'at_replacement'=>'',
                'dot_replacement'=>'',
                'RelUrl'=>'Changes absolute to relative urls',
                'OutputFilterMode'=>'',
                'ReplaceSysvar'=>'',
                'WbLink'=>'Changes wblink placeholder to absolute urls',
                'email_filter'=>'',
                'mailto_filter'=>'',
                'CssToHead'=> 'Searches the content for style blocks and link CSS tags'
                            . ' and moves it into the HEAD area!',
                'W3Css' => 'Valid for non-W3Css compliant templates, for modules styled with W3Css<br />'
                              . 'Searches the content for the first W3Css class selector'
                              . ' and loads the required external w3.css into the HEAD area!<br />'
                              . '</b>If the filter is activated, If the parameter is activated, it can influence the frontend output!</b>',
        ];
$MOD_MAIL_FILTER['HELP_MISSING'] = 'There is no help for this filter yet';
/* all shorturl and htaccess language vars */
$MOD_MAIL_FILTER['LOAD_SHORT_URL']      = 'Enable ShortUrl';
$MOD_MAIL_FILTER['WHITHOUT_HTACCESS']   = '(without htaccess)';
$MOD_MAIL_FILTER['DELETE_SHORT_URL']    = 'Disable ShortUrl';
$MOD_MAIL_FILTER['ADD_EDIT_URL']        = 'Edit new .htaccess';
$MOD_MAIL_FILTER['LOAD_EDIT_URL']       = 'Edit existing .htaccess';
$MOD_MAIL_FILTER['DELETE_EDIT_URL']     = 'Delete .htaccess';
$TEXT['DELETE_HTACCESS'] = 'Delete .htaccess';
$TEXT['RESET_HTACCESS'] = 'discard input';
$TEXT['IMPORT_HTACCESS'] = 'Importing';
$TEXT['PLEASE_SELECT_HTACCESS'] = 'Please select text block';
$MOD_MAIL_FILTER['SHORT_URL']  = 'The short url can be configured using the following options. Activate checkbox Shorturl creates the <b>short.php in the application path</b>. Disabling the CheckBox removes the short.php again.!';
$MOD_MAIL_FILTER['HTACCESS_URL'] = 'The .htaccess can be configured using the following options. The .htaccess will have the ShortUrl code section added and created if not present. An existing .htaccess can be edited and customized or deleted!';
//$MOD_MAIL_FILTER['EDIT_HTACCESS_FILE'] = 'Edit .htacces file';
$MOD_MAIL_FILTER['HTACCES'] = ' Create, delete and edit .htaccess file';

$MOD_MAIL_FILTER['REDIRECT_CODE'] =
'RewriteEngine On
## include redirect to https ------------------------------
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
RewriteCond %{HTTPS} !on
RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]';

$MOD_MAIL_FILTER['HTACCESS_CODE'] =
'RewriteEngine On
## BEGIN SHORTURL -----------------------------------------
## If old urls are called directly - redirect to short url version
RewriteCond %{REQUEST_URI} !/pages/intro.php
RewriteCond %{REQUEST_URI} /pages
RewriteRule ^pages/(.*).php$ /$1/ [R=301,L]

## Send the request to the short.php for processing
RewriteCond %{REQUEST_URI} !^/(pages|account|admin|framework|include|languages|media|modules|search|temp|templates|var|vendor/.*)$
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([\/\sa-zA-Z0-9._-]+)$ /short.php?_wb=$1 [QSA,L]
## END SHORTURL -------------------------------------------
';
