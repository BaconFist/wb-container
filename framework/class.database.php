<?php
/**
 *
 * @package      Core
 * @copyright    WebsiteBaker Org. e.V.
 * @author       Ryan Djurovich
 * @author       Manuela v.d.Decken <manuela@isteam.de>
 * @author       Dietmar Wöllbrink <luisehahne@websitebaker.org>
 * @license      GNU General Public License 2.0
 * @version      3.0.1
 * @requirements PHP 7.2.x and higher
 * @revision     $Id: class.database.php 237 2019-03-17 15:01:36Z Luisehahne $
 * @deprecated   no / since 0000/00/00
 * @description  This class will be used to interface between the database and the Website Baker code
 */

use bin\{WbAdaptor,wb};

    \define('DATABASE_CLASS_LOADED', true);

class database {

    private static $oInstances = [];

    protected $oReg         = null;
    protected $oDbHandle    = null; // readonly from outside
    protected $sDbName      = '';
    protected $sTablePrefix = '';
    protected $connected    = false;
    protected $sCharset     = 'utf8';
    protected $sCollation   = 'utf8_unicode_ci';
    protected $error        = '';
    protected $aErrorNo     = [];
    protected $sErrorType   = '';
    protected $message      = [];
    protected $sActionFile  = '';
    protected $iQueryCount  = 0;
    protected $statement    = '';
    protected $sStatement   = ''; // for error view

/** collected additional replacements pairs */
    protected $aAdditionalReplacements = ['key'=>[], 'value'=>[]];

/**
 * __constructor
 *  prevent from public instancing
 */
    private function __construct()
    {
        // Connect to database
        // Connect to database
        if (!$this->connect()) {
            $sError = (sprintf("check your database server details\nDBError: [%d] %s",mysqli_connect_errno(),$this->get_error()));
            throw new DatabaseException($sError);
        }
        $this->sTablePrefix = TABLE_PREFIX;
    }

/**
 * prevent from cloning
 */
    private function __clone() {}

/**
 * get a valid instance of this class
 * @param string $sIdentifier selector for several different instances
 * @return WbDatabase object
 */
    public static function getInstance($sIdentifier = 'core')
    {
        if (!isset(self::$oInstances[$sIdentifier])) {

            $c = __CLASS__;
            $oInstance = new $c;
            $oInstance->sInstanceIdentifier = $sIdentifier;
            self::$oInstances[$sIdentifier] = $oInstance;
        }
        return self::$oInstances[$sIdentifier];
    }
/**
 * disconnect and kills an existing instance
 * @param string $sIdentifier selector for instance to kill
 */
    final public static function killInstance($sIdentifier)
    {
        if($sIdentifier != 'core') {
            if (isset(self::$oInstances[$sIdentifier])) {
                self::$oInstances[$sIdentifier]->disconnect();
                unset(self::$oInstances[$sIdentifier]);
            }
        }
    }

    // Connect to the database   DB_CHARSET
    protected function connect()
    {
//        $aNeedles   =['utf8_unicode_ci', 'utf8mb4_unicode_ci'];
//        $this->Collation = (\defined('DB_CHARSET')&& \in_array(DB_CHARSET, $aNeedles)  ? DB_CHARSET : 'utf8_unicode_ci');
        $this->Collation = 'utf8mb4_unicode_ci';
        // extract charset from collation
        $this->sCharset = \explode('_', $this->Collation,2)[0];
        $port = \defined('DB_PORT') ? DB_PORT : \ini_get('mysqli.default_port');
        if (!($this->oDbHandle = @\mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, $port))) {
            $this->connected = false;
            $this->error = \mysqli_connect_error();
        } else {
            if ($this->sCharset) {
                \mysqli_query($this->oDbHandle, 'SET NAMES '.$this->sCharset);
                \mysqli_set_charset($this->oDbHandle, $this->sCharset);
            }
            $this->sDbName = DB_NAME;
            $this->connected = true;
        }
        return $this->connected;
    }

    // Disconnect from the database
    public function disconnect()
    {
        if ($this->connected==true) {
            \mysqli_close();
            return true;
        } else {
            return false;
        }
    }

    // Run a query
    public function query($statement)
    {
        $oRetval = null;
        $oMysql = new \mysql($this->oDbHandle);
        $oMysql->getQuery($statement);
        $this->set_error($oMysql->error());
        if ($oMysql->error()) {
            //echo nl2br(sprintf("%d Error errno: %d %s\n",__LINE__,$this->get_errno(),$statement));
            $this->statement = $statement;
            $oRetval = null;
        } else {
            $this->iQueryCount++;
            $oRetval = $oMysql;
        }
        return $oRetval;
    }

    // Gets the first column of the first row
    public function get_one( $statement )
    {
        $oRetval = null;
        $oMysql = new \mysql($this->oDbHandle);
        if ($oRes = $oMysql->getQuery($statement)) {
            $fetch_row = \mysqli_fetch_array($oRes);
            $oMysql = ($fetch_row[0] ?? null);
            $this->set_error(null);
            if (\mysqli_error($this->oDbHandle)) {
    //            print_r($statement).PHP_EOL;
                $this->statement = $statement;
                $this->set_error(\mysqli_error($this->oDbHandle));
                $oRetval = null;
            } else {
                $this->iQueryCount++;
                $oRetval = $oMysql;
            }
        }
        return $oRetval;
    }

    // Set the DB error
    function set_error($message = null)
    {
        $this->error = $message;
        $this->sErrorType = 'unknown';
        if ($message!=''){
        }
    }

    // Return true if there was an error
    public function is_error()
    {
        return (!empty($this->error)) ? true : false;
    }

    // Return the getQueryCount
    public function getQueryCount()
    {
        return $this->iQueryCount;
    }

    // Return the error
    public function get_error()
    {
        return $this->error;
    }
    // Return the errno
    public function get_errno()
    {
        return $this->is_error() ? \mysqli_errno($this->oDbHandle) : 0;
    }

    // Return the error
    public function getErrorStatement()
    {
        return $this->sStatement;
    }

