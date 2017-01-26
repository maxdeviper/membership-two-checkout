<?php

namespace epayment;

/**
 * Description of CItem
 *
 * @author Femi
 */
class CItem {

    var $m_oCustomFieldsToValues;
    var $m_sRef;
    var $m_nQty;
    var $m_sName;
    var $m_sDesc;
    var $m_nUnitPrice;
    
    function CItem($_sReference='',$_sName='',$_nQuantity=0,$_nUnitPrice=0,$_sDescription='',$_oCustomField=null){
        $this->m_sRef = $_sReference;
        $this->m_sName = $_sName;
        $this->m_nQty = $_nQuantity;
        $this->m_nUnitPrice = $_nUnitPrice;
        $this->m_sDesc = $_sDescription;
        if(null != $_oCustomField){
            $this->m_oCustomFieldsToValues = $_oCustomField;
        }
        else{
            $this->m_oCustomFieldsToValues = array();
        }
    }
    function setReference($_sReference){
        $this->m_sRef = $_sReference;
    }
    function setName($_sName){
        $this->m_sName = $_sName;
    }
    function setDescription($_sDescription){
        $this->m_sDesc = $_sDescription;
    }
    function setQuantity($_nQuantity){
        $this->m_nQty = $_nQuantity;
    }
    function setUnitPrice($_nUnitPrice){
        $this->m_nUnitPrice = $_nUnitPrice;
    }
    function setCustomField($_sField,$_value){
        $this->m_oCustomFieldsToValues[$_sField] = $_value;
    }
    function update($_sReference='',$_sName='',$_nQuantity=0,$_nUnitPrice=0,$_sDescription='',$_oCustomField=null){
        $this->m_sRef = $_sReference;
        $this->m_sName = $_sName;
        $this->m_nQty = $_nQuantity;
        $this->m_nUnitPrice = $_nUnitPrice;
        $this->m_sDesc = $_sDescription;
        if(null != $_oCustomField){
            $this->m_oCustomFieldsToValues = $_oCustomField;
        }
        else{
            $this->m_oCustomFieldsToValues = array();
        }
    }
    function exportItem(){
        $oTheObject = array($this->m_sRef,
                            $this->m_sName,
                            $this->m_nQty,
                            $this->m_nUnitPrice,
                            $this->m_sDesc,
                            $this->m_oCustomFieldsToValues);
        return $oTheObject;
    }
    
    function getName(){
        return $this->m_sName;
    }
    function getDescription(){
        return $this->m_sDesc;
    }
    function getReference(){
        return $this->m_sRef;
    }
    function getQuantity(){
        return $this->m_nQty;
    }
    function getUnitPrice(){
        return $this->m_nUnitPrice;
    }
    function getCustomField($_sFieldName){
        return $this->m_oCustomFieldsToValues[$_sFieldName];
    }
}
?>
