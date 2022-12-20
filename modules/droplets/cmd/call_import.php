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
 * @version         $Id: call_import.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/call_import.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */
/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
$sBackupDir = $sAddonRel.'/data/archiv/';
$aZipFiles = glob($oReg->AppPath.$sAddonRel.'/data/archiv/*.zip', GLOB_NOSORT);
?><form action="<?php echo $sActrionUrl; ?>" method="post" name="droplets_form" enctype="multipart/form-data" >

    <div class="droplet-import block-outer modalDialog" id="import" draggable="true" style="overflow: hidden;">
    <div style="width: 71.525em;">
        <button name="cancel" class="close url-reset" data-overview="<?php echo $ToolRel; ?>" type="button">X</button>
        <header class="modal-label"><h2><?php echo $oTrans->DR_TEXT_RESTORE; ?></h2></header>
        <div class="modal-inner file-select-box">
            <span style="margin-left: 10.525em;"></span>
            <span style="text-align: left; padding: 0.525em 0;">
                <select size="1" name="zipFiles" >
                    <option style=" padding: 0.225em 0.455em;" value=""><?php echo $oTrans->DR_TEXT_PLEASE_SELECT; ?></option>
<?php
foreach( $aZipFiles as $files ) {
      $value =  basename($files);
      $files = str_replace($oReg->AppPath, '', $files );
 ?>
              <option style=" padding: 0.225em 0.455em;" value="<?php echo $files; ?>"><?php echo $value; ?></option>
<?php } ?></select>
            <button class="btn w3-blue-wb w3-hover-green" type="submit" name="command" value="import_droplets"><?php echo $oTrans->DR_TEXT_ARCHIV_LOAD; ?></button>
            <button class="btn w3-blue-wb w3-hover-red" type="submit" name="command" value="delete_archiv"><?php echo $oTrans->TEXT_DELETE; ?></button>
            </span>
            <div class="file-box">
                <span style="margin-left: 10.025em;"> </span>
                <span style="text-align: left; padding: 0.525em 0; display: inline-block; margin: 0.525em;">
                    <input type="file" name="zipFiles" id="file" class="inputfile inputfile-6" data-multiple-caption="{count} files selected" multiple />
                    <label for="file"><span></span> <strong>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
                    <path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/>
                    </svg> Choose a file&hellip;</strong>
                    </label>
                    <button  class="input-file btn w3-blue-wb w3-hover-green command" name="command" value="import_droplets" type="submit"><?php echo $oTrans->DROPLET_MESSAGE_GENERIC_LOCAL_UPLOAD; ?></button>
                </span>
            </div>
        </div>
        <footer class="modal-label">
            <h4 style="margin-left: 0.955em; top: 0.925em; position: relative;">Upload icon by <a href="http://www.flaticon.com/free-icon/outbox_3686" >FlatIcon</a>.</h4>
        </footer>
    </div>
  </div>
</form>
