<?php

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
        // Print header
                echo $header."\n";
/* -------------------------------------------------------------------------- */
// Callback functions
/* -------------------------------------------------------------------------- */
// Function for generating an optionsfor a select field
    if (!is_callable('make_option')) {
        function make_option(&$n, $k, $values) {
            $aValues = (is_string($values) ? explode(',',$values) : $values);
            // start option group if it exists
            if (substr($n,0,2) == '[=') {
                 $n = '<optgroup label="'.substr($n,2,strlen($n)).'">';
            } elseif ($n == ']') {
                $n = '</optgroup>'."\n";
            } else {
                $sSelected = (in_array($n, $aValues) && (count($aValues)==1) ? ' selected="selected"' : '');
                $n = '<option class="w3--medium" value="'.$n.'"'.$sSelected.'>'.$n.'</option>'."\n";
            }
        }
    }
/* -------------------------------------------------------------------------- */
// Function for generating a checkbox
    if (!is_callable('make_checkbox')) {
        function make_checkbox(&$key, $idx, $params) {
            $tmp       = '';
            $field_id  = $params[0];
            $group     = $params[1];
            $seperator = $params[2];
//            $form_name = ($params[3] ?? 'form'.$field_id);
            $label_id  = 'wb_'.preg_replace('/[^a-z0-9]/i', '_', $key).$field_id;
            $sChecked  = (($key == $params[3]) ? ' checked="checked"' : '');
            $tmp .= "\n".'<input type="checkbox" name="field'.$field_id.'[]" id="'.$label_id.'" value="'.$key.'" '.$sChecked.' />';
            $tmp .= "\n".'<label class="testcheck" for="'.$label_id.'">'.'<span>'.$key.'</span>'.'</label>'.$seperator."\n";
            $key = $tmp;
        }
    }
/* -------------------------------------------------------------------------- */
// Function for generating a radio button
    if (!is_callable('make_radio')) {
        function make_radio(&$n, $idx, $params) {
            $tmp       = '';
            $field_id  = $params[0];
            $group     = $params[1];
            $seperator = $params[2];
            $form_name = ($params[3] ?? 'form');
            $label_id  = 'wb_'.preg_replace('/[^a-z0-9]/i', '_', $n).$field_id;
            $sChecked  = (($n == $params[3]) ? ' checked="checked"' : '');
            $tmp .= "\n".'<input type="radio" name="field'.$field_id.'" id="'.$label_id.'" value="'.$n.'" '.$sChecked.' />';
            $tmp .= "\n".'<label class="testradio" for="'.$label_id.'">'.'<span>'.$n.'</span>'.'</label>'.$seperator."\n";
            $n = $tmp;
        }
    }
