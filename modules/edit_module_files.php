<?php
/**
 *
 * @category        backend
 * @package         modules
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/edit_module_files.php $
 * @lastmodified    $Date: 2019-03-26 22:24:21 +0100 (Di, 26. Mrz 2019) $
 * @version         $Id: edit_module_files.php 294 2019-03-26 21:24:21Z Luisehahne $
 *
 */
declare(strict_types=1);

use bin\{WbAdaptor,SecureTokens,Sanitize,requester};
use bin\helpers\{PreCheck,msgQueue};

// include required libraries
//if (!\defined('SYSTEM_RUN')){ require(\dirname(__DIR__).'/config.php');}

    $sAddonFile   = \str_replace('\\','/',__FILE__);
    $sFolder      = \basename(\dirname($sAddonFile));
    switch ($sFolder):
        case 'cmd':
          $sAddonPath   = \dirname($sAddonFile,2).'/';
          break;
        default :
          $sAddonPath   = (\dirname($sAddonFile)).'/';
    endswitch;

    $sModulesPath = ($sAddonPath).'';
    $sModuleName  = \basename($sModulesPath);
    $sAddonName   = \basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = \preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (! \defined('SYSTEM_RUN') && \is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    }

/* ------------------------------------------------------------------ */
// print with or without header
    $admin_header = false;
// Workout if the developer wants to show the info banner
    $print_info_banner = false; // true/false
// Tells script to update when this page was last updated
    $update_when_modified = false;
// Include WB admin wrapper script
    require($sModulesPath.'admin.php');

/* -------------------------------------------------------- */
// settings needs a admin object
    $oReg     = WbAdaptor::getInstance();
    $oDb      = $oReg->getDatabase();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */

// include edit area wrapper script
    if (!\function_exists('loader_help')){require(WB_PATH.'/include/editarea/wb_wrapper_edit_area.php');}
