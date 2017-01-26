<?php
/**
 *
 * @author Femi
 */
abstract class ICRUDAPI {
    /*
     *  TODO IN IMPLEMENTING CLASS - create and set set the proper table names and primary keys;
    const DB_NAME = 'mm_images';
    const DB_PRIMARY_KEY_ID = "Image_Id";
     */
    /*
     *  TODO - add one or more database fields in the format: const FD_<fieldname> = "<database fieldname>"
     *  e.g: const FD_Title = "Title";
     */

    /*
     * TODO - member fieldnames to Database fieldname
     * e.g: 'm_sTitle' => 'Title'
     */
    public  $m_dbCols = array();
    
    /*
     * TODO - member fieldnames to CRUDMgr fieldtypes
     * e.g: 'm_sTitle' => CRUDMgr::kn_IS_STRING
     */
    public  $m_dbColsToTypes = array();

    /*
     * TODO - add the member fields as 'protected'
     */

    /*
     * Primary Id key
     */
    protected $m_nId;
    /**
     * CRUDMgr member
     */
    public  $m_dbMgr;
    /* TODO - ensure your constructor has the stated methods in the stated order (first and last)
	*/
    /**
     *
     * ensure all variables preceed the '_bCreate'
     * @param _nId
     * @param _bCreate
    function CTemplateClass($_nId=-1,$_bCreate=false){
        // initialise all members
        $this->m_nId = $_nId;
        if(NULL == $this->m_dbMgr){
            $this->m_dbMgr = new CRUDMgr($this);
        }
        $this->m_dbMgr->registerFieldlist($this->m_dbCols,
                                          $this->m_dbColsToTypes,
                                          CTemplateClass::DB_NAME,
                                          CTemplateClass::DB_PRIMARY_KEY_ID);
        if($_bCreate){
            $this->m_dbMgr->create();
        }
        else if($this->m_nId > -1){
            $this->m_dbMgr->fromDb("",$this->m_nId);
        }
        // TODO - Handle any other initialisation scenaraios here
    }
     */
	
    /*
     * Assign all member getters and setters
     */
    public function create($_oFieldsToValues,$_aExcludeFields=NULL){
        if(NULL != $_oFieldsToValues){
            // formats any keys that require it
            $_oFieldsToValues = $this->formatKeys($_oFieldsToValues);
            $oFieldsToTypes = $this->getFormFieldsToTypes();
            $aFields = array_keys($this->m_dbCols);
            foreach($aFields AS $nextField){
                if(isset($_oFieldsToValues[$nextField])){
                    if($oFieldsToTypes[$nextField] == CViewItemType::BASE64_STRING ||
                       $oFieldsToTypes[$nextField] == CViewItemType::TAGBOX ||
                       $oFieldsToTypes[$nextField] == CViewItemType::MULTI_COMBO ||
                       $oFieldsToTypes[$nextField] == CViewItemType::RTF_DESCRIPTION){
                        $this->__set($nextField, Utilities::decodeId($_oFieldsToValues[$nextField],true));
                    }
                    else{
                        $this->__set($nextField, $_oFieldsToValues[$nextField]);
                    }
                }
            }
        }
        if(NULL == $_aExcludeFields){
            $_aExcludeFields = array("m_nId");
        }
        else{
            array_push($_aExcludeFields,"m_nId");
        }
        array_unique($_aExcludeFields);
        $bSuccess = $this->m_dbMgr->create($_aExcludeFields);
        if($bSuccess){
            $this->m_nId = $this->m_dbMgr->m_nId;
            return array(true,$this->m_nId);
        }
        else{
            return array(false,$this->m_dbMgr->getLastError());
        }
    }    
    public function update($_oFieldsToValues, $_nItemId,$_aExcludeFields=NULL){
        if(NULL != $_oFieldsToValues && NULL != $_nItemId){
            if($_nItemId > 0){
                $this->m_nId = $_nItemId;
            }
            $_oFieldsToValues = $this->formatKeys($_oFieldsToValues);
            $oFieldsToTypes = $this->getFormFieldsToTypes();
            $aKeys = array_keys($this->m_dbCols);
            foreach($aKeys AS $nextField){
                try{
                    if(isset($_oFieldsToValues[$this->m_dbCols[$nextField]])){
                        if(isset($oFieldsToTypes[$nextField])){
                            if($oFieldsToTypes[$nextField] == CViewItemType::BASE64_STRING ||
                               $oFieldsToTypes[$nextField] == CViewItemType::TAGBOX ||
                               $oFieldsToTypes[$nextField] == CViewItemType::MULTI_COMBO ||
                               $oFieldsToTypes[$nextField] == CViewItemType::RTF_DESCRIPTION){
                                $this->__set($nextField, Utilities::decodeId($_oFieldsToValues[$this->m_dbCols[$nextField]],true));
                            }
                            else{
                                $this->__set($nextField, $_oFieldsToValues[$this->m_dbCols[$nextField]]);
                            }
                        }
                    }
                    else if(isset($_oFieldsToValues[$nextField])){
                        if(isset($oFieldsToTypes[$nextField])){
                            if($oFieldsToTypes[$nextField] == CViewItemType::BASE64_STRING ||
                               $oFieldsToTypes[$nextField] == CViewItemType::TAGBOX ||
                               $oFieldsToTypes[$nextField] == CViewItemType::MULTI_COMBO ||
                               $oFieldsToTypes[$nextField] == CViewItemType::RTF_DESCRIPTION){
                                $this->__set($nextField, Utilities::decodeId($_oFieldsToValues[$nextField],true));
                            }
                            else{
                                $this->__set($nextField, $_oFieldsToValues[$nextField]);
                            }
                        }
                    }
                }
                catch(Exception $ex){
                    // todo - handle the exception
                }
                catch(InvalidArgumentException $Iex){
                    // todo - handle the exception
                }
            }
        }
        if(NULL == $_aExcludeFields){
            $_aExcludeFields = array("m_nId");
        }
        else{
            array_push($_aExcludeFields,"m_nId");
        }
        array_unique($_aExcludeFields);
        return $this->m_dbMgr->toDb("",$_aExcludeFields);
    }
    public function delete($_nId = -1){
        if($_nId > -1){
            $this->m_nId = $_nId;
            $this->m_dbMgr->m_nId = $_nId;
        }
        return $this->m_dbMgr->delete();
    }
    public function fromDb($aIdList=NULL,$_nIndex=0,$_sCondition="",$_nMaxRows=10,$_sPrimaryDbKey=""){
        $aRetVal = NULL;
        if(NULL != $aIdList){
            $nSize = count($aIdList);
            for($x=0;$x<$nSize;$x++){
                $sIdList = $_sPrimaryDbKey."=".$aIdList[$x];
                if($x+1 < $nSize){
                    $sIdList .= " AND ";
                }
            }
            $aRetVal = $this->m_dbMgr->fromDb($sIdList.$_sCondition,-1,false,false,$_nIndex,$_nMaxRows);            
        }
        else{
            $aRetVal = $this->m_dbMgr->fromDb($_sCondition,-1,false,false,$_nIndex,$_nMaxRows);            
        }
        return $aRetVal;
    }
    abstract protected function formatKeys($_oFieldsToValues);
    /**
     * Assign all member getters and setters
     */
    public function getId()
    {
        return $this->m_nId;
    }

