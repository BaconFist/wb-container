<?php

namespace addon\ckeditor\ckeditor;

use bin\{WbAdaptor,SecureTokens,Sanitize};
use bin\helpers\{PreCheck};

/*
use bin;
* Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
* For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * \brief CKEditor class that can be used to create editor
 * instances in PHP pages on server side.
 * @see https://ckeditor.com
 *
 * Sample usage:
 * @code
 * $CKEditor = new CKEditor();
 * $CKEditor->editor("editor1", "<p>Initial value.</p>");
 * @endcode
 */
class CKEditor
{
    /**
     * The version of %CKEditor.
     */
    const version = '4.19.0';
    /**
     * A constant string unique for each release of %CKEditor.
     */
    const timestamp = 'K254';
    /**
     * A string indicating the creation date of %CKEditor.
     * Do not change it unless you want to force browsers to not use previously cached version of %CKEditor.
     */
    public $timestamp = "K254";

    /**
     * URL to the %CKEditor installation directory (absolute or relative to document root).
     * If not set, CKEditor will try to guess it's path.
     *
     * Example usage:
     * @code
     * $CKEditor->basePath = '/ckeditor/';
     * @endcode
     */
    public $basePath;
    /**
     * An array that holds the global %CKEditor configuration.
     * For the list of available options, see http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
     *
     * Example usage:
     * @code
     * $CKEditor->config['height'] = 400;
     * // Use @@ at the beggining of a string to ouput it without surrounding quotes.
     * $CKEditor->config['width'] = '@@screen.width * 0.8';
     * @endcode
     */
    public $config = [];
    /**
     * A boolean variable indicating whether CKEditor has been initialized.
     * Set it to true only if you have already included
     * &lt;script&gt; tag loading ckeditor.js in your website.
     */
    public $initialized = false;
    /**
     * Boolean variable indicating whether created code should be printed out or returned by a function.
     *
     * Example 1: get the code creating %CKEditor instance and print it on a page with the "echo" function.
     * @code
     * $CKEditor = new CKEditor();
     * $CKEditor->bOutputAsBuffer = true;
     * $code = $CKEditor->editor("editor1", "<p>Initial value.</p>");
     * echo "<p>Editor 1:</p>";
     * echo $code;
     * @endcode
     */
    public $bOutputAsBuffer = false;
    /**
     * An array with textarea attributes.
     *
     * When %CKEditor is created with the editor() method, a HTML &lt;textarea&gt; element is created,
     * it will be displayed to anyone with JavaScript disabled or with incompatible browser.
     */
    public $textareaAttributes = ["rows" => 16, "cols" => 60 ];
    /**
     * An array that holds event listeners.
     */
    private $events = [];
    /**
     * An array that holds global event listeners.
     */
    private $globalEvents = [];
  /**
   * json_last_error ??? JSON error codes
   */
    private $aMessage = array(
      'JSON_ERROR_NONE',
      'JSON_ERROR_DEPTH',
      'JSON_ERROR_STATE_MISMATCH',
      'JSON_ERROR_CTRL_CHAR',
      'JSON_ERROR_SYNTAX',
      'JSON_ERROR_UTF8',
      );
  /** Indents a flat JSON string to make it more human-readable. */
    public $prettyPrintJson = true;
    protected $sError = '';
    protected $iErrNo = 0;

    /**
     * Main Constructor.
     *
     *  @param $basePath (string) URL to the %CKEditor installation directory (optional).
     */
    public function __construct($basePath = null) {
        if (!empty($basePath)) {
            $this->basePath = $basePath;

        }
    }

    public function __set($name, $value)
    {
        throw new \Exception('Tried to set a readonly or nonexisting property ['.$name.']!!');
    }

    public function __get($sPropertyName)
    {
        if (isset($this->config[$sPropertyName])){
            return config[$sPropertyName];
        } else if (isset($this->event[$sPropertyName])){
            return event[$sPropertyName];
        } else {
          throw new \Exception('Tried to get nonexisting property ['.$sPropertyName.']');
        }
    }
    public function set($name, $value = '')
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return true;
        } else {
             $this->setError(4, 'variable set' . $name);
            return false;
        }
    }
