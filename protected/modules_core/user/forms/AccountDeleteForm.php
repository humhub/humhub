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

/**
 * Form Model for account deletion
 * 
 * @package humhub.modules_core.user.forms
 * @since 0.5
 */
class AccountDeleteForm extends CFormModel {

    public $currentPassword;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('currentPassword', 'required'),
            array('currentPassword', 'CheckPasswordValidator'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'currentPassword' => Yii::t('UserModule.forms_AccountDeleteForm', 'Your password'),
        );
    }

}
