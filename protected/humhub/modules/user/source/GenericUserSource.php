<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;
use Yii;
use yii\helpers\VarDumper;

/**
 * GenericUserSource is a fully config-driven UserSource for REST-API-based
 * provisioning or custom integrations that do not require custom code.
 *
 * Example config/common.php:
 *
 * ```php
 * 'userSourceCollection' => [
 *     'userSources' => [
 *         'hr_system' => [
 *             'class' => GenericUserSource::class,
 *             'title' => 'Workday HR',
 *             'managedAttributes' => ['email', 'firstname', 'lastname'],
 *             'approval' => false,
 *             'allowedAuthClientIds' => ['local', 'saml-sso'],
 *         ],
 *     ],
 * ],
 * ```
 *
 * @since 1.19
 */
class GenericUserSource extends BaseUserSource
{
    public array $managedAttributes = [];
    public bool $approval = false;
    public bool $deleteAccount = true;
    public bool $emailAutoLink = true;
    public string $usernameStrategy = UserSourceInterface::USERNAME_AUTO_GENERATE;
    public array $allowedAuthClientIds = [];

    public function getManagedAttributes(): array
    {
        return $this->managedAttributes;
    }

    public function requiresApproval(): bool
    {
        return $this->approval;
    }

    public function getAllowedAuthClientIds(): array
    {
        return $this->allowedAuthClientIds;
    }

    public function allowEmailAutoLink(): bool
    {
        return $this->emailAutoLink;
    }

    public function canDeleteAccount(): bool
    {
        return $this->deleteAccount;
    }

    public function getUsernameStrategy(): string
    {
        return $this->usernameStrategy;
    }

    public function createUser(array $attributes): ?User
    {
        $user = new User();
        $user->user_source = $this->getId();

        if (empty($attributes['username'])) {
            $resolved = $this->getUsernameResolver()->resolve($attributes, $this->getUsernameStrategy());
            if ($resolved === null) {
                Yii::warning('GenericUserSource (' . $this->getId() . '): could not resolve username.', 'user');
                return null;
            }
            $attributes['username'] = $resolved;
        }

        $user->setAttributes($attributes, false);

        if (!$user->save()) {
            Yii::warning(
                'GenericUserSource (' . $this->getId() . '): could not create user. Errors: '
                . VarDumper::dumpAsString($user->getErrors()),
                'user',
            );
            return null;
        }

        $user->profile->setAttributes($attributes, false);
        $user->profile->save();

        return $user;
    }

    public function updateUser(User $user, array $attributes): bool
    {
        $managed = $this->getManagedAttributes();
        if (empty($managed)) {
            return true;
        }

        foreach ($managed as $attr) {
            if (!isset($attributes[$attr])) {
                continue;
            }
            if ($user->hasAttribute($attr)) {
                $user->setAttribute($attr, $attributes[$attr]);
            } elseif ($user->profile->hasAttribute($attr)) {
                $user->profile->setAttribute($attr, $attributes[$attr]);
            }
        }

        if ($user->getDirtyAttributes() && !$user->save()) {
            Yii::warning(
                'GenericUserSource (' . $this->getId() . '): could not update user (' . $user->id . '). Errors: '
                . VarDumper::dumpAsString($user->getErrors()),
                'user',
            );
            return false;
        }

        if ($user->profile->getDirtyAttributes() && !$user->profile->save()) {
            Yii::warning(
                'GenericUserSource (' . $this->getId() . '): could not update profile (' . $user->id . '). Errors: '
                . VarDumper::dumpAsString($user->profile->getErrors()),
                'user',
            );
            return false;
        }

        return true;
    }
}
