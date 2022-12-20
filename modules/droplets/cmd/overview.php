
<?php
/**
 *
 * @category        Addon
 * @package         droplet
 * @subpackage      overview
 * @author          Ruud Eisinga (Ruud)
 * @contribute      John (PCWacht), Dietmar Wöllbrink (Luisehahne))
 * @author          WebsiteBaker Project
 * @copyright       Ryan Djurovich
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: overview.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/overview.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use vendor\phplib\Template;


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

    $msg = [];
    if (!$oApp->get_permission($sAddonName,'module' ) ) {
        $oApp->print_error($oTrans->MESSAGE_ADMIN_INSUFFICIENT_PRIVELLIGES, $js_back);
        exit();
    }
// Get userid for showing admin only droplets or not
    $loggedin_user  = ($oApp->ami_group_member('1') ? 1 : $oApp->getUserId() );
    $loggedin_group = $oApp->get_groups_id();
    $bAdminUser     = (($oApp->ami_group_member('1') ) || ($oApp->getUserId() === '1'));
//removes empty entries from the table so they will not be displayed
    $sql = 'DELETE FROM `'.TABLE_PREFIX.'mod_droplets` '
         . 'WHERE name = \'\' ';
    if (!$oDb->query($sql) ) {
        $msg[] = $oDb->get_error();
    }
// if import failed after installation, should be only 1 time
    $sql = 'SELECT COUNT(`id`) FROM `'.TABLE_PREFIX.'mod_droplets` ';
    if (!$oDb->get_one($sql) ) {
        include($sAddonPath.'/install.php');
    }

    function check_syntax($code) {
        $bRetval = false;
        // PHP Syntax errors?
        $bRetval = eval('return true;' .$code);
        return $bRetval;
    }

    $sql = 'SELECT * FROM `'.TABLE_PREFIX.'mod_droplets` ';
    $sql .= ($bAdminUser ? '' : 'WHERE `admin_view` NOT IN (1)');
    $sql .= 'ORDER BY `modified_when` DESC';
    if ($oDroplets = $oDb->query($sql)) {
        $num_droplets = $oDroplets->numRows();
    }
    $aFtan = $oApp->getFTAN('');
// prepare default data for phplib and twig
    $aTplData = [
            'action' => $action,
            'MODULE_NAME' => $sAddonName,
            'FTAN_NAME' => $aFtan['name'],
            'FTAN_VALUE' => $aFtan['value'],
            'IDKEY0' => $oApp->getIDKEY(0),
            ];

// Create new Template object with phplib  IDKEY0
    $oTpl = new Template($sAddonThemePath.'/');
    $oTpl->setDebug(0);
    $oTpl->set_file('page', 'overview.htt');
    $oTpl->set_block('page', 'main_block', 'main');
    $oTpl->set_var($aLang);
    $oTpl->set_var($aTplDefaults);
    $oTpl->set_var($aTplData);
    $oTpl->set_block('main_block', 'list_droplet_block', 'list_droplet');
/*----------------------------------------------------------------------------------------------------------------------*/
    while(!is_null($aDroplets = $oDroplets->fetchRow(MYSQLI_ASSOC)))
    {
//        if (\is_null($aDroplets)){continue;}
        $aComment =  [];
        $modified_user = $oTrans->TEXT_UNKNOWN;
        $modified_userid = 0;
        $sql =
        'SELECT `display_name`,`username`, `user_id` FROM `'.TABLE_PREFIX.'users`
        WHERE `user_id` = '.(int)$aDroplets['modified_by'].'
        ';
        $get_modified_user = $oDb->query($sql);
        if ($get_modified_user->numRows() > 0) {
            $fetch_modified_user = $get_modified_user->fetchRow(MYSQLI_ASSOC);
            if (!\is_null($fetch_modified_user)){
                $modified_user = $fetch_modified_user['username'];
                $modified_userid = $fetch_modified_user['user_id'];
            }
        }
        $sDropletName  = \mb_strlen($aDroplets['name']) > 20 ? \mb_substr($aDroplets['name'], 0, 19).'…' : $aDroplets['name'];
        $sDropletName  = \preg_replace('/^(.{5,20})\s(.*)$/su', '\1…', \str_replace('"', '', $sDropletName));

//        $sDropletDescription  =  mb_strlen($aDroplets['description']) > 60 ? mb_substr($aDroplets['description'], 0, 59).'…' : $aDroplets['description'];
        $sDropletDescription  =  $aDroplets['description'];
//        $sDropletDescription = preg_replace('/^(.{5,60})\s(.*)$/su', '\1…',  str_replace('"', '', $sDropletDescription));
//        $iDropletIdKey = $aDroplets['id'];
        $iDropletIdKey = $oApp->getIDKEY($aDroplets['id']);
        $comments = '';
//        $comments = str_replace(array("\r\n", "\n", "\r"), '<br >', $aDroplets['comments']);
        if (!\strpos($comments,"[[")) $comments = "Use: [[".$aDroplets['name']."]]<br />".$comments;
        $comments = \str_replace(["[[", "]]"], ['<b>[[',']]</b>'], $comments);

        $valid_code = true;
        try {
            $valid_code = ($aDroplets['active'] ? check_syntax($aDroplets['code']) : true);
        } catch (\ParseError $ex) {
            \trigger_error('Error in droplet ['.$sDropletName.'] '.$ex->getMessage(), \E_USER_NOTICE);
            $valid_code = false;
        }

        if (!$valid_code === true) {$comments = '<span color=\'red\'><strong>'.$oTrans->DR_TEXT_INVALIDCODE.'</strong></span><br />'.$comments;}
        $unique_droplet = true; // not used till yet
        if ($unique_droplet === false ) {$comments = '<span color=\'red\'><strong>'.$oTrans->DR_TEXT_NOTUNIQUE.'</strong></span><br />'.$comments;}

        $aTplData = [
            'iDropletIdKey'         => $iDropletIdKey,
            'sDropletName'          => $sDropletName,
            'sDropletTitle'         => $aDroplets['name'],
            'comments'              => '',
            'icon'                  => ($valid_code && $unique_droplet ? 'droplet' : 'invalid'),
            'sDropletDescription'   => $sDropletDescription,
            'sDescriptionTitle'     => $aDroplets['description'],
            'DropletId'             => $aDroplets['id'],
            'iDropletId'            => $aDroplets['id'],
            'modified_when'         => \date('d.m.Y'.' '.'H:i', $aDroplets['modified_when']+TIMEZONE),
            'active'                => $aDroplets['id'],
            'ActiveIcon'            => ($aDroplets['active'] ? '1' : '0'),
        ];

/*----------------------------------------------------------------------------------------------------------------------*/
        $oTpl->set_var($aTplData);
        $oTpl->parse('list_droplet', 'list_droplet_block', true);
    } // end while droplets
/*-- finalize the page -----------------------------------------------------------------*/

    $oTpl->parse('main', 'main_block', false);
    $oTpl->pparse('output', 'page');
