<?php
    $sAddonPath   = rtrim(str_replace('\\','/',__DIR__),'/').'/';
    $sModulesPath = ($sAddonPath).'/'; // \dirname
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

    phpinfo();
