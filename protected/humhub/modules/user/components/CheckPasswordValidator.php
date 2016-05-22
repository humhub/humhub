<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
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

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        $user = Yii::$app->user->getIdentity();
        if ($user->currentPassword !== null && !$user->currentPassword->validatePassword($value)) {
            $object->addError($attribute, Yii::t('UserModule.components_CheckPasswordValidator', "Your password is incorrect!"));
        }
    }

    /**
     * Checks if current user has a password set.
     * 
     * @return boolean
     */
    public static function hasPassword()
    {
        $user = Yii::$app->user->getIdentity();
        
        if ($user === null) {
            return false;
        }
        
        return ($user->currentPassword !== null);
    }

}
