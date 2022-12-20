<?php

/*
 * Copyright (C) 2017 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Description of SessionTrait
 *
 * @package      Vendor_Captcha
 * @copyright    Manuela v.d.Decken
 * @author       Manuela v.d.Decken
 * @license      GNU General Public License 2.0
 * @version      0.0.1
 * @revision     $Id:  $
 * @since        File available since 18.02.2019
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
declare(strict_types = 1);
//declare(encoding = 'UTF-8');

namespace App\traits;

use bin\WbAdaptor;
use bin\helpers\{PreCheck,msgQueue,ParentList};

/**
 * short description of trait
 */
trait CaptchaTrait
{

    public function checkCaptcha($iFlags=0)
    {
        $bRetval = false;
        $aRequestVars = $this->request_vars; // don't know where it come from
        if (isset($aRequestVars['captcha']) && isset($_SESSION['captcha']) && !empty($_SESSION['captcha'])){
            $bRetval = $_SESSION['captcha'] == $aRequestVars['captcha'];
        }
        return $bRetval;
    }

    public function captchaSettings()
    {
        $oReg = WbAdaptor::getInstance();
        $oDb = $oReg->getDatabase();
        $sValue = '';
        $sql  = 'SELECT `enabled_captcha` FROM `'.$oReg->TablePrefix.'mod_captcha_control` ';
        if ($sValue = $oDb->get_one($sql)){
        }
        return (int)$sValue;
    }

    public function getCaptchaFlags($iFlags=0)
    {
        $aRetval = ['enabled_loginform','enable_signup','enabled_lostpassword'];
        $oReg = WbAdaptor::getInstance();
        $oApp = $this->oReg->getApplication();
        $bRetval = $oApp->bit_isset($this->captchaSettings(),$iFlags);
        return $bRetval;
    }

    public function isCaptcha($iFlags=0)
    {
      //
    }

  /**
   * Save CAPTCHA data to old style session
   * set in methode saveData line 2501
   */
/*
    public function getWBSession()
    {
        global $_SESSION;
        $_SESSION[$this->namespace] = $this->code;
        $_SESSION['captcha_task'.$this->section_id] = $this->code_display;
        if ($this->debug) {
            \trigger_error(sprintf('[%d] namespace=>%s display %s = %s',__LINE__,$this->namespace,$this->code_display,$this->code ), E_USER_NOTICE);
        }
    }
*/
}
