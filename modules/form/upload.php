<?php

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
//
/* ---------------------------------------------------------------------------------- */
    // Loop through the files
    $aTmpFiles = ($_FILES['upload_file'] ?? []);
    if (($aTmpFiles['error']==0)) {
        $sFilename = $aTmpFiles['name'];
        if (!\is_readable($sTargetPath.$sFilename)) {
            if (\move_uploaded_file($aTmpFiles['tmp_name'], $sTargetPath.$sFilename)) {
                // Chmod the uploaded file
                change_mode($sTargetPath.$sFilename);
            } else {
              $sMessage = \sprintf($oTrans->MESSAGE_MEDIA_NO_FILE_UPLOADED,$sFilename, $iUploadedsFiles );
              throw new \Exception ($sMessage);
            }
        }
    }

