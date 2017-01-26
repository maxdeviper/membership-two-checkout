<?php

/**
 * @author      Victor Aluko 
 * @copyright   2010
 * @filesource  QueryException.cls.php
 */

require_once("iException.php");
require_once("constants.php");

/**
 * QueryException
 * 
 * @author      Victor Aluko 
 * @copyright   2010
 * @version     1.0
 * @access      public
 */
class QueryException extends Exception implements iException
{
    /**
     * The SQL associated with this exception
     * @access  protected
     * @var     string
     * */
    protected $sql;
    
    /**
     * The user friendly error message associated with this exception
     * @access  protected
     * @var     string
     * */
    protected $displayMsg;
        
    /**
     * The error level that should be used to display errors
     * @access      private
     * @staticvar   integer
     * */
    private static $errLevel = GLOB_ERR_LEVEL;
    
    
    /**
     * QueryException::__construct()
     * 
     * This is the class constructor to create an instance of this exception
     * 
     * @access  public
     * @param   string $msg - The error message of this exception
     * @param   int $errcode - The error code of this exception
     * @param   string $sql - The SQL statement that generated the exception
     * @param   string $displaymsg - Any user-friendly message that should be displayed when this exception is caught
     * @return  void
     */
    public function __construct($msg, $errcode, $sql, $displaymsg='')
    {
        $this->sql = $sql;
        $this->displayMsg = $displaymsg;
        parent::__construct($msg, $errcode);
    }
    
    /**
     * QueryException::getQuery()
     * 
     * This function returns the SQL statement that generated this instance of the exception
     * 
     * @access  public
     * @return  string
     */
    public function getQuery()
    {
        return $this->sql;
    }
    
    /**
     * QueryException::getDisplayMsg()
     * 
     * This method returns the user-friendly error message of this exception
     * 
     * @access  public
     * @return  string
     */
    public function getDisplayMsg()
    {
        return $this->displayMsg;
    }
    
    /**
     * QueryException::setErrorLevel()
     * 
     * This function changes the error level
     * 
     * @param   int $errLvl - The error level
     * @return  void
     */
    public static function setErrorLevel($errLvl)
    {
        self::$errLevel = $errLvl;
    }
    
    /**
     * QueryException::display()
     * 
     * This function handles the display of the exception class. 
     * This function can be called in place of getMessage() to display the error messages.
     * The error level must be a value among the class constants
     * 
     * @access  public
     * @param   void
     * @return  string
     */
    public function display()
    {
        $returnmsg = '';
        
        switch (self::$errLevel)
        {
            case ERR_LEVEL_MINIMAL : $returnmsg = $this->displayMsg;
                break;
            
            case ERR_LEVEL_DEBUG : 
                $returnmsg = "ERROR MSG : ".$this->getMessage();
                $returnmsg .= "\n ERROR CODE : ".$this->getCode();
                $returnmsg .= "\n SQL : ".$this->sql;
                $returnmsg .= "\n USER MSG : ".$this->displayMsg;
                break;
                
            case ERR_LEVEL_FULL : 
                $returnmsg = "ERROR MSG : ".$this->getMessage();
                $returnmsg .= "\n ERROR CODE : ".$this->getCode();
                $returnmsg .= "\n SQL : ".$this->sql;
                $returnmsg .= "\n USER MSG : ".$this->displayMsg;
                $returnmsg .= "\n FILE : ".$this->getFile()." \n LINE : ".$this->getLine();
                $returnmsg .= "\n TRACE : ".$this->getTraceAsString();
                break;
                
            case ERR_LEVEL_SILENT : $returnmsg = "";
                break;
                
            default : $returnmsg = get_class($this)." was thrown";
                break;
        }
        
        return $returnmsg;
    }
    
}

?>