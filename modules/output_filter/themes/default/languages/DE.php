<?php
/**
 *
 * @category        modules
 * @package         output_filter
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 7.4.x and higher
 * @version         $Id: DE.php 93 2018-09-20 18:09:30Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/modules/output_filter/themes/default/languages/DE.php $
 * @lastmodified    $Date: 2018-09-20 20:09:30 +0200 (Do, 20. Sep 2018) $
 *
 */

// Deutsche Modulbeschreibung
$module_description = 'Dieses Modul erlaubt die Filterung von Inhalten vor der Anzeige im Frontendbereich. Unterstützt die Filterung von Emailadressen in mailto Links und Text.';
$module_description .= '<span id="help-modfiles" style="visibility:hidden;"> <b>Achtung!</b> RegisterModfiles ist aktiviert, einige Filter können deswegen nicht verändert werden. Um alle Filter individuell einstellen zu können muss RegisterModfiles deaktiviert sein.</span>';
// Ueberschriften und Textausgaben
$MOD_MAIL_FILTER['HEADING']    = 'Optionen: Ausgabe Filterung';
$MOD_MAIL_FILTER['HOWTO']      = 'Über nachfolgende Optionen kann die Ausgabefilterung konfiguriert werden. Aktiviertes <b>Filtere E-Mail Adressen in mailto</b> werden mit einer Javascript Routine verschlüsselt.';
$MOD_MAIL_FILTER['W3CSS']      = 'Über nachfolgende Optionen kann der Filter W3Css konfiguriert werden. <b>Die externe w3.css kann mit dieser Einstellung dauerhaft geladen werden!</b>.';
$MOD_MAIL_FILTER['WARNING']             = 'Erfassen der eigenen Filter in der Reihenfolge wie Output Filter diese abarbeiten soll. Diese private Liste ist optional und wird beim Upgrade nicht überschrieben und an die Liste der Standardfilter angehängt. Hinweis: Bei einer leeren privaten Filterliste bleiben die Standardfilter erhalten.';
// Text von Form Elementen
$MOD_MAIL_FILTER['SET_ACTIVE']          = 'Filter aktivieren/deaktivieren';
$MOD_MAIL_FILTER['CLICK_HELP']          = 'Für Hilfe auf Beschriftung klicken';
$MOD_MAIL_FILTER['BASIC_CONF']          = 'Grundeinstellungen';
$MOD_MAIL_FILTER['SYS_REL']             = 'Frontendausgabe mit relativen Urls';
$MOD_MAIL_FILTER['opf']                 = 'Output filter Dashboard';
$MOD_MAIL_FILTER['EMAIL_FILTER']        = 'Filtere E-Mail Adressen im Text';
$MOD_MAIL_FILTER['MAILTO_FILTER']       = 'Filtere E-Mail Adressen in mailto';
$MOD_MAIL_FILTER['ENABLED']             = 'Aktiviert';
$MOD_MAIL_FILTER['DISABLED']            = 'Ausgeschaltet';
$MOD_MAIL_FILTER['LOAD_W3CSS']          = 'Lade W3Css dauerhaft';
$MOD_MAIL_FILTER['REPLACEMENT_CONF']    = 'Email Ersetzungen';
$MOD_MAIL_FILTER['AT_REPLACEMENT']      = 'Ersetze "@" durch';
$MOD_MAIL_FILTER['DOT_REPLACEMENT']     = 'Ersetze "." durch';
$MOD_MAIL_FILTER['ACTIVE_MODFILES']     = "Achtung! RegisterModfiles ist aktiviert, einige Filter können deswegen nicht verändert werden. Um alle Filter individuell einstellen zu können muss RegisterModfiles deaktiviert sein.";
$TEXT['SAVE_LIST']  = 'Liste Speichern';
$TEXT['ADD_LIST']   = 'Liste Anlegen';
$TEXT['EMPTY_LIST'] = 'Liste Leeren';
$output_filter_help = [
                'Droplets'=>'Führt Droplets aus',
                'Email'=>'Macht E-Mail Links zum ausspähen schwerer lesbar',
                'SnippetCss'=>'Lädt externe Snippet/Tool Style Files mit <code>register_frontend_modfiles("css")</code> in den HEAD',
                'FrontendCss'=>'Lädt externe Seitenmodul Style Files mit <code>register_frontend_modfiles("css")</code> in den HEAD',
                'ScriptVars'=>'Setzt Javascript Variablen für die weitere Bearbeitung in den HEAD.',
                'FrontendJs'=>'Lädt externe Seitenmodul Script Files in den HEAD',
                'SnippetBodyJs'=>'Lädt externe Snippet/Tool Script Files mit <code>register_frontend_modfiles_body("js")</code> vor BODY Ende.<br />'
                                . 'Es ist nicht nötig RegisterModFiles einzuschalten',
                'LoadOnFly'=>'Lädt DomReady sowie LoadOnFLy Script in den HEAD zum dynamischen Laden von externen Styles',
                'Jquery'=>'Aktiviert die Einbindung von jQuery<br />'
                        . '<ol start="1">'
                        . '<li>1) RegisterModFiles eingeschaltet altes Verfahren</li>'
                        . '<ul style="padding-left: 1.525em;">'
                        . '<li>Laden von jQuery in den HEAD mit <code>register_frontend_modfiles("jquery")</code></li>'
                        . '<li>Laden von jQuery  vor BODY Ende mit <code>register_frontend_modfiles_body("jquery")</code></li>'
                        . '<li>Setzen der Checkbox jQuery ist nicht erforderlich</li>'
                        . '</ul>'
                        . '<li>2) RegisterModFiles ausgeschaltet neues Verfahren</li>'
                        . '<ul style="padding-left: 1.525em;">'
                        . '<li>jQuery in den HEAD laden mit </li>'
                        . '<li><code>register_frontend_modfiles("css")</code></li>'
                        . '<li>Setzen der Checkbox jQuery ist erforderlich</li>'
                        . '</ul>'
                        . '</ol>'
                        . '',
                'JqueryUI'=>'Aktiviert jQueryUi zum jQuery',
                'SnippetJs'=>'Lädt externe Snippet/Tool Script Files in den HEAD',
                'FrontendBodyJs'=>'Lädt externe Seitenmodul Script Files mit <code>register_frontend_modfiles_body("js")</code> vor BODY Ende.<br />'
                                . 'Es ist nicht nötig RegisterModFiles einzuschalten',
                'OpF'=>'Output filter Dashboard',
                'RegisterModFiles'=>'<p>Zur Unterstützung älterer Templates und Abwärtskompabilität<br />'
                                  . 'der <code>register_frontend_modfiles...Funktionen..</code> </p>'
                                  . '<ol>'
                                  . '<li style="font-weight:bold;">1) RegisterModFiles eingeschaltet altes Verfahren</li>'
                                  . '<ul style="padding-left: 1.525em;">'
                                  . 'Setzen der Checkboxen ist nicht erforderlich und haben auch keinen Einfluss auf die Einbindung<br />'
                                  . '<li>Laden der externen Styles mit <code>register_frontend_modfiles("css")</code>.'
                                  . '<li>Laden der externen Scripte mit <code>register_frontend_modfiles("js")</code>.'
                                  . '<li>Laden von jQuery mit <code>register_frontend_modfiles("jquery")</code>.'
                                  . '<li>zusätzlich geladen werden <code>ScriptVar,domReady.js und LoadOnFly.js</code>.'
                                  . '<li>&nbsp;</li>'
                                  . '<li>'
                                  . 'Laden der front_body.js Scripte vor BODY Ende <br />'
                                  . '<code>register_frontend_modfiles_body("jquery")</code> und<br />'
                                  . '<code>register_frontend_modfiles_body("js")</code><br />'
                                  . '</li>'
                                  . '<li>&nbsp;</li>'
                                  . '</ul>'
                                  . '<li style="font-weight:bold;">2) RegisterModFiles ausgeschaltet neues Verfahren</li>'
                                  . '<ul style="padding-left: 1.525em;">'
                                  . '<li>Lädt alle externen Styles/Scripte in den HEAD über <code>register_frontend_modfiles("css")</code></li>'
                                  . '<li>Durch setzen der Checkboxen bestimmen sie selber was eingebunden werden soll</li>'
                                  . '</ul>'
                                  . '</ol>'
                ,'at_replacement'=>'',
                'dot_replacement'=>'',
                'RelUrl'=>'Ändert absolute in relative Urls',
                'OutputFilterMode'=> '',
                'ReplaceSysvar'=> '',
                'WbLink'=> 'Ändert wblink Platzhalter in absolute Urls',
                'email_filter'=> '',
                'mailto_filter'=> '',
                'CssToHead'=> 'Durchsucht den Inhalt nach Style Blöcken sowie Link CSS Tags'
                          . ' und verschiebt diese in den HEAD Bereich!',
                'W3Css' => 'Gültig für nicht W3Css konforme Templates, für Module die mit W3Css gestylt wurden<br />'
                        . ' Sucht im Inhalt nach nach dem ersten W3Css Klassen Selektor'
                        . ' und lädt die erforderliche externe w3.css in den HEAD Bereich!<br />'
                        . '<b>Wenn der Parameter aktiviert wird, kann es die Frontend Ausgabe beinflussen!</b>',
        ];
