<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humhub\components\validators;

use Yii;
use yii\validators\DateValidator;

/**
 * PastDateValidator ensurs the date is in the past
 * 
 * @deprecated since version 1.1.2
 * @author buddha
 */
class PastDateValidator extends \yii\validators\DbDateValidator
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->message = Yii::t('base', 'The date has to be in the past.');
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $timestamp = $this->parseDateValue($model->$attribute);
        if ($timestamp !== false && $timestamp > time()) {
            $this->addError($model, $attribute, $this->message);
        }
    }

}
