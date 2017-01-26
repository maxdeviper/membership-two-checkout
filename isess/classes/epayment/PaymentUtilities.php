<?php

namespace epayment;

/**
 * @author      Opus Hive
 * @copyright   2011
 * @filesource  PaymentUtilities.php
 */


/**
 * PaymentUtilities
 * 
 * This class will contain utility methods that will be used by the payment gateways
 * 
 * @package     epayment
 * @author      Opus Hive
 * @copyright   2011
 * @version     1.0
 * @access      public
 */
class PaymentUtilities
{
    
    const MAX_GEN_RETRIES = 100;
    
    /**
     * PaymentUtilities::getCurrencyIdFromName()
     * 
     * Fetches the ID of a currency, if it exists, in the DB else it returns -1 if the currency was not found
     * 
     * @access  public
     * @static
     * @param   string $currency
     * @return  integer
     */
    public static function getCurrencyIdFromName($currency)
    {
        $data = Currency_Dict::_get(array("CurrencyDict_Id"), 
                "Currency='".$currency."'",ADb::OBJECT,0,1);
        if(\Utilities::isValidDbObj($data)){
            return $data[0]->CurrencyDict_Id;
        }
        else{
            return -1;
        }
    }
    
    /**
     * PaymentUtilities::getStatusIdFromName()
     * 
     * Fetches the ID of a payment status, if it exists, in the DB else it returns -1 if the status was not found
     * 
     * @access  public
     * @static
     * @param   string $name - The name of the payment status
     * @return  integer
     */
    public static function getStatusIdFromName($name)
    {
        $data = Currency_Dict::_get(array("PaymentStatusDict_Id"), 
                "PaymentStatus LIKE '".$name."'",ADb::OBJECT,0,1);
        if(\Utilities::isValidDbObj($data)){
            return $data[0]->PaymentStatusDict_Id;
        }
        else{
            return -1;
        }
    }

    /**
     * checkTransNumInDB()
     *
     * Checks if a transaction number exists in the DB
     *
     * @access  public
     * @param   integer $_nTransNum - The transaction number to be checked in the DB
     * @return  bool
     */
    static function checkTransNumInDB($_nTransNum,$_sGatewayName)
    {
        global $wpdb;
        
        $data = Payment::_get(array('COUNT(Payment_Id) AS thecount'),
                            array($wpdb->prefix."pi_paymentgateway_dict b",
                                $wpdb->prefix."pi_payment.PaymentGatewayDict_Id = "
                                . "b.PaymentGatewayDict_Id AND b.PaymentType='".$_sGatewayName."' "
                                . "AND ".$wpdb->prefix."pi_payment.transactionId='".$_nTransNum."'"));
        if (Utilities::isValidDbObj($data)) {
            return -1;
        }
        else{
            return ($data[0]['thecount'] > 0);
        }
    }

    /**
     * genTransactionNumber()
     *
     * Generates a transaction number and validates it against records in the DB to ensure it is unique
     * Throws an ErrorException if a unique transaction number cannot be generated after the predefined max number of trials
     *
     * @access  private
     * @throws  ErrorException
     * @return  integer
     */
   static function genTransactionNumber($_sGatewayName)
    {
        // set the maximum number of recursions
        $maxTrials = self::MAX_GEN_RETRIES;
        $exists = false;
        do
        {
            // generate a 8-digit transaction number
            $num = mt_rand(10000000,99999999);
            #$_nTransNumber = sprintf("%08d", $num);
            $_nTransNumber = $num;
            // Decrement the max trials to indicate a new trial
            $maxTrials--;
            $exists = PaymentUtilities::checkTransNumInDB($_nTransNumber,$_sGatewayName);
            // Check the response
            if ($exists === -1)
                break;
        }
        while ($exists && $maxTrials > 0);

        // If the transaction number already exists and max trials already exhausted
        if ($exists && $maxTrials < 1)
        {
            // throw an exception to indicate and error
            throw new ErrorException("Transaction number could not be generated");
        }

        return $_nTransNumber;
    }

}

?>