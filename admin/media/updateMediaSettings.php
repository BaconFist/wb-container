<?php
// Must include code to stop this file being access directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

function _unserialize($sObject) {
    $aRetval = [];
    if ($sObject){
        $_ret = preg_replace_callback(
                        '!s:(\d+):"(.*?)";!',
                        function($matches) {return 's:'.strlen($matches[2]).':"'.$matches[2].'";';},
                        $sObject
                 );
        if ($_ret) {$aRetval = unserialize($_ret);}
    }
    return $aRetval;
}

function updateMediaSettings($database){
    if (
        defined('MEDIASETTINGS') &&
        trim(MEDIASETTINGS) != '' &&
            (version_compare(MEDIA_VERSION, VERSION, '<') || preg_match('/dev/', VERSION))
    ) {
        $aOldSettings = _unserialize(MEDIASETTINGS);
        $aNewSettings = [];
        array_walk(
            $aOldSettings,
            function (& $aValue, $sKey) use (&$aNewSettings) {
                $aNewSettings[str_replace('_'.trim(MEDIA_DIRECTORY,'/'), '', $sKey)] = $aValue;
            }
        );
        $sValueToSave = serialize($aNewSettings);
        db_update_key_value('settings', 'mediasettings', $sValueToSave);
        db_update_key_value('settings', 'media_version', VERSION);
        unset($sValueToSave, $aOldSettings, $aNewSettings, $sql);
        $bRetval = true;
    }
    return (isset($bRetval) ? $bRetval : false);
}
/* end of script */
