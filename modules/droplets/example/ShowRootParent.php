<?php
//:Shows the root_parent page of a page tree
//:Use [[ShowRootParent]]
global $page_id;
$oReg    = \bin\WbAdaptor::getInstance();
$oDb     = $oReg->getDatabase();
$oApp    = $oReg->getApplication();
$iField  = 0;//root
$sField  = 'parent';
$sIndex  = 'page_title';
$iPageId = (($oApp->page['page_id']) ?? $page_id );
//    \trigger_error(sprintf("[%03d] [[ShowRootParent]] (page_id==%d) for (%s == %d) ",__LINE__,$iPageId,$sField,$iField, E_USER_NOTICE));
$iField  = (($oApp->page[$sField]) ? $oApp->page[$sField] : $iPageId ); //$oApp->menu_title
if ($iField == 0) {
    \trigger_error(sprintf("[%03d] [[ShowRootParent]] (page_id==%d) not found for (%s == %d) ",__LINE__,$iPageId,$sField,$iField, E_USER_NOTICE));
    $sField = 'page_id';
    $iField = $oApp->getDefaultPageId();
}
// @parameter root_parent default or parent
$aRetval = $oApp->getPage($iField);
// @return page_title or menu_title
$sRetval = $aRetval['page_title'];
return $sRetval;
