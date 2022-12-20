;<?php \header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit; ?>
;###############################################################################
;###                                                                         ###
;###   configurable settings for frontend DSGVO                              ###
;###                                                                         ###
;###############################################################################
;
; This file is only for frontend register
; Choose the section with your privacy text
;
[dsgvo]
;
; switch the function to true/false (show the checkbox or not)
use_data_protection  = 1 ; bit settings => enabled_signup = 1 enabled_loginform = 2 enabled_lostpassword = 4
DE                   = 0 ; section id dsgvo page
EN                   = 0 ; section id dsgvo page
;NL                  = 0 ;
; and more languages