<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

/**
 * Description of JSONResponse
 *
 * @author buddha
 */
class JSONResponse
{
    
    /**
     * The resulting json array
     * @var type 
     */
    private $result = null;
    
    public function __construct()
    {
        $result = [];
    }
    
    public function error($errorMessage, $errorCode)
    {
        $this->result['error'] = $errorMessage;
        $this->result['errorCode'] = $errorCode;
        return $this;
    }
    
    public function content($content)
    {
        $this->result['content'] = $content;
        return $this;
    }
    
    public function data($key, $value)
    {
        if(!is_array($this->$result['data'])) {
            $this->$result['data'] = [];
        }
        $this->result['data'][$key] = $value;
        return $this;
    }
    
    public function asJSON() 
            {
        Yii::$app->response->format = 'json';
        return $this->result;
        
    }

}
