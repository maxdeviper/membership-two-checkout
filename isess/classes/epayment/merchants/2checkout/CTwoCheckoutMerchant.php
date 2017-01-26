<?php

namespace epayment;

/**
 * @author      Opus Hive
 * @copyright   2011
 * @filesource  CTwoCheckoutMerchant.php
 */

/**
 * CTwoCheckoutMerchant
 * 
 * @package     2checkout
 * @author      Opus Hive
 * @copyright   2011
 * @version     1.0
 * @access      public
 */
class CTwoCheckoutMerchant implements IPayment {
    private $merchantSettings;
    private $merchantType;
    private $demoMode;
    private $dataVals;
    
    const GATEWAY_NAME = '2CHECKOUT'; 
    // TODO - the platforms account id
    const ACCOUNT_ID = '';
    const GATEWAY_ERROR = 999;
    const MAX_GEN_RETRIES = 100;
    // TODO - enter the full path to the callback url here - ensure it calls the 'onReceivePaymentNotification' function
    const RESPONSE_PROCESSOR_URL = '';
    
    /**
     * CTwoCheckoutMerchant::__construct()
     * 
     * @param bool $_bDemoMode
     * @return void
     */
    public function __construct($_demoMode=false)
    {
        $this->demoMode = (bool)$_demoMode;
        $this->merchantSettings = new CTwoCheckoutSettings();
        $this->merchantType = new PaymentGateway_Dict(0, self::GATEWAY_NAME);
        $this->dataVals = array();
    }
    
    
    function cancelPayment($_sPaymentReference)
    {
        throw new Exception("Cancel Payment has not been implemented!");
    }

    /**
     * CTwoCheckoutMerchant::makePayment()
     * 
     * @param   array $_theCart
     * @return  void
     */
    public function makePayment($_theCart)
    {
        // Get the cart details
        $this->dataVals['totalPrice'] = $_theCart['price'];
        $this->dataVals['cartOrderId'] = $_theCart['cartId'];
        $this->dataVals['failedUrl'] = $_theCart['returnUrl'];
        $this->dataVals['Tangible'] = empty($_theCart['isPhysical']) ? 'N' : 'Y';
        $this->dataVals['quantity'] = (int)$_theCart['quantity'] ? (int)$_theCart['quantity'] : 1; 
        $this->dataVals['currency'] = empty($_theCart['currency']) ? 'dollars' : $_theCart['currency'];
        
        // Generate a transaction ID
        $this->dataVals['transId'] = $this->genTransactionId();
        
        // Set the other config values here
        $this->dataVals['payMethod'] = 'CC';
        $this->dataVals['successUrl'] = self::RESPONSE_PROCESSOR_URL;
        
        // TODO: Consider making the Shipping and card details inputtable
        
        // Build the request parameters
        $requestStr = $this->buildRequestParams();
        
        // Log payment request
        $rsp = $this->logNewPayment();
        // Indicate an error if the payment could not be logged
        if ($rsp === -1)
            throw new ErrorException("New Payment request could not be saved");
        
        // Call the Gateway URL
        $this->callGateway($requestStr);
        
    }

    
    /**
     * CTwoCheckoutMerchant::onReceivePaymentNotification()
     * 
     * Throws exceptions on FATAL errors
     * 
     * @access  public
     * @param   array $_oNoficationArgs
     * @throws  Exception
     * @return  bool
     */
    public function onReceivePaymentNotification($_oNoficationArgs,&$_sErrorMessage)
    {
        // TODO - this function has not been completed
        $isParsed = false;
        // Parse the gateway response values
        //TODO: Consider if the arg of this method should contain the values to parse
        //TODO: Consider if the parse gatewaey method is required
        
        #$isParsed = $this->parseGatewayValues();
        foreach ($keyArr as $key => $val)
        {
            // Add the value if it exists
            if ($this->merchantSettings->valueExists($key)) {
                $this->dataVals[$key] = $val;
                $isParsed = true;
            }                
        }
        // throw an exception if the response could not be parsed
        if (!$isParsed)
            throw new Exception("No response received from gateway");
            
        // Validate that the gateway response
        $isValid = $this->validateSource();
        if (!$isValid)
            throw new Exception('Suspicious Response. Not from Gateway');
            
        // Now update the status of the payment record
        $updateResp = $this->updatePaymentRecord();
        
        // Check the response
        if ($resp === -1)
            throw new Exception('Payment transaction not found');
        if (!$resp)
            throw new Exception('Transaction Info could not be updated');            
        if ($resp === 2)
            throw new Exception('Extra details could not be saved');
            
        return true;
        
    }
    
