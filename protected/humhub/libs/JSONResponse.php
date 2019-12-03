<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\BaseObject;
use yii\web\Response;

/**
 * Description of JSONResponse
 *
 * @author buddha
 */
class JSONResponse extends BaseObject
{
    const RESULT_KEY_OUTPUT = 'output';
    const RESULT_KEY_SUCCESS = 'success';

    /**
     * The resulting json array
     * @var []
     */
    private $result = [];

    public static function output($dom, $success = null)
    {
        return (new static())->withOutput($dom, $success)->result();
    }

    public function error($errors, $errorTitle = null, $status = null)
    {
        $this->result['errorTitle'] = $errorTitle;
        $this->result['errors'] = $errors;
        $this->result['status'] = ($status != null && $status > 0) ? $status : self::STATE_ERROR_APPLICATION;

        return $this;
    }

    public function success($success = true)
    {
        $this->result[static::RESULT_KEY_SUCCESS] = $success;
    }

    public function content($content)
    {
        $this->result['content'] = $content;

        return $this;
    }

    public function withOutput($dom, $success = null)
    {
        $this->result[static::RESULT_KEY_OUTPUT] = $dom;

        if($success !== null) {
            $this->success($success);
        }

        return $this;
    }

    public function data($key, $value)
    {
        if (!is_array($this->result['data'])) {
            $this->result['data'] = [];
        }
        $this->result['data'][$key] = $value;

        return $this;
    }

    public function result()
    {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $this->result;
        return $response;
    }
}