/**
 * default Getter for some properties
 * @param string $sPropertyName
 * @return mixed NULL on error or missing property
 */
    public function __get($sPropertyName)
    {
        switch ($sPropertyName):
            case 'db_handle':
            case 'DbHandle':
                $retval = $this->oDbHandle;
                break;
            case 'db_name':
            case 'DbName':
                $retval = $this->sDbName;
                break;
            case 'sTablePrefix':
            case 'TablePrefix':
                $retval = $this->sTablePrefix;
                break;
            case 'LastInsertId':
                $retval = $this->getLastInsertId();
                break;
            default:
                $retval = null;
                break;
        endswitch;
        return $retval;
    } // __get()

    public function getDbHandle()
    {
        return $this->oDbHandle;
    }
    public function getDbName()
    {
        return $this->sDbName;
    }
    public function getTablePrefix()
    {
        return $this->sTablePrefix;
    }
    public function getLastInsertId()
    {
        return \mysqli_insert_id($this->oDbHandle);
    }
/**
 * Escapes special characters in a string for use in an SQL statement
 * @param string $unescaped_string
 * @return string
 */
    public function escapeString(string $unescaped_string)
    {
        return \mysqli_real_escape_string($this->oDbHandle, $unescaped_string);
    }

    public function doBeginTrans()
    {
        return \mysqli_begin_transaction($this->oDbHandle);
    }
    public function doCommitTrans()
    {
        return \mysqli_commit($this->oDbHandle);
    }
    public function doRollbackTrans()
    {
        return \mysqli_rollback($this->oDbHandle);
    }
    /* ---------------------------------------------------------------------------------------------- */
/**
 * Check if field exists in table
 * @param string $sTable   incl. TABLE_PREFIX
 * @param string $sField
 * @return bool
 */
    public function field_exists(string $sTable, string $sField): bool
    {
        $bRetval = false;
        if ($this->table_exists($sTable)) {
            $sSql = 'SHOW COLUMNS FROM `'.$this->escapeString($sTable).'` '
                  . 'LIKE \''.$this->escapeString($sField).'\'';
            if (($oResult = $this->query($sSql))) {
                $bRetval = (bool)$oResult->numRows();
            }
        }
        return $bRetval;
    }

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $index_name: name of the index to seek for
 * @return bool: true if field exists
 */
    public function indexExists(string $sTable, string $sIndexName, ?int $iNumberFields):bool
    {
        $bRetval = false;
        $iFields = ($iNumberFields ?? 0);
        $sSql = 'SHOW INDEX FROM `'.$this->escapeString($sTable).'` '
              . 'WHERE `Key_name`=\''.$this->escapeString($sIndexName).'\'';
        if (($oResult = $this->query($sSql))) {
            $iFoundFields = $oResult->numRows();
            $bRetval = ($iFields ? ($iFoundFields === $iFields) : (bool)$iFoundFields);
        }
        return $bRetval;
    }
    public function index_exists($table_name, $index_name, $number_fields = 0):bool
    {
        return $this->indexExists($table_name, $index_name, (int)$number_fields);
    }
