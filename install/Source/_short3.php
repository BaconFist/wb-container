<?php

/*
  Short.php & .htaccess example & Dropletcode
  Version 3.0 - June 19, 2013
  Developer - Ruud Eisina / www.dev4me.nl

*/

$_pages = "/pages";
$_ext = ".php";
define('ERROR_PAGE' , '/'); //Change this to point to your existing 404 page.

if (isset($_GET['_wb'])) {
    $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
    $_SERVER['SCRIPT_NAME'] = $_SERVER['REQUEST_URI'];
    $page = trim($_GET['_wb'],'/');
    $fullpag = (__DIR__).$_pages.'/'.$page.$_ext;
    if(file_exists($fullpag)) {
        chdir(dirname($fullpag));
        include ($fullpag);
    } else {
        $page = trim(ERROR_PAGE,'/');
        $fullpag = (__DIR__).$_pages.'/'.$page.$_ext;
        if(file_exists($fullpag)) {
            chdir(dirname($fullpag));
            include ($fullpag);
        } else {
            header('Location: '.ERROR_PAGE);
        }
    }
} else {
    header('Location: '.ERROR_PAGE);
}


/* BEGIN droplet code obsolete
global $wb;
$wb->preprocess( $wb_page_data);
$linkstart = WB_URL.PAGES_DIRECTORY;
$linkend = PAGE_EXTENSION;
$nwlinkstart = WB_URL;
$nwlinkend = '/';

preg_match_all('~'.$linkstart.'(.*?)\\'.$linkend.'~', $wb_page_data, $links);
foreach ($links[1] as $link) {
    $wb_page_data = str_replace($linkstart.$link.$linkend, $nwlinkstart.$link.$nwlinkend, $wb_page_data);
}
return true;
-- END droplet code */

/* .htaccess
RewriteEngine On
# If called directly - redirect to short url version
RewriteCond %{REQUEST_URI} !/pages/intro.php
RewriteCond %{REQUEST_URI} /pages
RewriteRule ^pages/(.*).php$ /$1/ [R=301,L]

# Send the request to the short.php for processing
RewriteCond %{REQUEST_URI} !^/(pages|admin|framework|include|languages|media|account|search|temp|templates/.*)$
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([\/\sa-zA-Z0-9._-]+)$ /short.php?_wb=$1 [QSA,L]
-- END .htaccess */