    /**
     * @param $_nId
     * 
     */
    public function setId($_nId)
    {
        $this->m_nId = $_nId;
    }

    /**
     * initialises the class
     * 
     * @param _nId
     */
    public function initDataSet($_nId,$_bForceInit=false)
    {
        if($_nId > 0 || $_bForceInit){
            $this->m_nId = $_nId;
            if($this->m_nId > 0){
                $this->m_dbMgr->fromDb("",$this->m_nId);
            }
            else{
                $this->m_dbMgr->fromDb("");
            }
        }
    }

    /**
     * 
     * @param _sName
     */
    public function __get($_sName)
    {
        // Ensure the property exists
        if (!property_exists($this, $_sName)){
            throw new InvalidArgumentException("Property '".__CLASS__."::$_sName' does not exist");
        }
        return $this->$_sName;
    }

    /**
     * 
     * @param _sName
     * @param _value
     */
    public function __set($_sName, $_value)
    {
    // Ensure the property exists
    if (!property_exists($this, $_sName)){
        throw new InvalidArgumentException("Property '".__CLASS__."::$_sName' does not exist");
    }
    else
        $this->$_sName = $_value;
    }

    function getLastError(){
        return $this->m_dbMgr->getLastError();
    }
    function formatFieldNames($_oFieldList) {
        $oRetValue = array();
        $aFieldKeys = array_keys($_oFieldList);
        $aKeys = array_keys($this->m_dbCols);
        for ($x = 0; $x < count($aKeys); $x++) {
            $nextField = $this->m_dbCols[$aKeys[$x]];
            if (isset($_oFieldList[$nextField])) {
                $oRetValue[$aKeys[$x]] = $_oFieldList[$nextField];
            }
        }
        // set the PrimaryKey
        $sClass = get_class($this);
        $dbField = $sClass::DB_PRIMARY_KEY_ID;
        $Id = $_oFieldList[$dbField];
        $oRetValue["m_nId"] = Utilities::encodeId($Id);
        return $oRetValue;
    }
    function extractFieldList(){
        $aMembers = array_keys($this->m_dbCols);
        $nSize = count($aMembers);
        $oFieldlist = array();
        while($nSize--){
            $oFieldlist[$aMembers[$nSize]] = $this->__get($aMembers[$nSize]);
        }
        $oFieldlist["m_nId"] = Utilities::encodeId($this->getId());
        return $oFieldlist;
    }        
    function extractFields(){
        $aFields = array_keys($this->m_dbCols);
        return $aFields;
    }
}
?>
