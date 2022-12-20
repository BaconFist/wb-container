<?php

// $Id: DE.php 257 2019-03-17 20:00:55Z Luisehahne $

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
  DEUTSCHE SPRACHDATEI FUER DAS CAPTCHA-CONTROL ADMINISTRATIONS TOOL
 -----------------------------------------------------------------------------------------
*/

// Deutsche Modulbeschreibung
$module_description     = 'Admin-Tool um das Verhalten von CAPTCHA und ASP zu kontrollieren';

// Ueberschriften und Textausgaben
$MOD_CAPTCHA_CONTROL['HEADING']           = 'CAPTCHA- und ASP Steuerung';
$MOD_CAPTCHA_CONTROL['HOWTO']             = 'Hiermit kann das Verhalten von "CAPTCHA" und "Advanced Spam Protection" (ASP) gesteuert werden. Damit ASP in einem Modul wirken kann, muss das verwendete Modul entsprechend angepasst sein.';

// Text and captions of form elements
$MOD_CAPTCHA_CONTROL['CAPTCHA_CONF']      = 'CAPTCHA-Einstellungen';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TYPE']      = 'CAPTCHA-Typ';
$MOD_CAPTCHA_CONTROL['CAPTCHA_EXP']       = 'CAPTCHA Aktivierung/Einstellung für das System. Die CAPTCHA-Einstellungen für die Module befinden sich in den jeweiligen Modul-Optionen';
$MOD_CAPTCHA_CONTROL['USE_SIGNUP_CAPTCHA']= 'CAPTCHA für Registrierung aktivieren';
$MOD_CAPTCHA_CONTROL['ENABLED']           = 'Aktiviert';
$MOD_CAPTCHA_CONTROL['DISABLED']          = 'Ausgeschaltet';
$MOD_CAPTCHA_CONTROL['ASP_CONF']          = 'Erweiterter-Spam-Schutz (ASP) Einstellungen';
$MOD_CAPTCHA_CONTROL['ASP_TEXT']          = 'ASP benutzen (wenn im Modul vorhanden)';
$MOD_CAPTCHA_CONTROL['ASP_EXP']           = 'ASP versucht anhand der verschiedenen Verhaltensweisen zu erkennen, ob eine Formular-Eingabe von einem Menschen oder einem Spam-Bot kommt.';

$MOD_CAPTCHA_CONTROL['CALC_TEXT']         = 'Rechnung als Text';
$MOD_CAPTCHA_CONTROL['CALC_IMAGE']        = 'Rechnung als Bild';
$MOD_CAPTCHA_CONTROL['CALC_TTF_IMAGE']    = 'Rechnung als Bild mit wechselnden Schriften und Hintergünden';
$MOD_CAPTCHA_CONTROL['TTF_IMAGE']         = 'Bild mit wechselnden Schriften und Hintergründen';
$MOD_CAPTCHA_CONTROL['OLD_IMAGE']         = 'Alter Stil (nicht empfohlen)';

$MOD_CAPTCHA_CONTROL['TEXT']              = 'Text-CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_ENTER_TEXT']= 'Fragen und Antworten';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TEXT_DESC'] = 'Bitte hier alles löschen'."\n".'sonst werden Ihre Änderungen nicht gespeichert!'."\n".'### Beispiel ###'."\n".'Hier können sie Fragen und Antworten eingeben.'."\n".'Entweder:'."\n".'?Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".'?Frage 2'."\n".'!Antwort 2'."\n".' ... '."\n".'wenn nur eine Sprache benutzt wird.'."\n".''."\n".'Oder, wenn die Sprache relevant ist:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### Beispiel ###'."\n".'';
$MOD_CAPTCHA['VERIFICATION']              = 'Prüfziffer';
$MOD_CAPTCHA['ADDITION']                  = 'plus';
$MOD_CAPTCHA['SUBTRAKTION']               = 'minus';
$MOD_CAPTCHA['MULTIPLIKATION']            = 'mal';
$MOD_CAPTCHA['VERIFICATION_INFO_RES']     = 'Bitte Ergebnis eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_TEXT']    = 'Bitte Text eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_QUEST']   = 'Bitte Frage beantworten';
$MOD_CAPTCHA['INCORRECT_VERIFICATION']    = 'Das Ergebnis ist falsch. Bitte tragen Sie es erneut ein';

