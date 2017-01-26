<?php

namespace epayment;

/**
 * @author      Opus Hive
 * @copyright   2011
 * @filesource  CPayAtBank.php
 */

require_once(CLASSES_PATH_ISESS."epayment/interfaces/IPayment.php");
require_once(CLASSES_PATH_ISESS."epayment/PaymentUtilities.php");
require_once(CLASSES_PATH_ISESS."epayment/PaymentGateway_Dict.php");
require_once(CLASSES_PATH_ISESS."epayment/CCart.php");
require_once(CLASSES_PATH_ISESS."epayment/CItem.php");

/**
 * CPayAtBank
 * 
 * @package     offline
 * @author      Opus Hive
 * @copyright   2011
 * @version     1.0
 * @access      public
 */
class CPayAtBank implements IPayment
{
    public $merchantType;    
    
    const GATEWAY_NAME = 'BANK';
    const GATEWAY_ERROR = 999;
    const MAX_GEN_RETRIES = 100;
    
    /**
     * CPayAtBank::__construct()
     * 
     * @param   bool $_bDemoMode
     * @return  void
     */
    public function __construct()
    {
        $this->merchantType = new PaymentGateway_Dict(0, self::GATEWAY_NAME);
    }
    
    public function makePayment($_sPaymentReference,
                                $_oCartList,
                                $_sCurrency)
    {
        /**
         * Steps of usage : 
         *  1)  Extract details from cartObj 
         *  2)  Log Transaction
         *  5)  OnDb error, rollback transaction
         * */
        /**
         * STEP 1 : Parse and convert necessary values
         * */
        $aPaymentRefs = array();
        $maxRand = 20000;
        $aItemRefs = array_keys($_oCartList);
        $nCartSize = count($aItemRefs);
        for($x=0;$x<$nCartSize;$x++){
            $nextItem = $_oCartList[$aItemRefs[$x]];
            $paymentVals = array();
            $paymentVals['amount'] = $nextItem->getUnitPrice();
            $paymentVals['itemId'] = $nextItem->getCustomField('id');
            $paymentVals['itemName'] = $nextItem->getName();
            $paymentVals['desc'] = $nextItem->getDescription();
            $paymentVals['currency'] = $_sCurrency;
            $paymentVals['transId'] = PaymentUtilities::genTransactionNumber(self::GATEWAY_NAME);
            array_push($aPaymentRefs,$paymentVals);
        }
                
        /**
         * STEP 3 : Log the new payment in the DB
         * */
        for($y=0;$y<$nCartSize;$y++){
            $rsp = $this->logNewPayment($aPaymentRefs[$y],$_sPaymentReference);
            // Indicate an error if the payment could not be logged
            if (!$rsp){
                return false;
            }
        }
        return array(true,$_sPaymentReference);
    }
    
    public function cancelPayment($_sPaymentReference){        
    }
    
    /**
     * CPayAtBank::onReceivePaymentNotification()
     * 
     * @access  public
     * @param   array $_oNoficationArgs {productid,transid,status,paymentdate,amount,paymentref,isrefund}
     * @return  integer | TRUE
     */
    public function onReceivePaymentNotification($_oNoficationArgs,&$_sErrorMessage){
        // Get the details
        $aKeys = array_keys($_oNoficationArgs);
        if(count($aKeys) >= 7){
            $oPaymentValues = array();
            $oPaymentValues['ref'] = $_oNoficationArgs['ref'];
            $oPaymentValues['transId']  = $_oNoficationArgs['transid'];
            $oPaymentValues['status']  = $_oNoficationArgs['status'];
            $oPaymentValues['paymentDate']  = $_oNoficationArgs['paymentdate'];
            $oPaymentValues['amount']  = $_oNoficationArgs['amount'];
            $oPaymentValues['paymentRef']  = $_oNoficationArgs['paymentref'];
            /*
            $extraVals['mode'] = $notifObj->mode.'';
            $extraVals['bank'] = $notifObj->bank->name.'';
            $extraVals['branch'] = $notifObj->bank->branch.'';
            $paymentVals['code'] = $notifObj->status->code.'';
            $paymentVals['msg'] = $notifObj->status->description.'';
            */
            $oExtraVals = NULL;
            // Call the method to update the payment status
            $resp = $this->updatePaymentStatus($oPaymentValues, $oExtraVals);

            // Check the response]
            if ($resp === -1)
                $_sErrorMessage = 'Error:Payment transaction not found';
            if (!$resp)
                $_sErrorMessage = 'Error:Transaction Info could not be updated';
            if ($resp === 2)
                $_sErrorMessage = 'Error:Extra details cold not be saved';

            return true;
        }
        else{
            $_sErrorMessage = "INVALID REQUEST: DOES NOT HAVE THE RIGHT ARGUMENTS";
            return false;
        }
    }           
    
