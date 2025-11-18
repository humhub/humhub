<?php

namespace humhub\components\validators;

class RequiredValidator extends \yii\validators\RequiredValidator
{
    public $emptyPattern = '/[\s\p{Cc}\p{Cf}\p{Cs}\p{Cn}]+/u';

    public function isEmpty($value)
    {
        if (empty(preg_replace($this->emptyPattern, '', $value))) {
            return true;
        }

        return parent::isEmpty($value);
    }
}
