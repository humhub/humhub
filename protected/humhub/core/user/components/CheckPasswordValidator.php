<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\core\user\components;

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
        if (!$user->currentPassword->validatePassword($value)) {
            $object->addError($attribute, Yii::t('UserModule.components_CheckPasswordValidator', "Your password is incorrect!"));
        }
    }

}
