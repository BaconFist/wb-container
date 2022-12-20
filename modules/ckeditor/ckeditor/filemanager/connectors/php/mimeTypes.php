<?php

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
/**
 * allow adding mime-tpyes
 * using on your own risc
 *
 */
return $Config['AllowedExtensions'] = [
      'File'   => ['gz','pdf','zip','csv','docx','pptx','txt','xlsx','gif','jpeg','jpg','png','ico','mp3','mp4','ogg','wav'],
      'Image'  => ['bmp','gif','ico','jpeg','jpg','png'],
      'Media'  => ['bmp','gif','ico','jpg','jpeg','png','flv','avi','mpg','mpeg','mp4','mp3','ogg','wav']
    ];
