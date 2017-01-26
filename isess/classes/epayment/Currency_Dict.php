<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace epayment;

/**
 * Description of Currency_Dict
 *
 * @author femi
 */
class Currency_Dict extends ADb implements \IWp{
    
    public $CurrencyDict_Id;
    public $Currency;
    public $Currency_Symbol;
    
    
    public function __construct($_id, $_fieldlist=NULL, $_createOnDb = false)
    {
        if(NULL == $_id && $_fieldlist != NULL){
            if(isset($_fieldlist['Currency'])){
                $result = Currency_Dict::_get(array("CurrencyDict_Id"), "Currency == '".$_fieldlist['Currency']."'");
                if (Utilities::isValidDbObj($result)) {
                    $_id = $result[0]->CurrencyDict_Id;
                }
            }
        }
        $fieldToTypes = array('CurrencyDict_Id' => '%d',
                              'Currency' => '%s',
                              'Currency_Symbol' => '%s');
        parent::__construct($_id, $_fieldlist, $fieldToTypes, $_createOnDb);
    }    
    public function addHooks($_parent) {
        
    }

    public static function createTable($_aArgList = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix .'pi_currency_dict';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . $table_name . " (
          CurrencyDict_Id int(11) NOT NULL AUTO_INCREMENT,
          Currency varchar(255) NOT NULL,
          Currency_Symbol varchar(5) NOT NULL,
          PRIMARY KEY (CurrencyDict_Id)
        ) $charset_collate;".
        "INSERT INTO pi_currency_dict (CurrencyDict_Id, Currency, Currency_Symbol) VALUES
        (1, 'US Dollars', 'USD'),
        (2, 'British Pound', 'GBP'),
        (3, 'Euro', 'EUR');";                
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public static function uninstallTable($_aArgList = NULL) {
        throw new Exception("'Currency_Dict' Table Uninstall has not been implemented");
    }
    
    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . "pi_".strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }    

}
