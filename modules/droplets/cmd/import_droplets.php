<?php
/**
 *
 * @category        module
 * @package         droplet
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.3
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: import_droplets.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/import_droplets.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    if (!$oApp->checkFTAN()){
        $oApp->print_error(sprintf('[%04d] %s',__LINE__,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS), $ToolUrl );
        exit();
    }

//$sDropletTmpDir = 'temp/modules/'.$sAddonName.'/tmp/';
        $sDropletTmpDir = $sAddonRel.'/data/tmp/';
        rm_full_dir($oReg->AppPath.$sDropletTmpDir, true);
        make_dir( $oReg->AppPath.$sDropletTmpDir );

        if( isset( $_FILES['zipFiles'] ) && !$_FILES['zipFiles']['error']) {
            $aRequestVars['uploads']  = $_FILES['zipFiles'];
            $sArchiveFile = $_FILES['zipFiles']['tmp_name'];
            move_uploaded_file (
                 $_FILES['zipFiles']['tmp_name'] ,
                 $oReg->AppPath.$sDropletTmpDir. $_FILES['zipFiles']['name']
            );
            $sArchiveFile = ($sDropletTmpDir. $_FILES['zipFiles']['name'] );
        } else {
            $sArchiveFile = ($aRequestVars['zipFiles']);
        }

        if (!is_readable($oReg->AppPath.$sArchiveFile)) {
            \bin\helpers\msgQueue::add( $oTrans->DROPLET_MESSAGE_GENERIC_MISSING_ARCHIVE_FILE );
        } elseif (is_readable($oReg->AppPath.$sArchiveFile)) {
            $oArchive = new \vendor\pclzip\PclZip($oReg->AppPath.$sArchiveFile );
            $aFilesInArchiv = $oArchive->listContent();
            if ($aFilesInArchiv == 0) {
                \bin\helpers\msgQueue::add( $oTrans->DROPLET_MESSAGE_GENERIC_MISSING_ARCHIVE_FILE );
            } else {
                $aFtan = $oApp->getFTAN('');
                //  prepare default data for phplib and twig
                $aTplData = array (
                    'FTAN_NAME' => $aFtan['name'],
                    'FTAN_VALUE' => $aFtan['value'],
                    'MODULE_NAME' => $sAddonName,
                    'sArchiveFile' => $sArchiveFile,
                    'sArchiveFilename' => basename($sArchiveFile),
                    'THEME_URL' => THEME_URL,
                    );
        //  Create new Template object with phplib
                $oTpl = new Template($sAddonThemePath, 'keep' );
                $oTpl->set_file('page', 'import_droplets.htt');
                $oTpl->set_block('page', 'main_block', 'main');
                $oTpl->set_var($aLang);
                $oTpl->set_var($aTplDefaults);
                $oTpl->set_var($aTplData);
                $oTpl->set_block('main_block', 'list_archiv_block', 'list_archiv');
                $oTpl->set_block('list_archiv_block', 'show_archiv_folder_block', 'show_archiv_folder');
                foreach ($aFilesInArchiv as $key=>$value) {
                    $aData = array (
                        'index' => $value['index'],
                        'filename' => basename($value['filename'],'.php'),
                        'comment' => $value['comment'],
                        'size' => $value['size'],
                        'created_when' => date('d.m.Y'.' '.'H:i', $value['mtime']+TIMEZONE),
                        );
                    $oTpl->set_var($aData);
                    if ($value['folder']==true) {
                        $oTpl->parse('show_archiv_folder', 'show_archiv_folder_block', true);
                    } else {
                        $oTpl->set_block('show_archiv_folder', '');
                    }
                    $oTpl->parse('list_archiv', 'list_archiv_block', true);
                }
/*-- finalize the page -----------------------------------------------------------------*/
                $oTpl->parse('main', 'main_block', false);
                $oTpl->pparse('output', 'page');
            }
        }
