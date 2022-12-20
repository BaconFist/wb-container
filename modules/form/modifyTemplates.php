<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
/**
* newEmptyPHP
*
* @deprecated no / since 0000/00/00
* @description xxx
*/
//declare(strict_types=1);
//declare(encoding='UTF-8');

// namespace wx\yz

use bib\WbAdaptor;



    $sAddonPath = str_replace('\\','/',(__DIR__));
    $sAddonName = \basename($sAddonPath);
    $sModulesPath = \dirname($sAddonPath);
    $sPattern = "/^(.*?\/)modules\/.*$/";
    $sAppPath = preg_replace ($sPattern, "$1", $sAddonPath, 1 );
    if (!defined('SYSTEM_RUN')) {require($sAppPath.'/config.php');}
/* -------------------------------------------------------- */
    $sCallingScript = $oReg->Request->getServerVar('SCRIPT_NAME');
    $sqlEOL = (defined('DEBUG') && DEBUG ? PHP_EOL : '');
/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 File not found'; flush(); exit;}
/* -------------------------------------------------------- */

    $admin_header = true;
// Tells script to update when this page was last updated
    $update_when_modified = false;
// show the info banner
    $print_info_banner = true;
// Include WB admin wrapper script
    require(WB_PATH.'/modules/admin.php');

    $bExcecuteCommand = false;
    if (\is_readable($sModulesPath.'/SimpleCommandDispatcher.inc.php')) {require ($sModulesPath.'/SimpleCommandDispatcher.inc.php');}
    $aMsg = [];
//    $sAddonUrl  = WB_URL.'/modules/'.$sAddonName.'/';
    $sQueryStr = ($oRequest->getServerVar('QUERY_STRING') ?? '');
    $sArgSeperator = ini_get('arg_separator.output');
    $aQueryStr = explode($sArgSeperator, $sQueryStr ?? []);
    $sAddonBacklink = $sAddonUrl.'/'.\basename(__FILE__).'?page_id='.(int)$page_id;
    $sSectionIdPrefix = (defined('SEC_ANCHOR') && !empty(SEC_ANCHOR) ? SEC_ANCHOR : 'Sec' );
    $sBackLink = ADMIN_URL.'/pages/modify.php?page_id='.(int)$page_id;

    $sAction = ($aRequestVars['action'] ?? 'show');
    $sAction = (isset($aRequestVars['save_pagetree']) ? 'save_pagetree' : $sAction);
    $sAction = (isset($aRequestVars['save']) ? 'save' : $sAction);
    switch ($sAction):
        case 'save_pagetree':
          $sAddonBacklink = $sBackLink;
        case 'save':
            $tplData = ($aRequestVars['tpl_data'] ?? '');
            $tplData = ($admin->StripCodeFromText($tplData));
            if (!empty($tplData)){
                if (\file_put_contents($sAddonPath.$aRequestVars['edit_file'], $tplData)){
                  $aMsg[] = $oTrans->MESSAGE_PAGES_SAVED;
                  $admin->print_success(\implode('<br />',$aMsg), $sAddonBacklink.$sArgSeperator.'section_id='.(int)$section_id);
                }
            }//$tplData
    endswitch;

//    $sBackAddonLink =
// include edit area wrapper script
    if (!\function_exists('loader_help')){require(WB_PATH.'/include/editarea/wb_wrapper_edit_area.php');}
// check existing submessage.htt otherwise fetch default and save in right folder
    $sTplDefaultFile = $sAddonPath.rtrim($sAddonDefaultTemplateRel,'/').'/submessage.htt';
    $sTplFile        = $sAddonPath.rtrim($sAddonTemplateRel,'/').'/submessage.htt';
    $sTplFile        = (is_readable($sTplFile) ? $sTplFile : $sTplDefaultFile);
    $tpl_file        = $sAddonTemplateRel.'/submessage.htt';

    // store content of the template file in variable
    $tplContent = \file_get_contents($sTplFile, false, null, 0, \filesize($sTplFile));
    echo (function_exists('registerEditArea')) ? registerEditArea('code_area', 'html') : 'none';

?>
     <h2 class="w3-margin-left"><?= sprintf($oTrans->TEXT_SUBMESSAGE_FILE,$tpl_file); ?></h2>
     <form id="edit_module_file" action="#" method="post" style="margin: 0;">
        <input type="hidden" name="page_id" value="<?= $page_id; ?>" />
        <input type="hidden" name="section_id" value="<?= $section_id; ?>" />
        <input type="hidden" name="mod_dir" value="<?= $sAddonName; ?>" />
        <input type="hidden" name="edit_file" value="<?= $tpl_file; ?>" />
        <?= $admin->getFTAN(); ?>
        <div class="w3-row">
            <textarea class="w3-textarea w3-border w3-margin-top" id="code_area" name="tpl_data" cols="80" rows="20" wrap="virtual">
            <?= \htmlspecialchars($tplContent); ?>
        </textarea>
        </div>

        <div class="w3-margin-top w3-margin-bottom">
            <div class="w3-container w3-cell w3-mobile">
                 <input class="w3-blue-wb w3-hover-green w3-padding-6" name="save" type="submit" value="<?= $oTrans->TEXT_SAVE; ?>" style="min-width: 10.25em;"/>
            </div>
            <div class="w3-container w3-cell w3-mobile">
                <input class="w3-blue-wb w3-hover-green w3-padding-6" name="save_pagetree" type="submit" value="<?= $oTrans->TEXT_SAVE.' & '.$oTrans->TEXT_CLOSE; ?>" style="min-width: 10.25em;"/>
            </div>
            <div class="w3-container w3-cell w3-mobile">
                <input id="cancel" class="w3-blue-wb w3-hover-green w3-padding-6" type="button" value="<?= $oTrans->TEXT_CLOSE; ?>" onclick="window.location='<?= $sBackLink; ?>';" style="min-width: 10.25em;" />
            </div>
        </div>

      </form>
<?php
// Print admin footer
$admin->print_footer();
