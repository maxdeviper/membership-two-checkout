<?php
//    require_once("../../../startup_inc.php");
    require_once(DE_APP_CLASSES_PATH_ISESS."mail/htmlMimeMail.php");

/**
 * Utilities
 * 
 * This class contains a collection of utility functions that can be used to perform common actions
 * The class mimics a static class that is available in .NET
 * 
 * @copyright   2010
 * @version     1.1
 * @access      public
 */
class Utilities
{
    
    /**
     * Format : 2010-12-22
     * */
    const DATEFORM_DATEONLY = 'Y-m-d';
    
    /**
     * Format : 22-12-2010
     * */
    const DATEFORM_CALENDAR_DATE = 'd-m-Y';
    
    /**
     * Format : 2010-12-22 13:13:13
     * */
    const DATEFORM_DBDATE = 'Y-m-d H:i:s';
    
    /**
     * Format : 22/December/2010
     * */
    const DATEFORM_DATEDISPLAY = 'j/F/Y';
    
    /**
     * Format : 22/Dec/2010 13:13:13
     * */
    const DATEFORM_DTDISPLAY = 'j/M/Y H:i:s';
    
    /**
     * Format : Saturday, December 22, 2010
     * 
     * */
    const DATEFORM_DATEFULL = 'l, F j, Y';
    
    /**
     * Format : 22nd Dec, 2010
     * */
    const DATEFORM_FULLABRIV = 'jS M, Y';
    
    /**
     * Javascript Date time format
     * Format : 10, 01, 01, 13, 05, 05
     * */
    const DATEFORM_JSDATETIME = 'y, m, d, H, i, s';
    
    /**
     * Javascript Date format
     * Format : 10, 01, 01
     * */
    const DATEFORM_JSDATE = 'y, m, d';
    
    /**
     * 
     * */
    const DEFAULT_PHONE = '08011111111';
    
    /**
     * This is the default time for null time values
     * */
    const EPOCH_TIME = '1970-01-01 00:00:00';
    
    /**
     * This array contains the genders that are available for selection. 
     * The keys are mapped again the constants that can be used in the DB
     * @access  public
     * @static
     * @var     array
     * */
    public static $sexs = array(MALE => 'Male', FEMALE => 'Female');

    public static function createRandomId($_nLength=10){
        //generate a random id encrypt it and store it in $rnd_id
        $rnd_id = crypt(uniqid(rand(),1));
        //to remove any slashes that might have come
        $rnd_id = strip_tags(stripslashes($rnd_id));
        //Removing any . or / and reversing the string
        $rnd_id = str_replace(".","",$rnd_id);
        $rnd_id = strrev(str_replace("/","",$rnd_id));
        //finally I take the first 10 characters from the $rnd_id
        $rnd_id = substr($rnd_id,0,$_nLength);
        return $rnd_id;
    }
    
    /**
     * Utilities::formatDateTime()
     * 
     * This method formats a passed in date-time string to a requested format
     * The format must be in one of the predefined formats else epoch time '1970-01-01 00:00:00' will be returned
     * 
     * @access  public
     * @param   string $dateStr - The date string
     * @param   string $format - The requested output format
     * @return  string - The date string in the new format
     */
    public static function formatDateTime($dateStr, $format=self::DATEFORM_DBDATE)
    {
        try
        {
            if (empty($dateStr))
                throw new Exception('Empty Date String');
            // create a date object from the date string
            $dateObj = new DateTime($dateStr);
            // Set the date to the requested format
            return $dateObj->format($format);
        }
        catch (Exception $ex)
        {
            return $ex->getMessage();
        }
    }

    public static function isValidDbObj($_oDbObj) {
        if(NULL == $_oDbObj){
            return false;
        }
        else if(is_array($_oDbObj)){
            if(is_string($_oDbObj[0])){
                if ($_oDbObj[0] == "FALSE") {
                    return false;
                }
                else{
                    return true;
                }
            }
            else if(is_bool($_oDbObj[0])){
                if (!$_oDbObj[0]) {
                    return false;
                }
                else{
                    return true;
                }
            }
            else{
                return true;
            }
        }
        else{
            if(is_string($_oDbObj)){
                if ($_oDbObj == "FALSE") {
                    return false;
                }
                else{
                    return true;
                }
            }
            else if(is_bool($_oDbObj)){
                if (!$_oDbObj) {
                    return false;
                }
                else{
                    return true;
                }
            }
            else{
                return true;
            }
        }
    }


