<?php
/**
 *
 * @category        admin
 * @package         login
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.4
 * @requirements    PHP 5.4 and higher
 * @version         $Id: locking.php 100 2018-09-27 15:03:54Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/admin/settings/locking.php $
 * @lastmodified    $Date: 2018-09-27 17:03:54 +0200 (Do, 27. Sep 2018) $
 *
 */
if (!\defined('SYSTEM_RUN')) {
    $sStartupFile = \dirname(\dirname(__DIR__)).'/config.php';
    if (\is_readable($sStartupFile)) {
        require($sStartupFile);
    } else {
        throw new \Exception(
                            'tried to read a nonexisting or not readable startup file ['
                          . \basename(\dirname($sStartupFile)).'/'.\basename($sStartupFile).']!!'
        );
    }
}
$oTrans = \Translate::getInstance();
$oTrans->enableAddon(ADMIN_DIRECTORY.'\\settings');
$admin = new \admin('Start', 'settings', false, false);

if ($admin->getUserId() == 1) {
    $val = (((int)(\defined('SYSTEM_LOCKED') ? SYSTEM_LOCKED : 0)) + 1) & 1;
    db_update_key_value('settings', 'system_locked', (($val==0) ? 'false' : 'true' ));
}

/* */
// redirect to backend
\header('Location: ' . ADMIN_URL . '/index.php');
exit();
