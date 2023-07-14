<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\interfaces\StatableInterface;
use humhub\modules\user\components\CheckPasswordValidator;
use humhub\modules\user\jobs\SoftDeleteUser;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * AccountDelete is the model for account deletion.
 *
 * @since 0.5
 */
class AccountDelete extends Model
{
    /**
     * @var string the current password
     */
    public $currentPassword;

    /**
     * @since 1.3
     * @var User the user
     */
    public $user;

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
            ['currentPassword', CheckPasswordValidator::class, 'user' => $this->user],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => Yii::t('UserModule.auth', 'Your password'),
        ];
    }

    /**
     * Perform user deletion
     * @since 1.3
     */
    public function performDelete()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->status = StatableInterface::STATE_SOFT_DELETED;
        $this->user->save();

        Yii::$app->queue->push(new SoftDeleteUser(['user_id' => $this->user->id]));

        return true;
    }
}
