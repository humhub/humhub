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

/**
 * LocalUserSource handles self-registered and admin-created users, and acts as
 * the default fallback source for any AuthClient that is not explicitly claimed
 * by another UserSource.
 *
 * Attribute sync from external auth clients (e.g. SAML SSO) is opt-in: configure
 * `$allowedAuthClientIds` and `$managedAttributes` to enable it.
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

    public function requiresApproval(?string $authClientId = null): bool
    {
        if ($authClientId !== null && in_array($authClientId, $this->trustedAuthClientIds, true)) {
            return false;
        }
        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        return (bool) $module->settings->get('auth.needApproval');
    }

    public function getUsernameStrategy(): string
    {
        return UserSourceInterface::USERNAME_REQUIRE;
    }

    /**
     * Creates a user via the Registration form model.
     *
     * Returns null if validation fails — the caller should redirect to the
     * registration form so the user can supply missing data.
     */
    public function createUser(array $attributes): ?User
    {
        $registration = $this->buildRegistration($attributes);
        if ($registration === null) {
            return null;
        }

        if (!$registration->register()) {
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
}