    /**
     * CTwoCheckoutMerchant::genTransactionId()
     * 
     * Generates a transaction ID and validates it against records in the DB to ensure it is unique
     * Throws an ErrorException if a unique transaction number cannot be generated after the predefined max number of trials
     * 
     * @access  private
     * @throws  ErrorException
     * @return  string
     */
    private function genTransactionId()
    {
        // set the maximum number of recursions
        $maxTrials = self::MAX_GEN_RETRIES;
        $exists = false;
        
        do
        {
            // generate a 8-digit transaction number
            // generate a 8-character code
            $encStr = sha1(uniqid(rand(), true));
            // Used only to ignore the first 2 characters.... No special reason behind that just for a better unique key purpose
            $uniqId = substr($encStr, 2, 8);            
            // Set the new ID as the Transaction number            
            $transId = $uniqId;
            
            // Decrement the max trials to indicate a new trial            
            $maxTrials--;
            $exists = $this->checkTransIdInDB($transId);
            // Check the response
            if ($exists === -1)
                break;
        }
        while ($exists && $maxTrials > 0);
        
        // If the transaction number already exists and max trials already exhausted
        if ($exists && $maxTrials < 1)
        {
            // throw an exception to indicate and error
            throw new ErrorException("Transaction ID could not be generated");
        }
        
        return $transId;
            
    }
    
    /**
     * CTwoCheckoutMerchant::checkTransNumInDB()
     * 
     * Checks if a transaction ID exists in the DB
     * 
     * @access  public
     * @param   integer $transNum - The transaction number to be checked in the DB
     * @return  bool
     */
    private function checkTransIdInDB($transId)
    {
        global $wpdb;
        
        $data = Payment::_get(array('COUNT(Payment_Id) AS thecount'),
                            array($wpdb->prefix."pi_paymentgateway_dict b",
                                $wpdb->prefix."pi_payment.PaymentGatewayDict_Id = "
                                . "b.PaymentGatewayDict_Id AND b.PaymentType='".self::GATEWAY_NAME."' "
                                . "AND ".$wpdb->prefix."pi_payment.TransactionId='".$transId."'"));
        if (Utilities::isValidDbObj($data)) {
            return -1;
        }
        else{
            return ($data[0]['thecount'] > 0);
        }
    }
    
    /**
     * CTwoCheckoutMerchant::buildRequestParams()
     * 
     * Builds the request param strinbg that will be appended to the URL to connect to the gateway
     * 
     * @access  private
     * @param   array $values - An array containg the values to be set for the query string
     * @return  string
     */
    private function buildRequestParams()
    {
        // Declare the local variables
        $reqParams = array();
        $reqdVals = array();
        $reqStr = '';
        
        // Disable all error warnins in this function
        ini_set('display_errors', 0);
        
        // Build all the request params that will be passed to the gateway URL
        // Add the account ID
        $reqParams[$this->merchantSettings->getMerchantId()] = CTwoCheckoutSettings::ACCOUNT_ID;
        // generic details
        $reqParams[$this->merchantSettings->getPaymentReferenceKey()] = $this->checkDataValueKey('cartOrderId');
        $reqParams[$this->merchantSettings->getCustomField('IDType')] = 1;        
        $reqParams[$this->merchantSettings->getCustomField('FixedCart')] = 'Y';
        $reqParams[$this->merchantSettings->getCustomField('DemoMode')] = $this->demoMode ? 'Y' : 'N';
        //Website specific details
        $reqParams[$this->merchantSettings->getPaymentMethodKey()] = $this->checkDataValueKey('payMethod');
        $reqParams[$this->merchantSettings->getCustomField('ResponseUrl')] = $this->checkDataValueKey('successUrl');
        $reqParams[$this->merchantSettings->getCustomField('ReturnUrl')] = $this->checkDataValueKey('failedUrl');
        //Payment specific details
        $reqParams[$this->merchantSettings->getCustomField('MerchantOrderId')] = $this->checkDataValueKey('transId');
        $reqParams[$this->merchantSettings->getCustomField('Quantity')] = $this->checkDataValueKey('quantity');
        $reqParams[$this->merchantSettings->getCartTotalKey()] = $this->checkDataValueKey('totalPrice');
        $reqParams[$this->merchantSettings->getCustomField('Tangible')] = empty($this->dataVals['tangible']) ? 'N' : $this->dataVals['tangible'];
        // Card details go here
        $reqParams[$this->merchantSettings->getCustomField('CardHolderName')] = $this->checkDataValueKey('cardHolder');
        $reqParams[$this->merchantSettings->getCustomField('StreetAddr')] = $this->checkDataValueKey('HolderAddress');
        $reqParams[$this->merchantSettings->getCustomField('City')] = $this->checkDataValueKey('HolderCity');
        $reqParams[$this->merchantSettings->getCustomField('State')] = $this->checkDataValueKey('HolderState');
        $reqParams[$this->merchantSettings->getCustomField('Zip')] = $this->checkDataValueKey('HolderZip');
        $reqParams[$this->merchantSettings->getCustomField('Country')] = $this->checkDataValueKey('HolderCountry');
        $reqParams[$this->merchantSettings->getCustomField('Email')] = $this->checkDataValueKey('HolderEmail');
        $reqParams[$this->merchantSettings->getCustomField('Phone')] = $this->checkDataValueKey('HolderPhone');
        // Shipping and recipientr details        
        $reqParams[$this->merchantSettings->getCustomField('ShipRecipientName')] = $this->checkDataValueKey('RecipientName');
        $reqParams[$this->merchantSettings->getCustomField('ShipStreetAddr')] = $this->checkDataValueKey('RecipientAddr');
        $reqParams[$this->merchantSettings->getCustomField('ShipStreetAddr2')] = $this->checkDataValueKey('RecipientAddr2');
        $reqParams[$this->merchantSettings->getCustomField('ShipCity')] = $this->checkDataValueKey('RecipientCity');
        $reqParams[$this->merchantSettings->getCustomField('ShipState')] = $this->checkDataValueKey('RecipientState');
        $reqParams[$this->merchantSettings->getCustomField('ShipZip')] = $this->checkDataValueKey('RecipientZip');
        $reqParams[$this->merchantSettings->getCustomField('ShipCountry')] = $this->checkDataValueKey('RecipientCountry');
        $reqParams[$this->merchantSettings->getCustomField('ShipCity')] = $this->checkDataValueKey('RecipientCity');
                
        // Restore the default error display settings
        ini_restore('display_errors');
        
        // Check which params were not provided
        foreach ($reqParams as $key => $val)
        {
            // Check if its provided
            if (empty($reqParams[$key]))
            {
                // Check if the param is required
                if (in_array($key, $reqdVals))
                {
                    // throw an exception
                    throw new Exception("Required payment value '$key' was not provided");
                }
                else
                {
                    // just unset it
                    unset($reqParams[$key]);
                }
            }
            else
            {
                // Add it to the request string
                $reqStr .= "$key=".urlencode($val)."&";
            }
        }
        
        // return the request string
        return $reqStr;
        
    }
    