// include functions to edit the optional module CSS files (frontend.css, backend.css)
    if (!\function_exists('toggle_css_file')){require(WB_PATH.'/framework/module.functions.php');}
    $aCssFiles = ['frontend.css', 'backend.css','frontendUser.css', 'backendUser.css'];

    $is_request_name = function($name)use ($aRequestVars){return (isset($aRequestVars[$name]) ? 1 : 0);};
    $sAddonName      = ($admin->StripCodeFromText(isset($aRequestVars['mod_dir']) ? $aRequestVars['mod_dir'] : ''));
    $_edit_file      = ($admin->StripCodeFromText(isset($aRequestVars['edit_file']) ? $aRequestVars['edit_file'] : '',11));

    $sAddonRel  = '/modules/'.$sAddonName;
    $sAddonUrl  = WB_URL.$sAddonRel;
    $sAddonPath = WB_PATH.$sAddonRel;

    $sSectionIdPrefix = '#'.(\defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' ).(int)$section_id;
    $sBackAddonLink = ADMIN_URL.'/pages/modify.php?page_id='.$page_id.$sSectionIdPrefix;
    $oLang = \Translate::getInstance();
    $oLang->enableAddon('modules\\'.$sAddonName);

    if (!SecureTokens::checkFTAN ()) {
      $admin->print_header();
      $admin->print_error($oLang->MESSAGE_GENERIC_SECURITY_ACCESS, $sBackAddonLink);
    }

    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery    = sprintf('%s=%s',$aFtan['name'],$aFtan['value']);
    $sQueryString  = 'page_id='.$page_id.'&section_id='.$section_id.'&edit_file='.$_edit_file.'&mod_dir='.$sAddonName;
    $sQueryString .= '&'.$sFtanQuery;

    $sBackAddonUrl = $sAddonUrl.'/modify_settings.php?page_id='.$page_id.'&section_id='.$section_id.'&'.$sFtanQuery;
    $sBackEditUrl = WB_URL.'/modules/edit_module_files.php?'.$sQueryString;
    $sBackLink = ($is_request_name('save_pagetree')? $sBackAddonLink :$sBackEditUrl );

    if (empty($sAddonName) || empty($_edit_file)){
      throw new \Exception('Error: Empty Addon Name.'.$sQueryString);
    }
// After check print the header
    $admin->print_header();
// back to module settings
//    $FTAN = $admin->getFTAN('GET');

    $_action = 'edit';
    $_action = ($is_request_name('save') ? 'save' : $_action);
    $_action = ($is_request_name('save_pagetree') ? 'save' : $_action);
/*
//   $mod_dir = (isset($_POST['mod_dir']) ? $_POST['mod_dir'] : '');
//   $_edit_file = (isset($_POST['edit_file']) ? $_POST['edit_file'] : '');
*/
//check if given $sAddonName + edit_file is valid path/file
   $_realpath = \realpath(WB_PATH.'/modules/'.$sAddonName.'/'.$_edit_file);  //
   if ($_realpath){
   // realpath is a valid path, now test if it's inside WB_PATH
      $_realpath = \str_replace('\\','/', $_realpath);
      $_fileValid = (\strpos($_realpath, (\str_replace('\\','/', WB_PATH))) !== false);
   }
// check if all needed args are valid
   if (!$page_id || !$section_id || !$_realpath || !$_fileValid) {
      $sSriptRel = '/'.basename(__DIR__).'/'.basename(__FILE__);
      die (sprintf('[%1$04d] Invalid arguments passed - <b>%2$s</b> script stopped.',__LINE__,$sSriptRel));
   }

// set default text output if varibles are not defined in the global WB language files
   $TEXT['HEADING_CSS_FILE']  = sprintf("%s : ",($TEXT['HEADING_CSS_FILE'] ?? 'Actual module file'));
   $TEXT['EDIT_CSS_FILE'] = sprintf("%s ",($TEXT['EDIT_CSS_FILE'] ?? 'Edit the CSS <b>'.$_edit_file.'</b> definitions in the textarea below.'));

// check if action is: save or edit
    if ($_action === 'save') {
// SAVE THE UPDATED CONTENTS TO THE CSS FILE
        $css_content = '';
        if (isset($_POST['css_data']) && \strlen($_POST['css_data']) > 0) {
           $css_content = rtrim($admin->StripCodeFromText($_POST['css_data']));
        }

        $modFileName = $sAddonPath.'/' .$_edit_file;
        if (!empty($css_content)&& \file_put_contents($modFileName,$css_content."\n")) {
              $msg[] = $oLang->MESSAGE_PAGES_SAVED;
              $admin->print_success(\implode('<br />',$msg), $sBackLink);
              $admin->print_footer();
              exit;
       } else {
          $admin->print_error($oLang->MESSAGE_PAGES_NOT_SAVED, $sBackAddonUrl);
          $admin->print_footer();
          exit;
       }
    } else {
      // MODIFY CONTENTS OF THE CSS FILE VIA TEXT AREA
      // check which module file to edit (frontend.css, backend.css or '')
       $css_file = (\in_array($_edit_file, $aCssFiles)) ? $_edit_file : '';
    // display output
        if ( empty($css_file)){
      // no valid module file to edit; display error message and backlink to modify.php
           $msg = [];
           echo '<h2 style="text-align: center;">Nothing to edit</h2>';
           $msg[] = "<p>No valid module file exists for this module.</p>";
           $admin->print_error(\implode('<br />',$msg), $sBackAddonLink);
           $admin->print_footer();
           exit;
        } else {
        // echo registerEditArea('code_area', 'css');
           $aInitEditArea = [
            'id' => 'code_area',
            'syntax' => 'css',
            'syntax_selection_allow' => false,
            'allow_resize' => true,
            'allow_toggle' => true,
            'start_highlight' => true,
            'toolbar' => 'search, go_to_line, fullscreen, |, undo, redo, |, select_font, |, change_smooth_selection, highlight, reset_highlight, |, help',
            'font_size' => '14'
           ];
          echo (function_exists('registerEditArea')) ? registerEditArea($aInitEditArea) : 'none';
          // store content of the module file in variable
          $sCssFile = $oReg->AppPath.'modules/'.$sAddonName .'/'.$css_file;
          $css_content = \file_get_contents($sCssFile); // ,false,null,0,\filesize ($sCssFile)
          // write out heading
          echo '<h2>' .$TEXT['HEADING_CSS_FILE'].'"' .$css_file .'"</h2>';
          // include button to switch between frontend.css and backend.css (only shown if both files exists)
          toggle_css_file($sAddonName, $css_file);
          echo '<h4>'.$TEXT['EDIT_CSS_FILE'].'</h4>';
//          $sScriptUrl = WB_URL.$oRequest->getServerVar('SCRIPT_NAME');
          $sScriptUrl = $oRequest->getServerVar('SCRIPT_NAME');
      // output content of module file to textareas
          $sCSSFile = $_edit_file;
?>
<!-- -->
     <form id="editModuleCssFile" action="<?php echo $oReg->AppUrl.$sScriptUrl;?>" method="post" style="margin: 0;">
        <input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
        <input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
        <input type="hidden" name="mod_dir" value="<?php echo $sAddonName; ?>" />
        <input type="hidden" name="edit_file" value="<?php echo $sCSSFile; ?>" />
        <input type="hidden" name="<?php echo $aFtan['name']; ?>" value="<?php echo $aFtan['value']; ?>" />

        <div class="w3-row">
        <textarea class="w3-textarea w3-border w3-margin-top" id="code_area" name="css_data" cols="80" rows="20" wrap="virtual">
<?php echo \htmlspecialchars($css_content); ?>
        </textarea>
        </div>
        <div class="w3-margin-top">
            <div class="w3-container w3-cell w3-mobile">
                 <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="save" type="submit" value="<?php echo $oLang->TEXT_SAVE; ?>" style="min-width: 10.25em;"/>
            </div>
            <div class="w3-container w3-cell w3-mobile">
                <input class="w3-btn w3-btn-default w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" name="save_pagetree" type="submit" value="<?php echo $oLang->TEXT_SAVE.' & '.$oLang->TEXT_CLOSE; ?>" style="min-width: 10.25em;"/>
            </div>
            <div class="w3-container w3-cell w3-mobile">
                <input id="cancel" class="w3-btn w3-btn-default w3-blue-wb w3-hover-red w3--medium w3-btn-min-width" type="button" value="<?php echo $oLang->TEXT_CLOSE; ?>" onclick="window.location='<?php echo $sBackAddonLink; ?>';" style="min-width: 10.25em;" />
            </div>
        </div>

      </form>
<?php
        }// end registerEditArea
    }// end save
    // Print admin footer
    $admin->print_footer();
    exit;
