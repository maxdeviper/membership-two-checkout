<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CRequestMessage
 *
 * @author Femi
 */
class CRequestMessage {

    var $m_sName;
    var $m_sResponse;
    var $m_bIsValid;
    var $m_mpFieldList;

    function CRequestMessage($_sJSON=NULL){
        $this->m_sName = "";
        $this->m_sResponse = "";
        $this->m_bIsValid = false;
        $this->m_mpFieldList = array();
        if(NULL != $_sJSON){
            $this->fromJSON($_sJSON);
        }
    }

    function setName($_sMessage){
	$this->m_sName = $_sMessage;
    }
    function getName(){
	return $this->m_sName;
    }
    function addField($_sFieldName,$_value){
	$this->m_mpFieldList[$_sFieldName] = $_value;
    }
    function removeField($_sFieldName){
	$this->m_mpFieldList[$_sFieldName] = NULL;
    }
    function getField($_sFieldName){
        if(array_key_exists($_sFieldName, $this->m_mpFieldList)){
            return $this->m_mpFieldList[$_sFieldName];
        }
        else{
            return NULL;
        }
    }
    private function parseMap($_aArgs,$_bUseMember=true){
        $mpRetVal = NULL;
        if(MAP_CLASS == $_aArgs[0]){
            $aKeys = $_aArgs[1];
            $aValues = $_aArgs[2];
            $nSize = count($aKeys);
            $mpRetVal = array();
            for($x=0;$x<$nSize;$x++){
                $value = NULL;
                if(is_array($aValues[$x])){
                    if(3 == count($aValues[$x])){
                        $value = $this->parseMap($aValues[$x],false);
                    }
                }
                else{
                    $value = json_decode($aValues[$x],true);
                }
                if(NULL == $value){
                    $value = $aValues[$x];
                }
                if($_bUseMember){
                    $this->m_mpFieldList[$aKeys[$x]] = $value;
                }
                else{
                    $mpRetVal[$aKeys[$x]] = $value;
                }
            }
            $this->m_bIsValid = true;
        }
        else if(LAYOUT_GRID_CLASS == $_aArgs[0]){
            $mpRetVal = array();
            $aKeys = $_aArgs[1];
            $aValues = $_aArgs[2];
            $nSize = count($aKeys);
            for($x=0;$x<$nSize;$x++){
                $value = NULL;
                if(is_array($aValues[$x])){
                    if(3 == count($aValues[$x])){
                        $value = $this->parseMap($aValues[$x],false);
                    }
                }
                else{
                    $value = json_decode($aValues[$x],true);
                }
                if(NULL == $value){
                    $value = $aValues[$x];
                }
                if($_bUseMember){
                    $this->m_mpFieldList[$aKeys[$x]] = $value;
                }
                else{
                    $mpRetVal[$aKeys[$x]] = $value;
                }
            }
            $this->m_bIsValid = true;
        }
        return $mpRetVal;
    }
    function fromJSON($_sVal){
        $_sVal = str_replace('\\',"",$_sVal);
        $aArgs = json_decode($_sVal);
        if(4 == count($aArgs) && REQUEST_MSG_CLASS == $aArgs[0]){
            $this->m_sName = $aArgs[1];
            $this->m_sResponse = $aArgs[2];
            $this->parseMap($aArgs[3]);
        }
    }
    function toJSON($_bNoEcho=false){
        // return the message <bIsNewVersion,nVersion,sXML>
        $aRetVal = array("RequestMessage",
                         $this->m_sName,
                         $this->m_sResponse,
                         array_keys($this->m_mpFieldList),
                         array_values($this->m_mpFieldList));
        $sRetVal = json_encode($aRetVal);
        if(!$_bNoEcho){
            echo $sRetVal;
        }
        else{
            return $sRetVal;
        }
    }
}
?>