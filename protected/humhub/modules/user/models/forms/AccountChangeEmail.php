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

namespace humhub\modules\user\models\forms;

use Yii;
use yii\helpers\Url;
use humhub\models\Setting;

/**
 * Form Model for email change
 *
 * @package humhub.modules_core.user.forms
 * @since 0.5
 */
class AccountChangeEmail extends \yii\base\Model
{

    public $currentPassword;
    public $newEmail;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['currentPassword', 'newEmail'], 'required'),
            array('currentPassword', \humhub\modules\user\components\CheckPasswordValidator::className()),
            array('newEmail', 'email'),
            array('newEmail', 'unique', 'targetAttribute' => 'email', 'targetClass' => \humhub\modules\user\models\User::className(), 'message' => '{attribute} "{value}" is already in use!'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'currentPassword' => Yii::t('UserModule.forms_AccountChangeEmailForm', 'Current password'),
            'newEmail' => Yii::t('UserModule.forms_AccountChangeEmailForm', 'New E-Mail address'),
        );
    }

    /**
     * Sends Change E-Mail E-Mail
     *
     */
    public function sendChangeEmail()
    {
        $user = Yii::$app->user->getIdentity();

        $token = md5(Setting::Get('secret') . $user->guid . $this->newEmail);

        $mail = Yii::$app->mailer->compose([
			'html' => '@humhub/modules/user/views/mails/ChangeEmail',
			'text' => '@humhub/modules/user/views/mails/plaintext/ChangeEmail'
		], [
            'user' => $user,
            'newEmail' => $this->newEmail,
            'approveUrl' => Url::to(["/user/account/change-email-validate", 'email' => $this->newEmail, 'token' => $token], true)
        ]);
        $mail->setFrom([\humhub\models\Setting::Get('systemEmailAddress', 'mailing') => \humhub\models\Setting::Get('systemEmailName', 'mailing')]);
        $mail->setTo($this->newEmail);
        $mail->setSubject(Yii::t('UserModule.forms_AccountChangeEmailForm', 'E-Mail change'));
        $mail->send();
        
        return true;
    }

}
