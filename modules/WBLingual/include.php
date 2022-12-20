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
 * Description of Translate
 *
 * @category     Addon
 * @package      Addon package
 * @subpackage   Name of subpackage if needed
 * @copyright    Dietmar Wöllbrink <dietmar.woellbrink@websitebaker@org>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @author       Dietmar Wöllbrink <dietmar.woellbrink@websitebaker@org>
 * @license      GNU General Public License 3.0
 * @version      1.0.0-dev.1
 * @revision     $Revision: 300 $
 * @lastmodified $Date: 2019-03-27 10:00:11 +0100 (Mi, 27. Mrz 2019) $
 * @since        File available since 02.12.2017
 * @deprecated   no
 * @description  xxx
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use addon\WBLingual\Lingual;


if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

if (!\is_callable('LangPageId')){
    function LangPageId($sLang){
        $sLang = \strtoupper($sLang);
        $oReg = WbAdaptor::getInstance();
// have to be enabled
        if ($oReg->PageLanguages) {
            $oReg->PageLanguages = \filter_var($oReg->PageLanguages, \FILTER_VALIDATE_BOOLEAN);
            $oPageLang = new Lingual($oReg);
            $aLink = $oPageLang->getPageLangDetails();
// return page_id given by $sLang || valide page_id
            $iRetval = (isset($aLink[$sLang]['page_id']) ? $aLink[$sLang]['page_id'] : $oReg->App->page_id );
        }
        return ($iRetval ?? false);
    }
}

if (!\is_callable('getLangChildPageId')){
    /**
     * getLangChildPageId()
     *
     * @param string $sLang
     * @param string $sMenuTitle
     * @return page_id
     */
    function getLangChildPageId($sLang='en',$sMenuTitle=''){
        $iRetval = 0;
        $sLang = \strtoupper($sLang);
        $oReg = WbAdaptor::getInstance();
        $sSqlWhere = '
        WHERE
        '.(!empty($sMenuTitle) ? '`menu_title` = \''.($sMenuTitle).'\'' : '`level` = 0
          AND `parent`= 0
          AND `root_parent` = 0 ');
        $sSqlId = '
        SELECT
        `page_id` FROM `'.$oReg->Db->TablePrefix.'pages` '
         . $sSqlWhere.'
          AND `language` = \''.$sLang.'\'
          AND `visibility` NOT IN (\'hidden\',\'deleted\',\'none\')
        ';

        if (!($iRetval = $oReg->Db->get_one($sSqlId))){
          $sErrorMsg = sprintf('[%d] DB Error %s given menu_title \"%s\" ',__LINE__, $oReg->Db->get_error(),$sMenuTitle);
//          \trigger_error($sErrorMsg, E_USER_NOTICE);
        }
        return (int)$iRetval;
    }
}

if (!\is_callable('getLangStartPageIds')){
    function getLangStartPageIds($sLang='')
    {
        $sLang =(!empty($sLang) ? \strtoupper($sLang) : '');
        $aRetval = (!empty($sLang) ? [] : null);
        $oReg = WbAdaptor::getInstance();
// have to be enabled
        if ($oReg->PageLanguages) {
//            $oReg->PageLanguages = \filter_var($oReg->PageLanguages, \FILTER_VALIDATE_BOOLEAN);
            $oPageLang = new Lingual($oReg);
            $aLinks = $oPageLang->getPagesDetail();
            foreach ($aLinks as $aLang){
// return page_id given by $aLang || valide page_id
                if ((!empty($sLang) && ($aLang['language']==$sLang)) && ($aLang['parent']==$aLang['root_parent']))
                {
                    $aRetval = ($aLang['page_id'] ?? null );
                    break;
                } else {
                    $aRetval[] = ($aLang['page_id'] ?? [] );
                }
            }
        }
        return ($aRetval ?? []);
    }
}


if (!\is_callable('language_menu')){
    function language_menu($sExtension = "auto", $bOutput=true)
    {
        $oReg = WbAdaptor::getInstance();
        $sRetVal = '';
        $oReg->PageLanguages = \filter_var($oReg->PageLanguages, \FILTER_VALIDATE_BOOLEAN);
        if ($oReg->PageLanguages) {
        $sExtension = \strtolower($sExtension);
        switch($sExtension)
        {
            case 'gif':
            case 'png':
            case 'svg':
                break;
            default:
                $sExtension = 'auto';
        }
        $oPageLang = new Lingual($oReg);
        $oPageLang->setExtension($sExtension);
        $sRetVal = \trim($oPageLang->getLangMenu());
        if ($bOutput){echo $sRetVal;}
        }
        return $sRetVal ?? false;
    }
}
