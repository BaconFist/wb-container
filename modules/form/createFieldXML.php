<?php
/**
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS HEADER.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category        addons
 * @package         form
 * @subpackage      createXml
 * @copyright       WebsiteBaker Org. e.V.
 * @author          Dietmar Wöllbrink <dietmar.woellbrink@websitebaker.org>
 * @author          Manuela v.d.Decken <manuela@isteam.de>
 * @link            https://websitebaker.org/
 * @license         https://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.13.x
 * @requirements    PHP 7.4.x and higher
 * @version         0.0.1
 * @revision        $Id: $
 * @since           File available since 12.11.2017
 * @deprecated      no / since 0000/00/00
 * @description     xxx
 *
 */

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck,ParentList};
//use vendor\phplib\Template;

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

/**
 * @param $aXml created by modify/field_export, fields array from sql
 *
 */

    try {
            //
            $i = 0;
            $version  = '1.0';
            $encoding = 'utf-8'; //
            $dom = new \DOMDocument($version,$encoding);
            $dom->preserveWhiteSpace = true;
            $dom->formatOutput = true;
            $root = $dom->createElement("root");
            $oSectionId    = $dom->createElement("section_id", $section_id);
            $oPageId       = $dom->createElement("page_id", $page_id);
            $oLayout       = $dom->createElement("layout", $sLayout);
            $oDescription  = $dom->createElement("description", $sDescription);
            $root->appendChild($oSectionId);
            $root->appendChild($oPageId);
            $root->appendChild($oLayout);
            $root->appendChild($oDescription);
            $fields = $dom->createElement('fields');
// Iterate through the rows, adding XML nodes for each field
            while (($i < count($aXml))){
                // Add to XML document node
                $new_node = $dom->createElement('field');
                $attr_field_id = new DOMAttr('id', $aXml[$i]['field_id']);
                $new_node->setAttributeNode($attr_field_id);
                foreach ($aXml[$i] as $key=>$value){
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s %s</div>\n",__LINE__,$key ,$value));
                  // Ignore root elements
                    switch ($key):
                            case 'section_id':
                            case 'page_id':
                            case 'layout':
                            case 'description':
                            case 'field_id':
                          break;
                        default:
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s => %s</div>\n",__LINE__,$key,htmlspecialchars($value)));
                        $child_node = $dom->createElement($key, $value);
                        $new_node->appendChild($child_node);
                    endswitch;
                }// end foreach
                $fields->appendChild($new_node);
                $i++;
            }// end while $aXml
            $root->appendChild($fields);
            $dom->appendChild($root);
            $dom->save($sAbsFilename);
    } catch ( Exception $e ){
        $sMessagesprintf("Tried to set root in DOMElement! %s",$e);
        throw new \Exception ($sMessage );
    }
    return;

/*

*/

