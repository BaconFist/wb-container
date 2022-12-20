<?php
/**
 * doFilterReplaceSysvar
 * @param string to modify
 * @return string
 * Convert the {SYSVAR:xxxx} Placeholders into their real value
 */
   function doFilterSysvarMedia($sContent) {
      return doFilterReplaceSysvar($sContent);
    }

   function doFilterReplaceSysvar($sContent) {
        $aReg = array (
            'AppUrl' => WB_URL.'/',
            'MediaDir' => trim(MEDIA_DIRECTORY, '/').'/',
            'MEDIA_REL' => WB_URL.'/'.trim(MEDIA_DIRECTORY, '/').'/'
        );
        $aSearches = [];
        $aReplacements = [];
        // search for all SYSVARs
        if (preg_match_all('/\{SYSVAR\:([^\}]+)\}/sU', $sContent, $aMatches)) {
            $aMatches = array_unique($aMatches[1], SORT_STRING);
            foreach ($aMatches as $sMatch) {
                $sTmp = '';
                $aTmp = preg_split('/\./', $sMatch);
                foreach ($aTmp as $sSysvar) {
                    if (!isset($aReg[$sSysvar])) {
                        $sTmp = '';
                        break;
                    }
                    $sTmp .= $aReg[$sSysvar];
                }
                if ($sTmp) {
                    $aSearches[] = '{SYSVAR:'.$sMatch.'}';
                    $aReplacements[] = $sTmp;
                }
            }
            $sContent = str_replace($aSearches, $aReplacements, $sContent);
        }
      return $sContent;
   }
