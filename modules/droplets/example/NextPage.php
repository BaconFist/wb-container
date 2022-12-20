//:Create a next link to your page
//:Display a link to the next page on the same menu level
$sInfo = show_menu2(0, SM2_CURR, SM2_START, SM2_ALL|SM2_BUFFER|SM2_SHOWHIDDEN, '[if(class==menu-current){[level] [sib] [sibCount] [parent]}]', '', '', '');
$aInfo = (empty($sInfo) ? [] : explode(' ', $sInfo));
$nxt = 0;
$sRetval = '';
if (sizeof($aInfo)){
    list($nLevel, $nSib, $nSibCount, $nParent) = $aInfo;
    $nxt = $nSib < $nSibCount ? $nSib + 1 : 0;
}
// show next
if ($nxt > 0) {
    $sRetval = show_menu2(0, SM2_CURR, SM2_START, SM2_ALL|SM2_BUFFER|SM2_SHOWHIDDEN,    "[if(sib==$nxt){&gt;&gt; [a][menu_title]</a>}]", '', '', '');
}

return $sRetval;
