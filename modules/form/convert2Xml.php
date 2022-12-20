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
 * @subpackage      convert2Xml
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


/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */

/**
 *
 * @param $aNewRootXml from upgrade/upgradeXml,
 * @param $aNewChildsXml from upgrade/upgradeXml, like fields array from sql
 * @param $aNewXml from upgrade/upgradeXml, array_merge($aNewRootXml,$aNewChildsXml)
 *
 *
 *
 *
 */

    $version  = '1.0';
    $encoding = 'utf-8';
//echo nl2br(sprintf("<div class='w3-white w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$sLayout));
/* ------------------------------------------------------------ */
    if (!function_exists('array_to_xml')) {
        function array_to_xml($mInput, $xml = null) {
            if ($xml == null ) {
              $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?></root></root>');
            }
            if (is_array($mInput)) {
                foreach ($mInput as $key => $value) {
                    if (is_int($key)) {
                        if ($key == 0) {
                            $node = $xml;
                        } else {
                            $parent = $xml->xpath("..")[0];
                            $node = $parent->addChild($xml->getName());
                        }
                    } else {
                        $node = $xml->addChild($key);
                    }
                    array_to_xml($value, $node);
                }//end foreach
            } else {
                $xml[0] = $mInput;
            }
            return $xml->asXML();
        }
    }
/* ------------------------------------------------------------ */

    $sNewXml = (($aJson) ? array_to_xml($aJson) : 'array to xml failed');

return;

