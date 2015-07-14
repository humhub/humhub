<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use yii\validators\Validator;

/**
 * CheckPasswordValidator checks password of currently logged in user.
 *
 * @author luke
 */
class CheckPasswordValidator extends Validator
{

    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        $user = Yii::$app->user->getIdentity();
        if ($user->currentPassword !== null && !$user->currentPassword->validatePassword($value)) {
            $object->addError($attribute, Yii::t('UserModule.components_CheckPasswordValidator', "Your password is incorrect!"));
        }
    }

}