/**
 * check if an error occured
 * @return bool
 */
    public function isError()
    {
        return (bool)$this->iErrNo;
    }

/**
 * returns last occured error number
 * @return integer number of last error
 */
    public function getErrNo()
    {
        return $this->iErrNo;
    }

/**
 * returns last occured error message
 * @return string message of last error
 */
    public function getError()
    {
        return $this->sError;
    }

/* *********************************************************************
 *  internal methods
 * ********************************************************************/
/**
 * set occured error
 * @param int $iErr Number of the error
 * @param string $sError Error message
 */
    protected function setError($iErr = 0, $sError = 'unknown error')
    {
        $this->iErrNo = $iErr;
        $this->sError = $sError;
    }

    /**
     * Creates a %CKEditor instance.
     * In incompatible browsers %CKEditor will downgrade to plain HTML &lt;textarea&gt; element.
     *
     * @param $name (string) Name of the %CKEditor instance (this will be also the "name" attribute of textarea element).
     * @param $value (string) Initial value (optional).
     * @param $config (array) The specific configurations to apply to this editor instance (optional).
     * @param $events (array) Event listeners for this editor instance (optional).
     *
     * Example usage:
     * @code
     * $CKEditor = new CKEditor();
     * $CKEditor->editor("field1", "<p>Initial value.</p>");
     * @endcode
     *
     * Advanced example:
     * @code
     * $CKEditor = new CKEditor();
     * $config = array();
     * $config['toolbar'] = array(
     *     array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
     *     array( 'Image', 'Link', 'Unlink', 'Anchor' )
     * );
     * $events['instanceReady'] = 'function (ev) {
     *     alert("Loaded: " + ev.editor.name);
     * }';
     * $CKEditor->editor("field1", "<p>Initial value.</p>", $config, $events);
     * @endcode
     */
    public function editor($name, $value = "", $config = [], $events = [])
    {
        $attr = "";
        foreach ($this->textareaAttributes as $key => $val) {
            $attr.= " " . $key . '="' . str_replace('"', '&quot;', $val) . '"';
        }
        $out = '<textarea id="' . $name . '" name="' . $name . '"'. $attr . '>' . htmlspecialchars($value) . '</textarea>'."\n";
        if (!$this->initialized) {
            $out .= $this->init();
        }
        $js = $this->returnGlobalEvents();
        $_config = $this->configSettings($config, $events);

        if (($_config)){
            $js .= "CKEDITOR.replace('".$name."', ".($this->jsEncode($_config)).");";

        } else {
            $js .= "CKEDITOR.replace('".$name."');";
        }
        $out .= $this->script($js);
        if (!$this->bOutputAsBuffer) {
            print $out;
            $out = "";
        }
        return $out;
    }

/**
 * Replaces a &lt;textarea&gt; with a %CKEditor instance.
 *
 * @param $id (string) The id or name of textarea element.
 * @param $config (array) The specific configurations to apply to this editor instance (optional).
 * @param $events (array) Event listeners for this editor instance (optional).
 *
 * Example 1: adding %CKEditor to &lt;textarea name="article"&gt;&lt;/textarea&gt; element:
 * @code
 * $CKEditor = new CKEditor();
 * $CKEditor->replace("article");
 * @endcode
 */
    public function replace($id, $config = array(), $events = array())
    {
        $out = "";
        if (!$this->initialized) {
            $out .= $this->init();
        }
        $_config = $this->configSettings($config, $events);
        $js = $this->returnGlobalEvents();
        if (($_config)) {
            $js .= "CKEDITOR.replace('".$id."', ".$this->jsEncode($_config).");";
        }
        else {
            $js .= "CKEDITOR.replace('".$id."');";
        }
        $out .= $this->script($js);
        if (!$this->bOutputAsBuffer) {
            print $out;
            $out = "";
        }
        return $out;
    }