/* -------------------------------------------------------------------------- */
// loop content
/* -------------------------------------------------------------------------- */
                while(($field = $query_fields->fetchRow(MYSQLI_ASSOC))) {
                    if (!$field['active']){continue;}
/* -------------------------------------------------------------------------- */
                    // Set field values
                    $field_id     = $field['field_id'];
                    $value = $field['value'];
                    // Print field_loop after replacing vars with values
                    $field_title = '';
                    $aSearch     = ['{TITLE}'];
                    $aSearch[]   = '{FIELD}';
                    $aSearch[]   = '{REQUIRED}';
/* -------------------------------------------------------------------------- */
                    $sRequired = '';
/* feature settings add requiered attribute to tags ------------------------- */
                    $bRequired = (($field['required'] == 1) ? true : false);
                    $bFormRequired = (($aSettings['form_required'] == 1) ? true : false);
                    $sRequired = ' required="required"';
                    $sRequired = ($bRequired ? $sRequired : '');
                    $sRequired = (($bRequired && $bFormRequired) ? $sRequired : '' );
/* feature settings add placeholder attribute to tags ---------------------- */
                    $bPlaceholder = (($aSettings['title_placeholder'] == 1) ? true : false);
                    $sPlaceholder = ($bPlaceholder ? ' placeholder="'.$field['title'].'"' : '');
/* -------------------------------------------------------------------------- */
//                    $field_loop = str_replace(['{REQUIRED}'],$sRequiredStrinStar,$field_loop);
                    $sEmptyString        = '';
                    $bShowRequired       = preg_match('/\{REQUIRED\}/iu',$field_loop);
                    $sRequiredString     = ($bShowRequired ? '<span class="frm-required-star">*</span>' : $sEmptyString);
                    $sRequiredStringStar = (!$bPlaceholder && ($field['required'] == 1) ? $sRequiredString : $sEmptyString);
                    $sType = $field['type'];
                    switch ($sType):
                        case 'radio':
                        case 'checkbox':
                            $field_title = "\n".'<label class="check-btn">'.$field['title'].$sRequiredStringStar.'</label>'."\n";
                            break;
                        case 'heading':
                            $field_title = "\n".'<label class="frm-header-label">'.$field['title'].'</label>'."\n";
                            break;
                        default:
                            $field_title = "\n".'<label class="default-title" for="field'.$field_id.'">'.$field['title'].$sRequiredStringStar.'</label>'."\n";
                            $field_title = ($bPlaceholder ? '' : $field_title);
                    endswitch;
/* deprecated
                    if (($field['type'] == "radio") || ($field['type'] == "checkbox")) {
                    } elseif($field['type'] == 'heading') {
                    } else {
                    }
//                    $aReplacement[] = (!$bPlaceholder && ($field['required'] == 1) ? '<span class="frm-required-star ">*</span>' : '');
*/
                    $aReplacement = [$field_title];
                    $aRequiredAttribute = ($bRequired ? ' frm-required' : '');
//                    $aSearch[] = '{FIELD}';
                    switch ($sType):
                        case 'heading':
                            $str = '<input class="w3-textarea w3-border" type="hidden" name="field'.$field_id.'" id="field'.$field_id.'" value="====== '.$field['title'].' ======" />';
                            $aReplacement[] = ((true == $use_xhtml_strict) ? "<div>".$str."</div>" : $str);
                            $tmp_field_loop = $field_loop; // temporarily modify the field loop template
                            $field_loop = $field['extra'];
                            break;
                        case 'textfield':
                            $max_lenght_para = (intval($field['extra']) ? ' maxlength="'.\intval($field['extra']).'"' : '');
                            $tmpField = ($_SESSION[$form_name]['field'.$field_id] ?? $value);
                            $aReplacement[] = "\n".'<input class="frm-textfield frm-input frm-border'.$aRequiredAttribute.'" type="text" name="field'.$field_id.'" id="field'.$field_id.'"'.$max_lenght_para.' value="'.$tmpField.'"'.$sRequired.''.$sPlaceholder.' />'."\n";
                            break;
                        case 'subject':
                            $max_lenght_para = (\intval($field['extra']) ? ' maxlength="'.\intval($field['extra']).'"' : '');
                            $tmpField = ($_SESSION[$form_name]['field'.$field_id] ?? $value);
                            $aReplacement[] = '<input class="frm-textfield subject w3-input w3-border'.$aRequiredAttribute.'" type="text" name="field'.$field_id.'" id="field'.$field_id.'"'.$max_lenght_para.' value="'.$tmpField.'"'.$sRequired.''.$sPlaceholder.' />';
                            break;
                        case 'textarea':
                            $tmpField = ($_SESSION[$form_name]['field'.$field_id] ?? $value);
                            $aReplacement[] = "\n".'<textarea class="frm-textarea w3-textarea'.$aRequiredAttribute.'" name="field'.$field_id.'" id="field'.$field_id.'" cols="30" rows="8"'.$sRequired.''.$sPlaceholder.'>'.$tmpField.'</textarea>'."\n";
                            break;
                        case 'select':
                            $options = \explode(',', $value);
                            $aParams = ($_SESSION[$form_name]['field'.$field_id] ?? $value);
                            \array_walk($options, 'make_option', $aParams);
                            $field['extra'] = \explode(',',$field['extra']);
                            $field['extra'][1] = ($field['extra'][1]=='multiple') ? $field['extra'][1].'="'.$field['extra'][1].'"' : '';
                            $aReplacement[] = '<select class="frm-select w3-select w3-border'.$aRequiredAttribute.'" name="field'.$field_id.'[]" id="field'.$field_id.'" size="'.(!empty($field['extra'][0]) ? $field['extra'][0] : 1).'" '.$field['extra'][1].''.$sRequired.'>'.implode($options).'</select>'."\n";
                            break;
                        case 'radio':
                            $options = \explode(',', $value);
                            $aParams = [$field_id,$field['title'],$field['extra'],$form_name, ($_SESSION[$form_name]['field'.$field_id] ?? '')]; //
                            \array_walk($options, 'make_radio', $aParams);
                            $x = \count($options)-1;
                            $options[$x]=\substr($options[$x],0,\strlen($options[$x]));
                            $aReplacement[] = \implode($options);
                            break;
                        case 'checkbox':
                            $options = \explode(',', $value);
                            $aParams = [$field_id,$field['title'],$field['extra'],$form_name, ($_SESSION[$form_name]['field'.$field_id] ?? '')]; //
                            \array_walk($options, 'make_checkbox', $aParams);
                            $x = \sizeof($options)-1;
                            $options[$x]=\substr($options[$x],0,\strlen($options[$x]));
                            $aReplacement[] = \implode($options);
                            break;
                        case 'email':
//                            $sPlaceholder = ($isAuth ? ' Placeholder="'.$oApp->get_email().'"' : '');
                            $sReadonly = ($isAuth ? ' readonly="readonly"' : '');
//                            $sDisabled = ($isAuth ? ' disabled="disabled"' : '');
                            $sDisabled = '';
                            $max_lenght_para = (\intval($field['extra']) ? ' maxlength="'.\intval($field['extra']).'"' : '');
                            $tmpField = ($_SESSION[$form_name]['field'.$field_id] ?? '');
                            if ($sLocalDebug){
                                $sEmail = ($isAuth ? 'user-mail (authenticated)' : $tmpField);
                            }else {
                                $sEmail = (($isAuth ? $oApp->get_email() : $tmpField));
                            }
                            $aReplacement[] = "\n".'<input class="frm-email frm-input frm-border'.$aRequiredAttribute.'"'.$sReadonly.$sDisabled.' type="text" name="field'.$field_id.'" id="field'.$field_id.'" value="'.$sEmail.'"'.$max_lenght_para.''.$sRequired.''.$sPlaceholder.' />'."\n";
                            break;
                        default:
                    endswitch;
/*  deprecated
                    if ($field['type'] == 'textfield') {
                    } elseif ($field['type'] == 'subject') {
                    } elseif($field['type'] == 'textarea') {
                    } elseif($field['type'] == 'select') {
                    } elseif($field['type'] == 'heading') {
                    } elseif($field['type'] == 'checkbox') {
                    } elseif($field['type'] == 'radio') {
                    } elseif($field['type'] == 'email') {  // TODO only one time
                    }

$sDomain = \basename(__DIR__).'/'.\basename(__FILE__);
print '<pre class="w3-pre w3-border w3-white w3-small w3-container w3-padding">'.nl2br(sprintf("function: <span>%s</span> (%s) Filename: <span>%s</span> Line %d\n",(!empty(__FUNCTION__) ? __FUNCTION__ : 'global'),'myVar',$sDomain,__LINE__));
\print_r( ($aSearch[2]) ); print "</pre>"; \flush (); // htmlspecialchars() ob_flush();;sleep(10); die();
*/

                    if (isset($_SESSION[$form_name]['field'.$field_id])){
                        unset($_SESSION[$form_name]['field'.$field_id]);
                    }
/* -------------------------------------------------------------------------- */
                    if ($field['type'] != '') {
                        echo ($sOutput = \str_replace($aSearch, $aReplacement, $field_loop));
                    }
/* -------------------------------------------------------------------------- */
                    if (isset($tmp_field_loop)){ $field_loop = $tmp_field_loop; }
                } // end while
