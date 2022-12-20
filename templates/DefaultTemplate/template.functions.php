<?php

use bin\helpers\{WbAdaptaor,msgQueue};
use bin\helpers\{PreCheck};
use vendor\pclzip\PclZip;
use vendor\phplib\Template;


//if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
//      $localeCode = 'ge';
//else  $localeCode = 'de_DE';
//setlocale(LC_TIME,$localeCode);

date_default_timezone_set('Europe/Berlin');
mb_internal_encoding("UTF-8");
$loc = setlocale(LC_TIME, 'de_DE.UTF8', 'de_DE@euro', 'de_DE', 'deu_deu', 'ge');

// get the $menu array for nav title
require(WB_PATH.'/templates/'.TEMPLATE.'/info.php');
//if(!class_exists('csswalk'))  { require(WB_PATH.'/templates/'.TEMPLATE.'/class.csvwalk.php'); }
$data = new \templates\responsive\Breadcrumb($wb, database::getInstance());
$aMenu = 1;
$sub_expand  = [];
$sub_collaps = [];
$aTwigConfig = [];
$aTwigLoader = [];
$sFilename = (__DIR__).'/default.ini';
if(is_readable($sFilename)){
  $aTwigConfig = parse_ini_file($sFilename, true);
  $aTwigLoader = $aTwigConfig['twig-loader-file'];
  $loader = new \Twig\Loader\FilesystemLoader(__DIR__.$aTwigLoader['templates_dir'] );
  $twig   = new \Twig\Environment( $loader );
  $oTemplate = $twig->load($aTwigLoader['default_template']);
}
if (!function_exists('LoginBox')) {require __DIR__.'/LoginBox.php'; }

$aOptionsExpand  = array('flags' => ( SM2_ALL|SM2_PRETTY|SM2_NUMCLASS|SM2_XHTML_STRICT|SM2_BUFFER));
$aOptionsCollaps = array('flags' => (SM2_TRIM|SM2_PRETTY|SM2_NUMCLASS|SM2_XHTML_STRICT|SM2_BUFFER));
// $aItemOpen = '<li>[ac][menu_title]</a>';
$aItemOpen = '<li class="[if(class=menu-current||class=menu-parent){active}] [if(class==menu-expand){dropdown}]">'
           . '[if(class==menu-expand){<a href="[url]" class="dropdown-toggle" data-toggle="dropdown">[menu_title] <b class="caret"></b></a>}else {[ac][menu_title]</a>}]';
$aItemClose = '</li>';
$aMenuOpen  = '<ul class="nav navbar nav-pills nav-stacked" role="tablist">';   //
$aMenuClose = "</ul>";
$aTopItemOpen   = false;
$aTopMenuOpen   = false;

// show all menues in navigation too
$aMenuAll = ($aMenu==0 ? SM2_ALLMENU : $aMenu);
// show only given menu
$aStart      = SM2_CURR;
$aMaxLevel   = SM2_START+PAGE_LEVEL_LIMIT;
//$aTopMenuOpen   = "<dl class=\"submenu\"><dt>".$menu[$aMenu]."</dt>";
$subExpand [$aMenu] = show_menu2( 1, $aStart, $aMaxLevel, $aOptionsExpand,  $aItemOpen, $aItemClose, $aMenuOpen, $aMenuClose, $aTopItemOpen, $aTopMenuOpen);
$subCollaps[$aMenu] = show_menu2( 1, $aStart, $aMaxLevel, $aOptionsCollaps, $aItemOpen, $aItemClose, $aMenuOpen, $aMenuClose, $aTopItemOpen, $aTopMenuOpen);
$Navigation[$aMenu] = $subCollaps[$aMenu];
$Navigation[$aMenu] = preg_replace('/<\!--.*?-->/siu','',trim($Navigation[$aMenu] ));

$aStart        = SM2_ROOT;
$aMaxLevel     = SM2_ROOT+PAGE_LEVEL_LIMIT;
$NavHeaderCurr = SM2_CURR-$wb->page['level'];
$NavHeaderCurr = -1999;
$NavHeaderCurr = SM2_CURR;
$aMenuOpen  = '<ul class="nav navbar navbar-nav nav-tabs" role="tablist">';   // nav nav-pills nav-stacked
$aItemOpen  = '<li class="[if(class=menu-current||class=menu-parent){active}] [if(class==menu-expand){dropdown}]">'
            . '[if(class==menu-expand){<a href="[url]" class="dropdown-toggle" data-toggle="dropdown">[menu_title] <b class="caret"></b></a>}else {[ac][menu_title]</a>}]';

$aNavOption   = array('flags' => (SM2_TRIM|SM2_PRETTY|SM2_NUMCLASS|SM2_XHTML_STRICT|SM2_BUFFER ) );
$aTopOption   = array('flags' => ( SM2_ALL|SM2_PRETTY|SM2_NUMCLASS|SM2_XHTML_STRICT|SM2_CURRTREE|SM2_BUFFER ) );
/**
 **********************************************************************************************************************
 */
$HeaderNavigation = show_menu2(3, $aStart, $aMaxLevel, $aNavOption, $aItemOpen, $aItemClose,$aMenuOpen,$aMenuClose );
$HeaderNavigation = preg_replace('/<\!--.*?-->/siu','',trim($HeaderNavigation ));

$subMenuTitle = show_menu2(3, SM2_ROOT, SM2_START, SM2_TRIM|SM2_PRETTY|SM2_BUFFER, '[if(class = menu-current || class = menu-parent){[menu_title]}]', '', '', '', '', '');
$subMenuTitle = preg_replace('/<\!--.*?-->/siu', '', trim($subMenuTitle));

$BottomNavigation = show_menu2(2, $aStart, $aMaxLevel, $aNavOption, $aItemOpen, $aItemClose,$aMenuOpen,$aMenuClose );
$BottomNavigation = preg_replace('/<\!--.*?-->/siu','',trim($BottomNavigation ));
if(trim($subMenuTitle) == '') {
  $subMenuTitle = show_menu2(2, SM2_ROOT, SM2_START, SM2_TRIM|SM2_PRETTY|SM2_BUFFER, '[if(class = menu-current || class = menu-parent){[menu_title]}]', '', '', '', '', '');
  $subMenuTitle = preg_replace('/<\!--.*?-->/siu', '', trim($subMenuTitle));
}

$url = $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
if (!preg_match('#^http://#', $url)) $url = 'http://'.$url;

if (LEVEL >= 0) {
    $mainNaviLeftSub = show_menu2(SM2_ALLMENU, SM2_ROOT + 1, SM2_CURR + 1,
        SM2_TRIM | SM2_PRETTY | SM2_NUMCLASS | SM2_XHTML_STRICT|SM2_BUFFER, /* SM2_CURRTREE| */
        '<li class="[class]"><a href="[url]" class="[class]" title="[page_title]">[menu_title]</a>',
        '</li>',
        '<ul class="[class]">',
        '</ul>',
         '',
         ''
    );
} else {
    $mainNaviLeftSub = '';
}
$mainNaviLeftSub = preg_replace('/<\!--.*?-->/siu', '', trim($mainNaviLeftSub));
/* -------------------------------------------------------- */
    $sTemplateFunc = 'resolveTemplateImagesPath';
    $sImages       = $sTemplateFunc();
/* -------------------------------------------------------- */
