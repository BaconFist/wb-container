<?php
/**
 *
 * @category        admin
 * @package         admintools
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @author          Werner v.d. Decken
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: index.php 211 2019-01-29 22:32:17Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/admintools/index.php $
 * @lastmodified    $Date: 2019-01-29 23:32:17 +0100 (Di, 29. Jan 2019) $
 *
 */


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use vendor\phplib\Template;

    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'config.php');}

//if ( !defined( 'WB_PATH' ) ){ require( dirname(dirname((__DIR__))).'/config.php' ); }
$admin = new admin('admintools', 'admintools');
// Include the WB functions file
//require_once(WB_PATH.'/framework/functions.php');

// Setup template object, parse vars to it, then parse it
// Create new template object
$template = new Template(dirname($admin->correct_theme_source('admintools.htt')));
// $template->debug = true;
$template->set_file('page', 'admintools.htt');
$template->set_block('page', 'main_block', 'main');

// Insert required template variables
$template->set_var('ADMIN_URL', ADMIN_URL);
$template->set_var('THEME_URL', THEME_URL);
$template->set_var('HEADING_ADMINISTRATION_TOOLS', $HEADING['ADMINISTRATION_TOOLS']);

// Insert tools into tool list
$template->set_block('main_block', 'tool_list_block', 'tool_list');
$sql = 'SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = \'module\' AND `function` = \'tool\' order by `name`';
$results = $database->query($sql);

if($results->numRows() > 0) {
    while($tool = $results->fetchRow(MYSQLI_ASSOC)) {
      if( $admin->get_permission($tool['directory'], 'module' ) ) {
        $template->set_var('TOOL_NAME', $tool['name']);
        $template->set_var('TOOL_DIR', $tool['directory']);
        // check if a module description exists for the displayed backend language
        $tool_description = false;
        if(function_exists('file_get_contents') && file_exists(WB_PATH.'/modules/'.$tool['directory'].'/languages/'.LANGUAGE .'.php')) {
            // read contents of the module language file into string
            $data = @file_get_contents(WB_PATH .'/modules/' .$tool['directory'] .'/languages/' .LANGUAGE .'.php');
            $tool_description = get_variable_content('module_description', $data, true, false);
        }
        if (is_readable(WB_PATH.'/modules/' .$tool['directory'].'/tool_icon.png'))
        {
            $template->set_var('TOOL_ICON', WB_URL.'/modules/' .$tool['directory'].'/tool_icon.png');
        } else {
            $template->set_var('TOOL_ICON', THEME_URL.'/icons/admintools.png');
        }
        $template->set_var('TOOL_DESCRIPTION', ($tool_description === false)? $tool['description'] : $tool_description);
        $template->parse('tool_list', 'tool_list_block', true);
      }
    }
} else {
    $template->set_var('TOOL_LIST', $TEXT['NONE_FOUND']);
}

// Parse template objects output
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

$admin->print_footer();
