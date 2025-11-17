<?php

namespace humhub\libs\Utf8Trim;

use yii\validators\Validator;

class Utf8Trim extends Validator
{
    public $skipOnEmpty = false;

    public function validateAttribute($model, $attribute)
    {
        $model->$attribute = trim(preg_replace('/^[\p{Z}\s]+|[\p{Z}\s]+$/u', ' ', $model->$attribute));
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $options = $this->getClientOptions($model, $attribute);

        Utf8TrimAsset::register($view);

        return 'value = Utf8Trim($form, attribute, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ', value);';
    }
}
