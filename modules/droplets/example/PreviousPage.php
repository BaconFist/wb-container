//:Create a previous link to your page
//:Display a link to the previous page on the same menu level
$sInfo = show_menu2(0, SM2_CURR, SM2_START, SM2_ALL|SM2_BUFFER|SM2_SHOWHIDDEN, '[if(class==menu-current){[level] [sib] [sibCount] [parent]}]', '', '', '');
$aInfo = (empty($sInfo) ? [] : explode(' ', $sInfo));
$prv = 0;
$sRetval = '';
if (sizeof($aInfo)){
    list($nLevel, $nSib, $nSibCount, $nParent) = $aInfo;
    $prv = $nSib > 1 ? $nSib - 1 : 0;
}
// show previous
if ($prv > 0) {
    $sRetval = show_menu2(0, SM2_CURR, SM2_START, SM2_ALL|SM2_BUFFER|SM2_SHOWHIDDEN, "[if(sib==$prv){[a][menu_title]</a> &lt;&lt;}]", '', '', '');
}
return $sRetval;
