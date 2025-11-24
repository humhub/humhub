<?php

namespace humhub\components\validators;

use yii\helpers\Json;
use yii\validators\ValidationAsset;

class RequiredValidator extends \yii\validators\RequiredValidator
{
    /**
     * @inheritdoc
     */
    public function isEmpty($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/[\p{Z}\s]+/u', '', $value);
        }

        return parent::isEmpty($value);
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'humhub.require(\'ui.form.elements\').validate.required(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