    /**
     * CTwoCheckoutMerchant::logNewPayment()
     * 
     * 
     * Returns the ID of the record in the DB after saving
     * 
     * @access  private
     * @return  integer
     */
    private function logNewPayment()
    {
        // Get the values and save them
        $amount = $this->dataVals['totalPrice'];
        $transId = $this->dataVals['transId'];
        $currency = $this->dataVals['currency'];
        $status = 'PENDING';
        $orderDate = date('Y-m-d H:i:s');
        $paymentRef = $this->dataVals['cartOrderId'];
        #$productId = $params['');
        $unitPrice = $amount;
        
        // Get the gateway ID
        $gatewayId = $this->merchantType->id;
        // Get the currency ID
        $currencyId = PaymentUtilities::getCurrencyIdFromName($currency);
        // Get ther ID of the status
        $statusId = PaymentUtilities::getStatusIdFromName($status);
        $thePayment = new Payment(NULL,array("PaymentGatewayDict_Id" => $gatewayId,
                            "CurrencyDict_Id" => $currencyId,
                            "PaymentRef" => $paymentRef,
                            "TransactionId" => $transId,
                            "UnitPrice" => $unitPrice,
                            "AmountReceived" => $amount,
                            "OrderDate" => $orderDate,
                            "PaymentStatusDict_Id" => $statusId));
        // Save the records to the DB
        $paymentId = $thePayment->getId();
        if($paymentId > 0){
            return $paymentId;
        }
        else{
            return -1;
        }
    }
    
    /**
     * CTwoCheckoutMerchant::checkDataValueKey()
     * 
     * 
     * 
     * @access  private
     * @param   string $key - The key to be checked in the data value array
     * @param   bool $returnVal - Indicates if the value assigned to the key should be returned
     * @return  mixed
     */
    private function checkDataValueKey($key, $returnVal=true)
    {        
        if ($returnVal)
        {
            return (array_key_exists($key, $this->dataVals) ? $this->dataVals[$key] : null);
        }
        else
        {
            return array_key_exists($key, $this->dataVals);
        }
    }
    
    /**
     * CTwoCheckoutMerchant::callGateway()
     * 
     * This function contacts the gateway URL passing the request params
     * 
     * @access  private
     * @param   string $reqString - the request/query string to be appended to the URL
     * @return  void
     */
    private function callGateway($reqString)
    {
        // Get the URL
        $merchantUrl = $this->merchantSettings->getMerchantURL($this->demoMode);
        
        // Append the query string to the URL
        $url = $merchantUrl.'?'.$reqString;
        
        // call the URL with a header redirect
        Utilities::forceRedirect($url);
        #echo $url;exit();
    }
    
