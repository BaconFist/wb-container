<?php
/**
 *
 * @category        module
 * @package         droplets
 * @author          Ruud Eisinga (Ruud) John (PCWacht)
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2019, Website Baker Org. e.V.
 * @link            https://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.6 and higher
 * @version         $Id: droplets.php 12 2020-08-06 05:25:43Z Manuela $
 * @filesource      $HeadURL: svn://svn.kipanga.org/fwswm/intra/trunk/modules/droplets/droplets.php $
 * @lastmodified    $Date: 2020-08-06 07:25:43 +0200 (Thu, 06 Aug 2020) $
 *
 *    droplets are small codeblocks that are called from anywhere in the template.
 *     To call a droplet just use [[dropletname]]. optional parameters for a droplet can be used like [[dropletname?parameter=value&parameter2=value]]\
 *
 *  1.0.2, bugfix, Reused the evalDroplet function so the extracted parameters will be only available within the scope of the eval and cleared when ready.
 *  1.0.3, optimize, reduce memory consumption, increase speed, remove CSS, enable nested droplets
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,msgQueue};
use IvoPetkov\{HTML5DOMDocument,HTML5DOMElement,HTML5DOMNodeList,HTML5DOMTokenList};

 /* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */




    function do_eval($_x_codedata, $_x_varlist, &$wb_page_data) //, &
    {
        $sRetval = '';
        try {
            \extract($_x_varlist, EXTR_SKIP);
            $sRetval = (eval($_x_codedata));
        } catch (\Throwable $ex) {
            $sRetval = $sErrMsg = PreCheck::xnl2br(\sprintf('[%d] %s', $ex->getLine(), $ex->getMessage()));
//            $oApp->print_error ($sErrMsg, $sAddonBackUrl);
//            exit;
        }
        return $sRetval;
    }

    function processDroplets(&$wb_page_data)
    {
// collect all droplets from document
        $oReg = WbAdaptor::getInstance();
        $sAddonName = \basename(__DIR__);
        $sAddonRel  = '/modules/'.$sAddonName;
        $sAddonUrl  = WB_URL.$sAddonRel;
        $sAddonPath = WB_PATH.$sAddonRel;
        $sAbsAddonPath = $sAddonPath;
        $sLocalDebug  = (is_readable($sAddonPath.'/.setDebug'));
        $sSecureToken = (!is_readable($sAddonPath.'/.setToken'));
        $sPHP_EOL     = ($sLocalDebug ? "\n" : '');
        $droplet_tags = [];
        $found_droplets = [];
        $droplet_replacements = [];
        $droplet_search = '\[\[(.*?)\]\]'; // \[\[(\S*?)\]\]
        if (\preg_match_all('/'.$droplet_search.'/', $wb_page_data, $found_droplets)) {
            foreach ($found_droplets[1] as $droplet) {
                if (\array_key_exists('[['.$droplet.']]', $droplet_tags) == false) {
// go in if same droplet with same arguments is not processed already
                    $varlist = [];
// split each droplet command into droplet_name and request_string
                    $tmp = \preg_split('/\?/', $droplet, 2);
                    $droplet_name = $tmp[0];
                    $request_string = (isset($tmp[1]) ? $tmp[1] : '');
                    if ($request_string != '') {
// make sure we can parse the arguments correctly
                        $request_string = \html_entity_decode($request_string, ENT_COMPAT,DEFAULT_CHARSET);
// create array of arguments from query_string
                        $argv = \preg_split( '/&(?!amp;)/', $request_string );
                        foreach ($argv as $argument) {
// split argument in pair of varname, value
                            list( $variable, $value ) = \explode('=', $argument,2);
                            if (!empty($value)) {
// re-encode the value and push the var into varlist
                                $varlist[$variable] = \htmlentities($value, ENT_COMPAT,DEFAULT_CHARSET);
                            }
                        }
                    } else {
// no arguments given, so
                        $droplet_name = $droplet;
                    }
// request the droplet code from database
                    $sql  = 'SELECT `code` FROM `'.TABLE_PREFIX.'mod_droplets` '
                          . 'WHERE `name` LIKE \''.$droplet_name.'\''
                          .   'AND `active` = 1';
                    $codedata = $oReg->Db->get_one($sql);
                    if (!\is_null($codedata)) {
                        $newvalue = do_eval($codedata, $varlist, $wb_page_data);
// check returnvalue (must be a string of 1 char at least or (bool)true
                        if ($newvalue == '' && $newvalue !== true) {
                            if ($sLocalDebug === true) {
                                $newvalue = '<span class="mod_droplets_err">Error in: '.$droplet.', no valid returnvalue.</span>';
                            } else {
                                $newvalue = true;
                            }
                        }
                        if ($newvalue === true) { $newvalue = ""; }
// remove any defined CSS section from code. For valid XHTML a CSS-section is allowed inside <head>...</head> only!
                        $newvalue = \preg_replace('/<style.*>.*<\/style>/siU', '', $newvalue);
// push droplet-tag and it's replacement into Search/Replace array after executing only
                        $droplet_tags[]         = '[['.$droplet.']]';
                        $droplet_replacements[] = $newvalue;
                    } else {
                        $droplet_tags[]         = '[['.$droplet.']]';
                        $droplet_replacements[] = '';
                    }
                }
            }    // End foreach( $found_droplets[1] as $droplet )
// replace each Droplet-Tag with coresponding $newvalue
            $wb_page_data = \str_replace($droplet_tags, $droplet_replacements, $wb_page_data);
        }
// returns TRUE if droplets found in content, FALSE if not
        return (isset($wb_page_data) ? \count($droplet_tags) : 0);
    }

    function evalDroplets(&$wb_page_data, int $iMaxLoops = 4) {
        $iMaxLoops = ($iMaxLoops < 1 ? 4 : $iMaxLoops);
        do {
            $iMaxLoops--;
        } while ((processDroplets($wb_page_data) > 0) && ($iMaxLoops > 0));
//        $wb_page_data = \preg_replace('/\[@(\[.*?\]\])/', '[$1', $wb_page_data);
        return $wb_page_data;


    }
