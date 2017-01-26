<?php

namespace epayment;

/**
 * @author Femi
 * @version 1.0
 * @created 31-May-2011 17:48:01
 */
class CCart
{
    var $m_oCartList;
    var $m_merchantGateway;
    var $m_nOwnerId;
    var $m_sPaymentReference;
    var $m_sCurrency;

    function CCart($_nUserId,$_sCurrency='Naira',$_sPaymentRef=''){
        $this->m_nOwnerId = $_nUserId;
        $this->m_oCartList = array();
        $this->m_sPaymentReference = $_sPaymentRef;
        $this->m_sCurrency = $_sCurrency;
        if(strlen($this->m_sPaymentReference) < 10){
            $this->createPaymentReference();
        }
    }

    function merge($_theCart){
        if(NULL != $_theCart){
            $aItemList = $_theCart->getAllItem();
            for($x=0;$x<count($aItemList);$x++){
                $nextCartItem = $aItemList[$x];
                $this->m_oCartList[$nextCartItem->getReference()] = $nextCartItem;
            }
        }
    }
    function size(){
        $aKeys = array_keys($this->m_oCartList);
        return count($aKeys);
    }
    function createPaymentReference($_nLength=10){
        //generate a random id encrypt it and store it in $rnd_id
        $rnd_id = crypt(uniqid(rand(),1));
        //to remove any slashes that might have come
        $rnd_id = strip_tags(stripslashes($rnd_id));
        //Removing any . or / and reversing the string
        $rnd_id = str_replace(".","",$rnd_id);
        $rnd_id = strrev(str_replace("/","",$rnd_id));
        //finally I take the first 10 characters from the $rnd_id
        $rnd_id = substr($rnd_id,0,$_nLength);
        $this->m_sPaymentReference = $rnd_id;
    }

    function addItemToCart($_sItemRef,$_theItem){
        $this->m_oCartList[$_sItemRef] = $_theItem;
    }

    function hasItem($_sRef){
        if(array_key_exists($_sRef, $this->m_oCartList)){
            return true;
        }
        else{
            return false;
        }
    }
    function getItem($_sRef){
        if(array_key_exists($_sRef, $this->m_oCartList)){
            return $this->m_oCartList[$_sRef];
        }
        return NULL;
    }
    function getAllReferences(){
        if(NULL != $this->m_oCartList){
            return array_keys($this->m_oCartList);
        }
        return NULL;
    }
    
    function editCartItem($_sItemRef,$_oArgList){
        $theItem = $this->m_oCartList[$_sItemRef];
        if(null != $theItem && isset($theItem)){
            $theItem->update($_oArgList);
        }
    }

    function removeItemFromCart($_sItemRef){
        if(array_key_exists($_sItemRef,$this->m_oCartList)){
            $this->m_oCartList[$_sItemRef] = null;
            array_splice($this->m_oCartList,$_sItemRef,1);
        }
    }

    function payNow(){
        $oRetVal = NULL;
        if(NULL != $this->m_merchantGateway){
            $oRetVal =  $this->m_merchantGateway->makePayment($this->m_sPaymentReference,
                                                              $this->m_oCartList,
                                                              "Naira");
        }
        return $oRetVal;
    }
    function cancelPayment(){
        if(NULL != $this->m_merchantGateway){
            $this->m_merchantGateway->cancelPayment($this->m_nOwnerId,$this->m_oCartList);
        }
    }
    function deSerialize($_sSerializedString){
        $this->m_oCartList= array();
        $aCartItems = unserialize($_sSerializedString);
        $nSize = count($aCartItems);
        for($x=0;$x<$nSize;$x++){
            $nextCartItem = $aCartItems[$x];
            $nextItem = NULL;
            if(6 == count($nextCartItem)){
                $nextItem = new CItem($nextCartItem[0],
                                      $nextCartItem[1],
                                      $nextCartItem[2],
                                      $nextCartItem[3],
                                      $nextCartItem[4],
                                      $nextCartItem[5]);
            }
            else if(5 == count($nextCartItem)){
                $nextItem = new CItem($nextCartItem[0],
                                      $nextCartItem[1],
                                      $nextCartItem[2],
                                      $nextCartItem[3],
                                      $nextCartItem[4]);
            }
            else if(4 == count($nextCartItem)){
                $nextItem = new CItem($nextCartItem[0],
                                      $nextCartItem[1],
                                      $nextCartItem[2],
                                      $nextCartItem[3]);
            }
            else if(3 == count($nextCartItem)){
                $nextItem = new CItem($nextCartItem[0],
                                      $nextCartItem[1],
                                      $nextCartItem[2]);
            }
            if(NULL != $nextItem){
                $this->m_oCartList[$nextItem->getReference()] = $nextItem;
            }
        }
    }
    function serialize($_bSerializeToString=true){
        $aRetVal = array();
        $aKeys = array_keys($this->m_oCartList);
        $nSize = count($aKeys);
        for($x=0;$x<$nSize;$x++){
            $nextItem = $this->m_oCartList[$aKeys[$x]];
            if(NULL != $nextItem){
                array_push($aRetVal,$nextItem->exportItem());
            }
        }
        if($_bSerializeToString){
            if(0 == $aRetVal){
                return "";
            }
            else{
                return serialize($aRetVal);
            }
        }
        else{
            return $aRetVal;
        }
    }
    function clearCart(){
        $aKeys = array_keys($this->m_oCartList);
        $nSize = count($aKeys);
        $aRetVal = array();
        for($x=0;$x<$nSize;$x++){
            $this->removeItemFromCart($aKeys[$x]);
        }
    }
    function getAllItem(){
        $aRetVal = array();
        if(count($this->m_oCartList) > 0){
            $aKeys = array_keys($this->m_oCartList);
            $nSize = count($aKeys);
            for($x=0;$x<$nSize;$x++){
                array_push($aRetVal, $this->m_oCartList[$aKeys[$x]]);
            }
        }
        return $aRetVal;
    }
    function serializeToDb(){

    }
    function serializeFromDb(){

    }
    
    // use the PaymentGateway_Dict Class for this parameter
    function setMerchant($_nMerchantId){
        if(PaymentGateway_Dict::BANK == $_nMerchantId){
            $this->m_merchantGateway = new CPayAtBank();
        }
        else if(PaymentGateway_Dict::ON_DELIVERY == $_nMerchantId){
            $this->m_merchantGateway = new CPayOnDelivery();
        }
        else if(PaymentGateway_Dict::INTERSWITCH == $_nMerchantId){
            $this->m_merchantGateway = new CInterswitch();
        }
        else if(PaymentGateway_Dict::PAY4ME == $_nMerchantId){
            $this->m_merchantGateway = new CPay4mePayment(true);
        }
    }
}
?>