/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $field_name: name of the field to add
 * @param string $description: describes the new field like ( INT NOT NULL DEFAULT '0')
 * @return bool: true if successful, otherwise false and error will be set
 */
    public function field_add($table_name, $field_name, $description)
    {
        if (!$this->field_exists($table_name, $field_name) )
        { // add new field into a table
            $sql = 'ALTER TABLE `'.$table_name.'` ADD `'.$field_name.'` '.$description.' ';
            $query = $this->query($sql);
            $this->set_error(mysqli_error($this->oDbHandle));
            if( !$this->is_error() )
            {
                return ( $this->field_exists($table_name, $field_name)  ? true : false);
            }
        } else
        {
            $this->set_error('field \''.$field_name.'\' already exists');
        }
        return false;
    }

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $field_name: name of the field to add
 * @param string $description: describes the new field like ( INT NOT NULL DEFAULT '0')
 * @return bool: true if successful, otherwise false and error will be set
 */
    public function field_modify($table_name, $field_name, $description)
    {
        $retval = false;

        if ($this->field_exists($table_name, $field_name) )
        { // modify a existing field in a table
            $sql  = 'ALTER TABLE `'.$table_name.'` MODIFY `'.$field_name.'` '.$description;
            $retval = ( $this->query($sql) ? true : false);
            $this->set_error(\mysqli_error($this->oDbHandle));
        }
        return $retval;
    }

/**
 * check if table exists
 * @param string $sTable
 * @return bool
 */
    public function table_exists(string $sTable): bool
    {
        $bRetval = false;
        $sSql = 'SHOW TABLES LIKE \''.$this->escapeString($sTable).'\'';
        if (($oResult = $this->query($sSql))) {
            $bRetval = (bool)$oResult->numRows();
        }
        return $bRetval;
    }

/**
 * check if field is part of an index
 * @param string $sTable
 * @param string $sField
 * @return bool
 */
    public function isIndexed(string $sTable, string $sField): bool
    {
        $bRetval = false;
        if ($this->field_exists($sTable, $sField)) {
            $sSql = 'SHOW INDEX FROM `'.$this->escapeString($sTable).'` '
                  . 'WHERE `Column_name`=\''.$this->escapeString($sField).'\'';
            if (($oResult = $this->query($sSql))) {
                $bRetval = (bool)$oResult->numRows();
            }
        }
        return $bRetval;
    }

/**
 * rename a column of a table
 * @param type $sTable
 * @param type $sSourceField
 * @param type $sTargetField
 * @param type $sDescription
 * @return bool
 * @throws RuntimeError
 */
    public function renameField($sTable, $sSourceField, $sTargetField, $sDescription): bool
    {
        $bRetval = false;
        if ($this->field_exists($sTable, $sSourceField) && !$this->field_exists($sTable, $sTargetField)) {
            if ($this->isIndexed($sTable, $sSourceField)) {
                throw new RuntimeError('rename failed! Field still is part of one or more indexes!');
            }
            $sSql  = 'ALTER TABLE `'.$sTable.'` CHANGE '
                   . '`'.$this->escapeString($sSourceField).'` '
                   . '`'.$this->escapeString($sTargetField).'` '
                   . $this->escapeString($sDescription);
            if (($oResult = $this->query($sSql))) {
                $bRetval = (bool)$oResult->numRows();
            }
        }
        return $bRetval;
    }


    public function field_change($table_name, $field_org, $field_new, $description)
    {
        \trigger_error(sprintf('invalid method call: %s change it to renameField(...)',__METHOD__), E_USER_DEPRECATED);
        return $this->renameField($table_name, $field_org, $field_new, $description);
    }

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $field_name: name of the field to remove
 * @return bool: true if successful, otherwise false and error will be set
 */
    public function field_remove($table_name, $field_name)
    {
        $retval = false;
        if( $this->field_exists($table_name, $field_name) )
        { // modify a existing field in a table
            $sql  = 'ALTER TABLE `'.$table_name.'` DROP `'.$field_name.'`';
            $retval = ( $this->query($sql) ? true : false );
        }
        return $retval;
    }

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $index_name: name of the new index (empty string for PRIMARY)
 * @param string $field_list: comma seperated list of fields for this index
 * @param string $index_type: kind of index (PRIMARY, UNIQUE, KEY, FULLTEXT)
 * @return bool: true if successful, otherwise false and error will be set
 */
    public function index_add($table_name, $index_name, $field_list, $index_type = 'KEY')
    {
       $retval = false;
       $field_list = \explode(',', (\str_replace(' ', '', $field_list)));
       $number_fields = \sizeof($field_list);
       $field_list = '`'.\implode('`,`', $field_list).'`';
       $index_name = (($index_type == 'PRIMARY') ? $index_type : $index_name);
       if ( $this->index_exists($table_name, $index_name, $number_fields) ||
            $this->index_exists($table_name, $index_name))
       {
           $sql  = 'ALTER TABLE `'.$table_name.'` ';
           $sql .= 'DROP INDEX `'.$index_name.'`';
           if (!$this->query($sql)) { return false; }
       }
       $sql  = 'ALTER TABLE `'.$table_name.'` ';
       $sql .= 'ADD '.$index_type.' ';
       $sql .= (($index_type == 'PRIMARY') ? 'KEY ' : '`'.$index_name.'` ');
       $sql .= '( '.$field_list.' ); ';
       if ($this->query($sql)) { $retval = true; }
       return $retval;
    }

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $field_name: name of the field to remove
 * @return bool: true if successful, otherwise false and error will be set
 */
    public function index_remove($table_name, $index_name)
    {
        $retval = false;
        if (!empty($index_name) && $this->index_exists($table_name, $index_name)) {
        // modify a existing field in a table
            $sql  = 'ALTER TABLE `'.$table_name.'` DROP INDEX `'.$index_name.'`';
            $retval = ( $this->query($sql) ? true : false );
        }

        return (bool)$retval;
    }

    public function setSqlImportActionFile ( $sCallingScript ){
        $this->sActionFile = $sCallingScript;
        \trigger_error('Deprecated function call: '.__CLASS__.'::'.__METHOD__, E_USER_DEPRECATED);
    }