/**
 * Replace all &lt;textarea&gt; elements available in the document with editor instances.
 *
 * @param $className (string) If set, replace all textareas with class className in the page.
 *
 * Example 1: replace all &lt;textarea&gt; elements in the page.
 * @code
 * $CKEditor = new CKEditor();
 * $CKEditor->replaceAll();
 * @endcode
 *
 * Example 2: replace all &lt;textarea class="myClassName"&gt; elements in the page.
 * @code
 * $CKEditor = new CKEditor();
 * $CKEditor->replaceAll( 'myClassName' );
 * @endcode
 */
    public function replaceAll($className = null)
    {
        $out = "";
        if (!$this->initialized) {
            $out .= $this->init();
        }
        $_config = $this->configSettings();
        $js = $this->returnGlobalEvents();
        if (empty($_config)) {
            if (empty($className)) {
                $js .= "CKEDITOR.replaceAll();";
            }
            else {
                $js .= "CKEDITOR.replaceAll('".$className."');";
            }
        } else {
            $classDetection = "";
            $js .= "CKEDITOR.replaceAll( function(textarea, config) {\n";
            if (!empty($className)) {
                $js .= "    var classRegex = new RegExp('(?:^| )' + '". $className ."' + '(?:$| )');\n";
                $js .= "    if (!classRegex.test(textarea.className))\n";
                $js .= "        return false;\n";
            }
            $js .= "    CKEDITOR.tools.extend(config, ". $this->jsEncode($_config) .", true);";
            $js .= "} );";
        }
        $out .= $this->script($js);
        if (!$this->bOutputAsBuffer) {
            print $out;
            $out = "";
        }
        return $out;
    }
/**
 * Adds event listener.
 * Events are fired by %CKEditor in various situations.
 *
 * @param $event (string) Event name.
 * @param $javascriptCode (string) Javascript anonymous function or function name.
 *
 * Example usage:
 * @code
 * $CKEditor->addEventHandler('instanceReady', 'function (ev) {
 *     alert("Loaded: " + ev.editor.name);
 * }');
 * @endcode
 */
    public function addEventHandler($event, $javascriptCode)
    {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        // Avoid duplicates.
        if (!in_array($javascriptCode, $this->events[$event])) {
            $this->events[$event][] = $javascriptCode;
        }
    }
/**
 * Clear registered event handlers.
 * Note: this function will have no effect on already created editor instances.
 *
 * @param $event (string) Event name, if not set all event handlers will be removed (optional).
 */
    public function clearEventHandlers($event = null)
    {
        if (!empty($event)) {
            $this->events[$event] = [];
        }
        else {
            $this->events = [];
        }
    }
/**
 * Adds global event listener.
 *
 * @param $event (string) Event name.
 * @param $javascriptCode (string) Javascript anonymous function or function name.
 *
 * Example usage:
 * @code
 * $CKEditor->addGlobalEventHandler('dialogDefinition', 'function (ev) {
 *     alert("Loading dialog: " + ev.data.name);
 * }');
 * @endcode
 */
    public function addGlobalEventHandler($event, $javascriptCode)
    {
        if (!isset($this->globalEvents[$event])) {
            $this->globalEvents[$event] = [];
        }
        // Avoid duplicates.
        if (!in_array($javascriptCode, $this->globalEvents[$event])) {
            $this->globalEvents[$event][] = $javascriptCode;
        }
    }
/**
 * Clear registered global event handlers.
 * Note: this function will have no effect if the event handler has been already printed/returned.
 *
 * @param $event (string) Event name, if not set all event handlers will be removed (optional).
 */
    public function clearGlobalEventHandlers($event = null)
    {
        if (!empty($event)) {
            $this->globalEvents[$event] = array();
        }
        else {
            $this->globalEvents = array();
        }
    }
/**
 *
 *
 * @param
 */
    protected function loadBackendCss(  )
    {
        $sAddonName = basename(dirname(__DIR__));
        $out = ''
        . "<script>\n"
        . "if (document.querySelectorAll('.cke')) {
          LoadOnFly('head', ". "WB_URL+'/modules/".$sAddonName."/backend.css');
        }\n"
        . "</script>\n";
        return $out;
    }
/**
 * Prints javascript code.
 *
 * @param string $js
 */
    private function script($js)
    {
        $out  = "<script>";
        $out .= $js;
        $out .= "</script>\n";
        return $out;
    }
