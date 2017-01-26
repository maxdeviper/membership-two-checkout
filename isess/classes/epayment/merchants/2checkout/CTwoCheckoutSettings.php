<?php

namespace epayment;
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CTwoCheckoutSettings
 *
 * @author Femi
 */


class CTwoCheckoutSettings implements IMerchantSettings{

    /**
     * The settings for this class instance
     * @access  private
     * @var     array
     * */
    private $m_oKeysToValues;
    
    
    private $merchantUrl;
    
    
    const ACCOUNT_ID = '1531488';
    
    
    const MULTI_PAGE_URL = 'https://www.2checkout.com/checkout/purchase';
    
    
    const SINGLE_PAGE_URL = 'https://www.2checkout.com/checkout/spurchase';

    /**
     * CTwoCheckoutSettings::__construct()
     * 
     * @param   string $paymentPageType - Indicates 
     * @return  void
     */
    function __construct($useSinglePage=true)
    {
        // Determine the type of payment page to use
        if ((bool)$useSinglePage)
            $this->merchantUrl = self::SINGLE_PAGE_URL;
        else
            $this->merchantUrl = self::MULTI_PAGE_URL;
        
        $this->m_oKeysToValues = array();
        // initialise the array
        $this->m_oKeysToValues['IDType'] = "id_type";
        $this->m_oKeysToValues['DemoMode'] = "demo";
        $this->m_oKeysToValues['MerchantOrderId'] = "merchant_order_id";
        $this->m_oKeysToValues['MerchantProductId'] = "merchant_product_id";
        $this->m_oKeysToValues['SkipLanding'] = "skip_landing";
        $this->m_oKeysToValues['Tangible'] = 'c_tangible';
        $this->m_oKeysToValues['ResponseUrl'] = "x_Receipt_Link_URL";
        $this->m_oKeysToValues['ReturnUrl'] = "return_url";
        $this->m_oKeysToValues['CouponNum'] = "coupon";
        $this->m_oKeysToValues['RespOrderNumber'] = "order_number";
        $this->m_oKeysToValues['CartOrderId'] = "cart_order_id";
        $this->m_oKeysToValues['CCProcessed'] = "credit_card_processed";
        $this->m_oKeysToValues['CardHolderName'] = "card_holder_name";
        $this->m_oKeysToValues['ValidationKey'] = "key";
        $this->m_oKeysToValues['AccountId'] = 'sid';
        $this->m_oKeysToValues['ProductId'] = 'product_id';
        $this->m_oKeysToValues['Quantity'] = 'quantity';
        $this->m_oKeysToValues['FixedCart'] = 'fixed';
        $this->m_oKeysToValues['PaymentMethod'] = 'pay_method';
        $this->m_oKeysToValues['StreetAddr'] = 'street_address';
        $this->m_oKeysToValues['StreetAddr2'] = 'street_address2';
        $this->m_oKeysToValues['City'] = 'city';
        $this->m_oKeysToValues['State'] = 'state';
        $this->m_oKeysToValues['Zip'] = 'zip';
        $this->m_oKeysToValues['Country'] = 'country';
        $this->m_oKeysToValues['Email'] = 'email';
        $this->m_oKeysToValues['Phone'] = 'phone';
        $this->m_oKeysToValues['PhoneExt'] = 'phone_extension';
        $this->m_oKeysToValues['ShipRecipientName'] = 'ship_name';
        $this->m_oKeysToValues['ShipStreetAddr'] = 'ship_street_address';
        $this->m_oKeysToValues['ShipStreetAddr2'] = 'ship_street_address2';
        $this->m_oKeysToValues['ShipCity'] = 'ship_city';
        $this->m_oKeysToValues['ShipState'] = 'ship_state';
        $this->m_oKeysToValues['ShipZip'] = 'ship_zip';
        $this->m_oKeysToValues['ShipCountry'] = 'ship_country';
        $this->m_oKeysToValues['TotalPrice'] = 'total';
        $this->m_oKeysToValues['ShipMethod'] = 'ship_method';
        $this->m_oKeysToValues['IpCountry'] = 'ip_country';
        
        // Extra params not really used at this time but may be useful later
        $this->m_oKeysToValues['ProductName'] = 'c_name';
        $this->m_oKeysToValues['ProductUniqId'] = 'c_prod';
        $this->m_oKeysToValues['ProductDesc'] = 'c_description';
        $this->m_oKeysToValues['ProductPrice'] = 'c_price';
    }
    