/**
 * Add Key-Value pairs for additional placeholders
 * @param string $sKey
 * @param mixed $sValue
 */
    public function addReplacement($sKey, $sValue = '')
    {
        $sKey = strtoupper(preg_replace('/([a-z0-9])([A-Z])/', '\1_\2', ltrim($sKey, 'a..z')));
        $this->aAdditionalReplacements['key'][]  = '/\{'.$sKey.'\}/';
        $this->aAdditionalReplacements['value'][] = $sValue;
    }

/**
 * Import a standard *.sql dump file
 * @param string $sSqlDump link to the sql-dumpfile
 * @param string $sTablePrefix
 * @param mixed $mAction
 *        (bool)true => upgrade (default)
 *        (bool)false => install
 *        or command (install|uninstall|upgrade) as string
 *        or calling script as string
 * @param string $sTblEngine
 * @param string $sTblCollation
 * @return boolean true if import successful
 */
    public function SqlImport(
        $sSqlDump,
        $sTablePrefix  = '',
        $mAction       = true,
        $sTblEngine    = 'MyISAM',
        $sCollation    = ('utf8_unicode_ci'),
        $sXCollation   = ('utf8mb4_unicode_ci')
    ) {
        $iCount = 0;
        $sSqlBuffer  = '';
        $bRetval     = true;
        $this->error = '';
        // detect requested action
        if (\is_string($mAction)) {
            // search for valid command string in $mAction
            $sAction = \strtolower(preg_replace(
                '/^.*?(uninstall|install|upgrade)(\.[^\.]+)?$/is',
                '$1',
                $mAction,
                -1,
                $iCount
            ));
            $sAction = $iCount ? $sAction : 'upgrade';
        } else if (\is_bool($mAction)) {
            // on boolean request select true='upgrade' or false='install'
            $sAction = $mAction ? 'upgrade' : 'install';
        } else {
            // select 'upgrade' if no valid command found
            $sAction = 'upgrade';
        }
//        $sCollation = (($this->sCollation != $sCollation) ? $this->sCollation : $sTblCollation);
        //
        // extract charset from given collation
        $aTmp = \preg_split('/_/', $sCollation, -1, \PREG_SPLIT_NO_EMPTY);
        $sCharset = $aTmp[0];
        // extract charset from given xcollation
        $sXCharset = \explode('_', $sXCollation,2)[0];

        // get from addReplacements
        $aSearch  = $this->aAdditionalReplacements['key'];
        // define placeholders
        $aSearch[] = '/\{TABLE_PREFIX\}/';                                        /* step 0 */
        $aSearch[] = '/\{FIELD_CHARSET\}/';                                       /* step 1 */
        $aSearch[] = '/\{FIELD_COLLATION\}/';                                     /* step 2 */
        $aSearch[] = '/\{TABLE_ENGINE\}/';                                        /* step 3 */
        $aSearch[] = '/\{TABLE_ENGINE=([a-zA-Z_0-9]*)\}/';                        /* step 4 */
        $aSearch[] = '/\{CHARSET\}/';                                             /* step 5 */
        $aSearch[] = '/\{COLLATION\}/';                                           /* step 6 */
        $aSearch[] = '/\{XFIELD_COLLATION\}/';                                     /* step 2 */
        $aSearch[] = '/\{XTABLE_ENGINE\}/';                                        /* step 3 */
        $aSearch[] = '/\{XTABLE_ENGINE=([a-zA-Z_0-9]*)\}/';                        /* step 4 */
        $aSearch[] = '/\{XCHARSET\}/';                                             /* step 5 */
        $aSearch[] = '/\{XCOLLATION\}/';                                           /* step 6 */
        // get from addReplacements
        $aReplace = $this->aAdditionalReplacements['value'];
        // define replacements
        $aReplace[] = $sTablePrefix;                                              /* step 0 */
        $aReplace[] = ' CHARACTER SET {CHARSET}';                                 /* step 1 */
        $aReplace[] = ' COLLATE {COLLATION}';                                     /* step 2 */
        $aReplace[] = ' {TABLE_ENGINE='.$sTblEngine.'}';                          /* step 3 */
        $aReplace[] = ' ENGINE=$1 DEFAULT CHARSET={CHARSET} COLLATE={COLLATION}'; /* step 4 */
        $aReplace[] = $sCharset;                                                  /* step 5 */
        $aReplace[] = $sCollation;                                             /* step 6 */
        $aReplace[] = ' COLLATE {XCOLLATION}';                                     /* step 2 */
        $aReplace[] = ' {XTABLE_ENGINE='.$sTblEngine.'}';                          /* step 3 */
        $aReplace[] = ' ENGINE=$1 DEFAULT CHARSET={XCHARSET} COLLATE={XCOLLATION}'; /* step 4 */
        $aReplace[] = $sXCharset;                                                  /* step 5 */
        $aReplace[] = $sXCollation;                                             /* step 6 */

        // read file into an array
        if (($aSql = \file( $sSqlDump, \FILE_SKIP_EMPTY_LINES ))) {
            if (\sizeof($aSql) > 0) {
                // remove possible BOM from file
                $aSql[0] = \preg_replace('/^[\xAA-\xFF]{3}/', '', $aSql[0]);
                // replace placeholders by replacements over the whole file
                $aSql = \preg_replace($aSearch, $aReplace, $aSql);
            } else {
              $aSql = false;
            }
        }

        $iLine = 0;
        while ((bool)$aSql) {
            $ErrMsg = '';
            $aMatch = [];
            $iLine++;
            $sSqlLine = \trim(\array_shift($aSql));
            if (!\preg_match('/^[\-\/]+.*/', $sSqlLine)) {
                $sSqlBuffer .= ' '.$sSqlLine;
                if ((\substr($sSqlBuffer,-1,1) == ';')) {
                    if (
                        // drop tables on install or uninstall
                        \preg_match('/^\s*DROP TABLE IF EXISTS/siU', $sSqlBuffer) &&
                        ($sAction == 'install' || $sAction == 'uninstall')
                    ) {
                        if (!$this->query($sSqlBuffer)) {
                            $aSql = $bRetval = false;
                            break;
                        }
                   } else if (
                        // create and alter tables on install or upgrade
                        (
                          \preg_match('/^\s*CREATE TABLE/siU', $sSqlBuffer)
                          || \preg_match('/^\s*ALTER TABLE/siU', $sSqlBuffer)
                          || \preg_match('/^\s*SET /siU', $sSqlBuffer)
                          || \preg_match('/^\s*TRUNCATE /siU', $sSqlBuffer)
                          || \preg_match('/^\s*INSERT /siU', $sSqlBuffer)
                          || \preg_match('/^\s*UPDATE /siU', $sSqlBuffer)
                         ) &&
                        ($sAction == 'install' || $sAction == 'upgrade')
                    ) {
                        $cmd = '';
                        $sPattern = '/^(?:\s*)ALTER TABLE\s+`(?<table>[^`]+)`\s+(?<cmd1>DROP|ADD)\s+(?<cmd2>[A-Z]+)?\s*`(?<field>[^`]+)`\s*(?<desc>.*)?;(?:\s*)$/';
                        $aMatch = [];
                        if (preg_match($sPattern,$sSqlBuffer,$aMatch)) {
                            //$aParts = array_filter($aMatch, fn($key) => is_string($key), \ARRAY_FILTER_USE_KEY);
                            $cmd = $aMatch['cmd1'].(($aMatch['cmd2'] ?? '') ? '_'.$aMatch['cmd2'] : '');
                            $bContinueQuery = false;
                            switch ($cmd):
                                case 'ADD':
                                  $this->field_add($aMatch['table'],$aMatch['field'],$aMatch['desc']);
                                  break;
                                case 'ADD_INDEX':
                                case 'ADD_UNIQUE':
                                  /* do nothing because we already deleted the index */
                                  $bContinueQuery = true;
                                  break;
                                  break;
                                case 'DROP':
                                  $this->field_remove($aMatch['table'],$aMatch['field']);
                                  break;
                                case 'DROP_INDEX':
                                  $this->index_remove($aMatch['table'],$aMatch['field']);
                                  break;
                                default:
                                  // show if we forgot a cmd
                                  $message = sprintf("Import Error table %s type %s description %s \n",$aMatch['table'],$cmd,$aMatch['desc']);
                                  $this->set_error($message);
                                  //echo nl2br(sprintf("<div class='w3-border w3-padding'>[%s] %s</div>\n",$cmd,$sSqlBuffer));
                            endswitch;
                            if ($bContinueQuery==false){
                                $sSqlBuffer = '';
                                continue;
                            }
                        }
                        else
                        {
                            //echo \nl2br(\sprintf(" %s %s \n",$cmd,$sSqlBuffer));
                        }
                        if (!$this->query($sSqlBuffer))
                        {
                            switch ($this->get_errno()):
                                case 0: // no error
                                case 1054: // Unknown column '%s' in '%s'
                                case 1060: // Duplicate column name %s
                                case 1061: // Duplicate key name &s
                                case 1091: // Can't DROP '%s'; check that column/key exists
                                    $ErrMsg = sprintf("%d Error in Line [%d] errno: %d %s\n",__LINE__,$iLine,$this->get_errno(),$sSqlBuffer);
                                    //trigger_error($ErrMsg, E_USER_WARNING);
                                    break;
                                default: // all other errors
                                    $ErrMsg = sprintf("%d Error in Line [%d] errno: %d %s\n",__LINE__,$iLine,$this->get_errno(),$sSqlBuffer);
                                    //trigger_error($ErrMsg, E_USER_WARNING);
                                    $aSql = $bRetval = false;
                                    break;
                            endswitch;
                        } else {
    //echo nl2br(sprintf("%d update %s \n",__LINE__,$sSqlBuffer));
                        }
                    }
                    if (!empty($ErrMsg)) {
                        echo nl2br(sprintf("<div class='w3-border w3-padding'>[%03d] %s</div>\n",__LINE__,$ErrMsg));
                    }
                    // clear buffer for next statement
                    $sSqlBuffer = '';
                }
            }
        } // end while $sSqlLines
        return $bRetval;
    }

