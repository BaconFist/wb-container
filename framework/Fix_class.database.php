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
            $sSql = 'SHOW COLUMNS FROM `'.$this->escapeString($sTable).'\' '
                  . 'LIKE \''.$this->escapeString($sField).'\'';
            if (($oResult = $this->query($sSql))) {
                $bRetval = (bool)$oResult->numRows();
            }
        }
        return $bRetval;
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

/*
 * @param string $table_name: full name of the table (incl. TABLE_PREFIX)
 * @param string $index_name: name of the index to seek for
 * @return bool: true if field exists
 */
    public function index_exists(string $sTable, string $sIndexName, ?int $iNumberFields):bool
    {
        $bRetval = false;
        $iFields = $iNumberFields ?? 0;
        $sSql = 'SHOW INDEX FROM `'.$this->escapeString($sTable).'` '
              . 'WHERE `Key_name`=\''.$this->escapeString($sIndexName).'\'';
        if (($oResult = $this->query($sSql))) {
            $iFoundFields = $oResult->numRows();
            $bRetval = ($iFields ? ($iFoundFields === $iFields) : (bool)$iFoundFields);
        }
        return $bRetval;
    }
