<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * this is a container class that returns the keys and values for the various fields required to make
 * payment on the supported gateways
 *
 * @author Femi
 */
interface IMerchantSettings {
    //put your code here
    function getMerchantId();
    function getMerchantURL($_bDemoUrl);

    // payment references
    function getCartTotalKey();
    function getPaymentReferenceKey();
    function getProductIdKey();
    function getProductNameKey();
    function getProductDescriptionKey();
    function getProductPriceKey();
    function getlanguageKey();
    function getPaymentMethodKey();
    //
    function getCustomField($_sFieldName);
}
?>
