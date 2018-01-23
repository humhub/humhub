<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;
use humhub\modules\user\components\CheckPasswordValidator;

/**
 * AccountDelete is the model for account deletion.
 *
 * @since 0.5
 */
class AccountDelete extends \yii\base\Model
{

    /**
     * @var string the current password
     */
    public $currentPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        if (!CheckPasswordValidator::hasPassword()) {
            return [];
        }

        return [
            ['currentPassword', 'required'],
            ['currentPassword', CheckPasswordValidator::className()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'currentPassword' => Yii::t('UserModule.forms_AccountDeleteForm', 'Your password'),
        );
    }

}
