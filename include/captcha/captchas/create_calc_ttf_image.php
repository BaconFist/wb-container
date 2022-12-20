<?php

// $Id: create_calc_ttf_image.php 334 2019-04-13 05:30:57Z Luisehahne $

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
/* -------------------------------------------------------- */
    $sGetOldSecureToken = (SecureTokens::checkFTAN());
    $aFtan = SecureTokens::getFTAN();
    $sFtanQuery = $aFtan['name'].'='.$aFtan['value'];
    if (!$sGetOldSecureToken){
        $sMessage = sprintf("%s\n",$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
//        throw new \Exception($sMessage);
    }
/* -------------------------------------------------------- */
    if (!is_callable('display_captcha_real')) {require ($sAddonPath.'captcha.php');}
    $mIdKey = $oApp->StripCodeFromText(($oRequest->getParam('captchaId') ?? 0));
    $_SESSION['captcha'.$mIdKey] = '';
    mt_srand((double)microtime()*1000000);
    $n = \random_int(1,3);
    switch ($n) {
        case 1:
            $x = \random_int(1,9);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x + $y;
            $text = ("$x+$y");
            break;
        case 2:
            $x = \random_int(10,20);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x - $y;
            $text = ("$x-$y");
            break;
        case 3:
            $x = \random_int(2,10);
            $y = \random_int(2,5);
            $_SESSION['captcha'.$mIdKey] = $x * $y;
            $text = ("$x*$y");
            break;
    }

// get lists of fonts and backgrounds
    $fonts   = [];
    $bgs     = [];
    $t_fonts = file_list($sAddonPath.'fonts');
    $t_bgs   = file_list($sAddonPath.'backgrounds');
    foreach($t_fonts as $file) { if(preg_match('/\.ttf/',$file)) { $fonts[]=$file; } }
    foreach($t_bgs as $file) { if(preg_match('/\.png/',$file)) { $bgs[]=$file; } }
//unset($_SESSION['captcha_time']);        // otherwise there can't be 2 captchas on one page!
//    $text = $cap;
    // choose a font and background
    $font = $fonts[array_rand($fonts)];
    $bg = $bgs[array_rand($bgs)];
    // get image-dimensions
    list($width, $height, $type, $attr) = @getimagesize($bg);
    if(\random_int(0,2)==0) { // 1 out of 3
        // draw each character individualy
        $image     = ImageCreateFromPNG($bg); // background image
        $grey      = \random_int(0,50);
        $color     = ImageColorAllocate($image, $grey, $grey, $grey); // font-color
        $textcolor = imagecolorallocate($image, 0x30, 0x30, 0x30);
        $ttf       = $font;
        $ttfsize   = 24; // fontsize
        $count     = 0;
        $angle     = \random_int(-10,10);
        $x         = \random_int(20,35);
        $y         = \random_int($height-10,$height-2);
        $image_failed = true;
        do {
            for($i=0;$i<mb_strlen($text);$i++) {
                $res   = imagettftext($image, $ttfsize, $angle, $x, $y, $textcolor, $ttf, $text[$i]);
                $angle = \random_int(-10,10);
                $x     = \random_int($res[4],$res[4]+10);
                $y     = \random_int($height-12,$height-7);
            }
            if($res[4] > $width) {
                $image_failed = true;
            } else {
                $image_failed = false;
            }
            if(++$count > 4) // too many tries! Use the image
                break;
        } while($image_failed);
    } else {
        // draw whole string at once
        $image_failed = true;
        $count=0;
        do {
            $image = ImageCreateFromPNG($bg); // background image
            $grey  = \random_int(0,50);
            $color = ImageColorAllocate($image, $grey, $grey, $grey); // font-color
            $ttf   = $font;
            $ttfsize = 32; // fontsize
            $angle = \random_int(0,5);
            $x     = \random_int(20,35);
            $y     = \random_int($height-10,$height-2);
            $res   = imagettftext($image, $ttfsize, $angle, $x, $y, $color, $ttf, $text);
            // check if text fits into the image
            if(($res[0]>0 && $res[0]<$width) && ($res[1]>0 && $res[1]<$height) &&
                 ($res[2]>0 && $res[2]<$width) && ($res[3]>0 && $res[3]<$height) &&
                 ($res[4]>0 && $res[4]<$width) && ($res[5]>0 && $res[5]<$height) &&
                 ($res[6]>0 && $res[6]<$width) && ($res[7]>0 && $res[7]<$height)
            ) {
                $image_failed = false;
            }
            if(++$count > 4) // too many tries! Use the image
                break;
        } while($image_failed);
    }
/* */
    // create reload-image
    $reload = ImageCreateFromPNG($sAddonPath.'reload_140_40.png'); // reload-overlay
    imagealphablending($reload, TRUE);
    imagesavealpha($reload, TRUE);
    // overlay
    imagecopy($reload, $image, 0,0,0,0, 140,40);
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

