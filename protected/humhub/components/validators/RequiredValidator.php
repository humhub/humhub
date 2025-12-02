<?php

namespace humhub\components\validators;

use yii\helpers\Json;
use yii\validators\ValidationAsset;

class RequiredValidator extends \yii\validators\RequiredValidator
{
    private const REGEX = '/[\p{Z}\s]+/u';
    /**
     * @inheritdoc
     */
    public function isEmpty($value)
    {
        if (is_string($value) && empty(preg_replace(self::REGEX, '', $value))) {
            return true;
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
