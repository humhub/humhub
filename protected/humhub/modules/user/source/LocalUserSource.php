<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\authclient\ClientInterface;

/**
 * LocalUserSource handles self-registered and admin-created users.
 *
 * createUser() delegates to Registration::register(). On validation failure
 * null is returned and the caller (AuthController) redirects to the
 * registration form.
 *
 * @since 1.19
 */
class LocalUserSource extends BaseUserSource
{
    public function getId(): string
    {
        return 'local';
    }

    public function getTitle(): string
    {
        return Yii::t('UserModule.base', 'Local');
    }

    public function requiresApproval(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        return (bool) $module->settings->get('auth.needApproval');
    }

    public function getUsernameStrategy(): string
    {
        return UserSourceInterface::USERNAME_REQUIRE;
    }

    public function canDeleteAccount(): bool
    {
        return true;
    }

    /**
     * Creates a user via the Registration form model.
     * Returns null if validation fails — the caller should redirect to the
     * registration form in that case.
     *
     * @param ClientInterface|null $authClient optional auth client that triggered registration
     */
    public function createUser(array $attributes, ?ClientInterface $authClient = null): ?User
    {
        $registration = $this->buildRegistration($attributes);
        if ($registration === null) {
            return null;
        }

        if (!$registration->register($authClient)) {
            return null;
        }

        return $registration->getUser();
    }

    private function buildRegistration(array $attributes): ?Registration
    {
        $registration = new Registration(enableEmailField: true, enablePasswordForm: false);

        // Remove unsafe / system-managed attributes
        unset(
            $attributes['id'],
            $attributes['guid'],
            $attributes['contentcontainer_id'],
            $attributes['user_source'],
            $attributes['status'],
        );

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);
        $registration->getGroupUser()->setAttributes($attributes, false);
        $registration->setModels();

        return $registration;
    }

    public function updateUser(User $user, array $attributes): bool
    {
        return true;
    }

}
