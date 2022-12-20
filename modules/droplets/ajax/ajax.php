<?php

    $aJsonRespond = [];
    $sModuleDir   = basename(dirname(__DIR__));
    // require config for Constants and DB access
    require(dirname(dirname(dirname(__DIR__))).'/config.php');
    // Check if user has enough rights to do this:
    // initialize json_respond array  (will be sent back)
    try{
/* --------------------------------------------------------*/
        $sAddonName = \basename(__DIR__);
        $bExcecuteCommand = false;
/*******************************************************************************************/
//      SimpleCommandDispatcher
/*******************************************************************************************/
        if (\is_readable(\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php')) {
            require (\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php');
        }
        $admin = new admin('Modules', 'module_view', FALSE, FALSE);
    // first read and validate the $_POST arguments
        $aAllowedActions = ['toggle_active_status'];
        $aArgs = [
                    'action'    => ['filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                                    'options' => ['toggle_active_status']
                    ],
                    'iRecordId' => ['filter' => FILTER_VALIDATE_INT
                    ],
        ];
        $mRequestVars = filter_input_array (INPUT_POST, $aArgs);
        $sRequestAction  = $mRequestVars['action'];
        $mIdKey = (int)$mRequestVars['iRecordId'];

        // test if action value is in allowed list of actions
        if ( !in_array($sRequestAction, $aAllowedActions)) {
            throw new \Exception('no valid "action" was set');
        }
        if (is_null($mIdKey) || ($mIdKey===0)) {
            throw new \Exception('no valid RecordId was set ');
        }
        if (!($admin->is_authenticated() && $admin->get_permission($sModuleDir, 'module'))) {
            throw new \Exception('You\'re not allowed to make changes to Module: ['.$sModuleDir.']');
        }

        switch ($sRequestAction):
            case 'toggle_active_status':
                // Check the Parameters
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_droplets` SET '
                     . '`active`= (`active` IS NOT TRUE) '
                     . 'WHERE `id`='.$mIdKey;
                if (!(bool)$database->query($sql)) {
                    throw new \Exception(sprintf('DB access fail [%s]'.PHP_EOL.'%s',$database->get_error(),$sql));
                }
                break;
            default:
                throw new \Exception('no valid "action" was set ');
                break;
        endswitch;
        $aJsonRespond['message'] = 'Activity Status successfully changed';
        $aJsonRespond['success'] = true;
//        $aJsonRespond['sIdKey']  = $oApp->getIDKEY($iIdKey);
        $aJsonRespond['sIdKey']  = $mIdKey;
    } catch (Exception $e) {
        $aJsonRespond['message'] = $e->getMessage();
        $aJsonRespond['success'] = false;
//        $aJsonRespond['sIdKey']  = $oApp->getIDKEY($iIdKey);
        $aJsonRespond['sIdKey']  = $mIdKey;
    }
    // echo the json_respond to the ajax function
    exit(json_encode($aJsonRespond));
