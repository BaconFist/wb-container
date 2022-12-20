<?php
//:search for an image in current page. If no image is present, the image of the parent page is inherited.
//:Use: [[iPageIcon?type=1]]
//:Display the page-icon(0)(default) or menu_icon_0(1) or menu_icon_1(2) if found
// @author: Manuela von der Decken, Dietmar Wöllbrink
// @param int $type: 0=page_icon(default) | 1=menu_icon_0 | 2=menu_icon_1
// @param string $icon: name of a default image placed in WB_PATH/TEMPLATE/
// @return: a valid image-URL or empty string
//
$oReg    = \bin\WbAdaptor::getInstance();
$database = $oReg->getDatabase();
$oDb     = $database;
$oTrans  = $oReg->getTranslate();
$oApp    = $oReg->getApplication();
$type = (!isset($type) ? 0 : (intval($type) % 3));
$icontypes = [
    0 => 'page_icon',
    1 => 'menu_icon_0',
    2 => 'menu_icon_1'
    ];
$icon_url = '';
if (isset($icon) && is_readable($oReg->AppPath.'/templates/'.$oReg->Template.'/'.$icon)) {
    $icon_url = $oReg->AppUrl.'/templates/'.$oReg->Template.'/'.$icon;
}
$tmp_trail = array_reverse($oApp->page_trail);

foreach ($tmp_trail as $pid) {
    $sql = 'SELECT `'.$icontypes[$type].'` ';
    $sql .= 'FROM `'.$oReg->TablePrefix.'pages` ';
    $sql .= 'WHERE `page_id`='.(int)$pid;
    if (($icon = $oDb->get_one($sql)) != false) {
        $icon = ltrim(str_replace('\\', '/', $icon), '/');
        if (is_file($oReg->AppPath.'/'.$icon)) {
            $icon_url = $oReg->AppUrl.'/'.$icon;
            break;
        }
    }
}
return $icon_url;
