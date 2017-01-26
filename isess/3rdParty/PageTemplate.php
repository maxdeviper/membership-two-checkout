<?php

/**
 * @author      Opus Hive
 * @copyright   2011
 * @filesource  PageTemplate.php
 */

require_once('Utilities.php');
require_once (CLASSES_PATH_CHERRIES."localconfig.php");
require_once (CLASSES_PATH_ISESS."articles/CArticle.php");
require_once (CLASSES_PATH_ISESS."media/CAlbum.php");
require_once (CLASSES_PATH_ISESS."media/CImage.php");
require_once (CLASSES_PATH_ISESS."social_netwk/FB_Integration.php");
require_once (CLASSES_PATH_ISESS."social_netwk/TwitterIntegration.php");


/**
 * PageTemplate
 * 
 * @package     MART Website
 * @author      Opus Hive
 * @copyright   2011
 * @version     1.2
 * @access      public
 */
class PageTemplate
{
    
    private $jsCodes;
    
    
    private $jsFiles;
    
    
    private $cssFiles;
    
    
    private $cssCode;
    
    
    private $jqCode;
    
    
    private $bodyCode;
    
    
    private $sliderCode;
    
    
    private $pageTitle;
        
    
    private $linksCode;
    
    
    public $hasSlider;
    
    
    private $pageLinks;
    
    
    private $newsFeedParams;
        
    
    const SLIDER_DEFAULT = 1;
        
    
    /**
     * PageTemplate::__construct()
     * 
     * Class constructor that handles initialization of the properties of this class
     * 
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        $this->bodyCode = '';
        $this->jqCode = '';
        $this->sliderCode = '';
        $this->jsCodes = '';
        $this->pageTitle = '';
        $this->cssCode = '';
        $this->cssFiles = array();
        $this->jsFiles = array();
        
        $this->hasSlider = true;
        
        $this->init();
    }
    
    /**
     * PageTemplate::init()
     * 
     * This method handles the initialization of default values for the properties of this class
     * 
     * @access  protected
     * @return  void
     */
    protected function init()
    {
        
        // Initialize the links array
        $this->pageLinks = array('Home' => 'index.php',
                                 'About Us' => 'about.php',
                                 'Gallery' => 'gallery.php',
                                 'Services' => 'service.php',
                                 'Health Information' => 'books.php',
                                 'Success Stories' => 'cases.php',
                                'Contact Us' => 'contact.php',
                                 );
        // Set the default title if no ttitla is provided
        #$this->pageTitle = SITE_NAME; 
    }
    
    /**
     * PageTemplate::renderHtmlHead()
     * 
     * Generates the code to be placed in the <head> section of the HTML page
     * 
     * @access  public
     * @return  void
     */
    public function renderHtmlHead()
    {
        $headStr = '';
        
        // Build the collections        
        // Build the CSS Files as a list
        $cssExts = '';
        foreach ($this->cssFiles as $cssFile)
            $cssExts .= '<link href="'.$cssFile.'" rel="stylesheet" type="text/css" />'."\n";
        
        $jsExts = '';
        foreach ($this->jsFiles as $jsFile)
            $jsExts .= '<script type="text/javascript" src="'.$jsFile.'"></script>'."\n";
        
        // Add any jscript code created
        $jsCode = '';
        if (!empty($this->jsCodes))
        {
            $jsCode .= '<script type="text/javascript">
                '.$this->jsCodes.'
            </script>
            ';
        }
        
        // Add jquery codes
        $jqCode = '';
        if (!empty($this->jqCode))
        {
            $jqCode .= '<script type="text/javascript">
            $(document).ready(function(){
                '.$this->jqCode.'
            });
            
            </script>
            ';
        }
        
        // Add CSS code
        $cssCode = '';
        if (!empty($this->cssCode))
        {
            $cssCode .= "<style>\n {$this->cssCode} \n </style> \n";
        }
        
        $titleStr = "<title>".$this->pageTitle."</title> \n";
        
        $headStr .= '
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        ';
        
        // Add all the necessary additions to the header
        $headStr .= "\n $titleStr \n $cssExts \n $jsExts \n $jqCode \n $jsCode \n \n $cssCode";
        
        $headStr .= "</head>\n";
        
        return $headStr;
    }
    
    /**
     * PageTemplate::buildPageLinks()
     * 
     * Generates the code for the page links
     * 
     * @access  public
     * @param   string $currPg - The current page being viewed by the client
     * @param   bool $return - Indicates if the generated code should be returned or not
     * @return  void | string
     */
    public function buildPageLinks($currPg="", $return=false)
    {
        $currPg = trim($currPg);
        
        $this->linksCode = '
            <div>
            <ul class="navmenu">
        ';
        // Build the page list
        if (!empty($this->pageLinks))
        {
            $cnt = 1;
            $xtraStr = '';
            
            foreach ($this->pageLinks as $name => $link)
            {                                    
                if (empty($currPg) || $currPg == $name)
                    $this->linksCode .= " <li><a href='$link' class='active'>$name</a></li>";
                else
                    $this->linksCode .= " <li><a href='$link'>$name</a></li>";
                    
                $cnt++;
            }
        } 
                
        $this->linksCode .= '
                <div class="clear"></div>
            </ul>
            </div>';
        
        // return if necessary
        if ($return)
            return $this->linksCode;    
    }
    
