<?php

/**
 * Interface to ensure the DbAccess classes have the same methods and they are both implemented
 * @author Victor Aluko 
 */
interface IDbAccess {
    
    public function ReturnQueryExecution($_SQLQuery, $_bReturnIsertId = false, $_Login = '', $_Password = '', $_Database = DB_NAME);
    
    public function QueryExecution($_SQLQuery, $_Login = '', $_Password = '', $_Database = DB_NAME);
    
    public function GetRow($_szColumn, $_szTable, $_szCondition = "", $_Login = '', $_Password = '', $_Database = DB_NAME);
    
    public function ParseRows($_szColumn, $_szTable, $_szCondition = "", $_Login = '', $_Password = '');
    
    public function ParseColumnsAndRows($_szColumns, $_szTable, $_szCondition = "", $_Login = '', $_Password = '', $_bUseKeyVal = true, $_sDb = DB_NAME);
    
    public function sanitizeDbInput($_var);
    
    public function GetDBConnection($szUser, $szPassword, $szDb=DB_HOST);
}
