<?php
require_once("config.php");

class CRUDMgr extends CDbAccess {

    var $m_ownerClass;
    var $m_oFieldsToDBBols;
    var $m_oFieldsToTypes;
    var $m_sTables;
    var $m_nId;
    var $m_sPrimaryKey;
    var $m_sCondition;
    protected $m_sLastError;

    const kn_IS_STRING = 1;
    const kn_IS_NOT_STRING = 2;
    const kn_IS_ENC_STRING = 3;
    // API CALLS
    const EDIT = "ed";
    const DELETE = "de";
    const CREATE = "ct";
    const UPDATE = "ud";
    const DELETE_RESPONSE = "derp";
    const EDIT_RESPONSE = "ersp";
    const UPDATE_RESPONSE = "utrp";
    const CREATE_RESPONSE = "ctrp";
    // shared fields
    const NAME = "nm";
    const SUCCESS = "bSuccess";
    const MESSAGE = "sMsg";
    const GRID_ROW = "gr";
    const REQUEST_MSG_NAME = "rmn";
    const API_HANDLER = "api";
    const FIELD_LIST = "fl";
    // Error messages
    const DB_FIELD_ID = "id";
    const ERROR = "Error";
    const PAGINATE = "pgnt";
    const MAX_ROWS = "mr";
    const PAGINATE_LEFT = "pl";
    const PAGINATE_RIGHT = "pr";
    const ACTIVE_INDEX = "ix";
    const CONDITION = "cond";

    function CRUDMgr($_oOwnerClass) {
        parent::CDbAccess();
        $this->m_ownerClass = $_oOwnerClass;
        $this->m_nId = -1;
        $this->m_sTables = "";
        $this->m_sCondition = "";
        $this->m_sPrimaryKey = "";
        $this->m_oFieldsToDBBols = NULL;
        $this->m_oFieldsToTypes = NULL;
        $this->m_sLastError = "";
    }

    function registerFieldlist($_oFieldsToCols, $_oFieldsToTypes, $_sTables, $_sPrimarykey, $_sCondition = '') {
        $this->m_oFieldsToDBBols = $_oFieldsToCols;
        $this->m_oFieldsToTypes = $_oFieldsToTypes;
        $this->m_sTables = $_sTables;
        $this->m_sCondition = $_sCondition;
        $this->m_sPrimaryKey = $_sPrimarykey;
    }

    function create($_aExcludeField = NULL, $_sExcludeFromEncoding = NULL) {
        // takes the fields from the associative array
        $sParentTable = "";
        if (FALSE !== strpos($this->m_sTables, ",")) {
            $sParentTable = explode(",", $this->m_sTables);
            $sParentTable = $sParentTable[0];
        }
        $sQuery = "";
        if (strlen($sParentTable) > 0) {
            $sQuery = " INSERT INTO `" . $sParentTable . "` (";
        } else {
            $sQuery = " INSERT INTO `" . $this->m_sTables . "` (";
        }
        $aFields = NULL;
        $sQuery.= $this->getColumnList($aFields, $_aExcludeField);
        $sQuery .= ") ";
        // builds the insert query
        $sQuery .= " VALUES (NULL,";
        $nFieldlistSize = count($aFields);
        for ($x = 0; $x < $nFieldlistSize; $x++) {
            if (!$this->excludeField($aFields[$x], $_aExcludeField)) {
                if (CRUDMgr::kn_IS_NOT_STRING != $this->m_oFieldsToTypes[$aFields[$x]]) {
                    $sQuery .= "'";
                }
                if (CRUDMgr::kn_IS_ENC_STRING == $this->m_oFieldsToTypes[$aFields[$x]]) {
                    if (!$this->excludeField($aFields[$x], $_sExcludeFromEncoding)) {
                        $sQuery .= Utilities::encodeId($this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]), true);
                    } else {
                        $sQuery .= $this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]);
                    }
                } else {
                    $sQuery .= $this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]);
                }
                if (CRUDMgr::kn_IS_NOT_STRING != $this->m_oFieldsToTypes[$aFields[$x]]) {
                    $sQuery .= "'";
                }
                if ($x + 1 < $nFieldlistSize) {
                    $sQuery .= ',';
                }
            }
        }
        $sQuery .= ");";
        // creates the entry in the db
