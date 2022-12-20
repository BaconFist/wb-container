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
 * @version         $Id: backup_droplets.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/backup_droplets.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use bin\Requester\HttpRequester;
use vendor\pclzip\PclZip;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
    if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; flush(); exit;}
/* -------------------------------------------------------- */

    $sOverviewDroplets = $oTrans->TEXT_LIST_OPTIONS;

// suppress to print the header, so no new FTAN will be set
//$oApp = new admin('Addons', 'templates_uninstall', false);
    if( !$oApp->checkFTAN() ){
        $oApp->print_error($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS, $ToolUrl );
        exit();
    }
// After check print the header

//    if (!\function_exists( 'make_dir' ) ) { require($oReg->AppPath.'/framework/functions.php');  }
    if (!\function_exists('insertDropletFile')) { require($sAddonPath.'/droplets.functions.php'); }
//$oApp->print_header();
// create backup filename with pre index
    $sBackupDir = $sAddonRel.'/data/.archiv/';
    make_dir( $oReg->AppPath.$sBackupDir );

    $sDropletTmpDir = 'temp/modules/'.$sAddonName.'/.tmp/';
    $sDropletTmpDir = $sAddonRel.'/data/.tmp/';
    rm_full_dir($oReg->AppPath.$sDropletTmpDir, true);
    make_dir( $oReg->AppPath.$sDropletTmpDir );

    $sTimeStamp = '_'.PreCheck::getStrftime('Ymd_His', \time() + $oReg->Timezone ).'.zip';

    $FilesInDB = '*';
    $aFullList = \glob($sAddonPath.'/data/.archiv/*.zip', GLOB_NOSORT);

    if (isset($aRequestVars['cb']) && \count($aRequestVars['cb'])) {
        $FilesInDB  = '';
        $setDropletName = (\count($aRequestVars['cb'])===1);
        foreach( $aRequestVars['cb'] as $index => $FileName ) {
            $sSearchFor = $FileName;
            $FilesInDB .= '\''.$FileName.'\',';
        }
        $sBackupName = 'Droplet'.($setDropletName ? '_'.$sSearchFor : 'sBackup').$sTimeStamp;
    } else {
        $sSearchFor  = 'DropletsFullBackup';
        $sBackupName = 'DropletsFullBackup'.$sTimeStamp;
    }
/*
    $aFilesInDir = [];
    foreach ($aFullList as $index =>$sItem) {
        if (\preg_match('/[0-9]+_('.$sSearchFor.'_[^\.]*?)\.zip/si', $sItem, $aMatch)) {
            $aFilesInDir[$index+1] = $aMatch[1];
        }
    }
    unset($aFullList);
*/
    $sZipFile = $sBackupDir.$sBackupName;
    $aFilesToZip = backupDropletFromDatabase( $oReg->AppPath.$sDropletTmpDir, \rtrim($FilesInDB, ','), $oDb );
    $oArchive = new PclZip($oReg->AppPath.$sZipFile);
    $archiveList = $oArchive->create(
                   $aFilesToZip
                  ,PCLZIP_OPT_REMOVE_ALL_PATH
              );
    if ($archiveList == 0){
        echo 'Packaging error: '.$oArchive->errorInfo(true);
        msgQueue::add("Error : ".$oArchive->errorInfo(true));
    } elseif(\is_readable($oReg->AppPath.$sBackupDir)) {
?>
    <header class="droplets"><h4 >Create archive: <?php echo \basename($sZipFile); ?></h4></header>
    <section class="droplets drop-outer">
      <ol>
<?php
    foreach($archiveList AS $key=>$aDroplet ) {
?>
        <li>Backup <strong> <?php echo $aDroplet['stored_filename']; ?></strong></li>
<?php } ?>

      </ol>
      <div class="drop-backup">
        <p>Backup created - <a class="btn w3-blue-wb w3-hover-green" href="<?= $oReg->AppUrl.$sBackupDir.$sBackupName; ?>"><?= $oTrans->DROPLET_MESSAGE_GENERIC_LOCAL_DOWNLOAD; ?></a>
                  <button style="padding: 0.2825em 0.8525em; " name="cancel" data-overview="<?= $ToolRel; ?>" class="btn w3-blue-wb w3-hover-red url-reset" type="button"><?= $oTrans->TEXT_CANCEL; ?></button>
        </p>
      </div>
    </section>
<?php  } else {
    msgQueue::add('Backup not created - '.$oTrans->TEXT_BACK.'');
    }
