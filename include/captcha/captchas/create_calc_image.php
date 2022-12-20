<?php

// $Id: create_calc_image.php 334 2019-04-13 05:30:57Z Luisehahne $

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;

/* -------------------------------------------------------- */
    $sAddonPath   = str_replace('\\','/',dirname(__DIR__)).'/';
    $sModulesPath = \dirname($sAddonPath).'/';
    $sModuleName  = basename($sModulesPath);
    $sAddonName   = basename($sAddonPath);
    $ModuleRel    = ''.$sModuleName.'/';
    $sAddonRel    = ''.$sModuleName.'/'.$sAddonName.'/';
    $sPattern     = "/^(.*?\/)".$sModuleName."\/.*$/";
    $sAppPath     = preg_replace ($sPattern, "$1", $sModulesPath, 1 );

    if (!defined('SYSTEM_RUN') ){ require($sAppPath.'config.php' ); }
/* -------------------------------------------------------- */

try {
    $admin    = new admin('##skip##', false,false);
    $oReg     = WbAdaptor::getInstance();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();
    if(!is_callable('display_captcha_real')) {require ($sAddonPath.'captcha.php');}
/* -------------------------------------------------------- */
    $sGetOldSecureToken = (SecureTokens::checkFTAN());
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
    if (!$sGetOldSecureToken){
        $sMessage = sprintf("%s\n",$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
//        throw new \Exception($sMessage);
    }
/* -------------------------------------------------------- */
    $mIdKey = $oApp->StripCodeFromText(($oRequest->getParam('captchaId') ?? 0));
    $_SESSION['captcha'.$mIdKey] = '';
    mt_srand((int)microtime()*1000000);
    $n = \random_int(1,3);
    switch ($n) {
        case 1:
            $x = \random_int(1,9);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x + $y;
            $cap = ("$x+$y");
            break;
        case 2:
            $x = \random_int(10,20);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x - $y;
            $cap = ("$x-$y");
            break;
        case 3:
            $x = \random_int(2,10);
            $y = \random_int(2,5);
            $_SESSION['captcha'.$mIdKey] = $x * $y;
            $cap = ("$x*$y");
            break;
    }
/* -------------------------------------------------------- */
    $BgFile     =  $sAddonPath.'bg_16.png';
    $FontFile   = ($sAddonPath.'fonts/LLBd_cond.ttf'); //  AHGBold.ttf
    if (is_readable($BgFile)){
        list ($width, $height, $type, $attr) = @getimagesize($BgFile);
    } else {
          $width = 140;
          $height = 40;
          $type   = 3;//RGB-= 3, fCMYK = 4.
          $attr   = 'width="140" height="40"';
    }

    $image     = imagecreate($width, $height);
    $white     = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
    $gray      = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
    $textcolor = imagecolorallocate($image, 0x30, 0x30, 0x30);

    for($i = 0; $i < 30; $i++) {
        $x1 = \random_int(0,150);
        $y1 = \random_int(0,30);
        $x2 = \random_int(0,150);
        $y2 = \random_int(0,30);
        imageline($image, $x1, $y1, $x2, $y2 , $gray);
    }

    $Fontfile = ($sAddonPath.'fonts/LLBd_cond.ttf');
    $angle    = 0;
    $ttfsize  = 20; // fontsize
    $x        = 18;
    $l        = strlen($cap);
    for($i = 0; $i < $l; $i++) {
        $x = $x + \random_int(10 , 25);
        $y = \random_int(18, 25);
        $angle = \random_int(-2, 2);
        $res = imagettftext ($image, $ttfsize, $angle, $x, $y, $textcolor, $Fontfile, substr($cap, $i, 1) );
    //    $fnt = \random_int(5,7);
    //    imagestring($image, $fnt, $x, $y, substr($cap, $i, 1), $textcolor);
    }
/* */
    // create reload-image
    $reload    = ImageCreateFromPNG(WB_PATH.'/include/captcha/reload_140_40.png'); // reload-overlay
    imagealphablending($reload, true);
    imagesavealpha($reload, true);
    // overlay
    imagecopy($reload, $image, 0,0,0,0, 160,80);
    imagedestroy($image);
    $image = $reload;

    if(!is_callable('captcha_header')) {
        throw new \Exception( 'Can\'t call function captcha_header');
    }
    if (!headers_sent() && (strlen((string)ob_get_contents()) == 0))
    {
        ob_start();
        captcha_header();
        imagepng($image);
        header("Content-Length: ".ob_get_length());
        ob_get_flush();
    }
    imagedestroy($image);
} catch (Exception $e) {
    echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
}

