<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humhub\components\validators;

use yii\validators\Validator;

/**
 * Description of AbstractDateValidator
 *
 * @deprecated since version 1.1.2
 * @author buddha
 */
abstract class AbstractDateValidator extends Validator
{

    public $message;

    abstract public function dateValidation($timestamp);

    public function validateAttribute($model, $attribute)
    {
        $date = $model->$attribute;
        if (is_string($model->$attribute)) {
            $date = strtotime($model->$attribute);
        } else if ($model->$attribute instanceof DateTime) {
            $date = $model->$attribute->getTimestamp();
        }

        if ($this->dateValidation($date)) {
            $this->addError($model, $attribute, $this->message);
        }
    }

}
