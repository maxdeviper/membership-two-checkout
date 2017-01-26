<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author femi
 */
interface IAuth {
    
    /**
     * creates a new account on the server
     * @param object $_fieldlist - an array of key value pairs eg ("email" => f@me.com,"city" => "london")
     * @return object - {success:true/false,msg:an error message if the request fails} the return object 
     */    
    public function createAccount($_fieldlist);
    
    /**
     * logs into the server
     * @param string $_username - the username
     * @param string $_password - the password or true factor confirmation code (see next paramater)
     * @param boolean $_bIsTwoFactor - a flag that if set to true means we are using two-factor authentication
     * @return object - {success:true/false,token:unique id for the session,msg:an error message if the request fails} the return object
     */    
    public function login($_username,$_password,$_bIsTwoFactor=false);
    
    /**
     * logs out the active user
     * @param string $_accessToken - the users access token
     * @return object - {success:true/false,msg:an error message if the request fails} the return object 
     */    
    public function logout($_accessToken);
    
    /**
     * invoked when the user wants to confirm their account on the platform
     * @param string $_username - the username
     * @param object $_confirmationArgs - a key/value set of confirmation arguments
     * @return object - {success:true/false,id:int value,msg:an error message if the request fails} the return object 
     */    
    public function confirmAccount($_username,$_confirmationArgs);
    
    /**
     * generates a confirmation code to be sent to the user after a succesfull account registration
     * @param string $_username - the username
     * @param string $_method - the means the confirmation message will be sent (email,text,telephone)
     * @return object - {success:true/falsemsg:an error message if the request fails} the return object 
     */    
    public function generateConfirmationCode($_username,$_method);
    
    /**
     * validates the session of the user specified by the access token
     * @param string $_accessToken - the users access token
     * @return object - {success:true/false,id:int value,msg:an error message if the request fails} the return object 
     */    
    public function validateSession($_accessToken);
    
    /**
     * validates an API Key
     * @param array $_args - key=>value array of one or more fields
     * 
     * @return boolean 
     */
    static function validateAPIKey($_args);
    
    /**
     * validates the active users access to the resource
     * @param array $_args - key=>value array of one or more fields
     * 
     * @return boolean 
     */
    static function validateUserAccess($_args);
    
    /**
     * determines whether the user has access to the object in question
     * @param string $_permission - the required permission (create,retreive,update,delete)
     * @param string $_object - the name of the object or class
     * @return boolean - success:true/false 
     */    
    public function canUser($_permission,$_object,$_args=NULL);
}
