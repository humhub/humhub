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
        /* @var $userModule \humhub\modules\user\Module */
        $userModule = Yii::$app->getModule('user');

        if (!preg_match($userModule->usernameValidationPattern, $model->$attribute)) {
            $this->addError($model, $attribute, !empty($userModule->usernameValidationErrorText) ? $userModule->usernameValidationErrorText : Yii::t('UserModule.components_UsernameValidator', 'Username can contain only lowercase letters, numbers, hyphens and underscores!'));
        }
    }
}
