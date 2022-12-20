<?php

//declare(strict_types=1, encoding='UTF-8');

use bin\Exceptions\ErrorHandler;


// this part helps to prevent direct access
    $sAddonPath   = rtrim(str_replace('\\','/',__DIR__),'/').'/';
    $sModulesPath = \dirname($sAddonPath).'/'; //
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );
    if (! defined('SYSTEM_RUN') && is_readable($sAppPath.'config.php')) {
        require($sAppPath.'config.php');
    } else {
        \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; ob_flush();flush(); exit;
    }
    $admin = new admin('admintools','admintools_advanced',false,true);
    if (!$admin->is_authenticated() || !$admin->ami_group_member('1')){
        throw new \RuntimeException('Illegal file access!');
    }


/**
 * LoadErrorlog.php
 */
/*
    $sAppPath = (\dirname(\dirname(__DIR__)));
    if (\is_readable($sAppPath.'/config.php')) {require ($sAppPath.'/config.php');}
//    if (!\class_exists('admin')) {require (WB_PATH.'/framework/class.admin.php');}
*/
//    // An associative array that by default contains the contents of $_GET, $_POST and $_COOKIE.
//    $oRequest = (object) \filter_input_array (
//                (\strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST' ? \INPUT_POST : \INPUT_GET), \FILTER_UNSAFE_RAW
//    );
    $sErrorlogFile = ErrorHandler::getLogFile();
    $sErrorlogUrl  = \str_replace(WB_PATH, WB_URL, $sErrorlogFile);

    $aJsonRespond['url'] = $sErrorlogUrl;
    // initialize json_respond array  (will be sent back)
    $aJsonRespond = [];
    $aJsonRespond['content'] = [];
    $aJsonRespond['message'] = 'Load operation failed';
    $aJsonRespond['success'] = false;
    $admin = new admin('##skip##', false, false);
    if (!(int)$admin->ami_group_member('1')){
        $aJsonRespond['message'] = 'Access denied';
        exit(\json_encode($aJsonRespond));
    }
    if (!($aJsonRespond['content'] = \file($sErrorlogFile, \FILE_SKIP_EMPTY_LINES|\FILE_IGNORE_NEW_LINES))){
        exit(\json_encode($aJsonRespond));
    }
    $output = \implode('<br />',$aJsonRespond['content']);
    // If the script is still running, set success to true
    $aJsonRespond['success'] = 'true';
// and echo the answer as json to the ajax function
    $output = stripcslashes (\json_encode ($output, \JSON_PRETTY_PRINT|\JSON_INVALID_UTF8_IGNORE));
//    $output = \json_encode ($output, \JSON_PRETTY_PRINT|\JSON_UNESCAPED_SLASHES|\JSON_INVALID_UTF8_IGNORE);

    echo ($output); //\stripslashes

