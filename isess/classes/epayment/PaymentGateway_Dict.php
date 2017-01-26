<?php

namespace epayment;
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PaymentGateway_Dict
 *
 * @author Femi
 */
class PaymentGateway_Dict extends ADb implements IWp {
    //put your code here
    const PAY4ME = 1;
    const ON_DELIVERY = 2;
    const INTERSWITCH = 3;
    const BANK = 4;
    const TO_CHECKOUT = 5;
    
    public $paymentGatewayDict_Id;
    public $name;
    public $displayAs;
    public $desc;
    public $id;
    
    
    /**
     * PaymentGateway_Dict::__construct()
     * 
     * @param integer $id
     * @param string $name
     * @return void
     */
    public function __construct($_id, $_fieldlist=NULL, $_createOnDb = false)
    {
        if(NULL == $_id && $_fieldlist != NULL){
            if(isset($_fieldlist['paymentType'])){
                $result = ADb::_get(array("PaymentGatewayDict_Id"), "paymentType == '".$_fieldlist['paymentType']."'");
                if (Utilities::isValidDbObj($result)) {
                    $_id = $result[0]->PaymentGatewayDict_Id;
                }
            }
        }
        $fieldToTypes = array('PaymentGatewayDict_Id' => '%d',
                                'paymentType' => '%s',
                                'displayAs' => '%s',
                                'description' => '%s');
        parent::__construct($_id, $_fieldlist, $fieldToTypes, $_createOnDb);
    }
    

    static function getPaymentTypes(){
        $result = PaymentGateway_Dict::_get();
        if (Utilities::isValidDbObj($result)) {
            return $result;
        }
        else{
            return NULL;
        }
    }

    public function addHooks($_parent) {
        
    }

    public static function createTable($_aArgList = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix .'pi_paymentgateway_dict';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . $table_name . " (
          PaymentGatewayDict_Id int(11) NOT NULL AUTO_INCREMENT,
          paymentType varchar(255) NOT NULL,
          displayAs varchar(255) NOT NULL,
          description varchar(1000) NULL
          PRIMARY KEY (PaymentGatewayDict_Id)
        ) $charset_collate;".
        "INSERT INTO pi_paymentgateway_dict (PaymentaGatewayDict_Id, PaymentType) VALUES
            (1, '2CHECKOUT');";                
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public static function uninstallTable($_aArgList = NULL) {
        throw new Exception("'PaymentGateway_dict' Table Uninstall has not been implemented");
    }
    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . "pi_".strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }
}
?>
