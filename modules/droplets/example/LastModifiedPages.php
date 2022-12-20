<?php
//:Displays the last modification time of pages
//:Use [[LastModifiedPages?max=5]]
    $oReg = \bin\WbAdaptor::getInstance();
    $oTrans = $oReg->getTranslate();
    $oApp   = $oReg->getApplication();
    $iMax = (int)($max ?? 1);
    $sRetval = nl2br(sprintf($oTrans->TEXT_MODIFIED_PAGE,$iMax));
    if ($iMax > 1){
        $sRetval = nl2br(sprintf($oTrans->TEXT_MODIFIED_PAGES,$iMax));
    }

    $iNow = time();
    $sSql = '
      SELECT
      `p`.`page_title`,`p`.`modified_when`,`p`.`modified_by`,`p`.`link`
      ,`p`.`page_id` ,UNIX_TIMESTAMP() `time_now`,`u`.`display_name`
      FROM `'.$oReg->TablePrefix.'pages` `p`
      INNER JOIN `'.$oReg->TablePrefix.'users` `u`
      ON `u`.`user_id` = `p`.`modified_by`
      HAVING `p`.`modified_when`<= `time_now`
      ORDER BY `p`.`modified_when` DESC
    ';
    $i = 0;
    if ($oPages = $oReg->Db->query($sSql)){}
        while (($aPages=$oPages->fetchRow(MYSQLI_ASSOC))){
            $sLink = '<b><i>'.$aPages['page_title'].'</b></i>';
            if ($oApp->page_id != $aPages['page_id']) {
                $sLink = '<a href="'.$oReg->AppUrl.ltrim($oReg->PagesDir,'/').$aPages['link'].$oReg->PageExtension.'">'.$aPages['page_title'].'</a>';
            }
            $date = \bin\helpers\PreCheck::dateFormatToStrftime($oReg->DateFormat,$aPages['modified_when']);
            $time = \bin\helpers\PreCheck::dateFormatToStrftime($oReg->TimeFormat,$aPages['modified_when']);
            $sRetval .=  nl2br(sprintf(" %s %s %s \n",$sLink,$date, "".$time));
            $i++;
            if ($i  >= $iMax ){break;}
        }
    return (empty($sRetval) ? nl2br(sprintf($oTrans->NO_MODIFIED_PAGES)) : $sRetval);
