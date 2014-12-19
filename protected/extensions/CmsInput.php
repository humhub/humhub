<?php if ( ! defined('YII_PATH')) exit('No direct script access allowed');

/**
 * CmsInput
 * 
 * @package OneTwist CMS  
 * @author twisted1919 (cristian.serban@onetwist.com)
 * @copyright OneTwist CMS (www.onetwist.com)
 * @version 1.2
 * @since 1.0
 * @access public
 * 
 * 1.1 
 * - Added public $cleanMethod, which allows to specify which filter should be used to clean globals.
 * - Added stripEncode method, which will strip tags and encode.
 * - Added cleanEncode method that will xssClean and encode
 * - Added decode method to decode a string/array
 * - post/get methods can now retrieve the entire array at once
 * - getOriginalPost()/getOriginalGet() can retrieve a single key or the entire array
 * - Fixed a bug in the encode method
 * - Added logging for global filtering
 * - The cleaning of the globals is now set to true by default, it is safer this way
 * - Other various changes.
 * 
 * 1.2
 * - added getQuery(), a wrapper for get() to be more yii like.
 * - getPost will now retrieve a value from $_POST, being a post() wrapper to be more yii like.
 * - fixed a bug in get/post where if the $defaultValue was set and the variable didn't existed 
 *   it would return an empty string(thanks to Wiseon3 [http://www.yiiframework.com/user/13664/] who pointed it out)
 * - logging will occur just in debug mode from now on.
 * - changed the default cleaning method to from stripCleanEncode to stripClean
 */
class CmsInput extends CApplicationComponent
{
    // flag marked true when the $_POST has been globally cleaned.
    protected $cleanPostCompleted   = false;
    
    // flag marked true when $_GET has been globally cleaned.
    protected $cleanGetCompleted    = false;
    
    // holds the default cleaning method for global filtering.
    protected $defaultCleanMethod   = 'stripClean';
    
    // the Codeigniter Xss Filter object.
    protected $security;
    
    // array() holding the original $_POST.
    protected $originalPost = array();
    
    // array() holding the original $_GET
    protected $originalGet  = array();
    
    // HtmlPurifier object.
    protected $purifier;

    // determines if $_POST should be cleaned globally.
    public $_cleanPost   = true;
    
    // determines if $_GET should be cleaned globally
    public $_cleanGet    = true;
    
    // which methods will be used when doing the cleaning.
    public $_cleanMethod = 'stripClean';
    
    
    /**
     * CmsInput::init()
     * 
     * @return
     */
    public function init()
    {
        $this->originalPost=$_POST;
        $this->originalGet=$_GET;

        parent::init();
        Yii::app()->attachEventHandler('onBeginRequest', array($this, 'cleanGlobals'));
    }
    
    /**
     * CmsInput::purify()
     * 
     * @param mixed $str
     * @return
     */
    public function purify($str)
    {
        if(is_array($str))
        {
            foreach($str AS $k=>$v)
                $str[$k]=$this->purify($v);
            return $str;
        }
        return $this->getHtmlPurifier()->purify($str);
    }

    /**
     * CmsInput::xssClean()
     * 
     * @param mixed $str
     * @param bool $isImage
     * @return
     */
    public function xssClean($str, $isImage=false)
    {
        return $this->getSecurity()->xss_clean($str, $isImage);
    }

    /**
     * CmsInput::stripTags()
     * 
     * @param mixed $str
     * @param bool $encode
     * @return
     */
    public function stripTags($str, $encode=false)
	{
        if(is_array($str))
        {
            foreach($str AS $k=>$v) 
                $str[$k]=$this->stripTags($v, $encode);
            return $str;
        }     
        $str=trim(strip_tags($str));
        
        if($encode) 
            $str=$this->encode($str);
        return $str;              
	}
    
    /**
     * CmsInput::stripCleanEncode()
     * 
     * @param mixed $str
     * @return
     */
    public function stripCleanEncode($str)
    {
        if(is_array($str))
        {
            foreach($str AS $k=>$v)
                $str[$k]=$this->stripCleanEncode($v);
            return $str;
        }
        return $this->encode($this->stripClean($str)); 
    }
    
    /**
     * CmsInput::cleanEncode()
     * 
     * @param mixed $str
     * @return
     */
    public function cleanEncode($str)
    {
        return $this->encode($this->xssClean($str));
    }
    
    /**
     * CmsInput::stripClean()
     * 
     * @param mixed $str
     * @return
     */
    public function stripClean($str)
    {
        return $this->stripTags($this->xssClean($str));
    }
    
    /**
     * CmsInput::encode()
     * 
     * @param mixed $str
     * @return
     */
    public function encode($str)
    {
        if(is_array($str))
        {
            foreach($str AS $k=>$v)
                $str[$k]=$this->encode($v);
            return $str;
        }
        return CHtml::encode($str);
    }
    
    /**
     * CmsInput::decode()
     * 
     * @param mixed $str
     * @return
     */
    public function decode($str)
    {
        if(is_array($str))
        {
            foreach($str AS $k=>$v)
                $str[$k]=$this->decode($v);
            return $str;
        }
        return CHtml::decode($str);
    }
    
    /**
     * CmsInput::stripEncode()
     * 
     * @param mixed $str
     * @return
     */
    public function stripEncode($str)
    {
        return $this->stripTags($str, true);
    }
    