$MOD_MAIL_FILTER['HELP_MISSING'] = 'Für diesen Filter gibt es noch keine Hilfe';
/* alle shorturl- und htaccess-Sprachvariablen */
$MOD_MAIL_FILTER['LOAD_SHORT_URL']      = 'Aktiviere ShortUrl';
$MOD_MAIL_FILTER['WHITHOUT_HTACCESS']   = '(ohne htaccess)';
$MOD_MAIL_FILTER['DELETE_SHORT_URL']    = 'Deaktiviere ShortUrl';
$MOD_MAIL_FILTER['ADD_EDIT_URL']        = 'Neue .htaccess bearbeiten';
$MOD_MAIL_FILTER['LOAD_EDIT_URL']       = 'Vorhandene .htaccess bearbeiten';
$MOD_MAIL_FILTER['DELETE_EDIT_URL']     = '.htaccess löschen';
$TEXT['DELETE_HTACCESS'] = 'Lösche .htaccess';
$TEXT['RESET_HTACCESS'] = 'Eingabe verwerfen';
$TEXT['IMPORT_HTACCESS'] = 'Importieren';
$TEXT['PLEASE_SELECT_HTACCESS'] = 'Bitte Textbaustein auswählen';
$MOD_MAIL_FILTER['SHORT_URL']  = 'Die Short Url kann über nachfolgende Optionen konfiguriert werden. Checkbox Shorturl aktivieren erstellt die <b>short.php im Anwendungspfad (Root)</b>. CheckBox deaktivieren entfernt die short.php wieder.';
$MOD_MAIL_FILTER['HTACCESS_URL'] = 'Die .htaccess kann über nachfolgende Optionen konfiguriert werden. Bei vorhandener short.php wird der .htaccess, wenn nicht vorhanden, der passende ShortUrl Code Abschnitt hinzugefügt. Eine bestehende .htaccess kann bearbeitet oder gelöscht werden! Die .htaccess lässt sich über importieren von Textbausteinen erweitern';
//$MOD_MAIL_FILTER['EDIT_HTACCESS_FILE'] = 'Bearbeite .htacces Datei';
$MOD_MAIL_FILTER['HTACCES'] = '.htaccess Datei anlegen, bearbeiten und löschen';

$MOD_MAIL_FILTER['REDIRECT_CODE'] =
'RewriteEngine On
## include redirect to https ------------------------------
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
RewriteCond %{HTTPS} !on
RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]';

$MOD_MAIL_FILTER['HTACCESS_CODE'] =
'RewriteEngine On
## BEGIN SHORTURL -----------------------------------------
## If old urls are called directly - redirect to short url version
RewriteCond %{REQUEST_URI} !/pages/intro.php
RewriteCond %{REQUEST_URI} /pages
RewriteRule ^pages/(.*).php$ /$1/ [R=301,L]

## Send the request to the short.php for processing
RewriteCond %{REQUEST_URI} !^/(pages|account|admin|framework|include|languages|media|modules|search|temp|templates|var|vendor/.*)$
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([\/\sa-zA-Z0-9._-]+)$ /short.php?_wb=$1 [QSA,L]
## END SHORTURL -------------------------------------------
';
