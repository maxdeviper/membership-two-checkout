<?php
include_once '../../startup_inc.php';
namespace epayment;

/**
 * @author Femi
 * @version 1.0
 * @created 31-May-2011 17:48:01
 */
class Payment extends ADb implements IPayment,\IWp
{
    public  $Payment_Id;
    public  $PaymentGatewayDict_Id;
    public  $CurrencyDict_Id;
    public  $Product_Id;
    public  $PaymentRef;
    public  $TransactionId;
    public  $ValidationNumber;
    public  $Qty;
    public  $UnitPrice;
    public  $ShippingPrice;
    public  $AmountReceived;
    public  $OrderDate;
    public  $PaidDate;
    public  $PaymentStatusDict_Id;
    public  $IsPaid;
    public  $IsRefunded;
    public  $GatewayResposeCode;
    public  $GatewayResponseMsg;
    
    private $paymentHandler;
    private $paymentType;
    
    function Payment($_id, $_fieldlist=NULL, $_createOnDb=false)
    {
        $fieldToTypes = array(
            'Payment_Id' => '%d',
            'PaymentGatewayDict_Id' => '%d',
            'CurrencyDict_Id' => '%d',
            'Product_Id' => '%d',
            'PaymentRef' => '%s',
            'TransactionId' => '%d',
            'ValidationNumber' => '%s',
            'Qty' => '%f',
            'UnitPrice' => '%f',
            'ShippingPrice' => '%f',
            'AmountReceived' => '%f',
            'OrderDate' => '%s',
            'PaidDate' => '%s',
            'PaymentStatusDict_Id' => '%d',
            'IsPaid' => '%d',
            'IsRefunded' => '%d',
            'GatewayResposeCode' => '%d',
            'GatewayResponseMsg' => '%s'
        );
        if(isset($_fieldlist)){
            if(isset($_fieldlist['PaymentType'])){
                $this->paymentType = (bool)$_fieldlist['PaymentType'];
            }
        }
        else{
            $this->paymentType = false;
        }
        parent::__construct($_id, $_fieldlist, $fieldToTypes, $_createOnDb);
        if(PaymentGateway_Dict::BANK == $this->m_paymentType){
            // log payment details in the db
            // return valid transaction to the user
        }
        else if(PaymentGateway_Dict::INTERSWITCH == $this->m_paymentType){
            // pass to the interswitch handler
            // return transaction status to the user
        }
        else if(PaymentGateway_Dict::TO_CHECKOUT == $this->m_paymentType){
            // pass to the interswitch handler
            $this->paymentHandler = new CTwoCheckoutMerchant(NULL);
            // return transaction status to the user
        }
        else if(PaymentGateway_Dict::ON_DELIVERY == $this->m_paymentType){
            // log payment details in the db
            // return valid transaction to the user
        }
    }

    function makePayment($_theCart){
        $this->paymentHandler->makePayment($_theCart);
    }

    public function addHooks($_parent) {
        
    }

    public static function createTable($_aArgList = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix .'pi_payment';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . $table_name . " (
            Payment_Id int(11) unsigned NOT NULL AUTO_INCREMENT,
            paymentGatewayDict_Id int(11) NOT NULL,
            CurrencyDict_Id int(11) NOT NULL,
            Product_Id int(11) NOT NULL,
            PaymentRef varchar(255) DEFAULT NULL,
            TransactionId varchar(255) DEFAULT NULL,
            ValidationNumber varchar(50) DEFAULT NULL,
            Qty mediumint(9) NOT NULL DEFAULT '1',
            UnitPrice float(15,2) NOT NULL,
            ShippingPrice float(15,2) DEFAULT '0.00',
            AmountReceived float(15,2) DEFAULT NULL,
            OrderDate datetime NOT NULL,
            PaidDate datetime DEFAULT NULL,
            PaymentStatusDict_Id int(10) unsigned NOT NULL,
            IsPaid tinyint(1) NOT NULL DEFAULT '0',
            IsRefunded tinyint(1) NOT NULL DEFAULT '0',
            GatewayResposeCode int(11) DEFAULT NULL,
            GatewayResponseMsg varchar(200) DEFAULT NULL,
            PRIMARY KEY (Payment_Id)          
        ) $charset_collate;";                
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    protected static function _table() {
        global $wpdb;
        $tablename = ADb::$prefix . "pi_".strtolower(get_called_class());
        return $wpdb->prefix . $tablename;
    }
    
    public static function uninstallTable($_aArgList = NULL) {
        throw new Exception("'Payment' Table Uninstall has not been implemented");
    }

    public function cancelPayment($_sPaymentReference) {
        $this->paymentHandler->cancelPayment($_sPaymentReference);
    }

    public function onReceivePaymentNotification($_oNoficationArgs, &$_sErrorMessage) {
        $success = falsse;
        try{
            $success = $this->paymentHandler->onReceivePaymentNotification($_oNoficationArgs,$_sErrorMessage);
        }
        catch (\Exception $ex){
            if(empty($_sErrorMessage)){
                $_sErrorMessage = $ex->getMessage();
            }
        }
        return $success;
    }

    public function toFieldlist() {
        $fieldlist = parent::toFieldlist();
        unset($fieldlist['paymentHandler']);
        return $fieldlist;
    }    
}
?>