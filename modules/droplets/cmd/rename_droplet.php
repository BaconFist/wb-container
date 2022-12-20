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
 * @version         $Id: rename_droplet.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/rename_droplet.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use src\Security\{CsfrTokens,Randomizer};
use src\Interfaces\Requester;
use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
    $oReg     = WbAdaptor::getInstance();
    $oTrans   = $oReg->getTranslate();
    $oRequest = $oReg->getRequester();
    $database = database::getInstance();
    $oApp     = $oReg->getApplication();
    $isAuth   = $oApp->is_authenticated();
/* -------------------------------------------------------- */
   if ($bLocalDebug){ echo nl2br(sprintf("[%03d] %s \n",__LINE__,$droplet_id));}
    $iDropletAddId = ($oApp->getIdFromRequest($droplet_id));
    if (isset($iDropletAddId) && ($iDropletAddId >-1)) {
        $droplet_id = $iDropletAddId;
    }

    if (!isset($iDropletAddId)) {
        $sErrorMsg = sprintf("%s :: %s",$action,$oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
        $oApp->print_error($sErrorMsg, $ToolUrl);
        exit();
    }

/* ------------------------------------------------------------------ */

    $sOverviewDroplets = $oTrans->DR_TEXT_DROPLETS;
    $sTimeStamp = (isset($sTimeStamp) ? $sTimeStamp :'');
    $modified_by = $oApp->getUserId();

    switch ($action):
            case 'rename_droplet':
                $sHeaderDroplet = $oTrans->DR_TEXT_ADD_DROPLET;
                $sDropletHelp = $oTrans->DROPLET_HELP_DROPLET_RENAME_ADD;
                if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s %s \n",__LINE__,$action,$droplet_id));}
                break;
            case 'copy_droplet':
                $sHeaderDroplet = $oTrans->DROPLET_HEADER_COPY_DROPLET;
                $sDropletHelp   = $oTrans->DROPLET_HELP_DROPLET_COPY;
                if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s %s \n",__LINE__,$action,$droplet_id));}
                break;
            default:
                $sHeaderDroplet = $oTrans->DR_TEXT_ADD_DROPLET;
                $sDropletHelp = $oTrans->DROPLET_HELP_DROPLET_RENAME_ADD;
                if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s %s \n",__LINE__,$action,$droplet_id));}
        endswitch;

    if ((int)($droplet_id > 0)) {
        $sql  = 'SELECT
        *
        FROM `'.TABLE_PREFIX.'mod_droplets`
        WHERE `id` = '.$droplet_id.'
        ';
        if ($bLocalDebug){echo nl2br(sprintf("[%03d] %s \n",__LINE__,$sql));}
        $oDroplet = $oDb->query($sql);
        $aDroplet = $oDroplet->fetchRow(MYSQLI_ASSOC);
        $content  = (htmlspecialchars($aDroplet['code']));
        $DropletName    = $aDroplet['name'];
        $sSubmitButton  = $oTrans->TEXT_SAVE;
        $iDropletIdKey  = $oApp->getIDKEY($droplet_id);
        $iDropletAddId  = $droplet_id;
    } else if (isset($aCopyDroplet)){
        $aDroplet = $aCopyDroplet;
        $DropletName   = $aDroplet['name'];
        $sSubmitButton = $oTrans->TEXT_ADD;
        $iDropletIdKey = $droplet_id;
    } else {
        $aDroplet = [];
        // check if it is a normal add or a copy
        if (sizeof($aDroplet)===0) {
            $aDroplet = array(
                'id' => 0,
                'name' => 'Dropletname',
                'code' => 'return true;',
                'description' => '',
                'modified_when' => 0,
                'modified_by' => 0,
                'active' => 0,
                'admin_edit' => 0,
                'admin_view' => 0,
                'show_wysiwyg' => 0,
                'comments' => ''
                );
            $DropletName   = $aDroplet['name'];
            $content = '';
        }
        $sSubmitButton = $oTrans->TEXT_ADD;
        $iDropletIdKey = $oApp->getIDKEY($aDroplet['id']);
    }
    $aFtan = $oApp->getFTAN('');
    // prepare default data for phplib and twig
    $aTplData = array (
        'action' => $action,
        'FTAN_NAME' => $aFtan['name'],
        'FTAN_VALUE' => $aFtan['value'],
        'DropletName' => $aDroplet['name'],
        'iDropletAddId' => $iDropletAddId,
        'iDropletIdKey' => $iDropletIdKey,
        'show_wysiwyg' => $aDroplet['show_wysiwyg'],
        'sSubmitButton' => $sSubmitButton,
        'HEADER_DROPLET' => $sHeaderDroplet,
        'sDropletHelp' => $sDropletHelp,
        );
// Create new Template object with phplib
    $oTpl = new Template($sAddonThemePath, 'keep' );
    $oTpl->set_file('page', 'rename_droplet.htt');
    $oTpl->set_block('page', 'main_block', 'main');
    $oTpl->set_var($aLang);
    $oTpl->set_var($aTplDefaults);
    $oTpl->set_var($aTplData);
    $oTpl->set_block('main_block', 'show_admin_edit_block', 'show_admin_edit');
    if ($oApp->ami_group_member('1') || $aDroplet['admin_edit'] == 0 ) {
        $oTpl->parse('show_admin_edit', 'show_admin_edit_block', true);
    } else {
        $oTpl->set_block('show_admin_edit', '');
    }
/*-- finalize the page -----------------------------------------------------------------*/
    $oTpl->parse('main', 'main_block', false);
    $oTpl->pparse('output', 'page');
