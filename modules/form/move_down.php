<?php
/**
 *
 * @category        module
 * @package         Form
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.2
 * @requirements    PHP 7.2.x and higher
 * @version         $Id: move_up.php 68 2018-09-17 16:26:08Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/form/move_up.php $
 * @lastmodified    $Date: 2018-09-17 18:26:08 +0200 (Mo, 17. Sep 2018) $
 * @description
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
    require(WB_PATH.'/modules/admin.php');

    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();

// Get id
    $field_id = $oApp->getIdFromRequest('field_id');

    if (!$field_id) {
     $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
    }

// Create new order object an reorder
    $order = new order(TABLE_PREFIX.'mod_form_fields', 'position', 'field_id', 'section_id');
    if ($order->move_down($field_id)) {
        $admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
    } else {
        $admin->print_error($TEXT['ERROR'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
    }

// Print admin footer
    $admin->print_footer();