    /**
     * CPayAtBank::logNewPayment()
     * 
     * @access  private
     * @param   array $params
     * @return  integer
     */
    private function logNewPayment(array $params,$_sPaymenRef,$_sStatus='PENDING')
    {
        $dbAccess = new CDbAccess();
        
        // Get the values and save them
        $amount = 0;
        $unitPrice = $params['amount'];
        $transId = $params['transId'];
        $currency = $params['currency'];
        $status = $_sStatus;
        $orderDate = date('Y-m-d H:i:s');
        $productId = $params['itemId'];
        
        // Get the gateway ID
        $gatewayId = $this->merchantType->id;
        // Get the currency ID
        $currencyId = PaymentUtilities::getCurrencyIdFromName($currency);
        // Get ther ID of the status
        $statusId = PaymentUtilities::getStatusIdFromName($status);
        
        // Save the records to the DB
        $inSql = sprintf("INSERT INTO pi_payment (PaymentGatewayDict_Id,CurrencyDict_Id,Product_Id,PaymentRef,TransactionId,UnitPrice,AmountReceived,OrderDate,PaymentStatusDict_Id)
                            VALUES (%d,%d,%d,'%s','%s',%.2f,%.2f,'%s',%d)",
                            $gatewayId, $currencyId, $productId, $_sPaymenRef,$transId, $unitPrice, $amount, $orderDate, $statusId
                        );
        $data = $dbAccess->ReturnQueryExecution($inSql, true);
        // Validate the response
        if ($data === 'FALSE' || $data[0] === 'FALSE')
            return -1;
            
        return $data[1];
    }
    
    /**
     * CPayAtBank::updatePaymentStatus()
     * 
     * Updates the status of the payment transaction
     * Returns -1 if the payment record was not found in the DB, 0/FALSE if the DB could not be updated, else 1 if all was successful, 2 if the extra params could not be saved
     * 
     * @access  private
     * @param   array $params - The array of values 
     * @param   array $extraParams - Extra values like payment mode, bank name, branch etc
     *  {productid,transid,status,paymentdate,amount,paymentref,isrefund}
     * @return  integer
     */
    private function updatePaymentStatus(array $params, $extraParams=array())
    {
        // Get the details of the payment record
        $sTransId = $params['transId'];
        $nProductId = (int)$params['productid'];
        $sStatus = $params['status'];
        $payDate = empty($params['paymentdate']) ? date('Y-m-d H:i:s') : $params['paymentdate'];
        $nAmount = $params['amount'];
        $sPaymentRef = $params['paymentref'];
        $bIsRefund = (int)$params['isrefund'];
        $gatewayId = $this->merchantType->id;
        
        // Get the extra details
        $successPay = false;
        // Validate that the payment record exists
        $db = new CDbAccess();
        $cond = " WHERE TransactionId='$sTransId' AND Product_Id=$nProductId AND PaymentGatewayDict_Id=$gatewayId LIMIT 1";
        $data = $db->ParseColumnsAndRows('Payment_Id,UnitPrice', 'pi_payment', $cond);
        // Validate the response
        if (empty($data) || $data == 'FALSE' || $data[0] == 'FALSE')
            return -1;
        // Get the payment ID
        $paymentId = $data[0]['Payment_Id'];
        if($data[0]['UnitPrice'] <= $nAmount){
            $successPay = true;
        }
        // Get the status ID for successful payment
        $statusId = PaymentUtilities::getStatusIdFromName($sStaus);
        
        // Now update the status of this payment record
        $upSql = sprintf("UPDATE pi_payment SET IsPaid=%d, IsRefunded=%d,PaidDate='%s',
                        PaymentStatusDict_Id=%d, PaymentRef='%s' WHERE Payment_Id=%d",
                        (int)$successPay,$bIsRefund,$payDate,(int)$statusId, $db->sanitizeDbInput($sPaymentRef), (int)$paymentId);
        // Execute the query
        $data = $db->ReturnQueryExecution($upSql);
        if ($data === 'FALSE' || $data[0] === 'FALSE')
            return false;
        
        // Log the payment mode details at this point
        if ($successPay && !empty($extraParams))
        {            
            $inSql = "INSERT INTO pi_paymentextravalues (Payment_Id,ExtraValue_Key,ExtraValue_Value) VALUES";
            foreach ($extraParams as $key => $val)
            {
                $inSql = sprintf("%s (%d,'%s','%s'),", $inSql, $paymentId, $db->sanitizeDbInput($key), $db->sanitizeDbInput($val)); 
            }
            
            $inSql = rtrim($inSql, ',');
            // Execute the SQL query
            $data = $db->ReturnQueryExecution($inSql, true);
            if ($data === 'FALSE' || $data[0] === 'FALSE')
                return 2;
            
        }  
          
        return 1;                
        
    }
}

?>