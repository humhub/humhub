<?php

namespace humhub\libs;

use yii\helpers\Json;
use yii\validators\ValidationAsset;

class TrimValidator extends \yii\validators\TrimValidator
{
    public $skipOnEmpty = false;

    /**
     * @inerhitdoc
     */
    protected function trimValue($value)
    {
        return $this->isEmpty($value) ? '' : trim(preg_replace('/^[\p{Z}\s]+|[\p{Z}\s]+$/u', ' ', $value));
    }

    /**
     * @inerhitdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->skipOnArray && is_array($model->$attribute)) {
            return null;
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'value = humhub.require(\'ui.form.elements\').trim($form, attribute, ' . Json::htmlEncode($options) . ', value);';
    }
}
