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
 * Form Model for email change
 * 
 * @package humhub.modules_core.user.forms
 * @since 0.5
 */
class AccountChangeEmailForm extends CFormModel {

    public $currentPassword;
    public $newEmail;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('currentPassword, newEmail', 'required'),
            array('currentPassword', 'CheckPasswordValidator'),
            array('newEmail', 'email'),
            array('newEmail', 'unique', 'attributeName' => 'email', 'caseSensitive' => false, 'className' => 'User', 'message' => '{attribute} "{value}" is already in use!'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'currentPassword' => Yii::t('UserModule.forms_AccountChangeEmailForm', 'Current password'),
            'newEmail' => Yii::t('UserModule.forms_AccountChangeEmailForm', 'New E-Mail address'),
        );
    }

    /**
     * Sends Change E-Mail E-Mail
     *
     */
    public function sendChangeEmail() {

        if ($this->validate()) {

            $user = User::model()->findByPk(Yii::app()->user->id);

            $token = md5(HSetting::Get('secret') . $user->guid . $this->newEmail);

            $message = new HMailMessage();
            $message->view = "application.modules_core.user.views.mails.ChangeEmail";
            $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
            $message->addTo($this->newEmail);
            $message->subject = Yii::t('UserModule.forms_AccountChangeEmailForm', 'E-Mail change');
            $message->setBody(array('user' => $user, 'newEmail' => $this->newEmail, 'token' => $token), 'text/html');
            Yii::app()->mail->send($message);
        }
    }

}
