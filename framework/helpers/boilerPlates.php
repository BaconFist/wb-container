<?php


//if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}

const FULL        = 1;
const STRUCT      = 2;
const DATA        = 4;
const DROPTABLE   = 8;
const DATATYPES   = [
            '7'=>'timestamp',
            '10'=>'date',
            '11'=>'time',
            '12'=>'datetime',
            '13'=>'year',
            '16'=>'bit',
             //'252'is currently mapped to all text and blob types (MySQL 5.0.51a)
            '253'=>'varchar',
            '254'=>'char',
        ];
const NUMBERTYPES = [
            '1'=>'tinyint',
            '2'=>'smallint',
            '3'=>'int',
            '4'=>'float',
            '8'=>'bigint',
            '9'=>'mediumint',
            '5'=>'double',
            '246'=>'decimal'
        ];

const SETNAMES = PHP_EOL.''
        . 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";'.PHP_EOL
        . 'SET AUTOCOMMIT = 0;'.PHP_EOL
        . 'START TRANSACTION;'.PHP_EOL
        . 'SET time_zone = "+00:00";'.PHP_EOL
        . 'SET NAMES utf8;'.PHP_EOL
        . 'SET foreign_key_checks = 0;'.PHP_EOL
        . ''.PHP_EOL.PHP_EOL
        . '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;'.PHP_EOL
        . '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;'.PHP_EOL
        . '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;'.PHP_EOL
        . '/*!40101 SET NAMES utf8 */;'.PHP_EOL.PHP_EOL
        . ''.PHP_EOL;

const TABLETITLE =  ''
                    . '--'.PHP_EOL
                    . '-- Tabellenstruktur für Tabelle `%1$s`'.PHP_EOL
                    . '--'.PHP_EOL
                    . ''.PHP_EOL;

const INSERTDROP =  ''
                    . 'DROP TABLE IF EXISTS `%1$s`;'.PHP_EOL;

const INSERTTITLE = ''
                       . '--'.PHP_EOL
                      . '-- Daten für Tabelle `%1$s`'.PHP_EOL
                      . '--'.PHP_EOL
                      . ''.PHP_EOL;