    /**
     * PageTemplate::renderFooter()
     * 
     * This method handles the generation of the footer code
     * 
     * @access  public
     * @return  string
     */
    public function renderFooter($_sFooter,$fbObj=null, $twObj=null, $addFb=true, $addTw=true)
    {
    }
    
    /**
     * PageTemplate::renderAddressFooter()
     * 
     * Generates and returns the HTML code for the address details footer
     * 
     * @access  public
     * @return  string
     */
    public function renderAddressFooter()
    {
        $address = '';
        
        $address = '
        <div id="footer">2nd Floor LoFom House 21, Mobolaji Bank Anthony Way Ikeja, Lagos Nigeria<br />
            +234 1 7307356, +234 1 8724491, +234 803 334 7231<br />info@medicalartcenter.com
        </div>
        ';
        
        return $address;
    }
    
    /**
     * PageTemplate::buildSlider()
     * 
     * @access  public
     * @param   integer $sliderType - The type of the slider, if different from the default
     * @param   integer $imgCount - The maximum number of images to be displayed in the slides. 0 indicates 'all'
     * @return  void
     */
    public function buildSlider($sliderType=DEFAULT_SLIDER_ALBUM, $imgCount=5)
    {
        $aImages = array();
        
        // get default slider images
        $slider = new CAlbum($sliderType);
        if($slider->buildAlbumMedia(""," ORDER BY mm_images.Image_Id DESC LIMIT ".$imgCount)){
            $aImages = $slider->getMedia();
        }
        
        // Iterate through the array and render the data
        $this->sliderCode = '<div class="slider_content">';
        if(count($aImages))
        {
            foreach ($aImages as $anImg)
            {
                if((int)$anImg['Width'] > (int)$anImg['Height']){
                    $sResizedPath = CImage::makeResizedImgTag($anImg['Path'], "resized_slider_img", "890", "400", "height",array('alt'=>$anImg['Title'],'addDims' => true));
                }
                else{
                    $sResizedPath = CImage::makeResizedImgTag($anImg['Path'], "resized_slider_img", "890", "400", "width",array('alt'=>$anImg['Title'],'addDims' => true));
                }
                $this->sliderCode .= '<div class="item">'.$sResizedPath.'</div>';
            }
        }
        
        $this->sliderCode .= "</div>";
        
    }

    /**
     * PageTemplate::renderSlider()
     * 
     * @access  public
     * @return  string
     */
    public function renderSlider()
    {        
        return $this->sliderCode;
    }
    
    /**
     * PageTemplate::buildBody()
     * 
     * This method handles creating the code that should form the dynamic body of the page.
     * Note that this body IS NOT the same as the content of the <body> of the HTML page but is the content that will keep changing
     * 
     * @access  public
     * @param   string $body
     * @return  void
     */
    public function buildBody($body)
    {
        $this->bodyCode = $body;
    }
    
    /**
     * PageTemplate::showPage()
     * 
     * @access  public
     * @return  void
     */
    public function showPage()
    {
        
    }
    
    /**
     * PageTemplate::appendJSCode()
     * 
     * @access  public
     * @param   string $code
     * @return  void
     */
    public function appendJSCode($code)
    {
        // Append the code
        $this->jsCodes .= "\n".$code;
    }
    
    /**
     * PageTemplate::appendCSSCode()
     * 
     * @access  public
     * @param   string $code - The CSS code to be appended to the existing collection of codes
     * @return  void
     */
    public function appendCSSCode($code)
    {
        $this->cssCode .= "\n".$code;
    } 
    
    /**
     * PageTemplate::appendJSFile()
     * 
     * @access  public
     * @param   string $filepath
     * @return  void
     */
    public function appendJSFile($filepath)
    {
        // Verify that the file doesnt not already exist
        $filepath = strip_tags($filepath);
        if (!in_array($filepath, $this->jsFiles))
            $this->jsFiles[] = $filepath;
    }
    
    /**
     * PageTemplate::appendCSSFile()
     * 
     * @access  public
     * @param   string $filepath
     * @return  void
     */
    public function appendCSSFile ($filepath)
    {
        // Verify that the file doesnt not already exist
        $filepath = strip_tags($filepath);
        if (!in_array($filepath, $this->cssFiles))
            $this->cssFiles[] = $filepath;
    }
    
