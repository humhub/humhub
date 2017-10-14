<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

/**
 * Description of JSONResponse
 *
 * @deprecated since v1.2
 * @author buddha
 */
class JSONResponse
{

    const STATE_CONFIRM = 0;
    const STATE_ERROR_APPLICATION = 1;
    const STATE_ERROR_VALIDATOIN = 2;
    /**
     * The resulting json array
     * @var type
     */
    private $result = null;

    public function __construct()
    {
        $result = [];
    }

    public function error($errors, $errorTitle = null, $status = null)
    {
        $this->result['errorTitle'] = $errorTitle;
        $this->result['errors'] = $errors;
        $this->result['status'] = ($status != null && $status > 0) ? $status : self::STATE_ERROR_APPLICATION;

        return $this;
    }

    public function content($content)
    {
        $this->result['content'] = $content;
        return $this;
    }

    public function confirm($content)
    {
        if($content != null) {
            $this->content($content);
        }
        $this->result['status'] = self::STATE_CONFIRM;

        return $this;
    }

    public function data($key, $value)
    {
        if(!is_array($this->result['data'])) {
            $this->result['data'] = [];
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
