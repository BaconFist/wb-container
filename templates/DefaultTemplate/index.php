<?php
/**
 * $Id:  $
 * Website Baker template: allcss
 * This template is one of four basis templates distributed with Website Baker.
 * Feel free to modify or build up on this template.
 *
 * This file contains the overall template markup and the Website Baker
 * template functions to add the contents from the database.
 *
 * LICENSE: GNU General Public License
 *
 * @author     WebsiteBaker Project
 * @copyright  GNU General Public License
 * @license    https://www.gnu.org/licenses/gpl.html
 * @version    3.0.0
 * @platform   WebsiteBaker 2.13.x
 *
 * WebsiteBaker is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * WebsiteBaker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
declare(strict_types = 1);

use bin\{WbAdaptor};
use addon\WBLingual\Lingual;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {
    \header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404 Not Found';
    ob_\flush();flush();
    exit;
}
/* -------------------------------------------------------- */

//date_default_timezone_set('Europe/Berlin');
    mb_internal_encoding("UTF-8");
//$loc = setlocale(LC_TIME, 'de_DE.UTF8', 'de_DE@euro', 'de_DE', 'deu_deu', 'ge');
    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $oApp     = $oReg->getApplication();

    $sAddonFile = str_replace('\\','/',__FILE__);//$oApp->getCallingScript();
    $sAddonPath = (dirname($sAddonFile)).'/';
    $sAddonName = \basename($sAddonPath);

    $iSteps = 0;// steps to /modules/ root
    switch ($iSteps) {
        case 2:
          $sAddonPath    = \dirname($sAddonPath);
        case 1:
          $sAddonPath    = \dirname($sAddonPath);
        case 0:
          $sModulesPath  = \dirname($sAddonPath);
          break;
    }
    $sModulesName  = basename($sModulesPath);
    $sPattern      = "/^(.*?\/)".$sModulesName."\/.*$/";
    $sAppPath      = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    if (is_readable($sAddonPath.'languages/EN.php')) {require($sAddonPath.'languages/EN.php');}
    if (is_readable($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php')) {require($sAddonPath.'languages/'.DEFAULT_LANGUAGE.'.php');}
    if (is_readable($sAddonPath.'languages/'.LANGUAGE.'.php')) {require($sAddonPath.'languages/'.LANGUAGE.'.php');}
    $oTrans->enableAddon($sModulesName.'\\'.$sAddonName);

/* -------------------------------------------------------- */
//echo (sprintf("<!-- [%03d] %s -->\n",__LINE__,$sAddonPath));
// read info.php to get Blocks and menues
/* -------------------------------------------------------- */
    $sFileName = \str_replace('\\','/',__DIR__).'/info.php';
    if (!(isset($block) && \is_readable($sFileName))){
        include($sFileName);
    }
// Create PageContent Strings from info.php, Twig Data ready
    if (isset($block)) {
        $pageContent = 'pageContent';
        $aBlocks = [];
        foreach($block as $sIndex => $sName )
        {
            if ($sIndex==99){continue;}
            $sKey = \str_replace(' ', '', \ucwords(\str_replace(['_','-'], ' ', \strtolower($sName))));
            ob_start();
            page_content($sIndex);
            $aTwigData[$sKey] = \ob_get_clean();
// create variables with content from blocks declared in info.php
// $pageContentReplaceHeader, $pageContentMain, $pageContentSidebar, $pageContentFooter, etc
            ${$pageContent.$sKey} = $aTwigData[$sKey];
        }
    }

/* -------------------------------------------------------- */
// get image link from Teaser block
/* -------------------------------------------------------- */
    $aMatches = [];
    $sTeaser = '';
    $imgSrcPattern = "/\<img.+src\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/";
    if (\preg_match($imgSrcPattern, $aTwigData['Teaser'], $aMatches)){
        $sTeaser = $aMatches[1];
    }
/* -------------------------------------------------------- */
//BEGIN below part is needed for multilingual
/* -------------------------------------------------------- */
// fetch page language
    $sPageLang = strtolower(isset($wb->page) || ($wb instanceof frontend) ? $wb->page['language'] : 'EN');
// fetch page_id for loaded page, you need it for canonical
    $iPageId   = (isset($wb->page) || ($wb instanceof frontend) ? $wb->page['page_id'] :(PAGE_ID ?? ($page_id ?? 0)));
// Dummy function if Lingual Snippet not loaded
    if (!is_callable('LangPageId'))
    {
        function LangPageId($iPageId):?int
        {
            return (getLangStartPageIds ?? $iPageId);
        }
    }
//  get the page_id from language in level 0 for a given language
    if (\is_callable('getLangStartPageIds')) {
        $iLangStartPage = (int)(getLangStartPageIds($sPageLang));
    }
//  get the page trail from languages in level 0 as array
    $aLangStartPageIds = [];
    if (\is_callable('getLangStartPageIds')) {
        $aLangStartPageIds = getLangStartPageIds();
    }
// to show flags in frontend
    $iMultiLang = 0;
    $iLangFound = count($aLangStartPageIds);
// to set show_menu2 START + 1
    switch ($iLangFound):
        case 0:
        case 1:
            $iMultiLang = 0;
            break;
        default:
            $iMultiLang = 1;
    endswitch;
    $sMultiLang = '';
    if (is_callable('language_menu')) {
        $sMultiLang = language_menu('png', false);
        $iMultiLang = intval(!empty($sMultiLang) ? 1 : $iMultiLang);
    }
/* -------------------------------------------------------- */
//END above part is needed for multilingual
/* -------------------------------------------------------- */
    $sTemplateFunc = 'resolveTemplateImagesPath';
    $sImages       = $sTemplateFunc();
/* -------------------------------------------------------- */
//create different show_men2 calls
/* -------------------------------------------------------- */
  $menuLeft = show_menu2(
    SM2_ALLMENU,
    SM2_ROOT + $iMultiLang,
    SM2_CURR + 1,
    SM2_TRIM|SM2_BUFFER|SM2_NUMCLASS|SM2_PRETTY,
    '<li><span class="menu-default">'.'[ac][menu_title]</a>'.'</span>',
    '</li>',
    '<ul>',
    '</ul>'
    );
/*
$menuLeft = '';
     $mainnav = show_menu2(
        $aMenu          = 1,
        $aStart         = SM2_ROOT + $iMultiLang,
        $aMaxLevel      = SM2_CURR + 1,
        $aOptions       = SM2_TRIM|SM2_NUMCLASS|SM2_PRETTY|SM2_BUFFER,
        $aItemOpen      = '<li>'.PHP_EOL.'<a  href="[url]" class="[class]" target="[target]">[menu_title][if (class==menu-expand){<span class="drop-icon"></span><label title="Toggle Drop-down" class="drop-icon" for="sm[page_id]">+</label>}]</a>',
        $aItemClose     = '</li>',
        $aMenuOpen      = '<input type="checkbox" id="sm[parent]"><ul class="sub-menu">',
        $aMenuClose     = '</ul>',
        $aTopItemOpen   = false,
        $aTopMenuOpen   = ' <ul class="main-menu cf">'
      );
*/

            $aMenu          = 1;
            $aStart         = SM2_ROOT+$iMultiLang;
            $aMaxLevel      = SM2_ALL;
            $aOptions       = SM2_ALL|SM2_NUMCLASS|SM2_BUFFER|SM2_PRETTY;//
            $aItemOpen      = '<li class="[if(class=menu-current||class=menu-parent){active}]">[ac][menu_title]</a>';
                //$aItemOpen      = $open,
            $aItemClose     = '</li>';
            $aMenuOpen      = '<ul>';
            $aMenuClose     = '</ul>';
            $aTopItemOpen   = false;
            $aTopMenuOpen   = false;

            $sMainNav  = show_menu2(
                $aMenu,
                $aStart,
                $aMaxLevel,
                $aOptions,
                $aItemOpen,
                $aItemClose,
                $aMenuOpen,
                $aMenuClose,
                $aTopItemOpen,
                $aTopMenuOpen
            );
            $sMainNav = '';
// TEMPLATE CODE STARTS BELOW
?><!DOCTYPE HTML>
<html lang="<?= $sPageLang; ?>">
    <head>
        <meta charset="utf-8" />
        <title><?php page_title(); ?></title>
        <meta name="description" content="<?php page_description(); ?>" />
        <meta name="keywords" content="<?php page_keywords(); ?>" />
        <meta name="robots" content="noindex, nofollow" />
        <meta name="referrer" content="same-origin" />

        <!-- Mobile viewport optimisation -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <link rel="shortcut icon" href="<?= $sImages;?>favicon.ico" type="image/x-icon"/>
        <link rel="apple-touch-icon" href="<?= $sImages;?>apple-touch-icon.png"/>
        <!-- styles  -->
<!--
        <link rel="stylesheet" href="<?= TEMPLATE_DIR; ?>/css/4/w3.css" media="screen" />
-->
        <link rel="stylesheet" href="<?= TEMPLATE_DIR; ?>/css/screen.css" media="screen" />

        <link rel="stylesheet" href="<?= TEMPLATE_DIR; ?>/css/print.css" media="print" />
        <link rel="canonical" href="[wblink<?= $iPageId; ?>]"/>
        <link rel="alternate" type="application/rss+xml" title="Test RSS-Feed" href="<?= WB_URL; ?>/modules/news/rss.php?page_id=13" />

<?php
//echo (sprintf("<!-- [%03d] %s -->\n",__LINE__,$oReg->AppUrl));
//print '<!-- '."\n".'function '.__FUNCTION__.'( '.''.' );'."\n".'filename: '.basename(__FILE__)."\n".'line: '.__LINE__.' -> '."\n";
//print_r(  ); print "\n".'-->'."\n"; flush (); //  ob_flush();;sleep(10); die();
        if (is_callable('LangPageId') && $iMultiLang) {
?>
                <link rel="alternate" hreflang="x-default" href="[wblink<?= LangPageId($sPageLang); ?>]" />
                <link rel="alternate" hreflang="de" href="[wblink<?= LangPageId('DE'); ?>]" />
                <link rel="alternate" hreflang="en" href="[wblink<?= LangPageId('EN'); ?>]" />
<!--
                <link rel="alternate" hreflang="fr" href="[wblink<?= LangPageId('FR'); ?>]" />
                <link rel="alternate" hreflang="nl" href="[wblink<?= LangPageId('NL'); ?>]" />
 -->
<?php
        }

// automatically include optional WB module files (frontend.css)
    register_frontend_modfiles('css');
// automatically include optional WB module files (frontend.js, jQuery) enable OldModFiles in OutputFilter
    register_frontend_modfiles('jquery');
    register_frontend_modfiles('js');
?>
    </head>
    <body class="allcssRes gradient-sweet-home">
        <div id="allcssRes-wrapper" class="main outer-box ">
            <header >
                <div class="banner gradient">
                    <a class="h1" href="<?= WB_URL; ?>/" target="_top"><?php page_title('', '[WEBSITE_TITLE]'); ?></a>
                    <span class="h1"><?php page_title(' - ', '[SPACER]'); ?>[[ShowRootParent]]</span>
                    <div id="header-1" class="sticky header">
                        <nav id='cssmenu' class="w3-blue w3-hide">
                            <div class="logo hide"><a href="<?php echo WB_URL; ?>"><img src="<?php echo TEMPLATE_DIR; ?>/img/logo4.svg" alt="wb-logo"></a></div>
                            <div class="search-button w3-hide" style="width:35px"><a href="#" class="search-toggle" data-selector="#header-1"></a></div>
                            <div class="lang hide"><?php if (trim($sMultiLang)!=''){ echo $sMultiLang; } ?></div>
                            <div id="head-mobile"></div>
                            <div class="button"></div>

<?php
echo $sMainNav;
?>
    </nav>

                    </div>
                </div>
                <!-- frontend search -->
                <div class="search_box gradient round-top-left round-top-right">
<?php
                    // CODE FOR WEBSITE BAKER FRONTEND SEARCH
                    if (SHOW_SEARCH) {
?>
               <!-- Search -->
                        <form id="search" action="<?= WB_URL; ?>/search/index.php" method="get" >
                            <input type="hidden" name="referrer" value="<?= defined('REFERRER_ID') ? REFERRER_ID : $iPageId; ?>" />
                            <div class="input-container">
                            <button type="submit" name="wb_search" id="wb_search" value="" class="search_submit" ><i class="fa fa-search icon" style="cursor: pointer;"></i></button>
                            <input type="text" name="string" class="input-field search" placeholder="<?= $TEXT['SEARCH']; ?>" id="pageInput" type="text" value=""/>
                            </div>
                        </form>
                    <?php } ?>
                </div>
            </header>
            <?php if (!empty(trim($pageContentTeaser))) { ?>
                <div class="teaser">
                    <div class="content">
                        <?= $pageContentTeaser; ?>
                    </div><!-- content -->
                </div><!-- teaser -->
            <?php } ?>
            <input type="checkbox" id="open-menu" />
            <label for="open-menu" class="open-menu-label">
                <span class="title h4">&nbsp;<?php page_title('', '[PAGE_TITLE]'); ?>&nbsp;</span>
                <span class="fa fa-bars" aria-hidden="true">&#160;</span>
            </label>

            <div id="lang" style="height: 32px;">
                <?php if (trim($sMultiLang) != '') { ?>
                    <?= $sMultiLang; ?>
                <?php } ?>
            </div>

            <div id="left-col" class="w4-hide">
                [[iEditThisPage]]
                <div class="content">
                    <!-- left navigation menu -->
<?php if (!empty($menuLeft)){ ?>
                    <nav class="outer-box gradient-sweet-home">
                        <div class="menu" style="font-size: 86%;">
<!--
<?php //echo Lingual::getClassInfo(); ?>
-->
<?php echo $menuLeft;?>
                        </div>
                    </nav>
<?php } ?>
                    <div class="outer-box gradient-sweet-home">
                        <a class="btn" onclick="klaro.show()">Privacy Consent</a>
                    </div>
                    <?php if (trim($pageContentSidebar) != '') { ?>
                        <div class="left-content outer-box gradient-sweet-home">
                            <?= $pageContentSidebar; ?>
                        </div>
                    <?php } ?>
                    <?php if (defined('FRONTEND_LOGIN') && FRONTEND_LOGIN) { ?>
                        <div class="outer-box gradient-sweet-home">
                            [[LoginBox]]
                        </div>
                    <?php } ?>
                </div><!-- content -->
            </div><!-- left-col -->
            <div class="main-content w3-padding-bottom">
                <?= $pageContentMain; ?>
            </div>
            <footer>
                <div class="footer">
                    <?php page_footer('Y',' - ','[WEBSITE_FOOTER]'); ?>
                </div>
            </footer>
        </div>
        <div class="powered_by">
            Powered by <a class="btn" href="https://websitebaker.org" target="_blank" rel="noopener" >WebsiteBaker</a>
        </div>
<script id="bodyjs" src="<?= TEMPLATE_DIR; ?>/js/body.js"></script>
<script defer  src="<?= TEMPLATE_DIR; ?>/js/klaro_config.js"></script>
<script
    defer
    data-config="klaroConfig"
    src="<?= WB_URL;?>/include/plugins/default/klaro/klaro_v0.7.16.js">
</script>

<?php
// if you want to include jquery before body end,
register_frontend_modfiles_body('jquery');
// automatically include optional WB module file frontend_body.js) should be always set
register_frontend_modfiles_body('js');
?>
        [[PrevNextLink]]
    </body>
</html>
