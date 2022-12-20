<?php
//:List of pages below current page or page_id. Modified for servicelinks.
//:[[SiteMapChildRL?start=11]]
//:(optional parameter) start=page_id
//:@change 20220410
$oReg = \bin\WbAdaptor::getInstance();
$oApp = $oReg->getApplication();
$content = '';
if (isset($start) && !empty($start)) {
    $iChild = (isset($start) && is_numeric($start) ? $start : 0);
    if ($iChild > 0){
        $content = ''.
        show_menu2(SM2_ALLMENU,
                $iChild,
                SM2_ALL,
                SM2_ALL|SM2_ALLINFO|SM2_BUFFER,
                '<li class="w3-bar-item w3-button><span class="menu-default">'.'[a][page_title]</a></span>',
                '</li>',
                '<ul id="servicelinks" class="w3-bar w3-border w3-light-grey">');
    }
}
return ($content.'');