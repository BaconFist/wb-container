<?php
/*
 * About WebsiteBaker
 *
 * Website Baker is a PHP-based Content Management System (CMS)
 * designed with one goal in mind: to enable its users to produce websites
 * with ease.
 *
 * LICENSE INFORMATION
 *
 * WebsiteBaker is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * WebsiteBaker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
/**
 *
 * @category        core
 * @package         framework
 * @subpackage      frontend
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink
 * @author          Manuela v.d.Decken
 * @link            https://websitebaker.org/
 * @license         GNU General Public License 2.0
 * @platform        WebsiteBaker 2.12.0
 * @requirements    PHP 5.6.3 and higher
 * @version         $Id: module.functions.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @since           File available since 18.10.2017
 * @deprecated      no
 * @description     This file contains routines to edit the optional module files: frontend.css and backend.css
 *                  Mechanism was introduced with WB 2.7 to provide a global solution for all modules
 *                  To use this function, include this file from your module (e.g. from modify.php)
 *                  Then simply call the function edit_css('your_module_directory') - that's it
 *                  NOTE: Some functions were added for module developers to make the creation of own module easier
 *
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');
use bin\{WbAdaptor,SecureTokens,Sanitize,requester};
use bin\helpers\{PreCheck,msgQueue};
use vendor\phplib\Template;


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }
/* -------------------------------------------------------- */
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 FUNCTIONS REQUIRED TO EDIT THE OPTIONAL MODULE CSS FILES
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/

// this function checks the validity of the specified module directory
    if(!is_callable('check_module_dir')) {
       function check_module_dir($sAddonName) {
          $oReg = WbAdaptor::getInstance();
          // check if module directory is formal correct (only characters: "a-z,0-9,_,-")
          if (!preg_match('/^[a-z0-9_-]+$/iD', $sAddonName)){ return '';}
          // check if the module folder contains the required info.php file
          return (is_readable($oReg->AppPath .'modules/' .$sAddonName .'/info.php')) ? $sAddonName : '';
       }
    }

// this function checks if the specified optional module file exists
    if (!is_callable('mod_file_exists')) {
        function mod_file_exists($sAddonName, $mod_file='frontend.css') {
          $oReg     = WbAdaptor::getInstance();
          $oRequest = $oReg->getRequester();
            $bsRetVal = false;
            // check if the module file exists return if one match was found
            if (is_array(($mod_file))){
                foreach ($mod_file as $sRetVal){
                  $bsRetVal = is_readable(WB_PATH .'/modules/' .$sAddonName .'/' .$sRetVal);
                  if ($bsRetVal){break;}
                }
            } else {
                $bsRetVal = is_readable(WB_PATH .'/modules/' .$sAddonName .'/' .$mod_file);
            }
            return ($bsRetVal);
        }
    }

