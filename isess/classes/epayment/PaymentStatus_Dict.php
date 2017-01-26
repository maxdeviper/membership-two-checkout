<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace epayment;

/**
 * Description of PaymentStatus_Dict
 *
 * @author femi
 */
class PaymentStatus_Dict extends ADb implements \IWp{
    
    public $CurrencyDict_Id;
    public $Currency;
    public $Currency_Symbol;
    
    
    public function __construct($_id, $_fieldlist=NULL, $_createOnDb = false)
    {
        if(NULL == $_id && $_fieldlist != NULL){
            if(isset($_fieldlist['PaymentStatus'])){
                $result = ADb::_get(array("PaymentStatusDict_Id"), "PaymentStatus = '".$_fieldlist['PaymentStatus']."'");
                if (Utilities::isValidDbObj($result)) {
                    $_id = $result[0]->CurrencyDict_Id;
                }
            }
        }
        $fieldToTypes = array('PaymentStatusDict_Id' => '%d',
                              'PaymentStatus' => '%s');
        parent::__construct($_id, $_fieldlist, $fieldToTypes, $_createOnDb);
    }    
    public function addHooks($_parent) {
        
    }

    public static function createTable($_aArgList = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix .'pi_paymentstatus_dict';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . $table_name . " (
          PaymentStatusDict_Id int(11) NOT NULL AUTO_INCREMENT,
          PaymentStatus varchar(255) NOT NULL,
          PRIMARY KEY (PaymentStatusDict_Id)
        ) $charset_collate;".
        "INSERT INTO pi_paymentstatus_dict (PaymentStatusDict_Id,PaymentStatus) VALUES
        (1, 'PAID'),
        (2, 'PROCESSING'),
        (3, 'RETURNED'),
        (4, 'FAILED'),
        (5, 'DENIED'),
        (6, 'INVALID'),
        (7, 'FRAUD');";                
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public static function uninstallTable($_aArgList = NULL) {
        throw new Exception("'PaymentStatus_Dict' Table Uninstall has not been implemented");
    }
    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . "pi_".strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }
}
