<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace epayment;

/**
 * Description of PaymentExtraValues
 *
 * @author femi
 */
class PaymentExtraValues extends ADb implements \IWp{
    
    public $ExtraValue_Id;
    public $Payment_Id;
    public $ExtraValue_Key;
    public $ExtraValue_Value;
    
    /**
     * PaymentGateway_Dict::__construct()
     * 
     * @param integer $id
     * @param string $name
     * @return void
     */
    public function __construct($_id, $_fiedlist=NULL, $_createOnDb = false)
    {
        $fieldToTypes = array(
            'ExtraValue_Id' => '%d',
            'Payment_Id' => '%d',
            'ExtraValue_Key' => '%s',
            'ExtraValue_Value' => '%s');
        parent::__construct($_id, $_fiedlist, $fieldToTypes, $_createOnDb);
    }
    
    
    public function addHooks($_parent) {
        
    }

    public static function createTable($_aArgList = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix .'pi_paymentextravalues';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . $table_name . " (
          ExtraValue_Id int(11) NOT NULL AUTO_INCREMENT,
          Payment_Id int(11) NOT NULL,
          ExtraValue_Key varchar(255) NOT NULL
          ExtraValue_Value varchar(255) NOT NULL
          PRIMARY KEY (ExtraValue_Id)
        ) $charset_collate;";                
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public static function uninstallTable($_aArgList = NULL) {
        throw new Exception("'PaymentExtraValues' Table Uninstall has not been implemented");
    }
    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . "pi_".strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }
}
