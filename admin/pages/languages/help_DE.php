<?php
use bin\{WbAdaptor};
use bin\helpers\{PreCheck};

if (!\defined('SYSTEM_RUN')) {require(\dirname(\dirname(\dirname((__DIR__)))).'/config.php');}
//if (!\function_exists('make_dir')) {require (WB_PATH.'/framework/functions.php');}
    $oTrans = Translate::getInstance();
    $oTrans->enableAddon(ADMIN_DIRECTORY.'\\pages');
    $admin = new admin('##skip##','start', false, false);
    if (!$admin->is_authenticated() || !$admin->ami_group_member('1')){
        throw new \RuntimeException('Illegal file access!');
    }
    $oDb = \database::getInstance();
    $oRequest = \bin\requester\HttpRequester::getInstance();
    $page_id  = $oRequest->getParam('page_id', \FILTER_SANITIZE_NUMBER_INT);
    if ($page_id) {
        $sSql = 'SELECT `link`, `menu_title` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id;
        if ($oPage = $oDb->query($sSql)){
            if (($aPage = $oPage->fetchAssoc())){
              //$sFilename = ltrim($aPage['link'], '/');
              $sFilename = $aPage['link'];
              $title     = $aPage['menu_title'];
            }
            $sFile = basename($sFilename);
            $aPath = array_diff(explode('/',$sFilename),['',$sFile]);
            $sPageTrails = ltrim(implode('/',$aPath));
            $sNewStyle = '/'.$sPageTrails.(empty($sPageTrails) ? '' : '/' ).PreCheck::sanitizeFilename($title,true,true);//
            $sOldStyle = $sFilename;

            echo sprintf(
            '<div class="help-container w3-center" style="padding:10px;font-size:0.925em;">
            <h4>Einstellung Format der Zugriffs Dateien für <br />aktualisierte WebsiteBaker Versionen</h4
            <p>`link` Eintrag für diese Seite aus `%8$s`.`%9$spages`<br /> <strong>%1$s</strong></p>
            <p> Checkbox <strong>%5$s</strong></p>
            <ul class="pages-help">
                <li>a) %6$s </li>
                <li><strong>%2$s</strong></li>
                <li>b) %7$s </li>
                <li><strong>%3$s</strong></li>
            </ul>
            <p>In dem Feld <strong>%4$s</strong> kann unabhängig von Menü- oder Seitentitel ein gut lesbarer und aussagekräftiger Dateinamen für die Zugriffsdatei erstellt werden. Wird gerne zur Erstellung und Indizierung SEO-freundlicher Links benutzt.<br/>
            <p><strong>Beispiele Neues Format:</strong></p>
            <p>Eingabe Menütitel: <strong>Pressemitteilung</strong><br />
            Eingabe Dateiname: <strong>Pressemitteilung - und - Downloads</strong><br />
            Erstellt Zugriffsdatei: <strong>pressemitteilungen-und-downloads.php</strong></p>
            <p><strong>Beispiele Altes Format:</strong></p>
            <p>Eingabe Menütitel: <strong>Pressemitteilung</strong><br />
            Eingabe Dateiname: <strong>Pressemitteilung -  und -  Downloads</strong><br />
            Erstellt Zugriffsdatei: <strong>pressemitteilungen---und---downloads.php</strong></p><div>',
            $sFilename,$sNewStyle,$sOldStyle,$oTrans->TEXT_SEO_TITLE,$oTrans->TEXT_PAGE_NEWSTYLE,$oTrans->TEXT_SEO_NEWSTYLE,$oTrans->TEXT_SEO_OLDSTYLE,$oReg->DbName,$oReg->TablePrefix
            );

        } else {
            throw new \RuntimeException(sprintf("%s\n%s",$oDb->get_error(),$sSql));
        }

    }
