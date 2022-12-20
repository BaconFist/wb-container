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
 * @version         $Id: save_rename.php 92 2018-09-20 18:04:03Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/tags/2.12.1/modules/droplets/cmd/save_rename.php $
 * @lastmodified    $Date: 2018-09-20 20:04:03 +0200 (Do, 20 Sep 2018) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {
  die('Cannot access '.basename(__DIR__).'/'.basename(__FILE__).' directly');
/* -------------------------------------------------------- */
} else {
    if (($droplet_id === false)) {
     //   $oApp->print_header();
        $oApp->print_error('IDKEY_DROPLET::'. $oTrans->MESSAGE_GENERIC_SECURITY_ACCESS, $ToolUrl );
    }
    $modified_when = time();
    $modified_by   = (int) $oApp->getUserId();
    $sNewName      = trim($aRequestVars['title']);
    if (!$sNewName) {
        msgQueue::add($oTrans->DROPLET_MESSAGE_GENERIC_MISSING_TITLE);
    } else {
        $sOldName      = trim(@$aRequestVars['existingTitle']?:$sNewName);
        $sGenericNewName = getUniqueName($oDb, $sNewName);
        switch ($subCommand):
            case 'add_droplet':
                $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_droplets` SET '
                  .    '`name`=\''.$oDb->escapeString($sGenericNewName).'\','
                  .    '`code`=\'\', '
                  .    '`description`=\'\', '
                 .     '`modified_when`='.$modified_when.','
                 .     '`modified_by`='.$modified_by.','
                  .    '`active`=0,'
                  .    '`admin_edit`=0,'
                  .    '`admin_view`=0,'
                  .    '`show_wysiwyg`=0,'
                  .    '`comments`=\'\'';
                break;
            case 'copy_droplet':
                $sql = 'INSERT INTO `'.TABLE_PREFIX.'mod_droplets` '
                  .        'SELECT '
                  .        'NULL,'
                  .        '\''.$oDb->escapeString($sGenericNewName).'\','
                  .        '`code`,`description`,'.$modified_when.','.$modified_by.','
                  .        '`active`,`admin_edit`,`admin_view`,`show_wysiwyg`,`comments`'
                 . 'FROM `'.TABLE_PREFIX.'mod_droplets` '
                     .     'WHERE `name`=\''.$oDb->escapeString($sOldName).'\'';
                break;
            case 'rename_droplet':
                if ($sNewName == $sOldName) {$sGenericNewName = $sOldName;}
                $sql = 'UPDATE `'.TABLE_PREFIX.'mod_droplets` '
                     . 'SET `name`=\''.$sGenericNewName.'\', '
                  .        '`modified_when`='.$modified_when.','
                  .        '`modified_by`='.$modified_by.' '
                     . 'WHERE `name`=\''.$sOldName.'\'';
                break;
            default: /* do nothing */  break;
        endswitch;
        if (!$oDb->query($sql))
        {
            if($oDb->is_error()) {
                msgQueue::add($oDb->get_error());
            }
        } else {
            msgQueue::add($oTrans->TEXT_SUCCESS, true );
        }
    }
}