    /**
     * Utilities::dispObjProp()
     * 
     * This function attempts to retrieve a property of an object
     * 
     * @access  public
     * @param   object $object
     * @param   string $property - The name of the property to be retrieved
     * @return  mixed
     */
    public static function dispObjProp($object, $property)
    {
        try
        {
            if (!is_object($object))
                throw new Exception ('Not an object');
                
            #if (!$object->propertyExists($property))
            if (!property_exists($object, $property))
                throw new Exception ('Property does not exist');
                
            return $object->$property;
        }
        catch (Exception $ex)
        {
            //return $ex->getMessage();
            return '';
        }
    }

    public static function sendMail($_To,$_Title,$_Content,$_Sender=ADMINISTRATOR_EMAIL,$_sSenderDisplayName=''){
        // send the mail
        $result = "";
        $mail = new htmlMimeMail();
        $mail->setHtml($_Content);
        $mail->setReturnPath($_Sender);
        $mail->setFrom($_sSenderDisplayName.' <'.$_Sender.'>');
        $mail->setSubject($_Title);
        if(is_array($_To)){
            $result = $mail->send($_To);
        }
        else{
            $result = $mail->send(array($_To));
        }
        return $result;
    }

    /**
     * Utilities::callObjMtd()
     * 
     * This method calls the method of a object or class
     * 
     * @access  public
     * @static
     * @param   object $object - The object of a class
     * @param   string $method - The name of the method to be called
     * @param   array $params - The array of params, if any, to be passed to the method when called 
     * @return  mixed
     */
    public static function callObjMtd($object, $method, array $params=array())
    {
        $callback = array($object, $method);
        
        try
        {
            if (!is_object($object))
                throw new Exception ('Not an object');
                
            #if (!$object->methodExists($method))
            if (!method_exists($object, $method))
                throw new Exception ('Method does not exist');
            
            if (!is_callable($callback))
                throw new Exception ('Method is not callable');
            
            // Determine the function to use
            if (empty($params))
                return call_user_func($callback);
            else {
                return call_user_func_array($callback, $params);  
            }
                
        }
        catch (Exception $ex)
        {
            //return $ex->getMessage();
            return '';
        }
    }
    
    /**
     * Utilities::validateReqdField()
     * 
     * This method validates required fields
     * 
     * @access  public
     * @static
     * @param   array $dataArr - The array containing the values retrieved from the form 
     * @param   array $fldNames - An associative array containing the fields to be validated in the dataArr
     * @return  mixed - 
     */
    public static function validateReqdField(array $dataArr, array $fldNames)
    {
        // the array to hold the fields that are empty
        $emptyFlds = array();
        // Get the array keys
        $flds = array_keys($fldNames);
        // Check all the keys
        foreach ($flds as $aFld)
        {
            // validate the field
            if (!array_key_exists($aFld, $dataArr) || empty($dataArr[$aFld]))
            {
                // add the field to the list of empty fields
                $emptyFlds[] = $fldNames[$aFld];
            }
        }
        // check the empty fields array
        if (empty($emptyFlds))
            return true;
            
        return $emptyFlds;
        
    }
    
    /**
     * Utilities::validateSex()
     * 
     * This method validates a sex input. It throws an InvalidParamTypeException if the input is not a correct value
     * 
     * @access  public
     * @static
     * @param   string $sex - The sex provided
     * @return  bool - TRUE if the sex is a valid value
     */
    public static function validateSex($sex)
    {
        if (!array_key_exists($sex, self::$sexs))
        {
            #throw new Exception("An unknown value was provided for the sex");
            #throw new InvalidParamTypeException('sex', gettype($sex), 'Utitlities Constant', "An unknown value was provided for the sex");
            return false;
        }
        
        return true;
    }
    
