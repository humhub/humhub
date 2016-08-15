<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humhub\components\validators;

use Yii;
/**
 * Description of PastDateValidator
 *
 * @author buddha
 */
class PastDateValidator extends AbstractDateValidator
{   

    public function init()
    {
        $this->message = Yii::t('base', 'The date has to be in the past.');
    }
    
    public function dateValidation($dateTS)
    {
        return $dateTS >  time();
    }
}
