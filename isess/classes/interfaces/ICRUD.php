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
interface ICRUD {
    public function  genericCreate($_args);
    public function  genericRetrieve($_args);
    public function  genericUpdate($_args);
    public function  genericDelete($_args);
    
    public static function _table();
    public function _primaryKey();
}
