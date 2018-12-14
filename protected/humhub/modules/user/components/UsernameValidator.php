<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use yii\validators\Validator;

/**
 * UsernameValidator checks that username contains only lowercase letters, numbers, hyphens and underscores
 *
 */
class UsernameValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {        
        if (!preg_match('/^[a-z0-9-_]+$/', $model->$attribute)) {
            $this->addError($model, $attribute, Yii::t('UserModule.usernameError', 'Username can contain only lowercase letters, numbers, hyphens and underscores!'));
        }
    }
}