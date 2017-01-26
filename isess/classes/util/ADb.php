<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace epayment;

//include_once plugin_dir_path(dirname(__FILE__)) . 'util/interfaces/IDb.php';
include_once 'interfaces/IDb.php';

/**
 * Description of ADb
 *
 * @author femi
 */
class ADb implements IDb {

    //put your code here
    static $prefix = 'mab_';
    protected $fieldsToTypes = array();
    protected $id = NULL;
    protected $primaryKey = "";
    public $parentClass = NULL;
    
    const ARRAY_A = "ARRAY_A";
    const ARRAY_N = "ARRAY_N";
    const OBJECT = "OBJECT";
    const OBJECT_K = "OBJECT_K";
    const ERROR = "error";
    const SUCCESS = "success";
    const MSG = "msg";
    
    /*
     * Class Constructor
     */

    public function __construct($_id, $_fieldlist, $_fieldToTypes, $_createOnDb = false,$_parentClass=NULL) {
        $this->id = $_id;
        $this->fieldsToTypes = $_fieldToTypes;
        $this->parentClass = $_parentClass;
        $this->setFields($_fieldlist);
        if(NULL == $_parentClass){
            $this->primaryKey = strtolower(get_called_class()) . "_Id";
        }
        else{
            $this->primaryKey =  $_parentClass->primaryKey;
        }
        if ($_createOnDb) {
            $this->create();
        } else if ($this->id > 0) {
            $this->get();
        }
    }

    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }

    static function time_to_date($time) {
        return gmdate('Y-m-d H:i:s', $time);
    }

    static function now() {
        return self::time_to_date(time());
    }

    static function date_to_time($date) {
        return strtotime($date . ' GMT');
    }

    static function insert_id() {
        global $wpdb;
        return $wpdb->insert_id;
    }

    // FUNCTIONS TO HANDLE GROUP OPERATIONS (MULTIPLE ROWS ETC)

    /**
     * Retreives multiple OBJECT rows from the active table database based on the fields set and the condition
     * if any
     *
     * @param array $_fields - the fields to retrieve e.g. (line1,line2,city)
     * @param string $_condition - database condition e.g Address_Id > 4 (don't put WHERE in this field)
     * @param string $_returnType - one of many return types [ARRAY_A | ARRAY_N | OBJECT | OBJECT_K] use  ADb::ARRAY_A,ADb::ARRAY_N etc
     * @param integer $_index - the starting record
     * @param integer $_limit - number of rows to return
     * @return array - an array of objects
     */
    public static function _get($_fields = NULL, $_condition = NULL, $_returnType = ADb::OBJECT, $_index = 0, $_limit = 100,$_calledClass=NULL) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;
        if ($_condition !== NULL && !is_array($_condition)) {
            $_condition = sanitize_text_field($_condition);
        }
        // get the fieldlist of the class
        if(!isset($_calledClass)){
            $theCallingClass = get_called_class();
            $sampleClass = new $theCallingClass(NULL, NULL);
        }
        else{
            $sampleClass = $_calledClass;
            $theCallingClass = get_class($_calledClass);
        }
        if (NULL === $_fields) {
            // correct ambiguos primary keys
            if (count($_condition) == 2 && is_array($_condition)) {
                $currentFieldlist = array();
                if(!isset($_calledClass)){
                    $currentFieldlist[self::_table() . '.' . strtolower($theCallingClass) . '_Id'] = "";
                    $currentFieldlist = array_merge($currentFieldlist, get_object_vars($sampleClass));
                    unset($currentFieldlist[strtolower($theCallingClass) . '_Id']);
                }
                else if($_calledClass instanceof \ICRUD && $_calledClass instanceof \IWp){
                    $currentFieldlist[$_calledClass::_table().".".$_calledClass->_primaryKey()] = "";                    
                    $currentFieldlist = array_merge($currentFieldlist, $_calledClass->toFieldlist());
                    unset($currentFieldlist[$_calledClass->_primaryKey()]);
                }
            } else {
                if($_calledClass instanceof \ICRUD && $_calledClass instanceof \IWp){
                    $currentFieldlist = $_calledClass->toFieldlist();
                }
                else{
                    $currentFieldlist = get_object_vars($sampleClass);
                }
            }
            unset($currentFieldlist['fieldsToTypes']);
            unset($currentFieldlist['id']);
            unset($currentFieldlist['primaryKey']);
            unset($currentFieldlist['parentClass']);

        } else {
            $currentFieldlist = $_fields;
        }
        $key = array_keys($currentFieldlist);

        // get the keys
        $_fields = $key != array_keys($key) ? implode(",", array_keys($currentFieldlist)) :
                implode(",", $currentFieldlist);

        // get the keys
        $limit = "";
        if ($_index > 0) {
            $limit = $_index . ',' . $_limit;
        }
        if (NULL != $_condition) {
            // condition can be in two formats a string or an array of two fields [tables,condition]
            if (count($_condition) == 2 && is_array($_condition)) {
                if ("" != $limit) {
                    $sql = sprintf('SELECT %s FROM %s,%%s WHERE %%s LIMIT %%s', $_fields, $sampleClass::_table());
                    $preparedStatement = $wpdb->prepare($sql, $_condition[0], $_condition[1], $limit);
                } else {
                    $sql = sprintf('SELECT %s FROM %s,%%s WHERE %%s', $_fields, $sampleClass::_table());
                    $preparedStatement = sprintf($sql, $_condition[0], $_condition[1]);
                }
            } else {
                if ("" != $limit) {
                    $sql = sprintf('SELECT %s FROM %s WHERE %%s LIMIT %%s', $_fields, $sampleClass::_table());
                    $preparedStatement = $wpdb->prepare($sql, $_condition, $limit);
                } else {
                    $sql = sprintf('SELECT %s FROM %s WHERE %s', $_fields, $sampleClass::_table(), $_condition);
                    $preparedStatement = $sql;
                }
            }
        } else {

            if ("" != $limit) {
                $sql = sprintf('SELECT %s FROM %s WHERE %s LIMIT %%s', $_fields, $sampleClass::_table());
                $preparedStatement = $wpdb->prepare($sql, $limit);
            } else {
                $preparedStatement = sprintf('SELECT %s FROM %s', $_fields, $sampleClass::_table());
            }
        }

        $resultSet = $wpdb->get_results($preparedStatement, $_returnType);

        return $resultSet;
    }

    /**
     * Updates the set fields on the table representing the class calling this function
     * @param type $_fieldlist - an array of key value pairs eg ("line1" => 52,"city" => "london");
     * @param type $_condition - database condition e.g array("Address_Id" => 4)
     * @return boolean - true/false if succesfull or not
     */
    public static function _update(array $_fieldlist, array $_condition) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;

        $currentFieldlist = array();

        // check if any external fields have been set
        if (NULL != $_fieldlist) {
            // if so update the fields
            foreach ($_fieldlist AS $nextField => $value) {
                if (is_array($value)) {
                    $currentFieldlist[$nextField] = serialize($value);
                } else {
                    $currentFieldlist[$nextField] = $value;
                }
            }
        }


        // condition can be in two formats a string or an array of two fields [tables,condition] 
        $success = $wpdb->update(self::_table(), $currentFieldlist, $_condition);
        return $success;
    }

    /**
     * deletes an entry from the active table on the database according to the specified condition
     * @param type $_condition - database condition e.g Address_Id > 4 (don't put WHERE in this field)
     * @return boolean - true/false if succesfull or not
     */
    public static function _delete($_condition) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;
        $success = $wpdb->query(sprintf('DELETE FROM %s WHERE %s', self::_table(), $_condition));

        return $success;
    }

    /**
     * creates a new row on the active table of the database
     * @param type $_fieldlist - an array of key value pairs eg ("line1" => 52,"city" => "london")
     * @param type $_aExcludeFields - an optional array of fields that should nto be set in the create query. e.g. fields that have default
     * values generated by the db
     * @return boolean - true/false if succesfull or not
     */
    public function create(array $_fieldlist = NULL, $_aExcludeFields = NULL) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;

        // get the fieldlist of the class
        $currentFieldlist = get_object_vars($this);
        unset($currentFieldlist['fieldsToTypes']);
        unset($currentFieldlist['id']);
        unset($currentFieldlist['primaryKey']);
        unset($currentFieldlist['parentClass']);
        foreach ($currentFieldlist AS $nextField => $value) {
            if (is_array($value)) {
                $currentFieldlist[$nextField] = serialize($value);
            }
            if ($value instanceof DateTime) {
                $currentFieldlist[$nextField] = $value->format(\Utilities::DATEFORM_DBDATE);
            }
        }


        // check if any external fields have been set
        if (NULL != $_fieldlist) {
            // if so update the fields
            foreach ($_fieldlist AS $nextField => $value) {
                if (is_array($value)) {
                    $currentFieldlist[$nextField] = serialize($value);
                } else {
                    $currentFieldlist[$nextField] = $value;
                }
            }
        }
        // check the exclude statement for any terms to leave out
        if (NULL != $_aExcludeFields) {

            foreach ($_aExcludeFields AS $nextExclude) {

                if (isset($currentFieldlist[$nextExclude])) {

                    unset($currentFieldlist[$nextExclude]);
                }
            }
        }
        // get the field types
        foreach ($currentFieldlist as $key => $field) {
            if ($field == null) {
                unset($currentFieldlist[$key]);
            }
        }
        $types = array();
        if (NULL != $_fieldlist) {
            foreach ($_fieldlist AS $k => $nextField) {
                if (is_array($nextField)) {
                    $opt = $k;
                } else {
                    $opt = $nextField;
                }
                if (isset($this->fieldsToTypes[$opt]) && $this->fieldsToTypes[$opt]) {
//                    echo "field:" . $opt . ",type:" . $this->fieldsToTypes[$opt] . "<br />";
                    array_push($types, $this->fieldsToTypes[$opt]);
                }
            }
        } else {
            $fields = array_keys($currentFieldlist);
            foreach ($fields AS $nextField) {
                array_push($types, $this->fieldsToTypes[$nextField]);
            }
        }

