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
use_data_protection  = true
; section id dsgvo/gdpr page
DE                   = 0
EN                   = 0
;NL                  = 0
; and more languages