/**
 * retuns the type of the engine used for requested table
 * @param string $table name of the table, including prefix
 * @return boolean/string false on error, or name of the engine (myIsam/InnoDb)
 */
    public function getTableEngine($table)
    {
        $retVal = false;
        $mysqlVersion = \mysqli_get_server_info($this->oDbHandle);
        $engineValue = (\version_compare($mysqlVersion, '5.0') < 0) ? 'Type' : 'Engine';
        $sql = 'SHOW TABLE STATUS FROM `'.$this->sDbName.'` LIKE \'`' . $table .'`\'';
        if (($result = $this->query($sql))) {
            if (($row = $result->fetchAssoc())) {
                $retVal = $row[$engineValue];
            }
        }
        return $retVal;
    }

    public function prepare($sStatement)
    {
        $this->result = @\mysqli_prepare($this->oDbHandle, $sStatement);
/*
        if (\defined('DEBUG')&& DEBUG && ($this->result === false)) {
            if (DEBUG) {
                throw new \DatabaseException(\mysqli_error($this->oDbHandle));
            } else {
                $this->statement = $sStatement;
                throw new \DatabaseException('Error in SQL-Statement');
            }
        }
*/
        $this->error = \mysqli_error($this->oDbHandle);
        return $this->result;
    }
/**
 *
 * return string A string that represents the MySQL client library version.
 */
    public function getClientInfo(): string
    {
        return $this->oDbHandle->get_client_info();
}
/**
 * Calling the method is similar to a simple SQL statement that inserts one or more data records.
 * e.g .: INSERT INTO tbl_name (col_A, col_B, col_C) VALUES (1,2,3), (4,5,6), (7,8,9)
 * @param string $sTablename    name of the table, without TablePrefix
 * @param array $aFieldnames    Array of field names without backticks ['col_A', 'col_B', 'col_C']
 * @param array $aValueRecords  Array of arrays, each subarray must have the same number of values
 *                              like the array of field names  [[1,2,3], [4,5,6], [7,8,9]],
 *                              if one record only should be touched, use:  [1,2,3]
 * @return void
 */
    public function replace(string $sTablename, array $aFieldnames, array $aValueRecords): void
    {
        // sanitize tablename
        $sTable = \preg_replace(
            '/^(?:'.$this->sTablePrefix.')?(.*)$/',
            $this->sTablePrefix.'$1',
            \trim($sTablename, '`')
        );
        // sanitize array fieldnames anonym
        \array_walk(
            $aFieldnames,
            function (& $sName) {
                $sName = '`'.\trim($sName, '`').'`';
            }
        );
        // sanitize value records
        if ($aValueRecords && !is_array($aValueRecords[0])) {
            $aValueRecords = [$aValueRecords];
        }
        $iNumberOfFields = count($aFieldnames);// check the plausibility of the parameters
        // split the list of value records into manageable chunks
        $aChunks = \array_chunk($aValueRecords, 25);
        // proceed each of the chunks
        foreach ($aChunks as $aValueRecords):
            $sSql = 'REPLACE `'.$sTable.'` '
                  . '('.\implode(', ',$aFieldnames).') '
                  . 'VALUES ';
            $iRecord = 0;
            foreach ($aValueRecords as $aRecord):
                $iRecord++;
                if($iNumberOfFields !== count($aRecord)) {
                    throw new DatabaseException('wrong number of values in record #'.$iRecord);
                }
                \array_walk(
                    $aRecord,
                    function (& $sValue) {
                        $sValue = $this->escapeString($sValue);
                    }
                );
                $sSql .= '(\''.\implode('\', \'', $aRecord).'\'),';
            endforeach;
            $sSql = \rtrim($sSql, ' ,');
            $this->query($sSql);
        endforeach;
    }

} /// end of class database

