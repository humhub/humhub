<?php

namespace humhub\components\validators;

use yii\helpers\Json;
use yii\validators\ValidationAsset;

class RequiredValidator extends \yii\validators\RequiredValidator
{
    /**
     * @inerhitdoc
     */
    public function isEmpty($value)
    {
        if (empty(preg_replace('/[\p{Z}\s]+/u', '', $value))) {
            return true;
        }

        return parent::isEmpty($value);
    }

    /**
     * @inerhitdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'humhub.require(\'ui.form.elements\').validate.required(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