/**
 * Returns the configuration array (global and instance specific settings are merged into one array).
 *
 * @param $config (array) The specific configurations to apply to editor instance.
 * @param $events (array) Event listeners for editor instance.
 */
    private function configSettings($config = array(), $events = array())
    {
        $_config = $this->config;
        $_events = $this->events;
        if (is_array($config) && !empty($config)) {
            $_config = array_merge($_config, $config);
        }
        if (is_array($events) && !empty($events)) {
            foreach ($events as $eventName => $code) {
                if (!isset($_events[$eventName])) {
                    $_events[$eventName] = array();
                }
                if (!in_array($code, $_events[$eventName])) {
                    $_events[$eventName][] = $code;
                }
            }
        }
        if (!empty($_events)) {
            foreach($_events as $eventName => $handlers) {
                if (empty($handlers)) {
                    continue;
                } elseif (count($handlers) == 1) {
                    $_config['on'][$eventName] = '@@'.$handlers[0];
                } else {
                    $_config['on'][$eventName] = '@@function (ev){';
                    foreach ($handlers as $handler => $code) {
                        $_config['on'][$eventName] .= '('.$code.')(ev);';
                    }
                    $_config['on'][$eventName] .= '}';
                }
            }
        }
        return $_config;
    }
/**
 * CKEditor::setConfig()
 *
 * @param mixed $key
 * @param mixed $value
 * @return void
 */
    public function setConfig ( $key, $value ) {
        $this->config[$key] = $value;
    }
/**
 * Return global event handlers.
 */
    private function returnGlobalEvents()
    {
        static $returnedEvents;
        $out = "";
        if (!isset($returnedEvents)) {$returnedEvents = array();}
        if (!empty($this->globalEvents)) {
            foreach ($this->globalEvents as $eventName => $handlers) {
                foreach ($handlers as $handler => $code) {
                    if (!isset($returnedEvents[$eventName])) {
                        $returnedEvents[$eventName] = array();
                    }
                    // Return only new events
                    if (!in_array($code, $returnedEvents[$eventName])) {
                        $out .= ($code ? "\n" : "") . "CKEDITOR.on('". $eventName ."', $code);";
                        $returnedEvents[$eventName][] = $code;
                    }
                }
            }
        }
        return $out;
    }
/**
 * Initializes CKEditor (executed only once).
 */
    private function init()
    {
        static $initComplete;
        $out = "";
        if (!empty($initComplete)) {return "";}
        if ($this->initialized) {
            $initComplete = true;
            return "";
        }
        $out  = $this->loadBackendCss();
        $args = "";
        $ckeditorPath = $this->ckeditorPath();
        if (!empty($this->timestamp) && $this->timestamp != "%"."TIMESTAMP%") {
            $args = '?t=' . $this->timestamp;
        }
        // Skip relative paths...
        if (strpos($ckeditorPath, '..') !== 0) {
            $out .= $this->script("window.CKEDITOR_BASEPATH='". $ckeditorPath ."';");
        }
        $out .= "<script src=\"" . $ckeditorPath . 'ckeditor.js' . $args . "\"></script>\n";
        $extraCode = "";
        if ($this->timestamp != self::timestamp) {
            $extraCode .= ($extraCode ? "\n" : "") . "CKEDITOR.timestamp = '". $this->timestamp ."';";
        }
        if ($extraCode) {
            $out .= $this->script($extraCode);
        }
        $initComplete = $this->initialized = true;
        return $out;
    }