    /**
     * Utilities::validateDate()
     * 
     * This method attempts to validate a date string
     * It returns TRUE if the date string is valid else it returns FALSE
     * This method requires DateTime class of PHP 5.2 and above (5.3 is recommended)
     * 
     * @access  public
     * @static
     * @param   string $dateStr - The date string to be validated
     * @param   bool $return - If the date string generated should be returned
     * @param   bool $isDT - This indicates if the date should be returned as a datetime or only date
     * @return  bool - TRUE if the date string is valid else FALSE
     */
    public static function validateDate($dateStr, $return=false, $isDT=false)
    {
        try
        {
            if (empty($dateStr))
                throw new Exception('Empty Date String');
            // attempt to create the date from the date string
            $dt = new DateTime($dateStr);
            // Decide if the date string should be returned
            if ($return)
            {
                $format = $isDT ? 'Y-m-d H:i:s' : 'Y-m-d';
                $str = $dt->format($format);
                return $str;
            }
                        
            // return TRUE at this point if no exception is thrown
            return true;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }
    
    /**
     * Utilities::validateTime()
     * 
     * Validates a Time string and returns TRUE if valid else FALSE
     * If the second param is TRUE, then the time is assumed to be in 12hr-format containing AM/PM
     * 
     * @access  public
     * @static
     * @param   string $timeStr - The time string to be validated
     * @param   bool $is24hr - Indicates if the time string is in 24hr format or 12hr format
     * @return  bool
     */
    public static function validateTime($timeStr, $is24hr=true)
    {
        if ($is24hr)
        {
            return preg_match("/([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}/", $timeStr);
        }
        else
        {            
            return preg_match("/(0[1-9]|1[0-2]):[0-5][0-9]:[0-5][0-9]\s+([ap]m|[AP]M)/", $timeStr);
        }
    }
    
    /**
     * Utilities::validateEmail()
     * 
     * This function validates a supplied email address using the filter function
     * 
     * @access  public
     * @static
     * @param   string $email - The email address to be validated
     * @return  bool
     */
    public static function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            return false;
            
        return true;
    }
    
    /**
     * Utilities::validateURL()
     * 
     * This method validates a supplied URL using the filter function
     * 
     * @access  public
     * @static
     * @param   string $url - The URL to be validated
     * @return  bool
     */
    public static function validateURL($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false)
            return false;
            
