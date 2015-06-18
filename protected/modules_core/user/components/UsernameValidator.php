<?php

/**
 * HumHub
 * Copyright © 2015 The HumHub Project
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

/**
 * UsernameValidator checks the use of the available characters in the username and length of the username.
 *
 * @author lifard(zagran)
 */
class UsernameValidator extends CValidator {

    protected function validateAttribute($object, $attribute) {
        $value = $object->$attribute;
        $pattern = '/[a-z0-9A-Z0-9äöüÄÜÖß\+\-\._ ]/';
        if (!preg_match($pattern, $value)) 
        {
        	$object->addError($attribute, Yii::t('UserModule.models_User', 'Username can contain only letters, numbers, spaces and special characters (+-._)'));
        }
    }

}
