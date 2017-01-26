<?php

include_once("DbConfig.php");

//include_once($_SERVER['DOCUMENT_ROOT']."/okasho/admin/remote/amf-core/util/NetDebug.php");
require_once 'interfaces/IDbAccess.php';

class CDbAccess implements IDbAccess {

    /**
     * Static variable that indicates if an DB connection has already been established to avoid creating another connection
     * @access      public
     * @staticvar   bool
     * */
    public static $bConnCreated = false;
    public static $g_szConnectionID = NULL;
    
    function CDbAccess() {
        
    }


    function GetRow($_szColumn, $_szTable, $_szCondition = "", $_Login = '', $_Password = '', $_Database = DB_NAME) {
        // connect to the database
        if(!isset(self::$g_szConnectionID)){
            self::$g_szConnectionID = $this->GetDBConnection($_Login, $_Password);
        }
        if (NULL != self::$g_szConnectionID) {
            if (mysqli_select_db(self::$g_szConnectionID,$_Database)) {
                if ("" == $_szCondition) {
                    $SQLQuery = "SELECT $_szColumn FROM $_szTable";
                } else {
                    $SQLQuery = "SELECT $_szColumn FROM $_szTable $_szCondition";
                }
                #echo "GetRow:".$SQLQuery."<br/>";
                $RetObject = mysqli_query(self::$g_szConnectionID,$SQLQuery);
                if (FALSE != $RetObject) {
                    $ReturnedString = mysqli_fetch_object($RetObject);
                    if (FALSE != $ReturnedString) {
                        if (strpos($_szColumn, "*") > -1) {
                            $RetValue = $ReturnedString->$_szColumn;
                        } else {
                            $nIndex = strpos($_szColumn, '.');
                            if ($nIndex > 0) {
                                $_szColumn = substr($_szColumn, $nIndex + 1);
                            }
                            $RetValue = $ReturnedString->$_szColumn;
                        }
                    } else {
                        $RetValue = "FALSE";
                    }
                } else {
                    $RetValue = "FALSE";
                }
            } else {
                $RetValue = "FALSE";
            }
        } else {
            $RetValue = "FALSE";
        }
        return $RetValue;
    }
    
    /**
     * CDbAccess::ParseColumnsAndRows()
     * 
     * @param mixed $_szColumns - The list columns to be retrieved. This can also be any value that can be used in the SELECT clause
     * @param mixed $_szTable - The DB table(s) against which the SQL SELECT query should be ran. This can be any value that can be used in the FROM clause
     * @param string $_szCondition - The extra query param to be added to the SELECT query i.e. WHERE, ORDER, LIMIT clauses
     * @param string $_Login - DB login user name, if different from the global constant
     * @param string $_Password - DB user password, if different from the global constant
     * @param bool $_bUseKeyVal - boolean to indicate if the data should be returned as an index array 
     * @param mixed $_sDb - The name of the DB to use, if different from the global constant
     * @return associative array | array('FALSE',mysqli_error()) | 'FALSE'
     */
    function ParseColumnsAndRows($_szColumns, $_szTable, $_szCondition = "", $_Login = '', $_Password = '', $_bUseKeyVal = true, $_sDb = DB_NAME) {
        //$RetValue = "";
        // get the Feedback category options
        // connect to the database
        $ParsedColumns = array();
        $ColumnsAndRows = array();
        $RetColumsAndRows = array();
        $KeyRetVal = array();
        if(!isset(self::$g_szConnectionID)){
            self::$g_szConnectionID = $this->GetDBConnection($_Login, $_Password);
        }
        if (NULL != self::$g_szConnectionID) {
            if (mysqli_select_db(self::$g_szConnectionID,$_sDb)) {
                // get the available links and URLs
                if ("" == $_szCondition) {
                    $SQLQuery = "SELECT DISTINCT $_szColumns FROM $_szTable";
                } else if ("NO DISTINT" == $_szCondition) {
                    $SQLQuery = "SELECT $_szColumns FROM $_szTable";
                } else {
                    $SQLQuery = "SELECT DISTINCT $_szColumns FROM $_szTable $_szCondition";
                }
                //NetDebug::trace(array("Function ParseColumnsAndRows- SQLQuery: ",$SQLQuery));
                $RetObject = mysqli_query(self::$g_szConnectionID,$SQLQuery);   #echo $SQLQuery."\n";
                // get the number of rows
                if (FALSE != $RetObject) {
                    $Rows = mysqli_num_rows($RetObject);
                    if ($Rows > 0) {
                        if ($_bUseKeyVal) {
                            //NetDebug::trace(array("Function ParseColumnsAndRows-Getting $Rows Rows via Association"));
                            while ($Rows > 0) {
                                $RetArray = mysqli_fetch_assoc($RetObject);
                                //NetDebug::trace(array("Function ParseColumnsAndRows-Found Array",$RetArray));
                                array_push($KeyRetVal, $RetArray);
                                $Rows--;
                            }
                            $RetValue = $KeyRetVal;
                            return $RetValue;
                        } else {
                            // since it contains two columns, parse them into arrays first
                            // parse the output retreived from the database
                            for ($i = 0; $i < $Rows; $i++) {
                                $NextRow = mysqli_fetch_row($RetObject);
                                $ArraySize = count($NextRow);
                                // populate the returned array
                                for ($x = 0; $x < $ArraySize; $x++) {
                                    array_push($ParsedColumns, $NextRow[$x]);
                                    //									//NetDebug::trace(array("Function ParseColumnsAndRows- LocalRowAndColumns:",$NextRow[$x],"RowSize: ",$ArraySize));
                                }
                                array_push($ColumnsAndRows, $ParsedColumns);
                                // clear the local array
                                for ($x = 0; $x < $ArraySize; $x++) {
                                    array_pop($ParsedColumns);
                                }
                            }

                            // parse the returned Row
                            $MainArraySize = count($ColumnsAndRows);
                            $LocalArraySize = count($ColumnsAndRows[0]);
                            $LocalRowAndColumns = array();
                            for ($x = 0; $x < $LocalArraySize; $x++) {
                                for ($y = 0; $y < $MainArraySize; $y++) {
                                    if ("undefined" != $ColumnsAndRows[$y][$x]) {
                                        array_push($LocalRowAndColumns, $ColumnsAndRows[$y][$x]);
                                    }
                                }
                                array_push($RetColumsAndRows, $LocalRowAndColumns);
                                $ArraySize = count($LocalRowAndColumns);
                                for ($z = 0; $z < $ArraySize; $z++) {
                                    array_pop($LocalRowAndColumns);
                                }
                            }
                            $RetValue = $RetColumsAndRows;
                        }
                    } else if (0 == $Rows) {
                        $RetValue = array(false);
                    } else {
                        $RetValue = array(false, mysqli_error(self::$g_szConnectionID));
                    }
                    return $RetValue;
                }
            } else {
                $RetValue = array(false, mysqli_error(self::$g_szConnectionID));
                return $RetValue;
            }
        } else {
            $RetValue = false;
            return $RetValue;
        }
//			return $RetValue;
    }
    

