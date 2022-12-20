<?php ##
## mag@massl.de  2002
##
## CLASS to walk with filter through a csv-file
## Name of fields are defined in first row.
##
/*    EXAMPLE

include_once("csvwalk.php");
$ccsv = new csvwalk();
if ($ccsv->open("../csvfiles/file.csv")) {   # if is's a path, take the newest file of type

$ccsv->set_filter("Sachgebiet","^3\.");     # further filters are "and" combined

while ($ccsv->next_row()) {
echo "<br>".$ccsv->showfield("Sachgebiet");
echo "<br>".$ccsv->showfield("Titel");
echo "<br>".$ccsv->showfield("ID");
echo "<br>".$ccsv->showfield("Beginn");
echo "<br>".$ccsv->showfield("Ende");
echo "<br>".$ccsv->showfield("Ort");
echo "<br>".$ccsv->showfield("Betreuer");
echo "<hr>";}

$ccsv->close();}

EXAMPLE END
*/
namespace dispatch;

//echo nl2br(sprintf("[%03d] %s \n",__LINE__,$sFilePath));

class csvwalk {
    protected $fp = null;
    protected $sort = false;
    protected $length = 65536;
    protected $delimiter = ";";
    protected $csvfile = "";
    protected $header = [];
    protected $fields = [];
    protected $db = [];
    protected $filter = [];
    protected $arow = [];
    protected $adat = [];
    protected $irow = 0;
    protected $end = false;
    ## -- Constructor
    public function __construct(string $path = '', string $deli = ";", int $len = 65536) {
        $this->length = $len;
        $this->delimiter = $deli;
        if (!empty($path)) {
            if (!$this->open($path)) {
                throw new Exception(sprintf('-- [05%d] tried to set a nonexisting file [%s]!! ',__LINE__,basename($path)));
            }
        } else {
            throw new Exception(sprintf('-- [05%d] tried to set a empty file!! ',__LINE__));
        }
    }
    ## -- open
    protected function open($path) {
        if (is_dir($path)) {
            $this->newest_file($path, "\.csv$");
        } else {
            $this->csvfile = $path;
        }
        if ($this->fp = fopen($this->csvfile, "r")) {
            $this->header = fgetcsv($this->fp, $this->length, $this->delimiter);
            $this->fields = array_flip($this->header);
            $this->arow = $this->fields;
            if ($this->sort) {
                $this->read_db();
            }
            //   $this->next_row();
            return true;
        } else {
            return false;
        }
    }
    ## -- Looking for newest file of $type in $path
    protected function newest_file($path, $type = ".*") {
        clearstatcache();
        $path = preg_replace("/$", "", $path);
        if ($handle = opendir($path)) {
            $told = 0;
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && ereg($type, $file) && !is_dir($path."/".$file)) {
                    $tnew = filemtime($path."/".$file);
                    if ($tnew > $told) {
                        $told = $tnew;
                        $this->csvfile = $path."/".$file;
                    }
                }
            }
            closedir($handle);
        }
    }
    /**
     * Protect class from property injections
     * @param string name of property
     * @param mixed value
     * @throws TranslationException
     */
    public function __set($name, $value) {
        throw new Exception('tried to set a readonly or nonexisting property ['.$name.']!! ');
    }
    public function __get($sPropertyName) {
        $retval = null;
        switch ($sPropertyName) {
            case 'Eof':
                $retval = !$this->end;
                break;
            case 'currentRow':
                $retval = $this->irow;
                break;
            case 'Heading':
                $retval = $this->header;
                break;
            default:
                break;
        }
        if (isset($retval)) {
            return $retval;
        } else {
            return null;
            throw new Exception('tried to get a readonly or nonexisting property ['.$sPropertyName.']!! ');
        }
    }
    ## -- Set regex filter to field for reading
    public function set_filter($field, $regex) {
        $this->filter[$field] = $regex;
    }
    ## -- check one row for filter conditions
    protected function check_filter(&$row) {
        $retVal = true;
        foreach ($this->filter as $fld => $rex) {
            $v = $this->fields[$fld];
            if (!preg_match('/('.$rex.')/i', $row[$v])) {
                //    print_r($row); echo ' Filter::'.$row[$v].'<br />';
                $retVal = false;
            }
        }
        return $retVal;
    }
    ## -- read valid rows
    protected function read_db() {
        $this->end = true;
        while ($adat = fgetcsv($this->fp, $this->length, $this->delimiter)) {
            if ($this->check_filter($adat)) {
                $this->irow++;
                $this->db[$this->irow] = $adat;
                $this->end = false;
            } else {
                $this->irow++;
                $this->db[$this->irow] = $adat;
            }
        }
        return !$this->end;
    }
    ## -- read next valid row
    public function next_row() {
        $this->end = false;
        while (($adat = fgetcsv($this->fp, $this->length, $this->delimiter)) !== false) {
            $this->end = ((sizeof($adat) > 0)?true:false);
            if ($this->end && $this->check_filter($adat)) {
                $this->irow++;
                //      $this->arow = $adat;
                if (($x = sizeof($this->header)) == ($y = sizeof($adat))) {
                    $this->arow = array_combine($this->header, $adat);
                } else {
                    throw new Exception('<div class="alert alert-danger">Fieldcount ('.$y.') is not equal with heading ('.$x.') , please inform system administrator</div>');
                }
                break;
            }
        }
        return (($this->end)?$this->arow:$this->end);
    }
    public function fetchArray() {
        return $this->next_row();
    }
    ## -- read field value of row (see example)
    public function showfield($field) {
        $v = null;
        $retVal = null;
        if (isset($this->fields[$field])) {
            $v = $this->fields[$field];
        }
        if (isset($this->arow[$field])) {
            $retVal = $this->arow[$field];
        }
        return $retVal;
    }
    ## -- file-pointer to start of file; reset filter manually
    public function reset() {
        $this->irow = 0;
        $this->arow = array();
        rewind($this->fp);
    }
    ## -- finish!
    public function close() {
        fclose($this->fp);
    }
    public function show_html($val) {
        return html_entity_decode($val);
    }
} // end of class csvwalk
class Exception extends \Exception {}