/* -------------------------------------------------------------------------- */
// Print extra / footer
/* -------------------------------------------------------------------------- */
            $sTmpExtra = $sExtra;
            if ($aSettings['use_data_protection'] && !empty($sExtra)) {
                $bShowRequired   = preg_match('/\{REQUIRED\}/iu',$sExtra);
                $sRequiredString = ($bShowRequired ? '<span class="frm-required-star">*</span>' : $sEmptyString);
                $sExtra = \str_replace(['{DSGVO_LINK}','{CALL_DSGVO_LINK}'], $sDataProtection, $sExtra);
            } else {
                $sExtra = \str_replace(['{DSGVO_LINK}','{CALL_DSGVO_LINK}'], '', $sExtra);
            }
            // Captcha
            if (($use_captcha && !$isAuth) || ($use_captcha && ($isAuth && !$aSettings['use_captcha_auth']))) {
                $aCaptachs['ct_color'] = 1;
                if ($oCaptcha = $oDb->query('SELECT * FROM `'.TABLE_PREFIX.'mod_captcha_control` ')){
                    $aCaptachs = $oCaptcha->fetchRow(MYSQLI_ASSOC);
                }
                $sExtra = \str_replace(['{TEXT_VERIFICATION}','{REQUIRED}'], [$oTrans->TEXT_VERIFICATION,$sRequiredString], $sExtra);
                $sCaptcha[$form_name] = call_captcha($aSettings['captcha_action'], $aSettings['captcha_style'], $aSettings['section_id'], false,$aCaptachs['ct_color']);
                $sExtra = \str_replace('{CALL_CAPTCHA}', $sCaptcha[$form_name], $sExtra);
                if (isset($_SESSION['captcha'.$section_id])) {
                    $sNamespace = 'captcha'.$section_id;
                    $_SESSION[$form_name]['captcha'] = $_SESSION[$sNamespace];
                } else if (isset($_SESSION['captcha'])) {
                    $sNamespace = 'captcha';
                    $_SESSION[$form_name]['captcha'] = $_SESSION['captcha'];
                }
            } else {
                $sExtra = \str_replace(['{TEXT_VERIFICATION}','{CALL_CAPTCHA}','{REQUIRED}'], '', $sExtra);
            }
            $sFooter = \str_replace(['{TEXT_CUSTOM}','{CALL_CUSTOM}','{SUBMIT_FORM}'], [$oTrans->TEXT_EMPTY,$oTrans->TEXT_EMPTY,$oTrans->MOD_FORM_SUBMIT_FORM], $sFooter);
//            $sFooter = str_replace(, $oTrans->MOD_FORM_SUBMIT_FORM, $sFooter);
            echo \htmlspecialchars_decode($sExtra.$sFooter);
   // Add form end code $out.
/* -------------------------------------------------------------------------- */
//
/* -------------------------------------------------------------------------- */