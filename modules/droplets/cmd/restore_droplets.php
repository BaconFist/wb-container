<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        modules
 * @package         droplets
 * @subpackage      restore_droplets
 * @author          Dietmar Wöllbrink
 * @copyright       WebsiteBaker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.12.1
 * @requirements    PHP 5.6 and higher
 * @version         $Id: restore_droplets.php 319 2019-03-30 05:54:24Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/droplets/cmd/restore_droplets.php $
 * @lastmodified    $Date: 2019-03-30 06:54:24 +0100 (Sa, 30. Mrz 2019) $
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use vendor\pclzip\PclZip;
use bin\requester\HttpRequester;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

    $aUnzipDroplets = [];
    $oRequest = HttpRequester::getInstance();

    $bUpdateDroplets = $oRequest->getParam('enabled_overwrite_droplet',FILTER_VALIDATE_BOOLEAN);

    $sArchiveFile = $oReg->AppPath.$aRequestVars['ArchiveFile'];
    if (!$oApp->checkFTAN() ) {
        msgQueue::add($oTrans->MESSAGE_GENERIC_SECURITY_ACCESS);
    } elseif(!isset($aRequestVars['restore_id']) || !\is_array($aRequestVars['restore_id'])) {
        msgQueue::add('::'.$oTrans->DROPLET_MESSAGE_MISSING_UNMARKED_ARCHIVE_FILES );
    } else {
        $aDroplet = [];
        if (!\function_exists('insertDropletFile')) {require($sAddonPath.'/droplets.functions.php');}
      // unzip to buffer and store in DB / fetch ach entry as single process, to surpress buffer overflow
        foreach($aRequestVars['restore_id'] as $index => $iArchiveIndex ) {
            $oArchive = new PclZip($sArchiveFile);
            $sDroplet = $oArchive->extract(PCLZIP_OPT_BY_INDEX, $iArchiveIndex,
                                           PCLZIP_OPT_EXTRACT_AS_STRING);
            if ($sDroplet == 0) {
                  msgQueue::add(\sprintf('[%04d] UNABLE TO UNZIP FILE :: %s',__LINE__,$oArchive->errorInfo(true)));
            } else {
                $aDroplet['name'] = $sDroplet[0]['filename'];
                $aDroplet['content'] = \explode("\n",$sDroplet[0]['content']);
//                if ( !preg_match('/'.$file_types.'/si', $aDroplet['name'], $aMatch) ) {
//                  continue; }
                if ($sTmp = insertDroplet($aDroplet, $oDb, $oApp, $bUpdateDroplets)) {
                    $aUnzipDroplets[] = $sTmp;
                }
            }
        }
//
        if (($error = $oArchive->errorCode()) != 0 )
        {
            msgQueue::add(\sizeof( $aUnzipDroplets ).' '. $oTrans->DROPLET_IMPORT_ARCHIV_IMPORTED);
        } else {
            if (\sizeof( $aUnzipDroplets ) > 0 ) {
                msgQueue::add(\implode(', ',$aUnzipDroplets).'<br />'.\sizeof( $aUnzipDroplets ).' '. $oTrans->DROPLET_IMPORT_ARCHIV_IMPORTED, true);
            } else {
                msgQueue::add(\sizeof( $aUnzipDroplets ).' '. $oTrans->DROPLET_IMPORT_ARCHIV_IMPORTED, true);
            }
        }
    }
