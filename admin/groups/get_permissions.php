<?php
/**
 *
 * @category        admin
 * @package         groups
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: get_permissions.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/groups/get_permissions.php $
 * @lastmodified    $Date: 2019-03-17 07:05:56 +0100 (So, 17. Mrz 2019) $
 *
 */
/*---------------------------------------------------------------------------------------------------------------*/
if (!\defined('SYSTEM_RUN')) {
    \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>
    <p>The You do not have permission to view the requested URL '.$_SERVER['SCRIPT_NAME'].'
    .</p><hr>'.$_SERVER['SERVER_SIGNATURE'].'</body></html>';
    \flush(); exit;
} else {
/*---------------------------------------------------------------------------------------------------------------*/
// merge extended system_permission
    $system_permissions = array_flip($system_permissions);
// Get system permissions
    $system_permissions = (isset($bResetSystem) && ($bResetSystem==1) ? [] : $system_permissions);
    function getSystemDefaultPermission(database $oDb){
        $sqlAdmin = 'SELECT `system_permissions` FROM `'.TABLE_PREFIX.'groups` '
                  . 'WHERE `group_id`=\'1\' ';
        $sPermissions = $oDb->get_one($sqlAdmin);
        return ($oDb->is_error() ? $oDb->get_error() :$sPermissions);
    }
/*---------------------------------------------------------------------------------------------------------------*/
    function getSystemFromRequest($aRequestVars=null)
    {
        global $bResetSystem, $database,$system_permissions;
        if ($bResetSystem){return null;}
        $aPermissions = array_flip(explode(',', getSystemDefaultPermission($database)));
        // define Lambda-Callback for sanitize POST arguments   secunia 2010-92-2
        $cbSanitize = (function($sValue) { $sValue = preg_replace('/[^a-z0-9_-]/i', '', $sValue); return $sValue;});
        $aPermissions = (is_array($aPermissions) ? $aPermissions : []);
        $aPermissions = array_map($cbSanitize, $aPermissions);
        $aPermissions = array_intersect_key($aRequestVars, $aPermissions);
        return $aPermissions;
    }
/*---------------------------------------------------------------------------------------------------------------*/
    function getSystemPermissions($aRequestVars=null)
    {
        $aPermissions = [];
        if (!$aRequestVars){return $aPermissions;}
        $aValidType = $aValidView = $aValidAddons = $aValidAccess = $aValidSettings = [];
        $aTmpPermissions  = getSystemFromRequest($aRequestVars);
        if (($aTmpPermissions)){
            $aValidType     = preg_replace('/^(.*?)_.*$/', '\1', array_keys($aTmpPermissions));
            $aValidView     = preg_replace('/^(.*)/', '\1_view', $aValidType);
            $aValidAddons   = preg_replace('/^(modules.*|templates.*|languages.*)$/', 'addons', $aValidView);
            $aValidAccess   = preg_replace('/^(groups.*|users.*)$/', 'access', $aValidView);
            $aValidSettings = preg_replace('/^(settings.*)$/', 'settings_basic', $aValidView);
            $aPermissions   = array_merge(
                              $aTmpPermissions,
                              array_flip($aValidType),
                              array_flip($aValidView),
                              array_flip($aValidAccess),
                              array_flip($aValidAddons),
                              array_flip($aValidSettings)
                              );
            $iSortFlags = ((version_compare(PHP_VERSION, '5.4.0', '<')) ? SORT_REGULAR : SORT_NATURAL|SORT_FLAG_CASE);
            ksort ($aPermissions, \SORT_NATURAL|\SORT_FLAG_CASE);
        }
        return $aPermissions;
    }
    $aRequestSystemPermissions = getSystemPermissions($aRequestVars);

/* WB283 SP4 Fixes ***************************************************/
    // clean up system_permission
    $system_permissions = ($bAdvancedSave ? array_intersect_key($aRequestSystemPermissions, $system_permissions):$system_permissions);
    $aSystemPermissions = array_merge($aRequestSystemPermissions, $system_permissions);
    $aSystemPermissions = (@$bResetSystem ? [] : $aSystemPermissions);
    $iSortFlags = ((version_compare(PHP_VERSION, '5.4.0', '<'))?SORT_REGULAR:SORT_NATURAL|SORT_FLAG_CASE);
    ksort ($aSystemPermissions, $iSortFlags);
    // Implode system permissions
    $aAllowedSystemPermissions = [];
/*------------------------------------------------------------------------------------------------------------*/
    foreach ($aSystemPermissions as $sName => $sValue) {
        $aAllowedSystemPermissions[] = $sName;
    }
    $system_permissions = implode(',', $aAllowedSystemPermissions);
/*------------------------------------------------------------------------------------------------------------*/
    function getPermissionsFromPost($sType, $bReset=false)
    {
        $aAvailableItemsList = [];
        // define Lambda-Callback for sanitize POST arguments   secunia 2010-92-2
        $cbSanitize     = function($sValue) { $sValue = preg_replace('/[^a-z0-9_-]/i', '', $sValue); return $sValue; };
        $aPermissions   = $GLOBALS['admin']->get_post($sType.'_permissions');
        $aPermissions   = is_array($aPermissions) ? $aPermissions : [];
        $aPermissions   = array_map($cbSanitize, $aPermissions);
        $sOldWorkingDir = getcwd();
        chdir(WB_PATH.'/'.$sType.'s/');
        $aItemsList = glob('*', GLOB_ONLYDIR|GLOB_NOSORT);
        foreach($aItemsList as $sFolder){
          if (is_readable(WB_PATH.'/'.$sType.'s/'.$sFolder.'/info.php')){
              $aAvailableItemsList[] = $sFolder;
          }
        }
        chdir($sOldWorkingDir);
        $aPermissions    = ($bReset ? []:$aPermissions);
        $aUncheckedItems = array_diff($aAvailableItemsList, $aPermissions);
        return implode(',', $aUncheckedItems);
    }
    // Get module permissions
    $module_permissions   = getPermissionsFromPost('module', $bResetModules);
    // Get template permissions
    $template_permissions = getPermissionsFromPost('template', $bResetTemplates);

}
