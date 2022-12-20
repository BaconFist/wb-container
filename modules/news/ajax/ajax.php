<?php

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};


    $aJsonRespond = [];
    $sAddonFile   = str_replace('\\','/',__FILE__).'/';
    $sAddonPath   = \dirname(\dirname($sAddonFile)).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $sAddonRel    = '/'.$sModuleName.'/'.$sAddonPath;
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    // comment out if you have to load config.php
    if (!defined('SYSTEM_RUN') && is_readable($sAppPath.'/config.php')) {require($sAppPath.'/config.php');}

    // print with or without header
    $admin_header=false; //
    // Workout if the developer wants to show the info banner
    $print_info_banner = false; // true/false
    // Tells script to update when this page was last updated
    $update_when_modified = false;
    // Include WB admin wrapper script to sanitize page_id and section_id, print SectionInfoLine
    require(WB_PATH.'/modules/admin.php');

//    $sModuleDir   = basename(dirname(__DIR__));
//    require config for Constants and DB access
//    require(dirname(dirname(dirname(__DIR__))).'/config.php');
    // Check if user has enough rights to do this:
    // initialize json_respond array  (will be sent back)
    try{

        $admin = new admin('Modules', 'module_view', FALSE, FALSE);
    // first read and validate the $aRequestVars arguments
        $aAllowedActions = ['toggle_active_post','toggle_active_group'];
        $sRequestAction  = ($aRequestVars['action'] ??'');
        // test if action value is in allowed list of actions
        if ( !in_array($sRequestAction, $aAllowedActions)) {
            throw new \Exception('no valid "action" was set');
        }
        $sRequestIdKey = $aRequestVars['iRecordId'];
//        $iIdKey = $admin->checkIDKEY('iRecordId');
//        $iIdKey = $admin->checkIDKEY('iRecordId', 0, '', true);
        $iIdKey = $sRequestIdKey;
        if (!($iRequestRecordId = (int)$iIdKey ?: 0)) {
            throw new \Exception('no valid RecordId was set '.$iRequestRecordId);
        }
        if (!($admin->is_authenticated() && $admin->get_permission($sModuleName, 'module'))) {
            throw new \Exception('You\'re not allowed to make changes to Module: ['.$sModuleName.']');
        }
        switch ($sRequestAction):
            case 'toggle_active_post':
                // Check the Parameters
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_news_posts` SET '
                     . '`active`= (`active` IS NOT TRUE) '
                     . 'WHERE `post_id`='.(int)$iRequestRecordId;
                if (!(bool)$database->query($sql)) {
                    throw new \Exception('DB access fail ['.$database->get_error().']');
                }
                break;
            case 'toggle_active_group':
                // Check the Parameters
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_news_groups` SET '
                     . '`active`= (`active` IS NOT TRUE) '
                     . 'WHERE `group_id`='.(int)$iRequestRecordId;
                if (!(bool)$database->query($sql)) {
                    throw new \Exception('DB access fail ['.$database->get_error().']');
                }
                break;

            default:
                throw new \Exception('no valid "action" was set ');
                break;
        endswitch;
        $aJsonRespond['message'] = 'Activity Status successfully changed';
        $aJsonRespond['success'] = true;
//        $aJsonRespond['sIdKey']  = $admin->getIDKEY($iIdKey);
        $aJsonRespond['sIdKey']  = $iIdKey;
    } catch (Exception $e) {
        $aJsonRespond['message'] = $e->getMessage();
        $aJsonRespond['success'] = false;
//        $aJsonRespond['sIdKey']  = $admin->getIDKEY($iIdKey);
        $aJsonRespond['sIdKey']  = $iIdKey;
    }
    // echo the json_respond to the ajax function
    exit(json_encode($aJsonRespond));