// this function displays the "Edit CSS" button in modify.php
    if (!is_callable('edit_module_css')) {
       function edit_module_css($sAddonName) {
          global $page_id, $section_id, $admin; //
/* -------------------------------------------------------- */
// settings needs a admin object
          $oReg     = WbAdaptor::getInstance();
          $oRequest = $oReg->getRequester();
          //$sAppPath = $oReg->AppPath;
          $sAddonPath = check_module_dir($sAddonName);
          if (empty($sAddonPath)){ return;}
          // check if the required edit_module_css.php file exists
          if (!is_readable($oReg->AppPath.'modules/edit_module_files.php')){ return;}
          // check if specified module directory is valid
          $backend_css  = 'frontend.css';
          $frontend_css = 'backend.css';
          $_edit_file   = '';
/* -------------------------------------------------------- */
          //$sAddonName = basename($sAddonName);
          $sAddonRel  = str_replace(WB_PATH,'',WB_PATH.'/modules/'.$sAddonName).'/';
          $sAddonUrl  = WB_URL.str_replace('\\','/',$sAddonRel);
          $sAddonPath = str_replace('\\','/',WB_PATH.'/'.$sAddonRel);

          $_edit_file = ($admin->StripCodeFromText(isset($aRequestVars['edit_file']) ? $aRequestVars['edit_file'] : '',11));
          $aCssFiles = ['backendUser.css','backend.css','frontendUser.css','frontend.css'];
          // check if frontend.css or backend.css exist
            $frontend_css = (is_readable($sAddonPath.''.$aCssFiles['2']) ? $aCssFiles['2'] : '');
            $frontend_css = (is_readable($sAddonPath.''.$aCssFiles['3']) ? $aCssFiles['3'] : $frontend_css);
            $backend_css  = (is_readable($sAddonPath.''.$aCssFiles['0']) ? $aCssFiles['0'] : '');
            $backend_css  = (is_readable($sAddonPath.''.$aCssFiles['1']) ? $aCssFiles['1'] : $backend_css);
            $sCssFile = (is_readable($frontend_css) ? $frontend_css : (is_readable($backend_css) ? $backend_css : ''));
//          $frontend_css = mod_file_exists($mod_dir, 'frontend.css');
//          $backend_css  = mod_file_exists($mod_dir, 'backend.css');

          // output the edit CSS submtin button if required
          if (!empty($sCssFile)) {
             // default text used for the edit CSS routines if not defined in the WB core language files
             $edit_css_caption = (isset($GLOBALS['TEXT_CAP_EDIT_CSS'])) ?$GLOBALS['TEXT_CAP_EDIT_CSS'] :'Edit CSS';
?>
             <form id="callCssFile" action="<?php echo WB_URL .'/modules/edit_module_files.php';?>"
                method="post" style="margin: 0; text-align:right;">
                <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
                <input type="hidden" name="mod_dir" value="<?php echo $sAddonName; ?>" />
                <input type="hidden" name="edit_file" value="<?php echo $sCssFile;?>" />
                <input type="hidden" name="action" value="edit" />
                <?php echo $admin->getFTAN();?>
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width mod_<?php echo $sAddonName;?>_edit_css" type="submit" value="<?php echo $edit_css_caption;?>" />
             </form>
<?php
        }
      }
    }

// this function displays a button to toggle between CSS files (invoked from edit_css.php)
    if (!is_callable('toggle_css_file')) {
       function toggle_css_file($sAddonName, $sBaseCssFile = '') {
          global $page_id, $section_id, $admin;
          $oReg     = WbAdaptor::getInstance();
          $oRequest = $oReg->getRequester();
          $aCssDefaultFiles = ['backendUser.css','backend.css','frontendUser.css','frontend.css'];
          // check if the required edit_module_css.php file exists
          if (!is_readable($oReg->AppPath .'modules/edit_module_files.php')){ return;}
          if (!in_array($sBaseCssFile, $aCssDefaultFiles)){ return;}
          // check if specified module directory is valid
          if (check_module_dir($sAddonName) == ''){ return;}
?>
<article id="settings" class="form-block w3-padding w3-bar w3-row block-outer" style="float: right;">
          <div class="w3-container w3-bar-item w3-right" style="padding: 0.0em 10px!important;">&nbsp;</div>
<?php

          if (mod_file_exists($sAddonName, $aCssDefaultFiles)) {

              //$sChangeCss = 'frontend';
              foreach($aCssDefaultFiles as $sCssFile) {
                  $sFile = $oReg->AppPath.'modules/'.$sAddonName.'/'.$sCssFile;
                  if (is_readable($sFile)){
                      $sUserFile = substr($sCssFile, -8,4);
                      $ProFix   = (($sUserFile=='User') ? ($sUserFile) : '');
                      //$toggle_file = (($sCssFile == 'frontend.css') ? 'backend'.$ProFix.'.css' : 'frontend'.$ProFix.'.css');
                      $toggle_file = $sCssFile;
//echo \nl2br(\sprintf("---- [%04d] %s \n ",__LINE__,$toggle_file));
                      if (!is_readable( $oReg->AppPath.'modules/'.$sAddonName.'/'.$toggle_file)){continue;}
?>
          <div class="w3-container w3-bar-item w3-right" style="padding: 5px 10px!important;">
             <form id="toggleModuleCssFile" action="<?php echo $oReg->AppUrl.'modules/edit_module_files.php';?>" method="post" style="margin: 0; text-align:right;">
                <?php echo $admin->getFTAN();?>
                <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
                <input type="hidden" name="mod_dir" value="<?php echo $sAddonName; ?>" />
                <input type="hidden" name="edit_file" value="<?php echo $toggle_file; ?>" />
                <input type="hidden" name="action" value="edit" />
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width mod_<?php echo $sAddonName;?>_edit_css" type="submit" value="<?php echo ucwords($toggle_file);?>" />
             </form>
          </div>
<?php
              } // readable css file
          }// foreach
?>
</article>
<?php
      } //mod_file_exists
      return $toggle_file;
    }
}
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 FUNCTIONS WHICH CAN BE USED BY MODULE DEVELOPERS FOR OWN MODULES (E.G. VIEW.PHP, MODIFY.PHP)
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/

