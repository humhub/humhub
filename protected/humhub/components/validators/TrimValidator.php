<?php

namespace humhub\components\validators;

use yii\helpers\Json;
use yii\validators\ValidationAsset;

class TrimValidator extends \yii\validators\TrimValidator
{
    /**
     * @inheritdoc
     */
    protected function trimValue($value)
    {
        return $this->isEmpty($value) ? '' : trim((string) preg_replace('/^[\p{Z}\s]+|[\p{Z}\s]+$/u', ' ', (string) $value));
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->skipOnArray && is_array($model->$attribute)) {
            return null;
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'value = humhub.require(\'ui.form.elements\').validate.trim($form, attribute, ' . Json::htmlEncode($options) . ', value);';
    }
}
