<?php
/**
 *
 * @category        modules
 * @package         captcha_control
 * @author          WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 7.4.x and higher
 * @version         $Id: tool.php 331 2019-04-04 09:04:05Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/captcha_control/tool.php $
 * @lastmodified    $Date: 2019-04-04 11:04:05 +0200 (Do, 04. Apr 2019) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $sAddonName = \basename(__DIR__);
    $bExcecuteCommand = false;
/*******************************************************************************************/
//      SimpleCommandDispatcher
/*******************************************************************************************/
    if (\is_readable(\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php')) {
        require (\dirname(__DIR__).'/SimpleCommandDispatcher.inc.php');
    }
//  backward compatible vars
    $sAddonsPath = basename(__DIR__);
    $sModulName = $sAddonsPath;
    $sAddonName = basename(__DIR__);
    $sAddonRel  = str_replace(WB_PATH,'',__DIR__);
    $sAddonUrl  = WB_URL.str_replace(['\\'],['/'],$sAddonRel);
    $sAddonPath = str_replace(['\\'],['/'],WB_PATH.'/'.$sAddonRel);
    $sCaptchaPath = $oReg->AppPath.'include/captcha/';
// check if module language file exists for the language set by the user (e.g. DE, EN)
    if (is_readable(__DIR__.'/languages/EN.php')) {require(__DIR__.'/languages/EN.php');}
    if (is_readable(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php')) {require(__DIR__.'/languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable(__DIR__.'/languages/'.LANGUAGE.'.php')) {require(__DIR__.'/languages/'.LANGUAGE.'.php');}

    $oTrans = Translate::getInstance();
    $oTrans->enableAddon('modules\\'.$sAddonsPath);

    $sActionUrl = ADMIN_URL.'/admintools/tool.php';
    $ToolQuery  = '?tool='.$sAddonName;
    $ToolRel    = '/admintools/tool.php'.$ToolQuery;
    $js_back    = $sActionUrl;
    $ToolUrl    = $sActionUrl.'?tool='.$sAddonName;
    $sAdminToolRel = ADMIN_DIRECTORY.'/admintools/index.php';
    $sAdminToolUrl = $oReg->AcpUrl.$sAdminToolRel;
    if (!$oApp->get_permission($sModulName,'module' ) ) {
        echo $oApp->format_message($MESSAGE['ADMIN_INSUFFICIENT_PRIVELLIGES'],'error', $js_back);
    }
    $sForbiddenFileTypes  = \preg_replace( '/\s*[,;\|#]\s*/','|',RENAME_FILES_ON_UPLOAD);
    $aDefaults = [
        'enabled_captcha'=>'0', // registrierung = enabled_signup
        'enabled_asp'=>'1',
        'captcha_type'=>'Securimage',
        'asp_session_min_age'=>'20',
        'asp_view_min_age'=>'10',
        'asp_input_min_age'=>'5',
        'ct_text'=>'',
        'ct_color'=>'1',
        'use_sec_typ'=>'1',
        'code_length'=>'-1',
        'image_width'=>'200',
        'image_height'=>'80',
        'num_lines'=>'3',
        'noise_level'=>'5',
        'captcha_expiration'=>'900',

        'enabled_signup'=>'0',
        'enabled_loginform'=>'0',
        'enabled_lostpassword'=>'0',

        'image_bg_dir'=>'',
        'image_bg_color'=>'F2F2F2',
        'ttf_file'=>'LLBi_cond.tt',
        'text_color'=>'7D7D7D',
        'line_color'=>'7D7D7D',
        'noise_color'=>'7D7D7D',
        'signature_color'=>'777777',
        'image_signature'=>'',
    ];

    $table = TABLE_PREFIX.'mod_captcha_control';
//  connect to database and read out captcha settings
    if ($oCaptchas = $database->query("SELECT * FROM `$table` ")) {
        $aCaptchas = $oCaptchas->fetchAssoc();
        $aDefaults = array_merge($aDefaults,$aCaptchas);
    }

// check if data was submitted
    if ($doSave) {
        if (!$oApp->checkFTAN()){
        // show title if not function 'save' is requested
            if(!$admin_header) { $oApp->print_header(); }
         // show title if not function 'save' is requested
            print '<h4 style="margin:0!important;font-size:1.25em!important;"><a href="'.ADMIN_URL.'/admintools/index.php" '.
                  'title="'.$HEADING['ADMINISTRATION_TOOLS'].'">'.
                  $HEADING['ADMINISTRATION_TOOLS'].'</a>'.
                  '&nbsp;&raquo;&nbsp;'.$toolName.'</h4>'."\n";
            echo $oApp->format_message($MESSAGE['GENERIC_SECURITY_ACCESS'],'error', $js_back );
            exit;
        }

        $sRequestNames = $oRequest->getParamNames();
        $sInputs = [];
        foreach ($sRequestNames as $item) {
            $aInputs[$item] = $oRequest->getParam($item);
        }
        $aTemp = array_merge($aDefaults,$aInputs);
        extract($aTemp);
//print '<pre  class="mod-pre" style="margin-left:30px;">function <span>'.__FUNCTION__.'( '.''.' );</span>  filename: <span>'.basename(__FILE__).'</span>  line: '.__LINE__.' -> '."\n";
//print_r( [$aDefaults,$sInputs] ); print '</pre>'; \flush (); //  sleep(10); die();

    //  set flags for $enabled_captcha
        $enabled_captcha = $enabled_signup |= $enabled_loginform |= $enabled_lostpassword;
    //  update database settings
        $sql_ct_text = '';
        if ($captcha_type == 'text') { // ct_text
/* TODO
//            $ct_text = isset($_POST['text_qa']) ? $_POST['ct_text'] : 'calc_text';
            if (!preg_match('/### .*? ###/isU', $ct_text)) {
                $sql_ct_text = ',`ct_text` = \''.$database->escapeString($$ct_text).'\' ';
            }
*/
        }

        $sqlSet  = '
              `'.TABLE_PREFIX.'mod_captcha_control` SET
               `enabled_captcha` = '.$database->escapeString($enabled_captcha).'
              ,`enabled_asp` = '.$database->escapeString($enabled_asp).'
              ,`captcha_type` = \''.$database->escapeString($captcha_type).'\'
              ,`ct_text` = \''.$database->escapeString($ct_text).'\'
              ,`ct_color` = '.(int)$ct_color.'
              ,`use_sec_type`  = '.(int)$use_sec_type.'
              ,`code_length`  = '.(int)$code_length.'
              ,`image_width`  = '.(int)$image_width.'
              ,`image_height` = '.(int)$image_height.'
              ,`num_lines`    = '.(int)$num_lines.'
              ,`noise_level`    = '.(int)$noise_level.'
              ,`captcha_expiration` = '.(int)$captcha_expiration.'
              ,`image_bg_dir` = \''.$database->escapeString($image_bg_dir).'\'
              ,`image_bg_color` = \''.$database->escapeString($image_bg_color).'\'
              ,`ttf_file` = \''.$database->escapeString($ttf_file).'\'
              ,`text_color` = \''.$database->escapeString($text_color).'\'
              ,`line_color` = \''.$database->escapeString($line_color).'\'
              ,`noise_color` = \''.$database->escapeString($noise_color).'\'
              ,`signature_color` = \''.$database->escapeString($signature_color).'\'
              ,`image_signature` = \''.$database->escapeString($image_signature).'\' ';
//  look if INSERT INTO or UPDATE
        $sql = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'mod_captcha_control`';
        if ($database->get_one($sql)) {
        // if matching record already exists run UPDATE
            $sql  = 'UPDATE '.$sqlSet;
        } else {
        // if no matching record exists INSERT new record
            $sql = 'INSERT INTO '.$sqlSet;
        }

        // check if there is a database error, otherwise say successful
        if (!$database->query($sql)){
            $aErrorMessage[] = $database->get_error();
        }

    //  show title if not function 'save' is requested
        if (!$admin_header) {$oApp->print_header();}
        echo '<h4 style="margin:0!important;font-size:1.25em!important;"><a href="'.$sAdminToolUrl.'" '.
              'title="'.$HEADING['ADMINISTRATION_TOOLS'].'">'.
              $HEADING['ADMINISTRATION_TOOLS'].'</a>'.
              '&nbsp;&raquo;&nbsp;'.$toolName.'</h4>'."\n";
        if ($database->is_error()) {
            $sMessage = nl2br(sprintf("%s\n",$database->get_error()));
            echo $oApp->format_message($sMessage,'error', $ToolUrl);
//            exit;
        } else {
            $sMessage = nl2br(sprintf("%s%s\n",$database->get_error(),$MESSAGE['PAGES_SAVED']));
            echo $oApp->format_message($sMessage,'ok', $ToolUrl);
        }

    } //  end doSave
//  include captcha-file
    if (!function_exists('call_captcha')){require($sCaptchaPath.'captcha.php');}

    // load text-captchas
/* TODO as extern help dialogbox
    $ct_text='';
    if ($query = $database->query("SELECT `ct_text` FROM `$table`")) {
        $data = $query->fetchRow(MYSQLI_ASSOC);
        $text_qa = $data['ct_text'];
    }
    if ($ct_text == ''){
        $ct_text = $MOD_CAPTCHA_CONTROL['CAPTCHA_TEXT_DESC'];
    }
*/
//  connect to database and read out captcha settings
    if ($oCaptchas = $database->query("SELECT * FROM `$table` ")) {
        $aCaptchas = $oCaptchas->fetchAssoc();
        extract($aCaptchas);
    } else {
        // something went wrong, use dummy value
        extract($aDefaults);
    }
    $sDisplayOldNone    = (($captcha_type === 'calc_text') ? '' : 'display:none;');
    $sDisplayTextNone   = (($captcha_type === 'text') ? '' : 'display:none;');
    $sDisplaySecureNone = (($captcha_type === 'Securimage') ? '' : 'display:none;');
//    $image_signature    = ($image_signature ?? '');

    $aUseSecType = [
        ['value' => -1, 'caption' => $MOD_CAPTCHA_CONTROL['CAPTCHA_RAND']],
        ['value' =>  0, 'caption' => $MOD_CAPTCHA_CONTROL['CAPTCHA_STRING']],
        ['value' =>  1, 'caption' => $MOD_CAPTCHA_CONTROL['CAPTCHA_MATHEMATIC']],
        ['value' =>  2, 'caption' => $MOD_CAPTCHA_CONTROL['CAPTCHA_WORDS']],
    ];

    $aCodeLength = [
        ['value' =>  3, 'caption' => '3'],
        ['value' =>  4, 'caption' => '4'],
        ['value' =>  5, 'caption' => '5'],
        ['value' =>  6, 'caption' => '6'],
        ['value' =>  7, 'caption' => '7'],
        ['value' =>  8, 'caption' => '8'],
    ];
    $aNoiseLevel = [
        ['value' =>  1, 'caption' => '1'],
        ['value' =>  2, 'caption' => '2'],
        ['value' =>  3, 'caption' => '3'],
        ['value' =>  4, 'caption' => '4'],
        ['value' =>  5, 'caption' => '5'],
        ['value' =>  6, 'caption' => '6'],
        ['value' =>  7, 'caption' => '7'],
        ['value' =>  8, 'caption' => '8'],
        ['value' =>  9, 'caption' => '9'],
        ['value' =>  10, 'caption' => '10'],
    ];
    $bSignupChecked = $oApp->bit_isset($enabled_captcha,1);
    $bLoginChecked  = $oApp->bit_isset($enabled_captcha,2);
    $bLostPWChecked = $oApp->bit_isset($enabled_captcha,4);
    $aPathFiles = glob($oReg->AppPath.'include/captcha/fonts/*.ttf',GLOB_MARK|GLOB_NOSORT );
    $aFontFiles = [];
    foreach ($aPathFiles as $item){
      $value = str_replace($sCaptchaPath,'',$item);
      if (\preg_match('#^.*?\.('.$sForbiddenFileTypes.')?$#is', $value, $aMatches)){continue;}
      $caption = basename($item);
      $aResult = ['value' => $value,'caption' => $caption];
      array_push ($aFontFiles,$aResult);
    }

    $aPathFiles = glob($oReg->AppPath.'include/captcha/backgrounds/*.*',GLOB_MARK|GLOB_NOSORT );
    $aBackgroundFiles = [];
    foreach ($aPathFiles as $item){
      $value = str_replace($sCaptchaPath,'',$item);
      if (\preg_match('#^.*?\.('.$sForbiddenFileTypes.')?$#is', $value, $aMatches)){continue;}
      $caption = basename($item);
      $aResult = ['value' => $value,'caption' => $caption];
      array_push ($aBackgroundFiles,$aResult);
    }
    $sCaptchaDir  = 'include/captcha/';
    $sCaptchaId   = $captcha_type;
    $namespace    = $sCaptchaId;
    $sSecKeyId    = '999';

?>
<div class="block-outer">
    <div class="w3-container captcha-block">
        <h2> <?php echo $MOD_CAPTCHA_CONTROL['HEADING'];?></h2>
        <p> <?php echo $MOD_CAPTCHA_CONTROL['HOWTO'];?></p>
        <h4 class="w3-text-indigo  w3-large"><?php echo $MOD_CAPTCHA_CONTROL['CAPTCHA_CONF'];?></h4>
        <div class="w3-rest w3-panel w3-leftbar w3-sand w3-large w3-serif ">
            <p><?php echo $MOD_CAPTCHA_CONTROL['CAPTCHA_EXP'];?></p>
        </div>
    <form id="store_settings" action="<?= $sActionUrl; ?>" method="post">
        <input type="hidden" name="tool" value="<?= basename(__DIR__); ?>" />
        <input type="hidden" name="action" value="save" />
        <input type="hidden" name="SaveSettings" value="1" />
        <?php echo $oApp->getFTAN(); ?>
            <div class="w3-container w3-padding-8 w3-margin">
                <div class="w3-quarter">
                    <input type="submit" name="SaveSettings" class="w3-btn w3-padding w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" value="<?php echo $TEXT['SAVE']; ?>" />
                </div>
                <div class="w3-quarter">
                <button class="w3-btn w3-padding w3-blue-wb w3-hover-green w3--medium w3-btn-min-width url-close" data-overview="<?= $sAdminToolRel; ?>" type="button"><?= $TEXT['CLOSE'];?></button>
                </div>
            </div>

    <table class="form-table w3-table-all">
        <tbody id="cpt-defaults">
        <tr>
            <th scope="row" colspan="2" class="cpt-caption"><?php echo $CAPTCHA_CONTROL['HEADING'];?></th>
        </tr>
        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['ENABLED_SIGNUP'];?>:<br />
             <span class="label"><?php echo $CAPTCHA_CONTROL['LABEL_SIGNUP'];?></span>
            </th>
            <td>
            <label class="check-container" for="enabled_signup">
            <input type="checkbox" id="enabled_signup" name="enabled_signup" value="1"<?php echo ($bSignupChecked ? ' checked="checked"' : '');?> />
            <span class="checkbtn"></span>
            </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['ENABLED_LOGINFORM'];?>:<br />
            <span class="label"><?php echo $CAPTCHA_CONTROL['LABEL_LOGINFORM'];?></span>
            </th>
            <td>
            <label class="check-container" for="enabled_loginform">
            <input type="checkbox" id="enabled_loginform" name="enabled_loginform" value="2"<?php echo ($bLoginChecked ? ' checked="checked"' : '');?> />
            <span class="checkbtn"></span>
            </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['ENABLED_LOSTPASSWORD'];?>:<br />
            <span class="label"><?php echo $CAPTCHA_CONTROL['LABEL_LOSTPASSWORD'];?></span>
            </th>
            <td>
            <label class="check-container" for="enabled_lostpassword">
            <input type="checkbox" id="enabled_lostpassword" name="enabled_lostpassword" value="4"<?php echo ($bLostPWChecked ? ' checked="checked"' : '');?> />
            <span class="checkbtn"></span>
            &nbsp;</label>
            </td>
        </tr>
        <tr>
            <th scope="row">&nbsp;
                    <div class="row" style="height: 70px;display: flex;">
                      <div class="w3-container w3-cell">
                        <p><?php echo $MOD_CAPTCHA_CONTROL['CAPTCHA_TYPE'];?></p>
                      </div>
<?php
                $sSecId = $sCaptchaId;
                if ($captcha_type == 'Securimage'){
                    $CaptchaUrl = $oReg->AppUrl. 'include/captcha';
?>

                    <div class="captcha_table" style="width: 100%; margin-top: 0.825em;clear: both;" >
                        <label class="captcha-code" id="Secur-image" style="cursor: pointer;">
                          <img class="image-captcha <?php echo $captcha_type;?>" id="<?php echo $sSecId;?>" alt="<?php echo CAPTCHA_TYPE;?>" src="<?php echo $CaptchaUrl;?>/securimage_show.php?captchaId=<?php echo $sSecKeyId;?>" />
                          <img class="image-reload" id="refresh_<?php echo $sSecKeyId;?>" height="24" width="24" src="<?php echo $CaptchaUrl;?>/images/refresh.png" alt="Refresh Image" onclick="this.blur()" style="border: 0px; vertical-align: baseline"/>
                          <span></span>
                        </label>
                    </div>
<?php
                } else {
                  echo sprintf('<img class="%2$s" alt="captcha_show" id="captcha_show" height="40" src="%1$s/include/captcha/captchas/%2$s.png"/>',WB_URL,$captcha_type);
                }
?>
                    </div>
            </th>
            <td>
                  <select class="w3-select w3-border" name="captcha_type" id="captcha_type" onchange="load_captcha_image()">
<?php
              foreach($useable_captchas as $key=>$text) {
                  $sSelected = (($captcha_type==$key)?'selected="selected"':'');
?>
                  <option value="<?php echo $key;?>" <?php echo $sSelected;?>><?php echo $text;?></option>
<?php } ?>
                  </select>
           </td>
        </tr>
        <tr id="ct_color" style="<?php echo $sDisplayOldNone;?>">
            <th scope="row"><?php echo $MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA'];?>&nbsp;</th>
            <td>
                <input class="w3-radio w3-padding" type="radio" <?php echo ($ct_color=='1') ?'checked="checked"' :'';?> id="text_white" name="ct_color" value="1" />
                <label class="w3-validate" for="text_white"><?php echo $MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_BLACK'];?></label>
                <input class="w3-radio w3-padding" type="radio" <?php echo ($ct_color=='0') ?'checked="checked"' :'';?> id="text_black" name="ct_color" value="0" />
                <label class="w3-validate" for="text_black"><?php echo $MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_WHITE'];?></label>
            </td>
        </tr>
        <tr id="ct_text_label" style="<?php echo $sDisplayTextNone;?>">
            <th scope="row"><?php echo $MOD_CAPTCHA_CONTROL['CAPTCHA_ENTER_TEXT'];?>&nbsp;</th>
            <td>&nbsp;</td>
        </tr>
        <tr id="ct_text" style="<?php echo $sDisplayTextNone;?>">
            <td colspan="2">
                  <textarea name="ct_text" cols="55" rows="10"><?php echo $ct_text; ?></textarea>
            </td>
        </tr>
        </tbody>
        <tbody id="text_secure" style="<?php echo $sDisplaySecureNone;?>">
        <tr>
            <th colspan="2" scope="row" class="cpt-caption">
                <div class="w3-container">
                    <div class="w3-rest w3-panel w3-leftbar w3-sand w3--medium w3-serif ">
                        <p><?php echo $MOD_CAPTCHA_CONTROL['CAPTCHA_BLUR'];?></p>
                    </div>
                </div>
            </th>

        </tr>
        <tr>
            <th colspan="2" scope="row" class="cpt-caption w3-border-line"><?php echo $CAPTCHA_CONTROL['SECURIMAGE_HEADING'];?></th>
        </tr>

        <tr style="vertical-align: top;">
            <th scope="row"><?php echo $CAPTCHA_CONTROL['SECURIMAGE_TYPE'];?><br/>
            <span>&nbsp;</span></th>
            <td>
                <select class="w3-select w3-border" name="use_sec_type" >
<?php
              foreach($aUseSecType as $key => $item) {
              $sSelected = (($use_sec_type==$item['value']) ? ' selected="selected"':'');
?>
                  <option value="<?php echo $item['value'];?>" <?php echo $sSelected;?>><?php echo $item['caption'];?></option>
<?php } ?>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['NO_CHAR'];?>:<br /><span><?php echo $CAPTCHA_CONTROL['LABEL_NO_CHAR'];?></span></th>
            <td>
                <select class="w3-select w3-border" name="code_length" >
<?php
              foreach($aCodeLength as $key => $item) {
              $sSelected = (($code_length==$item['value']) ? 'selected="selected"':'');
?>
                  <option value="<?php echo $item['value'];?>" <?php echo $sSelected;?>><?php echo $item['caption'];?></option>
<?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['WIDTH'];?>:<br /><span><?php echo $CAPTCHA_CONTROL['LABEL_WIDTH'];?></span></th>
            <td><input type="text" name="image_width" value="<?php echo $image_width;?>" /></td>
        </tr>
        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['HEIGHT'];?>:<br /><span><?php echo $CAPTCHA_CONTROL['LABEL_HEIGHT'];?></span></th>
            <td><input type="text" name="image_height" value="<?php echo $image_height;?>" /></td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['IMAGE_BG_DIR'];?> <br />
            <span><?php echo $CAPTCHA_CONTROL['LABEL_IMAGE_BG_DIR'];?></span></th>
            <td>
                <select name="image_bg_dir">
                  <option value=""><?php echo $MOD_CAPTCHA_CONTROL['NO_DIR_CHOICE'];?></option>
<?php
              foreach($aBackgroundFiles as $key => $item) {
                  $sSelected = (($image_bg_dir==$item['value']) ? 'selected="selected"':'');
?>
                  <option value="<?php echo $item['value'];?>" <?php echo $sSelected;?>><?php echo $item['caption'];?></option>
<?php } ?>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['IMAGE_BG_COLOR'];?></th>
            <td><input class="jscolor{valueElement:'image_bg_color',shadow:false,borderRadius:0}" type="text" id="image_bg_color" name="image_bg_color" value="<?php echo $image_bg_color;?>" /></td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['TTF_FILE'];?><br />
            <span><?php echo $CAPTCHA_CONTROL['LABEL_TTF_FILE'];?></span></th>
            <td>
                <select name="ttf_file">
<?php
              foreach($aFontFiles as $key => $item) {
                  $sSelected = (($ttf_file==$item['value']) ? 'selected="selected"':'');
?>
                  <option value="<?php echo $item['value'];?>" <?php echo $sSelected;?>><?php echo $item['caption'];?></option>
<?php } ?>
                </select>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['TEXT_COLOR'];?></th>
            <td><input class="jscolor{valueElement:'text_color',shadow:false,borderRadius:0}" type="text" id="text_color" name="text_color" value="<?php echo $text_color;?>" /></td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['NUM_LINES'];?></th>
            <td><input type="text" name="num_lines" value="<?= ($num_lines ?? 3);?>" size="5" maxlength="2" /></td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['LINE_COLOR'];?></th>
            <td><input class="jscolor{valueElement:'line_color',shadow:false,borderRadius:0}" type="text" id="line_color" name="line_color" value="<?php echo $line_color;?>" /></td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['NOISE_LEVEL'];?></th>
            <td>
                <select class="w3-select w3-border" name="noise_level" >
<?php
              foreach($aNoiseLevel as $key => $item) {
              $sSelected = (($noise_level==$item['value']) ? 'selected="selected"':'');
?>
                  <option value="<?php echo $item['value'];?>" <?php echo $sSelected;?>><?php echo $item['caption'];?></option>
<?php } ?>
                </select>

            </td>
        </tr>

        <tr>
            <th scope="row"><?php echo $CAPTCHA_CONTROL['NOISE_COLOR'];?></th>
            <td><input class="jscolor{valueElement:'noise_color',shadow:false,borderRadius:0}" type="text" id="noise_color" name="noise_color" value="<?php echo $noise_color;?>" /></td>
        </tr>

        <tr style="vertical-align: top;">
            <th scope="row"><?php echo $CAPTCHA_CONTROL['IMAGE_SIGNATURE'];?></th>
            <td><input type="text" name="image_signature" value="<?php echo $image_signature;?>" /></td>
        </tr>

        <tr style="vertical-align: top;">
            <th scope="row"><?php echo $CAPTCHA_CONTROL['SIGNATURE_COLOR'];?></th>
            <td><input class="jscolor{valueElement:'signature_color',shadow:false,borderRadius:0}" type="text" id="signature_color" name="signature_color" value="<?php echo $signature_color;?>" /></td>
        </tr>

        <tr style="vertical-align: top;">
            <th scope="row"><?php echo $CAPTCHA_CONTROL['CAPTCHA_EXPIRATION'];?><br /><span ><?php echo $CAPTCHA_CONTROL['LABEL_CAPTCHA_EXPIRATION'];?></span></th>
            <td><input type="text" name="captcha_expiration" value="<?php echo $captcha_expiration;?>" /></td>
        </tr>

        </tbody>
    </table>
    <div class="w3-container w3-padding-8 w3-margin">
        <div class="w3-quarter">
            <input type="submit" name="SaveSettings" class="w3-btn w3-padding w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" value="<?php echo $TEXT['SAVE']; ?>" />
        </div>
        <div class="w3-quarter">
        <button class="w3-btn w3-padding w3-blue-wb w3-hover-red w3--medium w3-btn-min-width url-close" data-overview="<?= $sAdminToolRel; ?>" type="button"><?= $TEXT['CLOSE'];?></button>
        </div>
    </div>

    <div class="w3-container w3-padding">&nbsp;</div>
            <h4 class="w3-text-indigo"><?php echo $MOD_CAPTCHA_CONTROL['ASP_CONF'];?></h4>
            <div class="w3-container w3-padding">
                <div class="w3-rest w3-panel w3-leftbar w3-sand w3-large w3-serif">
        <p><?php echo $MOD_CAPTCHA_CONTROL['ASP_EXP'];?></p>
                </div>
            </div>
            <div class="w3-container w3-padding">
                <div class="w3-third cpt-setting_name">
        <?php echo $MOD_CAPTCHA_CONTROL['ASP_TEXT'];?>&nbsp;
                </div>
                <div class="w3-quarter">
            <label class="check-container" for="active" >
            <input type="radio" name="enabled_asp" id="active"  style="width: 14px; height: 14px;" value="1" <?php echo ($enabled_asp=='1') ?'checked="checked"' :'';?> />
            <span class="radiobtn"><span class="w3-hide">&nbsp;</span></span>
            <span style="padding-left:10px!important;"><?php echo $MOD_CAPTCHA_CONTROL['ENABLED'];?></span></label>
            <label class="check-container" for="disabled" >
            <input type="radio" name="enabled_asp" id="disabled"  style="width: 14px; height: 14px;" value="0" <?php echo ($enabled_asp=='0') ?'checked="checked"' :'';?> />
            <span class="radiobtn"><span class="w3-hide" style="margin-left: 0.9em;">&nbsp;</span></span>
            <span style="padding-left:10px!important;"><?php echo $MOD_CAPTCHA_CONTROL['DISABLED'];?></span></label>

<!--
            <input class="w3-radio" type="radio" <?php echo ($enabled_asp=='1') ?'checked="checked"' :'';?> id="enable_asp" name="enabled_asp" value="1" />
            <label class="w3-validate" for="enable_asp"><?php echo $MOD_CAPTCHA_CONTROL['ENABLED'];?></label>
            <input class="w3-radio" type="radio" <?php echo ($enabled_asp=='0') ?'checked="checked"' :'';?> id="disable_asp" name="enabled_asp" value="0" />
            <label class="w3-validate" for="disable_asp"><?php echo $MOD_CAPTCHA_CONTROL['DISABLED'];?></label>
-->
                </div>
            </div>

            <div class="w3-container w3-padding-8 w3-margin">
                <div class="w3-quarter">
                    <input type="submit" name="SaveSettings" class="w3-btn w3-padding w3-blue-wb w3-hover-green w3--medium w3-btn-min-width" value="<?php echo $TEXT['SAVE']; ?>" />
                </div>
                <div class="w3-quarter">
                <button class="w3-btn w3-padding w3-blue-wb w3-hover-red w3--medium w3-btn-min-width url-close" data-overview="<?= $sAdminToolRel; ?>" type="button"><?= $TEXT['CLOSE'];?></button>
                </div>
            </div>

      </form>
    </div>

</div>
<!--  script to load image -->
<script>
    if (typeof <?php echo strtoupper($sAddonName);?> === "undefined"){
        var <?php echo strtoupper($sAddonName);?> = {
            "WB_URL"     : "<?php echo WB_URL;?>",
            "AddonUrl"   : "<?php echo $sAddonUrl;?>",
            "AddonName"  : "<?php echo $sAddonName;?>",
        };
    }
        refresh = "refresh_<?php echo $sSecKeyId;?>";
        el = document.getElementById(refresh);
        if (el){
          el.addEventListener("click", function(){
              captcha = "<?php echo $sSecId;?>";
              url = "<?php echo $oReg->AppUrl.$sCaptchaDir;?>securimage_show.php?captchaId=<?php echo $sSecKeyId;?>&namespace=<?php echo $namespace;?>&";
              document.getElementById(captcha).src = url+Math.random();
  //console.log(url+Math.random());
          }, false);
        }
</script>