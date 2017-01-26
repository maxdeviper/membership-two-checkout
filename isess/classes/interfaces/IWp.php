<?php

//namespace dataexchange;

/**
 * Implement this interface to tie in wordpress support to your class
 * @author Femi
 */
interface IWp {
    /**
     * Override this function and add the neccessary code to create a new table of this class 
     * for wordpress on the database
     * @param mixed $_aArgList
     * @return boolean - true/false if succesfull or not
     */    
    public static function createTable($_aArgList=NULL);
    
    /**
     * 
     * Override this function and add the neccessary code to uninstall a table of this class  on the database
     * @param mixed $_aArgList
     * @return boolean - true/false if succesfull or not
     */    
    public static function uninstallTable($_aArgList=NULL);
    
    /**
     * 
     * Override this function and add the neccessary code to utilize the WordPress hooks required by the owner
     * class
     * 
     * @param object $_parent - instance of the class that performs the hook registrations
     */    
    public  function addHooks($_parent);
    
    public function toFieldlist();
}