/**
 * Return path to ckeditor.js.
 */
    private function ckeditorPath()
    {
        if (!empty($this->basePath)) {return $this->basePath;}
        /**
         * The absolute pathname of the currently executing script.
         * Note: If a script is executed with the CLI, as a relative path, such as file.php or ../file.php,
         * $_SERVER['SCRIPT_FILENAME'] will contain the relative path specified by the user.
         */
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $realPath = dirname($_SERVER['SCRIPT_FILENAME']);
        } else {
            /**
             * realpath - Returns canonicalized absolute pathname
             */
            $realPath = realpath( './' ) ;
        }
        /**
         * The filename of the currently executing script, relative to the document root.
         * For instance, $_SERVER['PHP_SELF'] in a script at the address http://example.com/test.php/foo.bar
         * would be /test.php/foo.bar.
         */
        $selfPath = dirname($_SERVER['PHP_SELF']);
        $file = str_replace("\\", "/", __FILE__);
        if (!$selfPath || !$realPath || !$file) {return "/ckeditor/";}
        $documentRoot = substr($realPath, 0, strlen($realPath) - strlen($selfPath));
        $fileUrl = substr($file, strlen($documentRoot));
        $ckeditorUrl = str_replace("ckeditor_php5.php", "", $fileUrl);
        return $ckeditorUrl;
    }
  /**
   * CKEditor::setJsonEncode()
   * only works with UTF-8 encoded data.
   * in moment not in use was for test only
   *
   * @param mixed $obj Can be any type except a resource.
   * @param integer $iBitmask consisting of
   *                         PHP_JSON_HEX_TAG,
   *                         PHP_JSON_HEX_AMP,
   *                         PHP_JSON_HEX_APOS
   * @return string JSON representation of $obj
   *
   */
  public function setJsonEncode( $obj, $iBitmask = 0)
  {
    $iBitmask = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    //        $retJson = ( (version_compare(PHP_VERSION, '5.3.0') < 0 ) ? json_encode($obj) : json_encode($obj, $iBitmask ) );
    return '"'.str_replace( array(
      "\\",
      "/",
      "\n",
      "\t",
      "\r",
      "\x08",
      "\x0c",
      '"'), array(
      '\\\\',
      '\\/',
      '\\n',
      '\\t',
      '\\r',
      '\\b',
      '\\f',
      '\"'), json_encode( $obj)).'"';
  }
  /**
   * Format a flat JSON string to make it more human-readable
   * original code: http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
   * adapted to allow native functionality in php version >= 5.4.0
   *
   * @param string $json The original JSON string to process
   *        When the input is not a string it is assumed the input is RAW
   *        and should be converted to JSON first of all.
   * @return string Indented version of the original JSON string
   *
   */
  public function getPrettyPrintJson( $json)
  {
    if( !is_string( $json)) {
      if( phpversion() && ( phpversion() >= 5.4) && $this->prettyPrintJson) {
        return json_encode( $json, JSON_PRETTY_PRINT);
      }
      $json = json_encode( $json);
    }
    if( $this->prettyPrintJson === false) {
      return $json;
    }
    $result = '';
    $pos = 0; // indentation level
    $strLen = strlen( $json);
    $indentStr = "\t";
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
    for ( $i = 0; $i < $strLen; $i++)
    {
      // Grab the next character in the string
      $char = substr( $json, $i, 1);
      // Are we inside a quoted string?
      if( $char == '"' && $prevChar != '\\') {
        $outOfQuotes = !$outOfQuotes;
      } else
      // If this character is the end of an element,
      // output a new line and indent the next line
        if( ( $char == '}' || $char == ']') && $outOfQuotes) {
          $result .= $newLine;
          $pos--;
          for ( $j = 0; $j < $pos; $j++) {
            $result .= $indentStr;
          }
        } else
      // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
          if( $outOfQuotes && false !== strpos( " \t\r\n", $char)) {continue;}
      // Add the character to the result string
      $result .= $char;
      // always add a space after a field colon:
      if( $char == ':' && $outOfQuotes) {$result .= ' ';}
      // If the last character was the beginning of an element,
      // output a new line and indent the next line
      if( ( $char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
        $result .= $newLine;
        if( $char == '{' || $char == '[') {
          $pos++;
        }
        for ( $j = 0; $j < $pos; $j++) {
          $result .= $indentStr;
        }
      }
      $prevChar = $char;
    }
    return $result;
  }

  /**
   * Takes a JSON encoded string and converts it into a PHP variable
   * JSON::Decode()
   * @param mixed $json
   * @param bool $toAssoc
   * @return array
   */
    public function getJsonDecode( $json, $toAssoc = false)
    {
      $iError = 0;
      $retJson = json_decode( $json, $toAssoc);
      if( ( $iError = intval( json_last_error())) != 0) {
        throw new Exception( 'JSON Error: '.$this->aMessage[$iError]);
      }
      return $retJson;
    }
/**
 * This little function provides a basic JSON support.
 *
 * @param mixed $val
 * @return string
 */
    private function jsEncode($val)
    {
        return $this->getPrettyPrintJson( $val);
    }
}