// function to obtain the module language file depending on the backend language of the current user
    if (!is_callable('get_module_language_file')) {
       function get_module_language_file($mymod_dir) {
          $mymod_dir = strip_tags($mymod_dir);
          if (file_exists(WB_PATH .'/modules/' .$mymod_dir .'/languages/' .LANGUAGE .'.php')) {
             // a module language file exists for the users backend language
             return (WB_PATH .'/modules/' .$mymod_dir .'/languages/' .LANGUAGE .'.php');
          } else {
             // an English module language file must exist in all multi-lingual modules
             if (file_exists(WB_PATH .'/modules/' .$mymod_dir .'/languages/EN.php')) {
                return (WB_PATH .'/modules/' .$mymod_dir .'/languages/EN.php');
             } else {
                echo '<p><strong>Error: </strong>';
                echo 'Default language file (EN.php) of module "' .htmlentities($mymod_dir) .'" does not exist.</p><br />';
                return false;
             }
          }
       }
    }

// function to include module CSS files in <body> (only if WB < 2.6.7 or register_frontend_modfiles('css') not invoked in template)
    if (!is_callable('include_module_css')) {
       function include_module_css($mymod_dir, $css_file) {
          $aCssFiles = ['backendUser.css','backend.css','frontendUser.css','frontend.css'];
          if (!in_array(($css_file), $aCssFiles)){ return;}
          if ($css_file == 'frontend.css') {
             // check if frontend.css needs to be included into the <body> section
             if (!((!is_callable('register_frontend_modfiles') || !defined('MOD_FRONTEND_CSS_REGISTERED')) &&
                   file_exists(WB_PATH .'/modules/' .$mymod_dir .'/frontend.css'))) {
                return false;
             }
          } else {
             // check if backend.css needs to be included into the <body> section
             global $admin;
             if (!(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH .'/modules/' .$mymod_dir .'/backend.css'))) {
                return false;
             }
          }
          // include frontend.css or backend.css into the <body> section
          echo "\n".'<style type="text/css">'."\n";
         include(WB_PATH .'/modules/' .$mymod_dir .'/' .$css_file);
         echo "\n</style>\n";
          return true;
       }
    }

