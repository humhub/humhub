<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;
use yii\base\Component;

/**
 * BaseUserSource provides sensible defaults for UserSourceInterface.
 * Concrete sources extend this and override what they need.
 *
 * @since 1.19
 */
abstract class BaseUserSource extends Component implements UserSourceInterface
{
    /**
     * @var string Source ID — used by config-driven sources (e.g. GenericUserSource).
     * Concrete sources with fixed IDs should override getId() instead of relying on this property.
     */
    public string $id = '';

    /**
     * @var string Human-readable title shown in admin UI.
     */
    public string $title = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title ?: $this->getId();
    }

    public function getManagedAttributes(): array
    {
        return [];
    }

    public function requiresApproval(): bool
    {
        return false;
    }

    public function getAllowedAuthClientIds(): array
    {
        return [];
    }

    public function allowEmailAutoLink(): bool
    {
        return true;
    }

    public function canDeleteAccount(): bool
    {
        return true;
    }

    public function getUsernameStrategy(): string
    {
        return UserSourceInterface::USERNAME_AUTO_GENERATE;
    }

    public function deleteUser(User $user): bool
    {
        $user->status = User::STATUS_DISABLED;
        return $user->save();
    }

    public function enableUser(User $user): bool
    {
        $user->status = User::STATUS_ENABLED;
        return $user->save();
    }

    public function updateUser(User $user, array $attributes): bool
    {
        return true;
    }

    protected function getUsernameResolver(): UsernameResolver
    {
        return new UsernameResolver();
    }
}