    /**
     * CTwoCheckoutMerchant::parseGatewayValues()
     * 
     * @access  private
     * @return  void
     */
    private function parseGatewayValues()
    {
        // Determine the response medium
        $keyArr = null;
        if (!empty($_GET['key']) && !empty($_GET['sid']))
            $keyArr = $_GET;
        elseif (!empty($_POST['key']) && !empty($_POST['sid']))
            $keyArr = $_POST;
        else
            throw new Exception('Unexpected source');
        
        // Get the expected values
        foreach ($keyArr as $key => $val)
        {
            if ($this->merchantSettings->valueExists($key))
                $this->dataVals[$key] = $val;
        }
        
        
    }
    
    /**
     * CTwoCheckoutMerchant::validateSource()
     * 
     * Executes the procedure to validate the response was from the gateway
     * 
     * @access  private
     * @return  bool
     */
    private function validateSource()
    {
        // Get the gateway key
        $gatewayKey = $this->dataVals[$this->merchantSettings->getCustomField('ValidationKey')];
             
        // Set necessary values
        $secretWord = '';
        $vendorNum = $this->dataVals[$this->merchantSettings->getMerchantId()];
        $orderNum = $this->dataVals[$this->merchantSettings->getCustomField('RespOrderNumber')];
        $totalVal = $this->dataVals[$this->merchantSettings->getCustomField('TotalPrice')];
        
        // Validation is as follows : 
        // md5 ( secret word + vendor number + order number + total )
        return (md5($secretWord.$vendorNum.$orderNum.$totalVal) == $gatewayKey);
    }
    
    /**
     * CTwoCheckoutMerchant::updatePaymentRecord()
     * 
     * @return  bool | integer
     */
    private function updatePaymentRecord()
    {
        // Declare the local variables
        $resp = false;
        
        // TODO - determine and set the payment status
        $statusId = NULL;
        
        // Get necessary values
        $transId = $this->dataVals[$this->merchantSettings->getCustomField('MerchantOrderId')];
        $paymentRef = $this->dataVals[$this->merchantSettings->getPaymentReferenceKey()];
        $gatewayId = $this->merchantType->id;
//        $respCode = $params['code'];
//        $respMsg = $params['msg'];
        $payDate = empty($params['paymentDate']) ? date('Y-m-d H:i:s') : $params['paymentDate'];
        $validNum = $this->dataVals[$this->merchantSettings->getCustomField('RespOrderNumber')];
        
        // Get the extra details
        $successPay = false;
        if (!empty($extraParams))
            $successPay = true;   
        
        // Validate that the payment record exists
        $data = Payment::_get(array("Payment_Id"), 
                              "TransactionId='$transId' AND PaymentRef='$paymentRef' "
                              . "AND PaymentGatewayDict_Id='$gatewayId' LIMIT 1");
        // Validate the response
        if(!\Utilities::isValidDbObj($data)){
            return -1;
        }
        // Get the payment ID
        $paymentId = $data[0]->Payment_Id;
        
        // Now update the status of this payment record
        $thePayment = new Payment($paymentId);
        if($thePayment->getId() > 0){
            $success = $thePayment->update(array("IsPaid" => (int)$successPay,
                        "PaidDate" => $payDate,
                        "PaymentStatusDict_Id" => (int)$statusId,
                        "ValidationNumber" => $validNum
                        ));
        }
        else{
            $success = false;
        }
        // Execute the query
        if(!$success){
            return false;
        }        
        // Save the extra params
        $resp = $this->saveExtraValues($paymentId);
        
        if (!$resp)
            return -2;
            
        return true;
    }
    
    /**
     * CTwoCheckoutMerchant::saveExtraValues()
     * 
     * @return  bool
     * @todo    Populate the $exclusions array
     */
    private function saveExtraValues($paymentId)
    {
        // List the params which will already be excluded and as such should be saved
        $exclusions = array();
        $theExtraValue = new PaymentExtraValues(NULL);
        $inSql = "INSERT INTO pi_paymentextravalues (Payment_Id,ExtraValue_Key,ExtraValue_Value) VALUES";
        foreach ($this->dataVals as $key => $val)
        {
            // Skip values whose key should not be saved
            if (in_array($key, $exclusions))
                continue;
            // append the value to the insert query    
            if(!$theExtraValue->create(array("Payment_Id" => $paymentId,
                                         "ExtraValue_Key" => $key,
                                         "ExtraValue_Value" => $val))){
                return false;
            }
        }
        return true;
    }
}
?>
