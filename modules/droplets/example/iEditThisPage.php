<?php
//:Puts Edit-Buttons on every page you have rights for. 1=modify page, 2=modify pagesettings, 4=modify sections, or add values to combine buttons.
//:Use: [[iEditThisPage?show=7]].
//:1=modify page, 2=modify pagesettings, 4=modify sections, or add values to combine buttons.
//:You can format the appearance using CSS-class 'div.iEditThisPage' in your basic-css file
//:@author: Manuela von der Decken
//:@changed 2022-04-09
//global $wb;
$oReg     = \bin\WbAdaptor::getInstance();
$database = $oReg->getDatabase();
$oDb      = $database;
$oTrans   = $oReg->getTranslate();
$oApp     = $oReg->getApplication();
$returnvalue = '';
if ($oApp->is_authenticated()) {
    $is_admin = false;
    $page_id = ((PAGE_ID == 0) ? $oApp->default_page_id : PAGE_ID);
    $user_id = $oApp->getUserId();
    $sql = 'SELECT `admin_users`, `admin_groups` '
    . 'FROM `'.$oReg->TablePrefix.'pages` '
    . 'WHERE `page_id` = '.$page_id;
    if (($rset = $oDb->query($sql)) != null) {
        if (($rec = $rset->fetchRow(\MYSQLI_ASSOC)) != null) {
            $is_admin = ($oApp->ami_group_member($rec['admin_groups']) ||
            ($oApp->is_group_match($user_id, $rec['admin_users'])) );
        }
    }
    if ($is_admin) {
        $tpl  = '<a href="'.$oReg->AcpUrl.'/pages/%1$s.php?page_id='.$page_id.'"  title="%2$s">'
        . '<img src="'.$oReg->ThemeUrl.'/images/%3$s_16.png" alt="%2$s" style="margin: 0 0.325em;" /></a>';
        $show = ((!isset($show) || $show == '') ? 7 : (int)$show);
        $show = ($show > 7 ? 7 : (int)$show);
        $show = ($show < 2 ? 1 : (int)$show );
        if ($show & 1) {
            $returnvalue .= sprintf($tpl, 'modify', $oTrans->HEADING_MODIFY_PAGE, 'modify');
        }
        $sys_perm = $oApp->get_session('SYSTEM_PERMISSIONS');
        if (@is_array($sys_perm)) {
            if (($show & 2) && (array_search('pages_settings', $sys_perm)!==false)) {
                $returnvalue .= sprintf($tpl, 'settings', $oTrans->HEADING_MODIFY_PAGE_SETTINGS, 'edit');
            }
            if (($show & 4) && (array_search('pages_modify', $sys_perm)!==false)) {
                $returnvalue .= sprintf($tpl, 'sections', $oTrans->HEADING_MANAGE_SECTIONS, 'sections');
            }
        }
        if ($returnvalue != '') {
            $returnvalue  = '<div class="iEditThisPage">'.$returnvalue.'</div>';
        }
    }
}
return(($returnvalue == '') ? true : $returnvalue);