    /**
     * CmsInput::get()
     * 
     * @param mixed $key
     * @param string $defaultValue
     * @param bool $clean
     * @return
     */
    public function get($key=null, $defaultValue=null, $clean=true)
    {
        $cleanMethod = $this->getCleanMethod();
        if(empty($key) && empty($defaultValue))
        {
            if($clean===true && $this->cleanGetCompleted===false)
                return $this->$cleanMethod($_GET);
            return $_GET;
        }
        $value=Yii::app()->request->getQuery($key, $defaultValue);
        if($clean===true && $this->cleanGetCompleted===false && !empty($value))
            return $this->$cleanMethod($value);
        return $value;
    }
 
    /**
     * CmsInput::getQuery()
     * 
     * @param mixed $key
     * @param string $defaultValue
     * @param bool $clean
     * @return
     */
     public function getQuery($key=null, $defaultValue=null, $clean=true)
     {
        return $this->get($key, $defaultValue, $clean);
     }
    
    /**
     * CmsInput::post()
     * 
     * @param mixed $key
     * @param string $defaultValue
     * @param bool $clean
     * @return
     */
    public function post($key=null, $defaultValue=null, $clean=true)
    {
        $cleanMethod = $this->getCleanMethod();
        if(empty($key) && empty($defaultValue))
        {
            if($clean===true && $this->cleanPostCompleted===false)
                return $this->$cleanMethod($_POST);
            return $_POST;
        }
        $value=Yii::app()->request->getPost($key, $defaultValue);
        if($clean===true && $this->cleanPostCompleted===false && !empty($value))
            return $this->$cleanMethod($value);
        return $value;
    }
    
    /**
     * CmsInput::getPost()
     * 
     * @param mixed $key
     * @param string $defaultValue
     * @param bool $clean
     * @return
     */
    public function getPost($key, $defaultValue=null, $clean=true)
    {
        return $this->post($key, $defaultValue, $clean);
    }

    /**
     * CmsInput::sanitizeFilename()
     * 
     * @param mixed $file
     * @return
     */
    public function sanitizeFilename($file)
    {
        return $this->getSecurity()->sanitize_filename($file);
    } 

    /**
     * CmsInput::cleanGlobals()
     * 
     * @return
     */
    protected function cleanGlobals()
    {
        $cleanMethod = $this->getCleanMethod();

        if($this->getCleanPost()===true && $this->cleanPostCompleted===false && !empty($_POST))
        {
            $_POST=$this->post();
            $this->cleanPostCompleted=true;
            if(defined('YII_DEBUG')&&YII_DEBUG)
                Yii::log(Yii::t('security', 'Global {global} array cleaned using {method} method.',array('{global}'=>'$_POST', '{method}'=>__CLASS__.'::'.$cleanMethod)));
        }
        if($this->getCleanGet()===true && $this->cleanGetCompleted===false && !empty($_GET))
        {
            $_GET=$this->get();
            $this->cleanGetCompleted=true;
            if(defined('YII_DEBUG')&&YII_DEBUG)
                Yii::log(Yii::t('security', 'Global {global} array cleaned using {method} method.',array('{global}'=>'$_GET', '{method}'=>__CLASS__.'::'.$cleanMethod)));
        }
    }
    
    /**
     * CmsInput::setCleanPost()
     * 
     * @param mixed $str
     * @return
     */
    public function setCleanPost($str)
    {
        $this->_cleanPost=(bool)$str;
    }
    
    /**
     * CmsInput::getCleanPost()
     * 
     * @return
     */
    public function getCleanPost()
    {
        return $this->_cleanPost;
    }
    
    /**
     * CmsInput::setCleanGet()
     * 
     * @param mixed $str
     * @return
     */
    public function setCleanGet($str)
    {
        $this->_cleanGet=(bool)$str;
    }
    
    /**
     * CmsInput::getCleanGet()
     * 
     * @return
     */
    public function getCleanGet()
    {
        return $this->_cleanGet;
    }
    
    /**
     * CmsInput::setCleanMethod()
     * 
     * @param mixed $str
     * @return
     */
    public function setCleanMethod($str)
    {
        if(!method_exists($this, $str))
            $str=$this->defaultCleanMethod;
        $this->_cleanMethod=$str;
    }
    
    /**
     * CmsInput::getCleanMethod()
     * 
     * @return
     */
    public function getCleanMethod()
    {
        return $this->_cleanMethod;
    }
    
    /**
     * CmsInput::getOriginalPost()
     * 
     * @param mixed $key
     * @param string $defaultValue
     * @return
     */
    public function getOriginalPost($key=null, $defaultValue=null)
    {
        if(empty($key))
            return $this->originalPost;
        return isset($this->originalPost[$key])?$this->originalPost[$key]:$defaultValue;
    }
    
    /**
     * CmsInput::getOriginalGet()
     *
     * @param mixed $key
     * @param string $defaultValue
     * @return
     */
    public function getOriginalGet($key=null, $defaultValue=null)
    {
        if(empty($key))
            return $this->originalGet;
        return isset($this->originalGet[$key])?$this->originalGet[$key]:$defaultValue;
    }
    
    /**
     * CmsInput::getSecurity()
     * 
     * @return Security
     */
    private function getSecurity()
    {
        if($this->security !== null) {
            return $this->security;
        }

        $this->security = new Security();

        return $this->security;
    }
    
    /**
     * CmsInput::getHtmlPurifier()
     * 
     * @return
     */
    private function getHtmlPurifier()
    {
        if($this->purifier!==null)
            return $this->purifier;
        $this->purifier=new CHtmlPurifier;
        if(file_exists($file=Yii::getPathOfAlias('application').DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'htmlpurifier.php'))
            $this->purifier->options=include($file);
        return $this->purifier;
    }


}