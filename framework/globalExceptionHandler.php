<?php
/**
 * @category        WebsiteBaker
 * @package         WebsiteBaker_core
 * @author          Werner v.d.Decken
 * @copyright       WebsiteBaker.org e.V.
 * @link            http://websitebaker.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @version         $Id: globalExceptionHandler.php 2 2018-06-09 10:45:22Z Manuela $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/framework/globalExceptionHandler.php $
 *
 * Global exception-handler
 * This module will activate a global exception handler to catch all thrown exceptions
 *
 */

/**\
 * define Exception to show error after accessing a forbidden file
 */
    class IllegalFileException extends \LogicException {
        public function __toString() {
            $file = str_replace(dirname(dirname(__FILE__)), '', $this->getFile());
            $out  = '<div style="color: #ff0000; text-align: center;"><br />';
            $out .= '<br /><br /><h1>Illegale file access</h1>';
            $out .= '<h2>'.$file.'</h2></div>';
            return $out;
        }
    } // end of class

/**
 * define several default exceptions directly to prevent from extra loading requests
 */
    class AppException extends \Exception{
        public function __toString() {
            $file = str_replace(dirname(dirname(__FILE__)), '', $this->getFile());
            if (defined('DEBUG')&& DEBUG) {
                $trace = $this->getTrace();
                $result = 'Exception: "'.$this->getMessage().'" @ ';
                if($trace[0]['class'] != '') {
                  $result .= $trace[0]['class'].'->';
                }
                $result .= $trace[0]['function'].'(); in'.$file.'<br />'."\n";
                if($GLOBALS['database']->get_error()) {
                    $result .= $GLOBALS['database']->get_error().': '.$GLOBALS['database']->get_error().'<br />'."\n";
                }
                $result .= '<pre>'."\n";
                $result .= print_r($trace, true)."\n";
                $result .= '</pre>'."\n";
            }else {
                $result = 'Exception: "'.$this->getMessage().'" >> Exception detected in: ['.$file.']<br />'."\n";
            }
            return $result;
        }
    }

/**
 *
 * @param Exception $e
 */
    function globalExceptionHandler($e) {
        // hide server internals from filename where the exception was thrown
        $file = str_replace(dirname(dirname(__FILE__)), '', $e->getFile());
        // select some exceptions for special handling
        if ($e instanceof \IllegalFileException) {
            $sResponse  = $_SERVER['SERVER_PROTOCOL'].' 403 Forbidden';
            header($sResponse);
            echo $e;
        } elseif ($e instanceof \AppException) {
            echo (string)$e;
        } else {
        // default exception handling
            $out  = 'There was an uncatched exception'."\n";
            $out .= $e->getMessage()."\n";
            $out .= 'in line ('.$e->getLine().') of ('.$file.'):'."\n";
            echo nl2br($out);
        }
    }
/**
 * now activate the new defined handler
 */
    set_exception_handler('globalExceptionHandler');
