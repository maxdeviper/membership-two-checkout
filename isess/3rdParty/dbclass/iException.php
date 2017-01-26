<?php

/**
 * @author Hedonmark 
 * @copyright 2010
 * @filesource  iException.int.php
 */


interface iException
{
    
    public function display();
    
    public static function setErrorLevel($errLvl);
}

?>