    /**
     * PageTemplate::appendJQuery()
     * 
     * @access  public
     * @param   string $jqCode
     * @return  void
     */
    public function appendJQuery($jqCode)
    {
        $this->jqCode .= "\n".$jqCode;
    }
    
    /**
     * PageTemplate::setTitle()
     * 
     * This method sets the page title that will be displayed in the browser title section
     * 
     * @access  public
     * @param   string $title
     * @return  void
     */
    public function setTitle($title)
    {
        $this->pageTitle = strip_tags($title);
    }
    
    /**
     * PageTemplate::renderTopBar()
     * 
     * Generates and returns the code for the top bar div of the page
     * 
     * @access  public
     * @return  string
     */
    public function renderTopBar()
    {
        $topBar = '';
        
        $topBar = '
        <div id="topbar">
            '.$this->renderLogo().'
            
            '.$this->renderQuickMenu().'
            <div class="clear"></div>
        </div>
        ';
        
        return $topBar;
    }
    
    /**
     * PageTemplate::renderLogo()
     * 
     * Generates and returns the code necessary for the logo creation on the view page
     * 
     * @access  public
     * @return  string
     */
    public function renderLogo()
    {
        $logoCode = '';
        
        $logoCode = '
            <div id="logo_div">
                <img src="imgs/med_logo.jpg" alt="medical art logo" width="104" height="109" align="middle" />
                <div id="logoname">
                    <h1>Medical Art Center</h1>
                    Assisted Reproductive Technology
                </div>            
            </div>
        ';
        
        return $logoCode;
    }
    
    /**
     * PageTemplate::renderQuickMenu()
     * 
     * Generates and returns the HTML code for the quick menu section of the page template display
     * 
     * @access  protected
     * @return  string
     */
    protected function renderQuickMenu()
    {
        $qmCode = '';
        
        $contactPg = $this->pageLinks['Contact Us'];
        $servicePg = $this->pageLinks['Services'];
        
        $qmCode = '
            <div id="quickmenu">
                <div id="min_link"><a href="'.$contactPg.'">Online Consultation</a> <a href="'.$contactPg.'">FAQs</a> <a href="'.$contactPg.'">Feedback</a></div>
                <form action="" method="post">
                <input name="search" type="text" id="search" />
                </form>
                <div><span class="hmh">How may we help you? <select name="" onchange="javascript: top.location = this.options[this.selectedIndex].value">
                  <option>Select an option</option>
                  <option value="'.$servicePg.'">Check our services</option>
                  <option value="'.$contactPg.'">Make an appointment</option>
                  <option value="'.$contactPg.'">Online Consultation</option>
                  <option value="'.$contactPg.'">FAQ</option>
                  <option value="'.$contactPg.'">Give us a feedback</option>
                  <option value="'.$contactPg.'">Contact us</option>
                </select></span>
                </div>
            </div>
        ';
        
        return $qmCode;
    }
    
    /**
     * PageTemplate::renderLightBoxDivs()
     * 
     * Generates the code necessary to embed a lightbox DIV
     * 
     * @access  public
     * @return  string
     */
    public function renderLightBoxDivs()
    {
        $lightbox = '';
        
        $lightbox = <<<LIGHT
        <div id="monobox">
            <div class="monocnt">
                <div class="close"><a href="#">Close</a></div>
                <div class="monocntholder">
                
                </div>
            </div>
        </div>  
        
LIGHT;
        
        return $lightbox;
    }
    
    /**
     * PageTemplate::renderGAnalyticsCode()
     * 
     * Renders the code for the Google analytics 
     * 
     * @access  public
     * @return  string
     */
    public function renderGAnalyticsCode()
    {
        $anaCode = '';
        
        $anaCode = <<<CODE
        <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26047408-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
    
CODE;
        
        return $anaCode;
    }
    
    /**
     * PageTemplate::showSearchForm()
     * 
     * Generates the HTML code for a search form to be displayed
     * 
     * @access  public
     * @return  string
     */
    public function showSearchForm()
    {
        $formStr = '';
        
        // Redefine the page URL constant if not already defined
        if (!defined('PAGE_URL'))
        {
            $sPgUrl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"].'?'.$_SERVER["QUERY_STRING"];
            define('PAGE_URL', $sPgUrl);
        }
        
        // Set the default value when the field is initialized
        $defStr = "Search..";
        if (!empty($_POST["srch_cont"]))
        {
            $defStr = strip_tags($_POST["srch_cont"]);
        }
        
        $page = 'test_search.php';
        $formStr = '
            <form name="frm_search" action="'.$page.'" method="post">
                <input id="search" name="search" value="'.$defStr.'" onfocus="clrDefaultTxt(\'search\', \'Search..\')" onblur="setDefaultTxt(\'search\', \'Search..\')"/>
                <input type="submit" name="pg_search" id="pg_search" value="" class="search-button" />
            </form>
        ';
        return $formStr;
    }
    
}

?>