\define('MYSQLI_SEEK_FIRST', 0);
\define('MYSQLI_SEEK_LAST', -1);

class mysql
{
    private $db_handle = null;
    private $result = null;
    private $error = '';

    public function __construct($handle)
    {
        $this->oDbHandle = $handle;
    }
/**
 * query sql statement
 * @param  string $sStatement
 * @return object
 * @throws DatabaseException
 */
    public function _getQuery($sStatement)
    {
        $this->result = \mysqli_query($this->oDbHandle, $sStatement);
        return $this->result;
    }

    public function getQuery(string $sStatement)
    {
//echo \nl2br(\sprintf("---- [%04d] %s \n",__LINE__,$sStatement));

    if (false === ($this->result = $this->oDbHandle->query($sStatement))) {
        $this->error = $this->oDbHandle->error;
    }
        return $this->result;
    }

//    public function getQuery($sStatement)
//    {

//        $this->result = @\mysqli_query($this->oDbHandle, $sStatement);
/*
        if (\defined('DEBUG')&& DEBUG && ($this->result === false)) {
            if (DEBUG) {
                throw new \DatabaseException(sprintf("%s \n %s",\mysqli_error($this->oDbHandle),$sStatement));
            } else {
                $this->statement = $sStatement;
                throw new \DatabaseException('Error in SQL-Statement');
            }
        }
*/
//        $this->error = \mysqli_error($this->oDbHandle);
//        return $this->result;
//    }