    function ParseRows($_szColumn, $_szTable, $_szCondition = "", $_Login = '', $_Password = '') {
        // connect to the database
        if(!isset(self::$g_szConnectionID)){
            self::$g_szConnectionID = $this->GetDBConnection($_Login, $_Password);
        }
        if (NULL != self::$g_szConnectionID) {
            if (mysqli_select_db(self::$g_szConnectionID,DB_NAME)) {
                // get the conditions
                if ("" == $_szCondition) {
                    $SQLQuery = "SELECT $_szColumn FROM $_szTable";
                } else {
                    $SQLQuery = "SELECT $_szColumn FROM $_szTable $_szCondition";
                }
                //NetDebug::trace(array("Function ParseRows- SQLQuery: ",$SQLQuery));
                $RetObject = mysqli_query(self::$g_szConnectionID,$SQLQuery);
                // get the number of rows
                $Rows = mysqli_num_rows($RetObject);
                //NetDebug::trace(array("Function ParseRows- Nos of Rows:",$Rows));
                if ($Rows > 0) {
                    $Result = mysqli_fetch_row($RetObject);
                    //NetDebug::trace(array("Function ParseRows- Nos of Rows:",$Rows,"Result Array",$Result));
                    // parse the output retreived from the database
                    for ($i = 0; $i < $Rows; $i++) {
                        $RetValue .= $Result[$i];
                        if ($i < ($Rows - 1)) {
                            $RetValue .= ',';
                        }
                    }
                    if (0 == $Rows) {
                        $RetValue = $Result;
                        //NetDebug::trace(array("Function ParseRows: No Rows Returned",mysqli_error()));
                    }
                } else {
                    $RetValue = mysqli_error(self::$g_szConnectionID);
                }
            } else {
                $RetValue = mysqli_error(self::$g_szConnectionID);
            }
        } else {
            $RetValue = "FALSE";
        }
        return $RetValue;
    }
    