    /**
     * CTwoCheckoutSettings::getCustomField()
     * 
     * Fetches the value of the passed in key in the settings array
     * 
     * @access  public
     * @param   string $_sFieldName
     * @return  string
     */
    public function getCustomField($_sFieldName)
    {
        if(array_key_exists($_sFieldName, $this->m_oKeysToValues)){
            return $this->m_oKeysToValues[$_sFieldName];
        }
        else{
            return null;
        }
    }

    /**
     * CTwoCheckoutSettings::getMerchantURL()
     * 
     * Returns the URL of the merchant gateway. 
     * If $_bDemoUrl is set to TRUE, the demo url will be returned else the live URL will be returned 
     * 
     * @access  public
     * @param   bool $_bDemoUrl - Indicates if the demo url of the payment gateway should be returned instead of the live URL
     * @return  string
     */
    function getMerchantURL($_bDemoUrl){
        // If demo, return demo URL else return live URL
        if($_bDemoUrl){
            return $this->merchantUrl;
        }
        else{
            return $this->merchantUrl;
        }
    }
    
    /**
     * CTwoCheckoutSettings::getMerchantId()
     * 
     * Returns the key for the Merchant ID (In the case of this Gateway Account ID)
     * 
     * @access  public
     * @return  string
     */
    public function getMerchantId(){
        return $this->m_oKeysToValues['AccountId'];
    }

    
    /**
     * CTwoCheckoutSettings::getCartTotalKey()
     * 
     * Returns the key for the cart total that will contain the total price
     * 
     * @access  public
     * @return  string
     */
    public function getCartTotalKey(){
        return $this->m_oKeysToValues['TotalPrice'];
    }
    
    /**
     * CTwoCheckoutSettings::getPaymentReferenceKey()
     * 
     * Returns the key that will reference the payment reference
     * 
     * @access  public
     * @return  string
     */
    public function getPaymentReferenceKey(){
        return $this->m_oKeysToValues['CartOrderId'];
    }
    
    /**
     * CTwoCheckoutSettings::getProductIdKey()
     * 
     * Returns the key that will reference the product ID
     * 
     * @access  public
     * @return  string
     */
    public function getProductIdKey(){
        return $this->m_oKeysToValues['ProductId'];
    }
    
    /**
     * CTwoCheckoutSettings::getProductNameKey()
     * 
     * Returns the key that will reference the product name (Not really used at this moment)
     * 
     * @access  public
     * @return  string
     */
    public function getProductNameKey(){
        return $this->m_oKeysToValues['ProductName'];
    }
    
    public function getProductDescriptionKey(){
        return $this->m_oKeysToValues['ProductDesc'];
    }
    
    public function getProductPriceKey(){
        return $this->m_oKeysToValues['ProductPrice'];
    }
    
    public function getlanguageKey(){
        return 'lang';
    }
    
    /**
     * CTwoCheckoutSettings::getPaymentMethodKey()
     * 
     * Returns the key that will reference the Payment method
     * 
     * @access  public
     * @return  string  
     */
    public function getPaymentMethodKey(){
        return $this->m_oKeysToValues['PaymentMethod'];
    }
    
    /**
     * CTwoCheckoutSettings::valueExists()
     * 
     * @param   string $value - The value to be searched for in the array
     * @param   bool $returnKey - Indicates if the key for the value should be returned or not
     * @return  string | bool
     */
    public function valueExists($value, $returnKey=false)
    {
        if (in_array($value, $this->m_oKeysToValues))
        {
            return ($returnKey ? array_keys($this->m_oKeysToValues, $value) : true);
        }
        else
            return false;
    }

}
?>
