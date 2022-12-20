<?php

// $Id: EN.php 257 2019-03-17 20:00:55Z Luisehahne $

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2009, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 -----------------------------------------------------------------------------------------
  ENGLISH LANGUAGE FILE FOR THE CAPTCHA-CONTROL ADMIN TOOL
 -----------------------------------------------------------------------------------------
*/

// German module description
$module_description = 'Admin-Tool to control CAPTCHA and ASP';
// Headings and text outputs
$MOD_CAPTCHA_CONTROL['HEADING']           = 'Captcha and ASP control';
$MOD_CAPTCHA_CONTROL['HOWTO']             = 'Here you can control the behavior of "CAPTCHA" and "Advanced Spam Protection" (ASP). For ASP to work with a given module, that module must be adapted to make use of ASP.';

// Text and captions of form elements
$MOD_CAPTCHA_CONTROL['CAPTCHA_CONF']      = 'CAPTCHA Configuration';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TYPE']      = 'Type of CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_EXP']       = 'CAPTCHA settings for modules are located in the respective module settings';
$MOD_CAPTCHA_CONTROL['USE_SIGNUP_CAPTCHA']= 'Activate CAPTCHA for signup';
$MOD_CAPTCHA_CONTROL['ENABLED']           = 'Enabled';
$MOD_CAPTCHA_CONTROL['DISABLED']          = 'Disabled';
$MOD_CAPTCHA_CONTROL['ASP_CONF']          = 'Advanced Spam Protection Configuration';
$MOD_CAPTCHA_CONTROL['ASP_TEXT']          = 'Activate ASP (if available)';
$MOD_CAPTCHA_CONTROL['ASP_EXP']           = 'ASP tries to determine if a form-input was originated from a human or a spam-bot.';

$MOD_CAPTCHA_CONTROL['CALC_TEXT']         = 'Calculation as text';
$MOD_CAPTCHA_CONTROL['CALC_IMAGE']        = 'Calculation as image';
$MOD_CAPTCHA_CONTROL['CALC_TTF_IMAGE']    = 'Calculation as image with varying fonts and backgrounds';
$MOD_CAPTCHA_CONTROL['TTF_IMAGE']         = 'Image with varying fonts and backgrounds';
$MOD_CAPTCHA_CONTROL['OLD_IMAGE']         = 'Old style (not recommended)';

$MOD_CAPTCHA_CONTROL['TEXT']              = 'Text-CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_ENTER_TEXT']= 'Questions and Answers';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TEXT_DESC'] = 'Delete ALL this sample content and add your own entries,'."\n".'otherwise your changes won\'t be saved!'."\n".'### example ###'."\n".'Here you can enter Questions and Answers.'."\n".'If language doesn\'t matter, use:'."\n".'?What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?Question 2'."\n".'!Answer 2'."\n".' ... '."\n".'Or, if language does matter, use:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### example ###'."\n".'';

$MOD_CAPTCHA['VERIFICATION']           = 'Verification';
$MOD_CAPTCHA['ADDITION']               = 'add';
$MOD_CAPTCHA['SUBTRAKTION']            = 'subtract';
$MOD_CAPTCHA['MULTIPLIKATION']         = 'multiply';
$MOD_CAPTCHA['VERIFICATION_INFO_RES']  = 'Fill in the result';
$MOD_CAPTCHA['VERIFICATION_INFO_TEXT'] = 'Fill in the text';
$MOD_CAPTCHA['VERIFICATION_INFO_QUEST'] = 'Answer the question';
$MOD_CAPTCHA['INCORRECT_VERIFICATION'] = 'Verification failed';

$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA'] = 'Select Text Color';
$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_WHITE'] = 'White';
$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_BLACK'] = 'Black';

$MOD_CAPTCHA_CONTROL['CAPTCHA_RAND'] = 'Captcha as random type';
$MOD_CAPTCHA_CONTROL['CAPTCHA_STRING'] = 'Captcha as character string';
$MOD_CAPTCHA_CONTROL['CAPTCHA_MATHEMATIC'] = 'Captcha as arithmetic problem';
$MOD_CAPTCHA_CONTROL['CAPTCHA_WORDS'] = 'Captcha as words';
$MOD_CAPTCHA_CONTROL['CAPTCHA_BLUR'] = 'New captcha preview only becomes visible after saving the settings';
$MOD_CAPTCHA_CONTROL['NO_DIR_CHOICE'] = 'Changing background';
$MOD_CAPTCHA_CONTROL['NO_TTF_CHOICE'] = 'Standard font';

$CAPTCHA_CONTROL = [
    'HEADING' => 'CAPTCHA protection settings',
    'ENABLED_SIGNUP' => 'Activate for registration form',
    'LABEL_SIGNUP' => 'Enable CAPTCHA protection on the registration form',
    'ENABLED_LOGINFORM' => 'Activate for Login form',
    'LABEL_LOGINFORM' => 'Activate CAPTCHA protection on the Login form',
    'ENABLED_LOSTPASSWORD' => 'Activate form for password forgotten',
    'LABEL_LOSTPASSWORD' => 'Enable CAPTCHA protection on password reset form',
    'SECURIMAGE_HEADING' => 'Securimage CAPTCHA image options',
    'SECURIMAGE_TYPE' => 'Use SECURIMAGE-CAPTCHA as',
    'NO_CHAR' => 'Number of characters',
    'LABEL_NO_CHAR' => 'Not relevant for math CAPTCHA',
    'WIDTH' => 'Image width',
    'LABEL_WIDTH' => 'Width of the image in pixels (125 - 500, default: 215)',
    'HEIGHT' => 'Image height',
    'LABEL_HEIGHT' => 'Height of the image in pixels (40 - 200, default: 80)',
    'IMAGE_BG_DIR' => 'Background image <span>(from folder /include/captcha/backgrounds/)</span>',
    'LABEL_IMAGE_BG_DIR' => 'Select background image or leave blank to show random images',
    'IMAGE_BG_COLOR' => 'Background color of the image',
    'TTF_FILE' => 'Font <span> (from folder /include/captcha/fonts/)</span>',
    'LABEL_TTF_FILE' => 'Enter TTF font or leave empty to set default AHGBold.ttf',
    'TEXT_COLOR' => 'Text color',
    'NUM_LINES' => 'Number of distortion lines',
    'LINE_COLOR' => 'Color of distortion lines',
    'NOISE_LEVEL' => 'Noise quantity (0 - 10)',
    'NOISE_COLOR' => 'noise color',
    'IMAGE_SIGNATURE' => 'Text for image signature',
    'SIGNATURE_COLOR' => 'Text color for image signature',
    'CAPTCHA_EXPIRATION' => 'CAPTCHA expiration time',
    'LABEL_CAPTCHA_EXPIRATION' => 'In seconds: How long until a CAPTCHA expires and is no longer valid',
];
