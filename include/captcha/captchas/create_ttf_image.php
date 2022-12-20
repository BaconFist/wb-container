<?php

// $Id: create_ttf_image.php 334 2019-04-13 05:30:57Z Luisehahne $

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
// make random string
    if(!is_callable('randomString')) {
        function randomString($len) {
            list($usec, $sec) = explode(' ', microtime());
            mt_srand((float)$sec + ((float)$usec * 100000));
            //$possible="ABCDEFGHJKLMNPRSTUVWXYZabcdefghkmnpqrstuvwxyz23456789";
            $possible="abdfhkrsvwxz23456789";
            $str="";
            while(strlen($str)<$len) {
                $str.=substr($possible,(\random_int()%(strlen($possible))),1);
            }
            return($str);
        }
    }
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
//    $_SESSION['captcha'.$mIdKey] = '';

    $text = randomString(5); // number of characters
    $_SESSION['captcha'.$mIdKey] = $text;

    $fonts   = [];
    $t_fonts = file_list($sAddonPath.'fonts');
    foreach($t_fonts as $file) { if(preg_match('/\.ttf/',$file)) { $fonts[]=$file; } }

    $bgs     = [];
    $t_bgs   = file_list($sAddonPath.'backgrounds');
    foreach($t_bgs   as $file) { if(preg_match('/\.png/',$file)) { $bgs[]=$file; } }
    // choose a font and background
    $font = $fonts[array_rand($fonts)];
    $bg   = $bgs[array_rand($bgs)];
// get image-dimensions
    list($width, $height, $type, $attr) = @getimagesize($bg);

    if (\random_int(0,2)==0) { // 1 out of 3
        // draw each character individualy
        $image   = ImageCreateFromPNG($bg); // background image
        $grey    = \random_int(0,50);
        $color   = ImageColorAllocate($image, $grey, $grey, $grey); // font-color
        $ttf     = $font;
        $ttfsize = 20; // fontsize
        $count   = 0;
        $angle   = \random_int(-15,15);
        $x       = \random_int(10,25);
        $y       = \random_int($height-10,$height-2);
        $image_failed = true;
        do {
            for($i=0;$i<strlen($text);$i++) {
                $res   = imagettftext($image, $ttfsize, $angle, $x, $y, $color, $ttf, $text[$i]);
                $angle = \random_int(-15,15);
                $x     = \random_int($res[4],$res[4]+10);
                $y     = \random_int($height-15,$height-5);
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
            $grey = \random_int(0,50);
            $color = ImageColorAllocate($image, $grey, $grey, $grey); // font-color
            $ttf = $font;
            $ttfsize = 24; // fontsize
            $angle = \random_int(0,5);
            $x = \random_int(5,30);
            $y = \random_int($height-10,$height-2);
            $res = imagettftext($image, $ttfsize, $angle, $x, $y, $color, $ttf, $text);
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
/**/
    // create reload-image
    $reload = ImageCreateFromPNG($sAddonPath.'reload_140_40.png'); // reload-overlay
    imagealphablending($reload, TRUE);
    imagesavealpha($reload, TRUE);
    // overlay
    imagecopy($reload, $image, 0,0,0,0, 140,40);
    imagedestroy($image);
    $image = $reload;

    if(!function_exists('captcha_header')) {
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
