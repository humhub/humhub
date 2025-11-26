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
            if (!( // check if string is not binary
                str_contains($value, "\0") && // check if contains NUL
                !mb_check_encoding($value, 'UTF-8') && // check if invalid UTF-8 encoding
                !!preg_match('/[^\x09\x0A\x0D\x20-\x7E]/', $value) // check for binary control chars
            )) {
                $value = preg_replace('/[\p{Z}\s]+/u', '', $value);
            }
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
