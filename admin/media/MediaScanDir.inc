<?php

    function mediaScanDir($directory='', $sPattern='/*'){
        $iKey = 0;
        $aListDir  = [];
        $aFileInfo = [];
        $DIR       = [];
        $FILE      = [];
        $temp_id   = 0;
//      Pattern check for potentially malicious files extensions
        $forbidden_file_types  = (''.\preg_replace( '/\s*[,;\|#]\s*/','|',RENAME_FILES_ON_UPLOAD));
        foreach(\glob(WB_PATH.MEDIA_DIRECTORY.$directory.$sPattern,\GLOB_MARK|\GLOB_NOSORT) as $sItem){
            if ((substr($sItem, -1) == '/')){
                $sType = 'folder';
                $DIR[] = basename($sItem);
            } else {
                $bResult = (preg_match('#.*?(\.[a-z][a-z0-9]+)$#isU',$sItem, $aMatch));
                $sExt = ($bResult ? $aMatch['1'] : '');
                if (!\preg_match('/'.($forbidden_file_types).'$/i',$sExt)) {
                    $sType = 'file';
                    $iSize = filesize($sItem);
                    $FILE[] = basename($sItem);
                }
            }
       } // foreach
        natcasesort($DIR);
        natcasesort($FILE);
        $aListDir = \array_merge_recursive($DIR,$FILE);;
        array_unshift($aListDir, '');
        unset($aListDir[0]);
        return $aListDir;
    } // function mediaScanDir