    // Fetch num rows
    public function numRows()
    {
        return \mysqli_num_rows($this->result);
    }

    // Fetch row  $typ = MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
    public function fetchRow($typ = MYSQLI_BOTH)
    {
        return \mysqli_fetch_array($this->result, $typ);
    }
/**
 * fetchAssoc
 * @return array with assotiative indexes
 * @description get current record and increment pointer
 */
    public function fetchAssoc()
    {
        return \mysqli_fetch_assoc($this->result);
    }
/**
 * fetchArray
 * @param  int $iType MYSQLI_ASSOC(default) | MYSQLI_BOTH | MYSQLI_NUM
 * @return array of current record
 * @description get current record and increment pointer
 */
    public function fetchArray($iType = \MYSQLI_ASSOC)
    {
        if ($iType < \MYSQLI_ASSOC || $iType > \MYSQLI_BOTH) {
            $iType = \MYSQLI_ASSOC;
        }
        return \mysqli_fetch_array($this->result, $iType);
    }
/**
 * fetchObject
 * @param  string $sClassName Name of the class to use. Is no given use stdClass
 * @param  string $aParams    optional array of arguments for the constructor
 * @return object
 * @description get current record as an object and increment pointer
 */
    public function fetchObject($sClassName = 'stdClass', array $aParams = [])
    {
        if ($sClassName === 'stdClass' || !$sClassName) {
            $oRetval = \mysqli_fetch_object($this->result, 'stdClass');
        } elseif (\class_exists($sClassName)) {
            $oRetval = \mysqli_fetch_object($this->result, $sClassName, $aParams);
        } else {
            throw new \DatabaseException('Class <'.$sClassName.'> not available on request of mysqli_fetch_object()');
        }
        return $oRetval;
    }
/**
 * fetchAll
 * @param  int $iResultType MYSQLI_ASSOC(default) | MYSQLI_NUM
 * @return array of rows
 * @description get all records of the result set
 */
    public function fetchAll($iResultType = \MYSQLI_ASSOC)
    {
        $iType = (($iResultType != \MYSQLI_NUM) ? \MYSQLI_ASSOC : \MYSQLI_NUM);
        $aRetval = [];
        if (\is_callable('mysqli_fetch_all')) { # Compatibility layer with PHP < 5.3
            $aRetval = \mysqli_fetch_all($this->result, $iType);
        } else {
            for ($aRetval = []; ($aTmp = \mysqli_fetch_array($this->result, $iType));) { $aRetval[] = $aTmp; }
        }
        return $aRetval;
    }

