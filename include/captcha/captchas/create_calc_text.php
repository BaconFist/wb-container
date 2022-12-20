<?php

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;

/* -------------------------------------------------------- */
    $sAddonPath   = str_replace(['\\','//'],'/',dirname(__DIR__)).'/';
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
    $sAddonCaptchaPath = $oReg->AppPath.'modules/captcha_control/';
    if (is_readable($sAddonCaptchaPath.'languages/EN.php')) {require($sAddonCaptchaPath.'languages/EN.php');}
    if (is_readable($sAddonCaptchaPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonCaptchaPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonCaptchaPath.'languages/'.LANGUAGE.'.php')) {require($sAddonCaptchaPath.'languages/'.LANGUAGE.'.php');}
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
    mt_srand((double)microtime()*1000000);
    $n = \random_int(1,3);
    switch ($n) {
        case 1:
            $x = \random_int(1,9);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x + $y;
            $cap = ("$x {$MOD_CAPTCHA['ADDITION']} $y");
            break;
        case 2:
            $x = \random_int(10,20);
            $y = \random_int(1,9);
            $_SESSION['captcha'.$mIdKey] = $x - $y;
            $cap = ("$x {$MOD_CAPTCHA['SUBTRAKTION']} $y");
            break;
        case 3:
            $x = \random_int(2,10);
            $y = \random_int(2,5);
            $_SESSION['captcha'.$mIdKey] = $x * $y;
            $cap = ("$x {$MOD_CAPTCHA['MULTIPLIKATION']} $y");
            break;
    }

/* -------------------------------------------------------- */
//    $col = 1;
//    if(isset($_GET['col']) && is_numeric($_GET['col'])){$col = abs($_GET['col']);}
    $col = \abs(($oRequest->getParam('col') ?? '1'));
    if (!in_array($col,[0,1])){
        throw new \Exception( 'Unexepted color parameter');
    }
/* -------------------------------------------------------- */
    $BgFile     =  $sAddonPath.'bg_16.png';
    $FontFile   = ($sAddonPath.'fonts/LLBd_cond.ttf'); //  AHGBold.ttf

    $iFontsize  = 18;
    $iFontangle = 0;
    $iFontRatio = 1.0;
    $iRatio = (($iFontRatio) ? $iFontRatio : 0.4);
    if ((float)$iRatio < 0.1 || (float)$iRatio >= 1) {$iRatio = 0.4;}
    $iTextLen   = mb_strlen($cap)*($iFontsize * $iRatio);
    // Create an image
    if (is_readable($BgFile)){
        list ($width, $height, $type, $attr) = @getimagesize($BgFile);
    } else {
          $width = 140;
          $height = 40;
          $type   = 3;//RGB-= 3, fCMYK = 4.
          $attr   = 'width="140" height="40"';
    }
//    $image     = imagecreate ($width+$iTextLen, $height+4) or die ("Cannot Create image");
    if (!($image = @imagecreate ($width+$iTextLen, $height+$iFontsize))){
//    if (!($image = @ImageCreateFromPNG ($BgFile))){
        return (sprintf("Cannot Initialize new GD image stream"));
    }

    $iBgcolor   = ImageColorAllocate ($image, 255, 255, 255);

    if ($col==1){
        $iTextcolor = ImageColorAllocate ($image, 0, 0, 0);
//        \trigger_error(\sprintf('Text-Farbe %d schwarz',$col), E_USER_WARNING);
    } else {
        $iTextcolor = ImageColorAllocate ($image, 255, 255, 255);
//        \trigger_error(\sprintf('Text-Farbe %d weiss',$col), E_USER_WARNING);
    }

    $box    = imagettfbbox($iFontsize,$iFontangle,$FontFile, $cap);
    $box[2] += 8;
    $box[5]  = abs ( $box[5]-10 );
    $min_x   = min( array($box[0], $box[2], $box[4], $box[6]) );
    $max_x   = max( array($box[0], $box[2], $box[4], $box[6]) );
    $min_y   = min( array($box[1], $box[3], $box[5], $box[7]) );
    $max_y   = max( array($box[1], $box[3], $box[5], $box[7]) );
    $width   = ( $max_x - $min_x);
    $height  = ( $max_y - $min_y );
    $left    = abs( $min_x ) + $width;  //
    $top     = abs( $min_y ) + $height;  //

    $x         = $max_x;//; $width
    $y         = $max_y;//; $height

    imagestring ($image, $iFontsize, $x, $y, $cap, $iTextcolor);
    // Make the background transparent
    imagecolortransparent($image, $iBgcolor);
/* */
    // create reload-image
//    $reload = ImageCreateFromPNG(WB_PATH.'/include/captcha/reload_140_40.png'); // reload-overlay
    if (!($reload = ImageCreateFromPNG($sAddonPath.'reload_140_40.png'))){ // reload-overlay
        echo nl2br(sprintf("%s\n",$sAddonPath.'reload_140_40.png'));
        die (sprintf("Cannot Initialize new GD reload image stream"));
    }

    imagecolortransparent($reload, $iBgcolor);
    imagealphablending($reload, true);
    imagesavealpha($reload, true);
    // overlay
    $dst_x = $box[5]-15;
    $dst_y = 10;
    $src_x = $width;
    $src_y = $box[5];
    $src_w = 200;
    $src_h = 40;
    imagecopy($reload, $image, $dst_x,  $dst_x, $src_x ,$src_y, $src_w,$src_h);
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

} catch (RuntimeException  $ex) {
//    exit;

} catch (Exception $ex) {
    $sErrorMsg = Precheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
//    \trigger_error($sErrorMsg, E_USER_WARNING);
}