        return true;
    }
    
    /**
     * Utilities::validateEmailRegEx()
     * 
     * This function validates a supplied email address using regular expression
     *  
     * @access  public
     * @static
     * @param   string $email - The email address to be validated
     * @return  bool
     */
    public static function validateEmailRegEx($email)
    {
        $pattern = '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i';
        //return ((bool) eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email));
        return ((bool) preg_match($pattern, $email));
    }
    
    /**
     * Utilities::validateWebsite()
     * 
     * This function validates a supplied website url using regular expression
     *  
     * @access  public
     * @static
     * @param   string $website - The website address to be validated
     * @return  bool
     */
    public static function validateWebsite($website)
    {
        $pattern = '/^(((h|H?)(t|T?)(t|T?)(p|P?)(s|S?))\:\/\/)?(www.|[a-zA-Z0-9].)[a-zA-Z0-9\-\.]+\.[a-zA-Z]*$/i';
        
        return ((bool) preg_match($pattern, $website));
    }
    
    /**
     * Utilities::validatePhone()
     * 
     * This method validates the phone number passed in as a param.
     * The method returns the match found else it returns FALSE
     * 
     * @access  public
     * @static
     * @param   string $phone
     * @return  string - The valid phone number format found. 
     */
    public static function validatePhone($phone)
    {
        // format for GSM number
        $mob_pattern = '/^(0|234|\+234)[78][01]\d{8}$/';
        // Format for landlines
        $land_pattern = '/^(0|234|\+234)\d{7,8}$/';
        // dummy, if matches are required
        $matches = '';
        
        if (preg_match($mob_pattern, $phone, $matches))
        	return $matches[0]; //echo 'Mobile found';
        elseif (preg_match($land_pattern, $phone, $matches))
        	return $matches[0]; //echo 'Land line found';
        else
        	return false; //echo 'Match not found';
    }
    
    /**
     * Utilities::validateIP()
     * 
     * This method attempts to validate a provided IP address
     * It returns TRUE if the address is valid else FALSE
     * 
     * @access  public
     * @static
     * @param   string $ipAddr - The IP address to be validated
     * @return  bool
     */
    public static function validateIP($ipAddr, $return=false)
    {
        // Convert the IP address string to integer format
        $long = ip2long($ipAddr);
        
        // If return is not required, then only validate the IP address 
        if (!$return)
            return (!($long == -1 || $long === FALSE));
        
        // Return is required.... get a valid IP address   
        $validIp = long2ip($long);
        // return the valid IP address
        return $validIp;
    }
    
    /**
     * Utilities::getClientIp()
     * 
     * This method fetchss the IP address of the client user of the application
     * It fetches the IP of the user even if the user is behind a proxy server 
     * 
     * @access  public
     * @static
     * @return  string
     */
    public static function getClientIp()
    {
        // Decide if the client is using proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $valid_ip = long2ip(ip2long(array_pop($ips)));
            return $valid_ip;
        }
        else
        {
            $ip = $_SERVER["REMOTE_ADDR"];
            $valid_ip = self::validateIP($ip, true);
            return $valid_ip;
        }
    } 
    
    /**
     * Utilities::buildYrRange()
     * 
     * This method attempts to create an array of years with the specified start year and the range
     * 
     * @access  public
     * @static
     * @param   integer $startYr - The year to use as the focus year to start counting from
     * @param   integer $range - The range of years before and after the start year
     * @param   bool $showPast - If the array should contain past years
     * @return  array - The array containing the years in a year => year relationship
     */
    public static function buildYrRange($startYr, $range=5, $showPast=false)
    {
        // Check the start the year
        $startYr = (int)$startYr;
        if (empty($startYr))
            $startYr = date('Y');
        // Check the range
        $range = (int)$range;
        if (empty($range))
            $range = 5;
            
        // Determine the min and max vals
        $min = ($showPast) ? $startYr-$range : $startYr;
        $max = $startYr+$range;
        // create the array
        $yrs = range($min, $max);
        // build a new array with the appropriate key => val relationship
        $yrArr = array_combine($yrs, $yrs);
        // return the array
        return $yrArr;
        
    }

    public static function subscribeToNewsletter($email){
        $aRetArr= NULL;

        try
        {
            // Validate the email address
            if (!Utilities::validateEmail($email))
                throw new Exception("Email address not valid", PHPEXCP_ERROR);

            // Check if the email already exists in the DB
            $db = new CDbAccess();
            $clEmail = $db->sanitizeDbInput($email);
            $data = $db->ParseColumnsAndRows('COUNT(*) AS thecount,IsSubscribed', 'newsletter_emails', " WHERE email = '$clEmail'");
            if (@$data[0]['thecount'] > 0){
                $bIsSubscribed = (int)$data[0]['IsSubscribed'];
                if ($bIsSubscribed > 0){
                    // save the email to the DB at this point
                    $inSql = "UPDATE newsletter_emails SET IsSubscribed=0 WHERE email='$clEmail'";
                    $retVal = $db->ReturnQueryExecution($inSql);
                    // check the response and throw an exception for a DB error
                    if ($retVal[0] === 'FALSE' || empty($retVal))
                        throw new Exception("Email could not be deregistered. Please try again", PHPEXCP_ERROR);
                    $aRetArr = array('klass' => 'success', 'msg' => 'Email address successfully unsubscribed');
                }
                else{
                    // save the email to the DB at this point
                    $inSql = "UPDATE newsletter_emails SET IsSubscribed=1 WHERE email='$clEmail'";
                    $retVal = $db->ReturnQueryExecution($inSql);
                    // check the response and throw an exception for a DB error
                    if ($retVal[0] === 'FALSE' || empty($retVal))
                        throw new Exception("Email could not be registered. Please try again", PHPEXCP_ERROR);
                    $aRetArr = array('klass' => 'success', 'msg' => 'Email address successfully subscribed');
                }
            }
            else{
                // save the email to the DB at this point
                $inSql = "INSERT INTO newsletter_emails SET email='$clEmail',IsSubscribed=1";
                $data = $db->ReturnQueryExecution($inSql);
                // check the response and throw an exception for a DB error
                if ($data[0] === 'FALSE' || empty($data))
                    throw new Exception("Email could not be registered. Please try again", PHPEXCP_ERROR);

                #$aRetArr = array('klass' => 'success', 'msg' => 'Your email address has been subscribed for newsletter alerts');
                $aRetArr = array('klass' => 'success', 'msg' => 'Email address successfully subscribed');
            }
        }
        catch (Exception $ex)
        {
            $msgType = 'info';
            if ($ex->getCode() == PHPEXCP_ERROR)
                $msgType = 'error';

            $aRetArr = array('klass' => $msgType, 'msg' => $ex->getMessage());
        }
        return $aRetArr;
    }
    /**
     * Utilities::makeSexMenu()
     * 
     * This method displays the HTML code for a HTML select menu containing the values for sex/gender
     * HTMLObjects class is required in order to use this method
     * 
     * @access  public
     * @static
     * @param   string $menuName
     * @param   string $selected - The value selected 
     * @param   string $cssClass - The name of the CSS class that should be associated with this menu
     * @param   array $jsEvent - The array of Javascript events that should be triggered by this form item
     * @return  void
     */
    public static function makeSexMenu($menuName, $selected='', $cssClass='', array $jsEvent=array())
    {
        
        echo HTMLObjects::createHtmlMenu($menuName, self::$sexs, '', $selected, '', $cssClass, $jsEvent); 
    }
    
    /**
     * Utilities::makeTimeMenu()
     * 
     * Creates and displays menu for time selection
     * HTMLObjects class is required in order to use this method
     * 
     * @param   string $menuname - The name of the time menu
     * @param   bool $withSec - If the seconds menu should be displayed
     * @param   string $selected - The selected time value with the ':' delimiter
     * @param   string $cssClass - The CSS class associated with the menus
     * @return  void
     */
    public static function makeTimeMenu($menuname, $withSec=false, $selected='', $cssClass='')
    {
        $hrs = range(0, 23);
        $hrArr = array();
        foreach($hrs as $hr)
        {
            $val = sprintf("%02d", $hr);
            $hrArr[$val] = $val;
        }
        
        $mins = range(0, 59);
        $minArr = array();
        foreach($mins as $min)
        {
            $val = sprintf("%02d", $min);
            $minArr[$val] = $val;
        }
        
        // Extract the values from the selected value
        $timeVals = explode(':', $selected);
        for($i=0; $i<3; $i++)
        {
            if (empty($timeVals[$i]))
                $timeVals[$i] = '00';
        }
        
        
        // Build the display menus
        echo HTMLObjects::createHtmlMenu($menuname.'_hr', $hrArr, '', $timeVals[0], '00', $cssClass);
        echo ' : '.HTMLObjects::createHtmlMenu($menuname.'_min', $minArr, '', $timeVals[1], '00', $cssClass);
        if ($withSec)
            echo ' : '.HTMLObjects::createHtmlMenu($menuname.'_sec', $minArr, '', $timeVals[2], '00', $cssClass);
            
        
    }
    
    
    /**
     * Utilities::encodeId()
     * 
     * This method encodes an ID that is to be passed as a GET param
     * 
     * @access  public
     * @static
     * @param   string $id
     * @return  string $encId - The encoded value of the ID
     */
    public static function encodeId($id,$_bBase64Only=false)
    {
        // use base64 encoding
        $bEnId = base64_encode($id);
        if($_bBase64Only){
            return $bEnId;
        }
        else{
            // use url encoding
            $encId = urlencode($bEnId);
            // return the encoded id
            return $encId;
        }
    }
    
    /**
     * Utilities::decodeId()
     * 
     * This method decodes an encoded ID that was passed as a GET param
     * 
     * @access  public
     * @static
     * @param   string $encId - The encoded value of the ID
     * @return  string $id - The plain ID
     */
    public static function decodeId($encId,$_bBase64Only=false)
    {
        if($_bBase64Only){
            // use base64 decoding
            $id = base64_decode($encId);
        }
        else{
            // use url decoding
            $urlDId = urldecode($encId);
            // use base64 decoding
            $id = base64_decode($urlDId);
        }
        // return the decoded ID
        return $id;
    }
    
    /**
     * Utilities::makeSummary()
     * 
     * This method summarizes a string into $len number of characters
     * If $cut is true, $string will be cut at EXACTLY $len number of characters else it will be cut after the next word
     * 
     * @access  public
     * @static
     * @param   string $string - The string to be summarized
     * @param   integer $len - The length of the output string
     * @param   bool $cutWord - Indicates if the string should be truncated at EXACTLY $len characters
     * @param   bool $useEllipsis - Indicates if the ellipsis ('...') should be added at the end of the truncated word
     * @return  string
     */
    public static function makeSummary($string, $len=100, $cutWord=false, $useEllipsis=true)
    {
        $len = (int)$len;
        $summary = '';
        
        if (strlen($string) <= $len)
            $summary = $string;
        else
        {
            if ($cutWord)
            {
                $summary = substr($string, 0, $len) . ($useEllipsis ? '...' : '');
            }
            else
            {
                $summary = substr($string, 0, strrpos(substr($string, 0, $len), ' ')) . ($useEllipsis ? '...' : '');
                //$summary = substr($string, 0, strpos(substr($string, $len), ' ')) . '...';
            }
        }
        
        return $summary;
    }
    
    /**
     * Utilities::array_sort()
     * 
     * This method sorts an associative array
     * 
     * @access  public
     * @static
     * @param   array $array - The array of records to be sorted
     * @param   string $on - The column to use for sorting the internal data
     * @param   integer $order - The order to sort by
     * @return  array
     */
    public static function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
    
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
    
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }
    
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
    
        return $new_array;
    }
    
    /**
     * Utilities::forceRedirect()
     * 
     * This method caused the client browser to perform a forced redirect on the user
     * 
     * @access  public
     * @static
     * @param   string $url - The URL to redirect the client browser to
     * @return  void
     */
    public static function forceRedirect($url)
    {
        if (!headers_sent())
        {
            header("Location: $url");            
        }
        else
        {
            /**
             * TEST THIS LINE EXPLICITLY!!!
             * */
            $redirectStr = "<script> top.location.href='" . $url . "'</script>";
            echo $redirectStr;
        }
        
        exit(); 
    }
    
    /**
     * Utilities::shuffleWithKeys()
     * 
     * Shuffles an array and maintained the key=>value relationship
     * This is a substitute for PHP's native shuffle function which doesnt keep the key=>value relationship
     * 
     * @access  public
     * @static
     * @param   array $array - The array to be shuffled
     * @return  array
     */
    public static function shuffleWithKeys($array) 
    {
        /* Auxiliary array to hold the new order */
        $aux = array();
        /* We work with an array of the keys */
        $keys = array_keys($array);
        /* We shuffle the keys */
        shuffle($keys);
        /* We iterate thru' the new order of the keys */
        foreach($keys as $key) {
          /* We insert the key, value pair in its new order */
          $aux[$key] = $array[$key];
          /* We remove the element from the old array to save memory */
          #unset($array[$key]);
        }
        /* The auxiliary array with the new order overwrites the old variable */
        #$array = $aux;
        return $aux;
    }
    
    /**
     * Utilities::loadReqs()
     * 
     * Fetches a required constant that must be included
     * 
     * @access  public
     * @static
     * @return  string
     */
    public static function loadReqs()
    {
        $notAvail = SITE_ROOT.'not_avail.php';
        // Check for the constant
        $constVal = constant("ABOUT_US"); 
        if (is_null($constVal))
        {
            // redirect to the not available page
            self::forceRedirect($notAvail);
        }
        
        return $constVal;
    }

    static public function object_to_array(stdClass $Class){
            # Typecast to (array) automatically converts stdClass -> array.
            $Class = (array)$Class;

        # Iterate through the former properties looking for any stdClass properties.
        # Recursively apply (array).
        foreach($Class as $key => $value){
            if(is_object($value)&&get_class($value)==='stdClass'){
                $Class[$key] = self::object_to_array($value);
            }
        }
        return $Class;
    }

        /**
        * Trim a string to a given number of words
        *
        * @param $string
        *   the original string
        * @param $count
        *   the word count
        * @param $ellipsis
        *   TRUE to add "..."
        *   or use a string to define other character
        * @param $node
        *   provide the node and we'll set the $node->
        *
        * @return
        *   trimmed string with ellipsis added if it was truncated
        */
        static function word_trim($string, $count, $ellipsis = FALSE){
          $words = explode(' ', $string);
          if (count($words) > $count){
            array_splice($words, $count);
            $string = implode(' ', $words);
            if (is_string($ellipsis)){
              $string .= $ellipsis;
            }
            elseif ($ellipsis){
              $string .= '&hellip;';
            }
          }
          return $string;
        }

        public static function normalizeUtf8String( $s)
        {
            // Normalizer-class missing!
            $original_string = $s;
            if (! class_exists("Normalizer", $autoload = false))
                return $original_string;


            // maps German (umlauts) and other European characters onto two characters before just removing diacritics
            $s    = preg_replace( '@\x{00c4}@u'    , "AE",    $s );    // umlaut Ä => AE
            $s    = preg_replace( '@\x{00d6}@u'    , "OE",    $s );    // umlaut Ö => OE
            $s    = preg_replace( '@\x{00dc}@u'    , "UE",    $s );    // umlaut Ü => UE
            $s    = preg_replace( '@\x{00e4}@u'    , "ae",    $s );    // umlaut ä => ae
            $s    = preg_replace( '@\x{00f6}@u'    , "oe",    $s );    // umlaut ö => oe
            $s    = preg_replace( '@\x{00fc}@u'    , "ue",    $s );    // umlaut ü => ue
            $s    = preg_replace( '@\x{00f1}@u'    , "ny",    $s );    // ñ => ny
            $s    = preg_replace( '@\x{00ff}@u'    , "yu",    $s );    // ÿ => yu


            // maps special characters (characters with diacritics) on their base-character followed by the diacritical mark
                // exmaple:  Ú => U´,  á => a`
            $s    = Normalizer::normalize( $s, Normalizer::FORM_D );


            $s    = preg_replace( '@\pM@u'        , "",    $s );    // removes diacritics


            $s    = preg_replace( '@\x{00df}@u'    , "ss",    $s );    // maps German ß onto ss
            $s    = preg_replace( '@\x{00c6}@u'    , "AE",    $s );    // Æ => AE
            $s    = preg_replace( '@\x{00e6}@u'    , "ae",    $s );    // æ => ae
            $s    = preg_replace( '@\x{0132}@u'    , "IJ",    $s );    // ? => IJ
            $s    = preg_replace( '@\x{0133}@u'    , "ij",    $s );    // ? => ij
            $s    = preg_replace( '@\x{0152}@u'    , "OE",    $s );    // Œ => OE
            $s    = preg_replace( '@\x{0153}@u'    , "oe",    $s );    // œ => oe

            $s    = preg_replace( '@\x{00d0}@u'    , "D",    $s );    // Ð => D
            $s    = preg_replace( '@\x{0110}@u'    , "D",    $s );    // Ð => D
            $s    = preg_replace( '@\x{00f0}@u'    , "d",    $s );    // ð => d
            $s    = preg_replace( '@\x{0111}@u'    , "d",    $s );    // d => d
            $s    = preg_replace( '@\x{0126}@u'    , "H",    $s );    // H => H
            $s    = preg_replace( '@\x{0127}@u'    , "h",    $s );    // h => h
            $s    = preg_replace( '@\x{0131}@u'    , "i",    $s );    // i => i
            $s    = preg_replace( '@\x{0138}@u'    , "k",    $s );    // ? => k
            $s    = preg_replace( '@\x{013f}@u'    , "L",    $s );    // ? => L
            $s    = preg_replace( '@\x{0141}@u'    , "L",    $s );    // L => L
            $s    = preg_replace( '@\x{0140}@u'    , "l",    $s );    // ? => l
            $s    = preg_replace( '@\x{0142}@u'    , "l",    $s );    // l => l
            $s    = preg_replace( '@\x{014a}@u'    , "N",    $s );    // ? => N
            $s    = preg_replace( '@\x{0149}@u'    , "n",    $s );    // ? => n
            $s    = preg_replace( '@\x{014b}@u'    , "n",    $s );    // ? => n
            $s    = preg_replace( '@\x{00d8}@u'    , "O",    $s );    // Ø => O
            $s    = preg_replace( '@\x{00f8}@u'    , "o",    $s );    // ø => o
            $s    = preg_replace( '@\x{017f}@u'    , "s",    $s );    // ? => s
            $s    = preg_replace( '@\x{00de}@u'    , "T",    $s );    // Þ => T
            $s    = preg_replace( '@\x{0166}@u'    , "T",    $s );    // T => T
            $s    = preg_replace( '@\x{00fe}@u'    , "t",    $s );    // þ => t
            $s    = preg_replace( '@\x{0167}@u'    , "t",    $s );    // t => t

            // remove all non-ASCii characters
            $s    = preg_replace( '@[^\0-\x80]@u'    , "",    $s );


            // possible errors in UTF8-regular-expressions
            if (empty($s))
                return $original_string;
            else
                return $s;
        }

        public static function normalize_special_characters( $str )
        {
            # Quotes cleanup
            $str = ereg_replace( chr(ord("`")), "'", $str );        # `
            $str = ereg_replace( chr(ord("´")), "'", $str );        # ´
            $str = ereg_replace( chr(ord("„")), ",", $str );        # „
            $str = ereg_replace( chr(ord("`")), "'", $str );        # `
            $str = ereg_replace( chr(ord("´")), "'", $str );        # ´
            $str = ereg_replace( chr(ord("“")), "\"", $str );        # “
            $str = ereg_replace( chr(ord("”")), "\"", $str );        # ”
            $str = ereg_replace( chr(ord("´")), "'", $str );        # ´

        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $str = strtr( $str, $unwanted_array );

        # Bullets, dashes, and trademarks
        $str = ereg_replace( chr(149), "&#8226;", $str );    # bullet •
        $str = ereg_replace( chr(150), "&ndash;", $str );    # en dash
        $str = ereg_replace( chr(151), "&mdash;", $str );    # em dash
        $str = ereg_replace( chr(153), "&#8482;", $str );    # trademark
        $str = ereg_replace( chr(169), "&copy;", $str );    # copyright mark
        $str = ereg_replace( chr(174), "&reg;", $str );        # registration mark

            return $str;
        }
        
        
        static function crypto_rand_secure($min, $max) {
            $range = $max - $min;
            if ($range < 0) return $min; // not so random...
            $log = log($range, 2);
            $bytes = (int) ($log / 8) + 1; // length in bytes
            $bits = (int) $log + 1; // length in bits
            $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);
            return $min + $rnd;
        }

        static function getToken($length){
            $token = "";
            $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
            $codeAlphabet.= "0123456789";
            for($i=0;$i<$length;$i++){
                $token .= $codeAlphabet[mt_rand(0,strlen($codeAlphabet))];
//                $token .= $codeAlphabet[Utilities::crypto_rand_secure(0,strlen($codeAlphabet))];
            }
            return $token;
        }
        
        static function getRandomToken($length = 10, $chars = '1234567890') {
                // Alpha lowercase
            $randomString = "";
            if ($chars == 'alphalower') {
                $chars = 'abcdefghijklmnopqrstuvwxyz';
            }

            // Numeric
            if ($chars == 'numeric') {
                $chars = '1234567890';
            }

            // Alpha Numeric
            if ($chars == 'alphanumeric') {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            }

            // Hex
            if ($chars == 'hex') {
                $chars = 'ABCDEF1234567890';
            }

            $charLength = strlen($chars) - 1;

            for ($i = 0; $i < $length; $i++) {
                $randomString .= $chars[mt_rand(0, $charLength)];
            }

            return $randomString;
        }
}

?>