    public function rewind()
    {
        return $this->seekRow();
    }

    public function seekRow( $position = MYSQLI_SEEK_FIRST )
    {
        $pmax = $this->numRows() - 1;
        $offset = (($position < 0 || $position > $pmax) ? $pmax : $position);
        return \mysqli_data_seek($this->result, $offset);
    }

    // Get error
    public function error()
    {
        $oRetval = null;
        if (isset($this->error)) {
            $oRetval = $this->error;
        } else {
            $oRetval = null;
        }
        return $oRetval;
    }

} // end of class mysql

    class DatabaseException extends \AppException {}


/* this function is placed inside this file temporarely until a better place is found */
/*  function to update a var/value-pair(s) in table ****************************
 *  nonexisting keys are inserted
 *  @param string $table: name of table to use (without prefix)
 *  @param mixed $key:    a array of key->value pairs to update
 *                        or a string with name of the key to update
 *  @param string $value: a sting with needed value, if $key is a string too
 *  @return bool:  true if any keys are updated, otherwise false
 */
    function db_update_key_value($table='settings', $key='', $value='')
    {
        $oReg = WbAdaptor::getInstance();
        $oDb  = $oReg->getDatabase();
        //
        if (!\is_array($key)) {
            if (!empty(\trim($key)) ) {
                $key = array(\trim($key) => trim($value) );
            } else {
                $key = [];
            }
        }
        $retval = true;
        foreach ($key as $index=>$val) {
            $index = \strtolower($index);
            $sql = '
            SELECT COUNT(*)
            FROM `'.$oReg->TablePrefix.$table.'`
            WHERE `name` = \''.$index.'\'
            ';
            if (\intval($oDb->get_one($sql)) > 0 ) {
                $sql = 'UPDATE ';
                $sql_where = 'WHERE `name` = \''.$index.'\'';
            } else {
                $sql = 'INSERT INTO ';
                $sql_where = '';
            }
            $sql .= '`'.$oReg->TablePrefix.$table.'` ';
            $sql .= 'SET `name` = \''.$index.'\', ';
            $sql .= '`value` = \''.$val.'\' '.$sql_where;
            if (!$oDb->query($sql) )
            {
                $retval = false;
            }
        }// foreach
        return $retval;
    }
/*

                if (in_array($index, ['wbmailer_smtp_auth','sgc_execute','dev_infos','page_oldstyle','page_newstyle','wbmailer_low_security'])) {
                    echo \nl2br(\sprintf("---- [%04d] %s\n",__LINE__,$sql));
                    echo \nl2br(\sprintf("---- [%04d] %s->%s \n",__LINE__,$index,$val));
                }

*/