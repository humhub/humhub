<?php

namespace humhub\libs;

class RequiredValidator extends \yii\validators\RequiredValidator
{
    public function isEmpty($value)
    {
        if (empty(preg_replace('/[\p{Z}\s]+/u', '', $value))) {
            return true;
        }

        return parent::isEmpty($value);
    }
}
