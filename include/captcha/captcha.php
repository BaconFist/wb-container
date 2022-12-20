<?php


use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};
use bin\requester\HttpRequester;

// Make sure page cannot be accessed directly
    if (!defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit; }

/* ----------------------------------------------------- */
/* start this deprecated section for backward compabilty */
/* ----------------------------------------------------- */
// check if module language file exists for the language set by the user (e.g. DE, EN)
// no module language file exists for the language set by the user, include default module language file EN.php

    $sAbsAddonPath = WB_PATH.'/modules/captcha_control/';
    if (is_readable($sAbsAddonPath.'/languages/EN.php')) {require($sAbsAddonPath.'/languages/EN.php');}
    if (is_readable($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAbsAddonPath.'/languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'/languages/'.LANGUAGE.'.php');}

//    global $MOD_CAPTCHA,$MOD_CAPTCHA_CONTROL,$mIdKey;

// output-handler for image-captchas to determine size of image
    if(!is_callable('captcha_header')) {
        function captcha_header() {
            header("Expires: Mon, 1 Jan 1990 05:00:00 GMT");
            header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate");
            header("Pragma: no-cache");
            header("Content-type: image/png");
            return;
        }
    }

// displays the image or text inside an <iframe>
    if (!is_callable('display_captcha_real')) {
        function display_captcha_real($kind='image') {
/* do nothing function only for backward compability*/
        }
    }

//  get list of available CAPTCHAS for the dropdown-listbox in deprecated admin-tools
    if (extension_loaded('gd') && is_callable('imagepng') && is_callable('imagettftext')) {
        $useable_captchas = [
            'Securimage'     => 'Securimage Captcha',
            'calc_text'      => $MOD_CAPTCHA_CONTROL['CALC_TEXT'],
            'calc_image'     => $MOD_CAPTCHA_CONTROL['CALC_IMAGE'],
            'calc_ttf_image' => $MOD_CAPTCHA_CONTROL['CALC_TTF_IMAGE'],
            'ttf_image'      => $MOD_CAPTCHA_CONTROL['TTF_IMAGE'],
//            'old_image'      => $MOD_CAPTCHA_CONTROL['OLD_IMAGE'],
            'text'           => $MOD_CAPTCHA_CONTROL['TEXT']
        ];
    } elseif(extension_loaded('gd') && is_callable('imagepng')) {
        $useable_captchas = [
            'Securimage'     => 'Securimage Captcha',
            'calc_text'      => $MOD_CAPTCHA_CONTROL['CALC_TEXT'],
            'calc_image'     => $MOD_CAPTCHA_CONTROL['CALC_IMAGE'],
//            'old_image'      => $MOD_CAPTCHA_CONTROL['OLD_IMAGE'],
            'text'           => $MOD_CAPTCHA_CONTROL['TEXT']
        ];
    } else {
        $useable_captchas = [
            'Securimage'     => 'Securimage Captcha',
            'calc_text'      => $MOD_CAPTCHA_CONTROL['CALC_TEXT'],
            'text'           => $MOD_CAPTCHA_CONTROL['TEXT']
        ];
    }

/* ----------------------------------------------------- */
/*  end this deprecated section for backward compabilty  */
/* ----------------------------------------------------- */
    /**
     * Validate a captcha code input against a captcha ID
     *
     * @param string $sInput      The captcha value supplied by the user
     * @param string $sNamespace  The captcha namespace used for having multiple captchas on a page or
     *                            to separate captchas from differen forms on your site.
     * @return bool   true if the code was valid for the given captcha ID, false if not
     */
    if (!is_callable('checkCaptcha')) {
        function checkCaptcha($sInput='',$sNamespace='default'){
            $bRetval = false;
            $sCaptcha = [
            'code_disp'   => $_SESSION['securimage_code_disp'][$sNamespace],
            'code_value'  => $_SESSION['securimage_code_value'][$sNamespace],
            'captcha_req' => ((!empty($sInput)) ? $sInput : ''),
            ];
            $bRetval = $sCaptcha['code_value'] == $sCaptcha['captcha_req'];
            return $bRetval;
        }
    }
    if (!is_callable('call_captcha')) {
        function call_captcha($action='all',$style='',$mIdKey=null,$bPrint=true, $col=1) {
            $oReg = WbAdaptor::getInstance();
            $sAbsAddonPath = $oReg->AppPath.'modules/captcha_control/';
            if (is_readable($sAbsAddonPath.'languages/EN.php')) {require($sAbsAddonPath.'languages/EN.php');}
            if (is_readable($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
            if (is_readable($sAbsAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAbsAddonPath.'languages/'.LANGUAGE.'.php');}
            $t = time();
            $sSecId = is_null($mIdKey) ? '' : $mIdKey;
            $sSecKeyId = $sSecId;
/*          future use
            $sSecKeyId = \bin\SecureTokens::getIDKEY($sSecId);
            \trigger_error(sprintf('[%d] id (%s) getIDKEY %s',__LINE__,$sSecId,$sSecKeyId), E_USER_NOTICE);
*/
            $sAddonPath = str_replace('\\','/',__DIR__).'/';

            $namespace  = 'captcha'.$sSecId;
            $CaptchaUrl = $oReg->AppUrl. 'include/captcha';
            $_SESSION[$namespace] = 'no value';
            $_SESSION['captcha_task'.$sSecId] = '';
            $_SESSION['captcha_time'.$sSecId] = $t;
            $_SESSION['captchaId'] = $sSecId;
            $sCaptchaType = CAPTCHA_TYPE;
            $sCaptchaId   = $sCaptchaType.$sSecId;
            $sCaptchaDir  = '/include/captcha';
            $retVal = '';
            $aRetVal = [];
            // get width and height of captcha image for use in <iframe>
            $sIFrameStyle = ' style="overflow:hidden;border: 1px solid #999;"';
            ob_start();
            $captcha_width  = 250;
            $captcha_height = 54;
            $sRefresh = '<i id="refresh'.$sSecId.'" class="fas fa-sync-alt w3-text-green w3-xlarge"></i>';
            if ($action=='all') {
                ob_start();   //  add the inner buffering envelope
                switch ($sCaptchaType) :
                    case 'text': // text-captcha
?>
                          <div class="captcha_table" style="width: 100%; margin-top: 0.825em;" >
                            <div class="text_captcha <?php echo $sCaptchaType;?>" >
                            <?php include($sAddonPath.'captchas/'.$sCaptchaType.'.php'); ?></div>
                            <div style="width:100%"><input type="text" name="<?php echo $namespace;?>" maxlength="50"  /></div>
                            <div class="captcha_expl" ><?php echo $MOD_CAPTCHA['VERIFICATION_INFO_QUEST']; ?></div>
                          </div>

<?php
                          break;
                      case 'calc_text': // calculation as text
?>
                          <div class="captcha_table" style="width: 100%; margin-top: 0.825em;clear: both;" >
                              <label class="captcha-code" style="cursor: pointer;">
                                <img width="160" class="image-captcha <?php echo $sCaptchaType;?>" id="<?php echo $sCaptchaId;?>" alt="<?= $sCaptchaType;?>" src="<?php echo $CaptchaUrl;?>/captchas/create_<?= $sCaptchaType;?>.php?captchaId=<?php echo $sSecKeyId;?>&col=<?= $col;?>" />
                                <img class="image-reload" id="refresh<?php echo $sSecId;?>" height="24" width="24" src="<?php echo $CaptchaUrl;?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" style="border: 0px; vertical-align: middle"/>
                                <input class="core-required" type="text" name="<?php echo $namespace;?>" id="captcha-code" placeholder="<?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?>" autocomplete="off" style="height:32px;"/>
                              </label>
                          </div>
<?php
                          break;
                      case 'calc_image': // calculation with image (old captcha)
                      case 'calc_ttf_image': // calculation with varying background and ttf-font
?>
                          <div class="captcha_table" style="display:grid; width: 100%; margin-top: 0.825em;clear: both;" >
                              <label class="captcha-code" style="cursor: pointer;">
                                <img width="160" class="image-captcha <?php echo $sCaptchaType;?>" id="<?php echo $sCaptchaId;?>" alt="<?php echo $sCaptchaType;?>" src="<?php echo $CaptchaUrl;?>/captchas/create_<?= $sCaptchaType;?>.php?captchaId=<?php echo $sSecKeyId;?>&col=<?= $col;?>" />
                                <img class="image-reload" id="refresh<?php echo $sSecId;?>" height="24" width="24" src="<?php echo $CaptchaUrl;?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" style="border: 0px; vertical-align: middle"/>
                                <input class="core-required" type="text" name="<?php echo $namespace;?>" id="captcha-code" placeholder="<?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?>" autocomplete="off" style="height: 32px;"/>
                              </label>
                          </div>
<?php
                          break;
                      // normal images
                      case 'old_image': // old captcha
                          break;
                      case 'ttf_image': // captcha with varying background and ttf-font
//                        $sIframeUrl = WB_URL . '/include/captcha/captcha.php?display_captcha_X986E21=1&amp;s='.$mIdKey;
?>
                          <div class="captcha_table" style="width: 100%; margin-top: 0.825em;clear: both;" >
                              <label class="captcha-code" style="cursor: pointer;">
                                <img width="160" class="image-captcha <?php echo $sCaptchaType;?>" id="<?php echo $sCaptchaId;?>" alt="<?php echo $sCaptchaType;?>" src="<?php echo $CaptchaUrl;?>/captchas/create_<?= $sCaptchaType;?>.php?captchaId=<?php echo $sSecKeyId;?>" />
                                <img class="image-reload" id="refresh<?php echo $sSecId;?>" height="24" width="24" src="<?php echo $CaptchaUrl;?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" style="border: 0px; vertical-align: middle"/>
                                <input class="core-required" type="text" name="<?php echo $namespace;?>" id="captcha-code" autocomplete="off" placeholder="<?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?>" style="height: 32px;"/>
                              </label>
                          </div>

<?php
                          break;
                      default:
?>
                          <div class="captcha_table" style="width: 100%; margin-top: 0.825em;clear: both;" >
                              <label class="captcha-code" id="Secur-image" style="cursor: pointer;">
                                <img class="image-captcha <?php echo $sCaptchaType;?>" id="<?php echo $sCaptchaId;?>" alt="<?php echo $sCaptchaType;?>" src="<?php echo $CaptchaUrl;?>/securimage_show.php?captchaId=<?php echo $sSecKeyId;?>" />
                                <img class="image-reload" id="refresh<?php echo $sSecId;?>" height="24" width="24" src="<?php echo $CaptchaUrl;?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" style="border: 0px; vertical-align: baseline"/>
                                <input class="core-required" type="text" name="<?php echo $namespace;?>" id="captcha-code" placeholder="<?php echo $MOD_CAPTCHA['VERIFICATION_INFO_RES']; ?>" autocomplete="off" style="height: 32px;"/>
                                <span></span>
                              </label>
                          </div>
<?php
                endswitch;

            } //  end $action
            ob_end_flush();  //  closing the inner envelope will activate URL rewriting
?>
    <script>

        var url=WB_REL+'/include/assets/w3-css/fontawesome.css';
        var NodeList = window.document.head.querySelectorAll('HEAD LINK[rel=stylesheet]');
        var css = document.createElement('LINK');
        len = 0;
        css.setAttribute('rel', 'stylesheet');
//        css.setAttribute('type', 'text/css');
        css.setAttribute('href', url);
        css.setAttribute('media', 'all');
        if (NodeList) {
          len = NodeList.length - 1;
        };
        // insert after last link element if exist otherwise before first script
        if (len > - 1) {
          node = NodeList[len];
//console.info( node );
        //  console.info(node);
          //    return false;
          node.parentNode.insertBefore(css, node.nextSibling);
          // console.info('CSS ' + url);
        } else {
          node = window.document.head.querySelectorAll('SCRIPT') [0];
          node.parentNode.insertBefore(css, node);
        }

        refresh = "refresh<?php echo $sSecId;?>";
        el = document.getElementById(refresh);
        if (el){
          el.addEventListener("click", function(){
              captcha = "<?php echo $sCaptchaId;?>";
              url = document.getElementById(captcha).src;
//              var newURL = new URL(url);
              const pathname = url.split('&');
              const newUrl = pathname[0]+"&amp;"+pathname[1];
//console.log(pathname.pop());
//console.log(captcha);
//console.log(pathname);
//console.log(newUrl);
              document.getElementById(captcha).src=newUrl+"&amp;"+Math.random();
          }, false);
        }
//console.log(el);
    </script>
<?php
            $aRetVal['content'] = ob_get_clean();
            $aRetVal['display'] = $_SESSION['captcha_task'.$sSecId];
            $aRetVal['code']    = $_SESSION[$namespace];
            if ($bPrint){echo $aRetVal['content'];} else {return $aRetVal['content'];}
        } //  end function call_captcha
    } //  end !function_exists
