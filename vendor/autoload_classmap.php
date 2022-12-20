<?php
declare(strict_types=1);

    $sVendorDir = 'include';
    $sOrgVendorDir = 'vendor';
    $aClassMap  = [];
    $aClassMap['default']  = [
        'dispatch\\' => $sVendorDir.'/system/dispatcher/src',
        'Ifsnop\\Mysqldump\\' => $sVendorDir.'/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump',
        'Securimage'  => $sVendorDir.'/captcha',
        'Securimage\\StorageAdapter'  => $sVendorDir.'/captcha/StorageAdapter',
    ];

/* ---------------------------------------------------------------------------- */
return $aClassMap;
