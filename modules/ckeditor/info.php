<?php
/**
 *
 * @category       modules
 * @package        ckeditor
 * @authors        WebsiteBaker Project, Michael Tenschert, Dietrich Roland Pehlke, Dietmar Wöllbrink
 * @copyright      WebsiteBaker Org. e.V.
 * @link           https://websitebaker.org/
 * @license        https://www.gnu.org/licenses/gpl.html
 * @platform       WebsiteBaker 2.13.2
 * @requirements   PHP 7.4.x and higher
 * @version        $Id: info.php 276 2019-03-22 00:06:26Z Luisehahne $
 * @filesource     $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/ckeditor/info.php $
 *
 *
 *
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */

$module_directory   = 'ckeditor';
$module_name        = 'CKEditor v4.19.0.0';
$module_function    = 'wysiwyg';
$module_version     = '4.19.0.0';
$module_platform    = '2.13.0';
$module_author      = 'Michael Tenschert, Dietrich Roland Pehlke, erpe, WebBird, Marmot, Luisehahne';
$module_license     = '<a href="https://www.gnu.org/licenses/lgpl.html">LGPL</a>';
$module_description = 'includes CKEditor 4.19.0 Standard, CKE allows editing content and can be integrated in frontend and backend modules.';

/*
CHANGELOG

## CKEditor 4.19.0

New features:

    #2444: Togglable toolbar buttons are now exposed as toggle buttons in the browser's accessibility tree.
    #4641: Added an option allowing to cancel the Delayed Editor Creation feature as a function handle for editor creators (CKEDITOR.replace, CKEDITOR.inline, CKEDITOR.appendTo).
    #4986: Added config.shiftLineBreaks allowing to preserve inline elements formatting when the shift+enter keystroke is used.
    #2445: Added config.applicationTitle configuration option allowing to customize or disable the editor's application region label. This option, combined with config.title, gives much better control over the editor's labels read by screen readers.

Fixed Issues:

    #4543: Fixed: Toolbar buttons toggle state is not correctly announced by screen readers lacking the information whether the feature is on or off.
    #4052: Fixed: Editor labels are read incorrectly by screen readers due to invalid editor control type for the Iframe Editing Area editors.
    #1904: Fixed: Screen readers are not announcing the read-only editor state.
    #4904: Fixed: Table cell selection and navigation with the tab key behavior is inconsistent after adding a new row.
    #3394: Fixed: Enhanced image plugin dialog is not supporting URL with query string parameters. Thanks to Simon Urli!
    #5049: Fixed: The editor fails in strict mode due to not following the use strict directives in a core editor module.
    #5095: Fixed: The clipboard plugin shows notification about unsupported file format when the file type is different than jpg, gif, png, not respecting supported types by the Upload Widget plugin.
    #4855: [iOS] Fixed: Focusing toolbar buttons with an enabled VoiceOver screen reader moves the browser focus into an editable area and interrupts button functionality.

API changes:

    #4641: The CKEDITOR.replace, CKEDITOR.inline, CKEDITOR.appendTo functions are now returning a handle function allowing to cancel the Delayed Editor Creation feature.
    #5095: Added the CKEDITOR.plugins.clipboard.addFileMatcher function allowing to define file formats supported by the clipboard plugin. Trying to paste unsupported files will result in a notification that a file cannot be dropped or pasted into the editor.
    #2445: Added config.applicationTitle alongside CKEDITOR.editor#applicationTitle to allow customizing editor's application region label.


## CKEditor 4.18.0

**Security Updates:**
* Fixed an XSS vulnerability in the core module reported by GitHub Security Lab team member [Kevin Backhouse](https://github.com/kevinbackhouse).
  Issue summary: The vulnerability allowed to inject malformed HTML bypassing content sanitization, which could result in executing a JavaScript code. See [CVE-2022-24728](https://github.com/ckeditor/ckeditor4/security/advisories/GHSA-4fc4-4p5g-6w89) for more details.
* Fixed a Regular expression Denial of Service (ReDoS) vulnerability in dialog plugin discovered by the CKEditor 4 team during our regular security audit.
  Issue summary: The vulnerability allowed to abuse a dialog input validator regular expression, which could cause a significant performance drop resulting in a browser tab freeze. See [CVE-2022-24729](https://github.com/ckeditor/ckeditor4/security/advisories/GHSA-f6rf-9m92-x2hh) for more details.
You can read more details in the relevant security advisory and [contact us](security@cksource.com) if you have more questions.
**An upgrade is highly recommended!**

CKEditor 4.16
Fixed ReDoS vulnerability in the Autolink plugin.
Fixed ReDoS vulnerability in the Advanced Tab for Dialogs plugin.

CKEditor 4.15.1
CKEditor 4.15.1 fixes an XSS vulnerability in the Color History feature
(CVE‑2020‑27193). Prior to this version, it was possible to execute an
XSS-type attack conducted with a specially crafted HTML code injected by the
victim via the Color Button dialog. However, the vulnerability required the
user to manually paste the code, minimizing the risk.

CKEditor v4.11.1.2
2019-02-09
Bugfixed removed deprecated Open Paste DialogBoxes
CKEditor v4.11.1.1
2018-12-16
recoding wblink and wbdroplet plugin, for stable Content-type: application/javascript,
for working with security "header setting X-Content-Type-Options: nosniff"
Ckeditor no longer sets a absolute url after choosing a entry from an addon selectbox , after choosing an addon entry, link will be inserted by
[wblink{page_id}?addon=name&item={addon_id}]. Marking the link, the ckeditor jumps to the correct addon entry in select box

*/