// function to check if the optional module Javascript files are loaded into the <head> section
    if (!is_callable('requires_module_js')) {
       function requires_module_js($mymod_dir, $js_file) {
          $aJsFiles = ['frontend.js','frontendUser.js', 'backend.js', 'backendUser.js'];
          if (!in_array(strtolower($js_file), $aJsFiles)) {
             echo '<strong>Note: </strong>Javascript file "' .htmlentities($js_file) .'"
             specified in module "' .htmlentities($mymod_dir) .'" not valid.';
             return false;
          }

          if ($js_file == 'frontend.js') {
             // check if frontend.js is included to the <head> section
             if (!defined('MOD_FRONTEND_JAVASCRIPT_REGISTERED')) {
                echo '<p><strong>Note:</strong> The module: "' .htmlentities($mymod_dir) .'" requires WB 2.6.7 or higher</p>
                <p>This module uses Javascript functions contained in frontend.js of the module.<br />
                Add the code below to the &lt;head&gt; section in the index.php of your template
                to ensure that module frontend.js files are automatically loaded if required.</p>
                <code style="color: #800000;">&lt;?php<br />if(is_callable(\'register_frontend_modfiles\')) { <br />
                &nbsp;&nbsp;register_frontend_modfiles(\'js\');<br />?&gt;</code><br />
                <p><strong>Tip:</strong> For WB 2.6.7 copy the code above to the index.php of your template.
                Then open the view.php of the "' .htmlentities($mymod_dir) .'" module and set the variable
                <code>$requires_frontend_js</code> to false. This may do the trick.</p><p>All WB versions below 2.6.7 needs
                to be upgraded to work with this module.</p>
                ';
                return false;
             }
          } else {
         // check if backend.js is included to the <head> section
             global $admin;
                if (!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH .'/modules/' .$mymod_dir .'/backend.js')) {
                echo '<p><strong>Note:</strong> The module: "' .htmlentities($mymod_dir) .'" requires WB 2.6.7 or higher</p>
                <p>This module uses Javascript functions contained in backend.js of the module.<br />
                You need WB 2.6.7 or higher to ensure that module backend.js files are automatically loaded if required.</p>
                <p>Sorry, you can not use this tool with your WB installation, please upgrade to the latest WB version available.</p><br />
                ';
                return false;
             }
          }
          return true;
       }
    }
// function to check if the optional module Javascript files are loaded into the <body> section
    if (!is_callable('requires_module_body_js')) {
       function requires_module_body_js($mymod_dir, $js_file) {
          if (!in_array(strtolower($js_file), array('frontend_body.js', 'backend_body.js'))) {
             echo '<strong>Note: </strong>Javascript file "' .htmlentities($js_file) .'"
             specified in module "' .htmlentities($mymod_dir) .'" not valid.';
             return false;
          }

          if ($js_file == 'frontend_body.js') {
             // check if frontend_body.js is included to the <body> section
             if(!defined('MOD_FRONTEND_BODY_JAVASCRIPT_REGISTERED')) {
                echo '<p><strong>Note:</strong> The module: "' .htmlentities($mymod_dir) .'" requires WB 2.6.7 or higher</p>
                <p>This module uses Javascript functions contained in frontend_body.js of the module.<br />
                Add the code below before to the &lt;/body&gt; section in the index.php of your template
                to ensure that module frontend_body.js files are automatically loaded if required.</p>
                <code style="color: #800000;">&lt;?php<br />if(is_callable(\'register_frontend_modfiles_body\')) { <br />
                &nbsp;&nbsp;register_frontend_modfiles_body(\'js\');<br />?&gt;</code><br />
                <p><strong>Tip:</strong> For WB 2.6.7 copy the code above to the index.php of your template.
                Then open the view.php of the "' .htmlentities($mymod_dir) .'" module and set the variable
                <code>$requires_frontend_body_js</code> to false. This may do the trick.</p><p>All WB versions below 2.6.7 needs
                to be upgraded to work with this module.</p>
                ';
                return false;
             }
          } else {
             // check if backend_body.js is included to the <body> section
             global $admin;
                if (!method_exists($admin, 'register_backend_modfiles_body') && file_exists(WB_PATH .'/modules/' .$mymod_dir .'/backend_body.js')) {
                echo '<p><strong>Note:</strong> The module: "' .htmlentities($mymod_dir) .'" requires WB 2.6.7 or higher</p>
                <p>This module uses Javascript functions contained in backend_body.js of the module.<br />
                You need WB 2.6.7 or higher to ensure that module backend_body.js files are automatically loaded if required.</p>
                <p>Sorry, you can not use this tool with your WB installation, please upgrade to the latest WB version available.</p><br />
                ';
                return false;
             }
          }
          return true;
       }
    }