//		echo "Test Code-Create Query".$sQuery."\n";
        $aRetVal = $this->ReturnQueryExecution($sQuery, true);
//		echo "Test Code-SQL Query Result".var_dump($aRetVal)."\n";
        // sets the id on creation or returns false on error
        if (Utilities::isValidDbObj($aRetVal)) {
            $this->m_nId = $aRetVal[1];
            return true;
        } else {
            $this->m_sLastError = $aRetVal[1];
            return false;
        }
    }

    private function excludeField($_sField, $_aFieldList) {
        for ($x = 0; $x < count($_aFieldList); $x++) {
            if ($_aFieldList[$x] == $_sField) {
                return true;
            }
        }
        return false;
    }

    private function formatValue($_Value, $_nType) {
        if (is_bool($_Value) && !$_Value) {
            return 0;
        } else if (is_string($_Value) && $_Value == NULL) {
            if (CRUDMgr::kn_IS_NOT_STRING == $_nType) {
                return 'NULL';
            } else {
                return "";
            }
        } else if (is_null($_Value)) {
            return 'NULL';
        } else {
            return $_Value;
        }
    }

    public function setLastError($_sLastError) {
        $this->m_sLastError = $_sLastError;
    }

    public function toDb($_sCondition = '', $_aExcludeField = NULL, $_sExcludeFromEncoding = NULL) {
        // takes the fields from the associative array
        $sParentTable = "";
        if (FALSE !== strpos($this->m_sTables, ",")) {
            $sParentTable = explode(",", $this->m_sTables);
            $sParentTable = $sParentTable[0];
        }
        $sQuery = "";
        if (strlen($sParentTable) == 0) {
            $sQuery = "UPDATE `" . $this->m_sTables . "` SET ";
        } else {
            $sQuery = "UPDATE `" . $sParentTable . "` SET ";
        }
        $aFields = array_keys($this->m_oFieldsToDBBols);
        $nFieldlistSize = count($aFields);
        // builds the insert query
        for ($x = 0; $x < $nFieldlistSize; $x++) {
            if (!$this->excludeField($aFields[$x], $_aExcludeField)) {
                if (isset($this->m_oFieldsToTypes[$aFields[$x]])) {
                    $sQuery .= $this->m_oFieldsToDBBols[$aFields[$x]] . " = ";
                    if (CRUDMgr::kn_IS_NOT_STRING != $this->m_oFieldsToTypes[$aFields[$x]]) {
                        $sQuery .= "'";
                    }
                    if (CRUDMgr::kn_IS_ENC_STRING == $this->m_oFieldsToTypes[$aFields[$x]]) {
                        if (!$this->excludeField($aFields[$x], $_sExcludeFromEncoding)) {
                            $sQuery .= Utilities::encodeId($this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]), true);
                        } else {
                            $sQuery .= $this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]);
                        }
                    } else {
                        $sQuery .= $this->formatValue($this->m_ownerClass->__get($aFields[$x]), $this->m_oFieldsToTypes[$aFields[$x]]);
                    }
                    if (CRUDMgr::kn_IS_NOT_STRING != $this->m_oFieldsToTypes[$aFields[$x]]) {
                        $sQuery .= "'";
                    }
                    if ($x + 1 < $nFieldlistSize) {
                        $sQuery .= ',';
                    }
                }
            }
        }
        // remove any trailing commas
        $sQuery = trim($sQuery, ",");
        if ($this->m_nId > 0) {
            $sQuery .= " WHERE " . $this->m_sPrimaryKey . "=" . $this->m_nId;
        } else {
            $sQuery .= " WHERE " . $this->m_sPrimaryKey . "=" . $this->getOwnerFields($this->m_sPrimaryKey);
        }
        if ("" != $_sCondition) {
            $sQuery .= " AND " . $_sCondition;
        }
        // creates the entry in the db
