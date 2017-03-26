<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
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
