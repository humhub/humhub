<?php

namespace humhub\modules\user\models\forms;

use Yii;

/**
 * Register Form just collects users e-mail and sends an invite
 *
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountRegister extends \yii\base\Model
{

    public $email;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => \humhub\modules\user\models\User::className(), 'message' => Yii::t('UserModule.forms_AccountRegisterForm', 'E-Mail is already in use! - Try forgot password.')],
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
            'email' => Yii::t('UserModule.forms_AccountRegisterForm', 'E-Mail'),
        );
    }

}