//		echo "Test Code-Update Query".$sQuery."\n";
        $aRetVal = $this->ReturnQueryExecution($sQuery);
//        echo "Test Code-Update Query Resulr".var_dump($aRetVal)."\n";
        // sets the id on creation or returns false on error
        if (Utilities::isValidDbObj($aRetVal)) {
            if ((bool) $aRetVal[0] || (int) $aRetVal[1] > 0) {
                return true;
            } else {
                $this->m_sLastError = $aRetVal[1];
                return false;
            }
        } else {
            $this->m_sLastError = $aRetVal[1];
            return false;
        }
    }

    public function fromDb($_sCondition = '', $_nId = -1, $_bNoLimit = false, $_bSetOwnerFields = true, $_nStartIndex = 0, $_nEndLimit = 0) {
        $dataArr = array();
        // Determine if the ID is set
        $cond = "";
        $nBaseLength = 0;
        if (strlen($_sCondition) > 0 || $_nId > 0 || $this->m_nId > 0) {
            $cond = " WHERE ";
        }
        if ($_nId > 0) {
            $this->m_nId = $_nId;
        }
        if (!empty($this->m_nId)) {
            if ($this->m_nId > 0) {
                $cond .= $this->m_sPrimaryKey . " = " . (int) $this->m_nId;
            }
        }
        if ("" != $_sCondition) {
            if (strlen($cond) > 7) {
                $cond .= " AND " . $_sCondition;
            } else {
                $cond .= $_sCondition;
            }
        } else {
            if (!$_bNoLimit) {
                $sLimit = "";
                if ($_nEndLimit > 0) {
                    $sLimit .= " LIMIT $_nStartIndex,$_nEndLimit";
                } else if ($_nEndLimit == 0) {
                    $sLimit .= " LIMIT 1";
                }
                if (strlen($cond) > 7) {
                    $cond .= $sLimit;
                } else {
                    $cond = $sLimit;
                }
            }
        }
        // Execute the query and fetch the result
        $aFields = NULL;
        $dataArr = $this->ParseColumnsAndRows($this->getColumnList($aFields), $this->m_sTables, $cond);
        if (!Utilities::isValidDbObj($dataArr)) {
//        if (empty($dataArr) || $dataArr == 'FALSE' || $dataArr[0] == 'FALSE' ||
//            $dataArr == false || $dataArr[0] == false)
            if (NULL != $dataArr) {
                if (is_array($dataArr)) {
                    if (count($dataArr) == 2) {
                        $this->m_sLastError = $dataArr[1];
                    }
                } else if (is_string($dataArr)) {
                    $this->m_sLastError = $dataArr;
                }
            }
            return false;
        }
        if ($_bSetOwnerFields) {
            $dataArr = $dataArr[0];
            $this->setOwnerFields($dataArr);
        } else {
            return $dataArr;
        }
        return true;
    }

    function delete($_sCondition = '') {
        $sParentTable = "";
        if (FALSE !== strpos($this->m_sTables, ",")) {
            $sParentTable = explode(",", $this->m_sTables);
            $sParentTable = $sParentTable[0];
        } else {
            $sParentTable = $this->m_sTables;
        }
        $sSQL = "";
        if ($this->m_nId > 0) {
            $sSQL = "DELETE FROM " . $sParentTable . " WHERE " . $this->m_sPrimaryKey . "=" . $this->m_nId;
        } else {
            $sSQL = "DELETE FROM " . $sParentTable . " WHERE " . $this->getOwnerFields($this->m_sPrimaryKey);
        }
        if (strlen($_sCondition) > 0) {
            $sSQL .= " AND " . $_sCondition;
        }
        $aRetVal = $this->ReturnQueryExecution($sSQL);
        if (!Utilities::isValidDbObj($aRetVal[0]))
            return false;
        else if ($aRetVal[1] > 0)
            return true;
        else
            $this->m_sLastError = $aRetVal[1] . ",Query:" . $sSQL;
        return false;
    }

    /**
     * CRUDMgr::__get()
     *
     * Returns the value of the property '$_sName' of this class
     * throws an InvalidArgumentException if the property does not exist
     *
     * @access  public
     * @throws  InvalidArgumentException
     * @param  string name - The name of the property whose value is to be retrieved
     */
    public function __get($_sName) {
        // Ensure the property exists
        if (!property_exists($this, $_sName)) {
            throw new InvalidArgumentException("Property '" . __CLASS__ . "::$_sName' does not exist");
        }
        return $this->$_sName;
    }

    function setId($_nId) {
        $this->m_nId = $_nId;
    }

    /**
     * CRUDMgr::__set()
     *
     * Assigns a value to property '$name' of this class
     * throws an InvalidArgumentException if the property does not exist
     *
     * @access  public
     * @throws  InvalidArgumentException, UnexpectedValueException
     * @param   string name - The name of the property whose value is to be changed
     * @param   mixed value  - The new value of the property
     * @return  void
     */
    public function __set($_sName, $_value) {
        // Ensure the property exists
        if (!property_exists($this, $_sName)) {
            throw new InvalidArgumentException("Property '" . __CLASS__ . "::$_sName' does not exist");
        } else
            $this->$_sName = $_value;
    }

    private function getColumnList(&$_aFields, $_aExcludeFields = NULL) {
        $aFields = array_keys($this->m_oFieldsToDBBols);
        if (NULL != $_aExcludeFields) {
            for ($x = 0; $x < count($aFields); $x++) {
                if ($this->excludeField($aFields[$x], $_aExcludeFields)) {
                    array_splice($aFields, $x, 1);
                }
            }
        }
        $nFieldlistSize = count($aFields);
        $_aFields = $aFields;
        $sQuery = $this->m_sPrimaryKey . ",";
        for ($x = 0; $x < $nFieldlistSize; $x++) {
            $sQuery .= $this->m_oFieldsToDBBols[$aFields[$x]];
            if ($x + 1 < $nFieldlistSize) {
                $sQuery .= ',';
            }
        }
        return $sQuery;
    }

    private function setOwnerFields($_oValue) {
        $aOwnerFields = array_keys($this->m_oFieldsToDBBols);
        $nOwnerFieldSize = count($aOwnerFields);
        $aDbFields = array_keys($_oValue);
        $nSize = count($aDbFields);
        for ($y = 0; $y < $nOwnerFieldSize; $y++) {
            for ($x = 0; $x < $nSize; $x++) {
                try {
                    if ($this->m_oFieldsToDBBols[$aOwnerFields[$y]] == $aDbFields[$x]) {
                        $this->m_ownerClass->__set($aOwnerFields[$y], $_oValue[$aDbFields[$x]]);
                    }
                } catch (Exception $ex) {
                    // TODO - log the error
                }
            }
        }
    }

    private function getOwnerFields($_sField) {
        $retVal = null;
        $aOwnerFields = array_keys($this->m_oFieldsToDBBols);
        $nOwnerFieldSize = count($aOwnerFields);
        for ($y = 0; $y < $nOwnerFieldSize; $y++) {
            try {
                if ($this->m_oFieldsToDBBols[$aOwnerFields[$y]] == $_sField) {
                    $retVal = $this->m_ownerClass->__get($aOwnerFields[$y]);
                }
            } catch (Exception $ex) {
                // TODO - log the error
            }
        }
        return $retVal;
    }

    function getLastError() {
        return $this->m_sLastError;
    }

}

?>
