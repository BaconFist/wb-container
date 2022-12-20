<?php


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */



// Function for generating an optionsfor a select field
    if (!\function_exists('make_option')) {
        function make_option(&$n, $k, $values) {
            // start option group if it exists
            if (\substr($n,0,2) == '[=') {
                 $n = '<optgroup label="'.\substr($n,2,\strlen($n)).'">';
            } elseif ($n == ']') {
                $n = '</optgroup>'."\n";
            } else {
                if (\in_array($n, $values)) {
                    $n = '<option selected="selected" value="'.$n.'">'.$n.'</option>'."\n";
                } else {
                    $n = '<option value="'.$n.'">'.$n.'</option>'."\n";
                }
            }
        }
    }
// Function for generating a checkbox
    if (!\function_exists('make_checkbox')) {
        function make_checkbox(&$key, $idx, $params) {
            $field_id = $params[0][0];
            $seperator = $params[0][1];
            $label_id = 'wb_'.\preg_replace('/[^a-z0-9]/i', '_', $key).$field_id;
            if (\in_array($key, $params[1])) {
                $key = '<input class="frm-field_checkbox" type="checkbox" id="'.$label_id.'" name="field'.$field_id.'['.$idx.']" value="'.$key.'" />'.PHP_EOL.'<label for="'.$label_id.'" class="frm-checkbox_label">'.$key.'</lable>'.$seperator;
            } else {
                $key = '<input class="frm-field_checkbox" type="checkbox" id="'.$label_id.'" name="field'.$field_id.'['.$idx.']" value="'.$key.'" />'.PHP_EOL.'<label for="'.$label_id.'" class="frm-checkbox_label">'.$key.'</label>'.$seperator;
            }
        }
    }
// Function for generating a radio button
    if (!\function_exists('make_radio')) {
        function make_radio(&$n, $idx, $params) {
            $field_id = $params[0];
            $group = $params[1];
            $seperator = $params[2];
            $label_id = 'wb_'.\preg_replace('/[^a-z0-9]/i', '_', $n).$field_id;
            if($n == $params[3]) {
                $n = '<input class="frm-field_checkbox" type="radio" id="'.$label_id.'" name="field'.$field_id.'" value="'.$n.'" checked="checked" />'.PHP_EOL.'<label for="'.$label_id.'" class="frm-checkbox_label">'.$n.'</label>'.$seperator;
            } else {
                $n = '<input class="frm-field_checkbox" type="radio" id="'.$label_id.'" name="field'.$field_id.'" value="'.$n.'" />'.PHP_EOL.'<label for="'.$label_id.'" class="frm-checkbox_label">'.$n.'</label>'.$seperator;
            }
        }
    }

    if (!\function_exists("new_submission_id") ) {
        function new_submission_id() {
            $submission_id = '';
            $salt = "abchefghjkmnpqrstuvwxyz0123456789";
            \srand((double)microtime()*1000000);
            $i = 0;
            while ($i <= 7) {
                $num = \rand() % 33;
                $tmp = \substr($salt, $num, 1);
                $submission_id = $submission_id . $tmp;
                $i++;
            }
            return $submission_id;
        }
    }