    function QueryExecution($_SQLQuery, $_Login = '', $_Password = '', $_Database = DB_NAME) {
        // execute the query
        $RetValue = array("FALSE");
        //NetDebug::trace(array("Function QueryExecution-Execute: ",$_SQLQuery,"on",DB_NAME));
        if ("" != $_SQLQuery) {
            if(!isset(self::$g_szConnectionID)){
                self::$g_szConnectionID = $this->GetDBConnection($_Login, $_Password);
            }
            if (NULL != self::$g_szConnectionID) {
                $Connected = mysqli_select_db(self::$g_szConnectionID,$_Database);
                //NetDebug::trace(array("Function QueryExecution-Connected Succesfully"));
                if ($Connected) {
                    $RetObject = mysqli_query(self::$g_szConnectionID,$_SQLQuery);
                    //NetDebug::trace(array("Function QueryExecution-ExecutedQuery"));
                    if ($RetObject == false) {
                        array_push($RetValue, mysqli_error(self::$g_szConnectionID));
                    } else {
                        $RetValue[0] = "TRUE";
                    }
                } else {
                    //NetDebug::trace(array("Function QueryExecution-FAILED"));
                    array_push($RetValue, mysqli_error(self::$g_szConnectionID));
                }
            } else {
                //NetDebug::trace(array("Function QueryExecution-FAILED-No Connection"));
                array_push($RetValue, mysqli_error(self::$g_szConnectionID));
            }
        } else {
            //NetDebug::trace(array("Function QueryExecution-FAILED-No Query statement"));
            array_push($RetValue, "There was no section specified to be updated");
        }
        return $RetValue;
    }
    
    function ReturnQueryExecution($_SQLQuery, $_bReturnIsertId = false, $_Login = '', $_Password = '', $_Database = DB_NAME) {
        // execute the query
        //NetDebug::trace(array("Function QueryExecution-Execute: ",$_SQLQuery,"on",DB_NAME));
        if ("" != $_SQLQuery) {
            if(!isset(self::$g_szConnectionID)){
                self::$g_szConnectionID = $this->GetDBConnection($_Login, $_Password);
            }
            if (NULL != self::$g_szConnectionID) {
                $Connected = mysqli_select_db(self::$g_szConnectionID,$_Database);
                //NetDebug::trace(array("Function QueryExecution-Connected Succesfully"));
                if ($Connected) {
                    $RetObject = mysqli_query(self::$g_szConnectionID,$_SQLQuery);
                    #echo "ReturnQueryExecution:".$_SQLQuery."<br/>";
                    //NetDebug::trace(array("Function QueryExecution-ExecutedQuery"));
                    if ($RetObject == false) {
                        $RetValue = array(false, mysqli_error(self::$g_szConnectionID));
                    } else {
                        $Rows = mysqli_affected_rows(self::$g_szConnectionID);
                        if (!$_bReturnIsertId) {
                            $RetValue = array($RetObject, $Rows);
                        } else {
                            $RetValue = array($RetObject, mysqli_insert_id(self::$g_szConnectionID));
                        }
                    }
                } else {
                    //NetDebug::trace(array("Function QueryExecution-FAILED"));
                    $RetValue = array(false, mysqli_error(self::$g_szConnectionID));
                }
            } else {
                //NetDebug::trace(array("Function QueryExecution-FAILED-No Connection"));
                $RetValue = array(false, mysqli_error(self::$g_szConnectionID));
            }
        } else {
            //NetDebug::trace(array("Function QueryExecution-FAILED-No Query statement"));
            $RetValue = array(false, "There was no section specified to be updated");
        }
        return $RetValue;
    }

    public function GetDBConnection($szUser, $szPassword, $szDb=DB_HOST) {
        //NetDebug::trace(array("GetDBConnection","User",$szUser,"Password",$szPassword));
        //NetDebug::trace(array("GetDBConnection","DB_HOST",DB_HOST,"DB_USER",DB_USER,"DB_PASS",DB_PASS));
        // connect to the database
        if ("" == $szUser || "" == $szPassword) {
            $g_szConnectionID = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
        } else {
            // connect as normal
            // check for the user's login details in the database
            // ensure he/she has rights to access the database
            $g_szConnectionID = mysqli_connect(DB_HOST, $szUser, $szPassword);
        }
        // determine if the connection was succesfull
        if (!$g_szConnectionID) {
            die(mysqli_error(self::$g_szConnectionID));
            return NULL;
            //NetDebug::trace($g_szReturnString);
        } else {
//            $g_szReturnString = "GetDBConnection: Connected to 'DB_HOST' Successfully";
            self::$bConnCreated = true;
            //NetDebug::trace(array("GetDBConnection","Connected to 'DB_HOST' Successfully"));
            return $g_szConnectionID;
        }
        return NULL;
    }

    /**
     * CDbAccess::sanitizeDbInput()
     * 
     * Sanitizes a variable which is meant to be used as part of a SQL query
     * This is to avoid SQL Injection attacks
     * 
     * @param   mixed $_var - The variable whose value is to be sanitized
     * @return  mixed
     */
    public function sanitizeDbInput($_var) {
        // Create a connection to the DB if non exists
        if (!self::$bConnCreated) {
            if (NULL == (self::$g_szConnectionID = $this->GetDBConnection("", ""))) {
                // Attempted connection failed... return the input variable
                return $_var;
            }
        }
        // sanitize the input
        $clVar = mysqli_real_escape_string(self::$g_szConnectionID, $_var);
        return $clVar;
    }

}

?>
