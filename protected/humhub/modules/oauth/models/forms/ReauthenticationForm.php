<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models\forms;

use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Confirms the current user before an action that hands out a credential. Forms creating
 * such a credential extend it and merge [[rules()]] and [[attributeLabels()]].
 *
 * @since 1.20
 */
class ReauthenticationForm extends Model
{
    public $currentPassword;

    private ?RecentAuthentication $recentAuthentication = null;

    public function __construct(protected readonly User $user, $config = [])
    {
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            ['currentPassword', 'validateReauthentication', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'currentPassword' => Yii::t('OauthModule.base', 'Your password'),
        ];
    }

    public function requiresPassword(): bool
    {
        return $this->getRecentAuthentication()->requiresPassword();
    }

    public function validateReauthentication(string $attribute): void
    {
        $authentication = $this->getRecentAuthentication();
        if ($authentication->isThrottled()) {
            $this->addError($attribute, Yii::t('OauthModule.base', 'Too many attempts, try again later.'));
            return;
        }

        if ($authentication->check(is_string($this->currentPassword) ? $this->currentPassword : null)) {
            return;
        }

        $this->addError($attribute, $this->requiresPassword()
            ? Yii::t('OauthModule.base', 'Your password is incorrect.')
            : Yii::t('OauthModule.base', 'Please sign out and sign in again to continue.'));
    }

    private function getRecentAuthentication(): RecentAuthentication
    {
        return $this->recentAuthentication ??= RecentAuthentication::forCurrentSession($this->user);
    }
}