$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA'] = 'Textfarbe auswählen';
$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_WHITE'] = 'Weiß';
$MOD_CAPTCHA_CONTROL['USE_COLOR_CAPTCHA_BLACK'] = 'Schwarz';

$MOD_CAPTCHA_CONTROL['CAPTCHA_RAND'] = 'Captcha als zufälliger Type';
$MOD_CAPTCHA_CONTROL['CAPTCHA_STRING'] = 'Captcha als Zeichenkette';
$MOD_CAPTCHA_CONTROL['CAPTCHA_MATHEMATIC'] = 'Captcha als Rechenaufgabe';
$MOD_CAPTCHA_CONTROL['CAPTCHA_WORDS'] = 'Captcha als Wörter';
$MOD_CAPTCHA_CONTROL['CAPTCHA_BLUR'] = 'Neue Captcha Vorschau wird erst nach Speichern der Einstellungen sichtbar';
$MOD_CAPTCHA_CONTROL['NO_DIR_CHOICE'] = 'Wechselender Hintergrund';
$MOD_CAPTCHA_CONTROL['NO_TTF_CHOICE'] = 'Standard Schriftart';

$CAPTCHA_CONTROL = [
    'HEADING' => 'CAPTCHA-Schutz Einstellungen',
    'ENABLED_SIGNUP' => 'Für Registrierungsformular aktivieren',
    'LABEL_SIGNUP' => 'CAPTCHA-Schutz auf dem Registrierungsformular aktivieren',
    'ENABLED_LOGINFORM' => 'Für Anmeldeformular aktivieren',
    'LABEL_LOGINFORM' => 'CAPTCHA-Schutz auf dem Anmeldeformular aktivieren',
    'ENABLED_LOSTPASSWORD' => 'Für Passwort Vergessen Formular aktivieren',
    'LABEL_LOSTPASSWORD' => 'CAPTCHA-Schutz auf dem Formular zum Rücksetzen des Passworts aktivieren',
    'SECURIMAGE_HEADING' => 'Securimage CAPTCHA-Bild Optionen',
    'SECURIMAGE_TYPE' => 'SECURIMAGE-CAPTCHA verwenden als',
    'NO_CHAR' => 'Anzahl Zeichen',
    'LABEL_NO_CHAR' => 'Nicht relevant bei Mathe-CAPTCHA',
    'WIDTH' => 'Bildbreite',
    'LABEL_WIDTH' => 'Breite des Bildes in Pixel (125 - 500, Standard: 215)',
    'HEIGHT' => 'Bildhöhe',
    'LABEL_HEIGHT' => 'Höhe des Bildes in Pixel (40 - 200, Standard: 80)',
    'IMAGE_BG_DIR' => 'Hintergrund Bild <span>(aus dem Ordner /include/captcha/backgrounds/)</span>',
    'LABEL_IMAGE_BG_DIR' => 'Auswahl Hintergrund Bild oder leer lassen um Zufalls Bilder anzuzeigen',
    'IMAGE_BG_COLOR' => 'Hintergrundfarbe des Bildes',
    'TTF_FILE' => 'Schriftart <span> (aus dem Ordner /include/captcha/fonts/)</span>',
    'LABEL_TTF_FILE' => 'Eingabe TTF Schriftart oder leer lassen um Standard AHGBold.ttf zu setzen',
    'TEXT_COLOR' => 'Textfarbe',
    'NUM_LINES' => 'Anzahl der Verzerrungslinien',
    'LINE_COLOR' => 'Farbe der Verzerrungslinien',
    'NOISE_LEVEL' => 'Rauschmenge (0 - 10)',
    'NOISE_COLOR' => 'Rauschfarbe',
    'IMAGE_SIGNATURE' => 'Text für Bildsignatur',
    'SIGNATURE_COLOR' => 'Textfarbe für Bildsignatur',
    'CAPTCHA_EXPIRATION' => 'CAPTCHA Ablaufzeit',
    'LABEL_CAPTCHA_EXPIRATION' => 'In Sekunden: Wie Lange bis ein CAPTCHA verfällt und nicht mehr gültig ist',
];
