<?php
/**
 *
 * @category        modules
 * @package         form
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: move_to.php 284 2019-03-22 08:13:16Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/move_to.php $
 * @lastmodified    $Date: 2019-03-22 09:13:16 +0100 (Fr, 22. Mrz 2019) $
 *
*/

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = str_replace('\\','/',__DIR__).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'/config.php')) {require($sAppPath.'/config.php');}
    $sDumpPathname = \basename($sAddonPath).'/'.\basename($sAddonFile);

    // Include WB admin wrapper script
    $update_when_modified = false;
// Tells script to update when this page was last updated
    $admin_header = false;
    require($sAppPath.'/modules/admin.php');

    $aJsonRespond['success'] = true;
/*
    $aJsonRespond['modules'] = $aRequestVars['module'];
    $aJsonRespond['modules_dir'] = '/modules/'.$aRequestVars['module'];
*/
// Get id
    $table = TABLE_PREFIX.'mod_form_fields';
    $id = (int)$aRequestVars['move_id'];
    $id_field = 'field_id';
    $common_field = 'section_id';
    $sFieldOrderName = 'position';
    $aJsonRespond['message'] = 'Activity position '.$id.' successfully changed';
//    $group = (int)$aRequestVars['section_id'];