//        echo "post fieldlist:" . var_dump($currentFieldlist) . var_dump($types);
        if (count($types) > 0) {
            $success = $wpdb->insert(self::_table(), $currentFieldlist, $types);
        } else {
            $success = $wpdb->insert(self::_table(), $currentFieldlist);
        }
        if ($success == 1) {

            $this->setFields($currentFieldlist);
            $this->setId(ADb::insert_id());
        }
        Event::triggerEvent($this, 'CREATE', get_object_vars($this));

        return $success;
    }

    /**
     * Retreives the latest values of all the fields in this class or a selected number set in $_fields
     * for the active class instance and the condition if any
     * @param array $_fields - the fields to retrieve e.g. (line1,line2,city)
     * @param integer $_index - the starting record
     * @param string $_condition - database condition e.g Address_Id > 4 (don't put WHERE in this field)
     * @return array - the result set from the db
     */
    public function get(array $_fields = NULL, $_index = 0, $_condition = NULL) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;

        if (NULL == $_fields) {

            // get the fieldlist of the class
            $currentFieldlistwithvalues = get_object_vars($this);

            unset($currentFieldlistwithvalues['fieldsToTypes']);
            unset($currentFieldlistwithvalues['id']);
            unset($currentFieldlistwithvalues['primaryKey']);
            $currentFieldlist = array_keys($currentFieldlistwithvalues);
        } else {
            $currentFieldlist = $_fields;
        }
        // get the keys

        $tableColumns = implode(",", $currentFieldlist);

        if (NULL != $_condition) {
            // condition can be in two formats a string or an array of two fields [tables,condition]
            if (count($_condition) == 2 && is_array($_condition)) {
                $sql = sprintf('SELECT %s FROM %s,%s WHERE %s AND %s = %%s', $tableColumns, self::_table(), $_condition[0], $_condition, $this->primaryKey);
            } else {
                $sql = sprintf('SELECT %s FROM %s WHERE %s AND %s = %%s', $tableColumns, self::_table(), $_condition, $this->primaryKey);
            }
        } else {
            $sql = sprintf('SELECT %s FROM %s WHERE %s = %%s', $tableColumns, self::_table(), $this->primaryKey);
        }

        $preparedStatement = $wpdb->prepare($sql, $this->id);

        if ($_index > 0) {
            $resultSet = $wpdb->get_row($preparedStatement, OBJECT, $_index);
        } else {
            $resultSet = $wpdb->get_row($preparedStatement);
        }

        $this->setFields($resultSet);
        if ($resultSet) {
            \Event::triggerEvent($this, 'READ', get_object_vars($this));
        }
        return $resultSet;
    }

    /**
     * Updates all fields or the set fields in $_fieldlist of the active class instance (calling this function)
     * @param type $_fieldlist - an array of key value pairs eg ("line1" => 52,"city" => "london");
     * @param type $_condition - database condition e.g Address_Id > 4 (don't put WHERE in this field)
     * @return boolean - true/false if succesfull or not
     */
    public function update(array $_fieldlist = NULL, $_condition = NULL) {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;
        if (NULL == $_fieldlist) {
            // get the fieldlist of the class
            $currentFieldlist = get_object_vars($this);
            unset($currentFieldlist['fieldsToTypes']);
            unset($currentFieldlist['id']);
            unset($currentFieldlist['primaryKey']);
            unset($currentFieldlist['parentClass']);
        } else {
            $currentFieldlist = $_fieldlist;
        }
        foreach ($currentFieldlist AS $nextField => $value) {
            if (is_array($value)) {
                $currentFieldlist[$nextField] = serialize($value);
            }
            if ($value instanceof DateTime) {
                $currentFieldlist[$nextField] = $value->format(\Utilities::DATEFORM_DBDATE);
            }
        }
        //unset field whose value is null so as to be able to store them as null in db table
        foreach ($currentFieldlist as $key => $field) {
            if ($field == null) {
                unset($currentFieldlist[$key]);
            }
        }
        if (NULL != $_condition) {
            // condition can be in two formats a string or an array of two fields [tables,condition]
            if (isset($currentFieldlist[$this->primaryKey])) {
                unset($currentFieldlist[$this->primaryKey]);
            }
            $conditions = explode(",", $_condition);
            $preparedConditions = array($this->primaryKey => $this->id);
            foreach ($conditions AS $nextCondition) {
                $keyAndVal = explode("=", $nextCondition);
                if (count($keyAndVal) == 2) {
                    $preparedConditions[$keyAndVal[0]] = $keyAndVal[1];
                } else {
                    throw new Exception("The condition ($_condition ) for the class " . get_called_class() . " has not been properly formatted!");
                }
            }
            $success = $wpdb->update(self::_table(), $currentFieldlist, $preparedConditions);
        } else {
            $success = $wpdb->update(self::_table(), $currentFieldlist, array($this->primaryKey => $this->id));
        }
        if ($success && NULL != $_fieldlist) {
            $this->setFields($currentFieldlist);
        }
        if ($success) {
            \Event::triggerEvent($this, 'UPDATE', get_object_vars($this));
        }
        return $success;
    }

    /**
     * deletes this classes instance from the database
     * @return boolean - true/false if succesfull or not
     */
    public function delete() {
        global $wpdb;
        $wpdb->show_errors = TRUE;
        $wpdb->suppress_errors = FALSE;
        $sql = sprintf('DELETE FROM %s WHERE %s = %%s', self::_table(), $this->primaryKey);
        $result = $wpdb->query($wpdb->prepare($sql, $this->id));
        if ($result) {
            \Event::triggerEvent($this, 'DELETE', get_object_vars($this));
        }
        return $result;
    }

    /**
     * returns the value of the active class instance PrimaryKey
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     *
     * sets the value of  the active class instance PrimaryKey
     * @param integer $_id
     */
    public function setId($_id) {
        $this->id = $_id;
        $primary_key = $this->primaryKey;
        $this->$primary_key = $_id;
    }

    /**
     * Override this function to return the last error that occurred by an action on this class
     * @return string
     */
    function getLastError() {
        global $wpdb;
        $wpdb->last_error;
    }

    /**
     * sets the fields on the active class with the values specified in $_fieldList
     * @param array $_fieldList - key => value set of (fields => values)
     */
    private function setFields($_fieldList) {
        if (is_array($_fieldList)) {
            $fields = array_keys($_fieldList);
            foreach ($fields AS $nextField) {
                if(!isset($this->parentClass)){                    
                    $this->__set($nextField, $_fieldList[$nextField]);
                }
                else{
                    $this->parentClass->__set($nextField, $_fieldList[$nextField]);
                }
            }
        } else if (is_object($_fieldList)) {
            $fields = array_keys(get_object_vars($_fieldList));
            foreach ($fields AS $nextField) {
                if(!isset($this->parentClass)){                    
                    $this->__set($nextField, $_fieldList->$nextField);
                }
                else{
                    $this->parentClass->__set($nextField, $_fieldList->$nextField);
                }
            }
        }
    }

    /**
     * gets the field specified by $_sName
     * @param _sName
     */
    public function __get($_sName) {
        // Ensure the property exists
        if (!property_exists($this, $_sName)) {
            throw new \Exception("Property '" . __CLASS__ . "::$_sName' does not exist");
        }
        return $this->$_sName;
    }

    /**
     * sets the field specified by $_sName
     * @param _sName
     * @param _value
     */
    public function __set($_sName, $_value) {
        // Ensure the property exists
        if (!property_exists($this, $_sName)) {
            throw new \Exception("Property '" . __CLASS__ . "::$_sName' does not exist");
        } else {
            $this->$_sName = $_value;
        }
    }

    public function toFieldlist() {
        $currentFieldlist = get_object_vars($this);
        unset($currentFieldlist['fieldsToTypes']);
        unset($currentFieldlist['id']);
        unset($currentFieldlist['primaryKey']);
        unset($currentFieldlist['parentClass']);
        return $currentFieldlist;
    }

}
