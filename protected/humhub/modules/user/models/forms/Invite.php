<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\LinkRegistrationService;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Url;
use yii\validators\EmailValidator;
use yii\validators\StringValidator;

/**
 * Invite Form Model
 *
 * @since 1.1
 */
class Invite extends Model
{
    /**
     * @var string Target where this form is used
     */
    public string $target = LinkRegistrationService::TARGET_PEOPLE;

    /**
     * @var string user's username or email address
     */
    public $emails;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['emails'], 'required'],
            ['emails', 'checkEmails'],
        ];
    }

    /**
     * Checks a comma separated list of e-mails which should invited.
     * E-Mails needs to be valid and not already registered.
     *
     * @param string $attribute
     */
    public function checkEmails($attribute)
    {
        if (empty($this->$attribute)) {
            return;
        }

        foreach ($this->getEmails() as $email) {
            $validator = new StringValidator(['max' => 150]);
            if (!$validator->validate($email)) {
                $this->addError($attribute, Yii::t('UserModule.invite', '{email} should contain at most {charNum} characters.', ['email' => $email, 'charNum' => 150]));
                continue;
            }

            $validator = new EmailValidator();
            if (!$validator->validate($email)) {
                $this->addError($attribute, Yii::t('UserModule.invite', '{email} is not valid!', ['email' => $email]));
                continue;
            }

            if (User::find()->where(['email' => $email])->exists()) {
                $this->addError($attribute, Yii::t('UserModule.invite', '{email} is already registered!', ['email' => $email]));
            }
        }
    }

    /**
     * E-Mails entered in form
     *
     * @return array the emails
     */
    public function getEmails(): array
    {
        $emails = [];
        foreach (explode(',', $this->emails) as $email) {
            $emails[] = trim($email);
        }

        return $emails;
    }

    /**
     * Checks if external user invitation setting is enabled
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function canInviteByEmail(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return (!Yii::$app->user->isGuest && $module->settings->get('auth.internalUsersCanInviteByEmail'))
            || Yii::$app->user->isAdmin()
            || Yii::$app->user->can([ManageUsers::class, ManageGroups::class]);
    }

    /**
     * Checks if external user invitation setting is enabled
     *
     * @return bool
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function canInviteByLink(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (!Yii::$app->user->isGuest && $module->settings->get('auth.internalUsersCanInviteByLink')) {
            return true;
        }

        if ($this->target === LinkRegistrationService::TARGET_ADMIN) {
            // Admins always can invite by link
            return Yii::$app->user->isAdmin() || Yii::$app->user->can([ManageUsers::class, ManageGroups::class]);
        }

        return false;
    }

    /**
     * @param bool $forceResetToken
     * @return string
     * @throws Exception
     */
    public function getInviteLink(bool $forceResetToken = false): string
    {
        $linkRegistrationService = new LinkRegistrationService();
        $linkRegistrationService->target = $this->target;
        $token = $linkRegistrationService->getStoredToken();
        if ($forceResetToken || !$token) {
            $token = $linkRegistrationService->setNewToken();
        }

        return Url::to(['/user/registration/by-link', 'token' => $token], true);
    }
}
