<?php
/*
 * Copyright (C) 2020 Manuela v.d.Decken <manuela@isteam.de>
 *
 * DO NOT ALTER OR REMOVE COPYRIGHT OR THIS HEADER
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License 2 for more details.
 *
 * You should have received a copy of the GNU General Public License 2
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * mb_extension
 * Provides multibyte versions of some native string functions
 * as well as new functions
 * @category     name
 * @package      Core package
 * @copyright    Manuela v.d.Decken <manuela@isteam.de>
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @license      GNU General Public License 2.0
 * @version      0.0.1 $Rev: $
 * @revision     $Id: $
 * @since        File available since 17.08.2020
 * @deprecated   no / since 0000/00/00
 * @description  xxx
 */
//declare(strict_types = 1);
//declare(encoding = 'UTF-8');

/**
 * Multibyte version of \ucfirst()
 */
    if (! \is_callable('\mb_ucfirst')) {
        function mb_ucfirst(string $str): string
        {
            return \mb_ereg_replace_callback(
                "^(.)(.*)",
                function ($aMatches) {
                    $aMatches[1] = \mb_strtoupper($aMatches[1]);
                    unset($aMatches[0]);
                    return \implode($aMatches);
                },
                \mb_strtolower($str)
            );
        }
    }
/**
 * Multibyte version of \ucwords()
 */
    if (! \is_callable('\mb_ucwords')) {
        function mb_ucwords ( string $str, string $delimiters = " \t\r\n\f\v"): string
        {
            $delimiters = \preg_quote($delimiters);
            $sPattern = '=([^'.$delimiters.']*)(['.$delimiters.']*?)?=u';
            $aMatches = [];
            if (\preg_match_all($sPattern, $str, $aMatches)) {
                $aResult = \array_diff($aMatches[0], ['']);
                $tags = \array_map('mb_ucfirst', $aResult);
                $str = \implode('', $tags);
            }
            return $str;
        }
    }
/**
 * Is first char of string in uppercase
 *
 * @param string $sChar  one or some characters
 * @return bool
 */
    if (! \is_callable('mb_is_uppercase')) {
        function mb_is_uppercase(string $sChar): bool
        {
            $c = \mb_substr($sChar, 0, 1);
            return \mb_ord($c) !== \mb_ord(\mb_strtolower($c));
        }
    }
/**
 * Is first char of string in lowercase
 *
 * @param string $sChar  one or some characters
 * @return bool
 */
    if (! \is_callable('mb_is_lowercase')) {
        function mb_is_lowercase(string $sChar): bool
        {
            $c = \mb_substr($sChar, 0, 1);
            return \mb_ord($c) !== \mb_ord(\mb_strtoupper($c));
        }
    }
/**
 * Convert strings with underscores into CamelCase
 *
 * @param    string  $string    The string to convert
 * @param    bool    $first_char_caps    camelCase or CamelCase
 * @return   string  The converted string
 *
 */
    if (! \is_callable('mb_underscore_to_camelcase')) {
        function mb_underscore_to_camelcase(string $sString, bool $bUpperCamelCase = false): string
        {
            $str = \preg_replace_callback(
                '/_(.)/u',
                function ($a) {
                    return \mb_strtoupper($a[1]);
                },
                \mb_strtolower($sString)
            );
            if ($bUpperCamelCase) {
                if (false !== ($aParts = preg_split('//u', $str, 2))) {
                    $str = \mb_strtolower($aParts[0]).$aParts[1];
                }
            }
            return $str;
        }
    }
/**
 * Convert a camel case string to underscores (eg: camelCase becomes camel_case)
 *
 * @param    string  The string to convert
 * @return   string
 */
    if (! is_callable('mb_camelcase_to_underscore')) {
        function mb_camelcase_to_underscore(string $sString): string
        {
            $aString = \mb_str_split($sString);
            $sRetval = '';
            $bFindUpperChar = false;
            foreach ($aString as $sChar) {
                $sRetval .= (($bFindUpperChar && \mb_is_uppercase($sChar)) ? '_'.$sChar : $sChar);
                $bFindUpperChar = true;
            }
            return mb_strtolower($sRetval);
        }
    }
