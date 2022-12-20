<?php
//:Create a Searchbox on the position
//:Usage:  [[Searchbox]]
//:Optional parameter "?msg=the search message"
//:or in HTML Templates
//:Optional parameter "?msg="phptag echo lang variable; ?>"

$return_value = '';
if (SHOW_SEARCH) {
    $oTrans = Translate::getInstance();
    if (!isset($msg)){$msg=$oTrans->TEXT_SEARCHING;}
    $return_value    = '<div class="form-wrapper cf">';
    $return_value   .= '<form action="'.WB_URL.'/search/index'.PAGE_EXTENSION.'" method="get" name="search" class="searchform" id="search">';
    //$return_value .= '<input style="color:#b3b3b3;" type="text" name="string" size="25" class="textbox" value="'.$msg.'" '.$j.'  />&nbsp;';
    $return_value   .= '<input type="text" name="string" placeholder="'.$msg.'" value="" required>';
    $return_value   .= '<button type="submit">'.$oTrans->TEXT_SEARCH.'</button>';
    $return_value   .= '</form>';
    $return_value   .= '</div>';
}
return $return_value;