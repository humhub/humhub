<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\events\UserEvent;
use humhub\modules\user\models\User;
use humhub\modules\user\source\UserSourceCollection;
use humhub\modules\user\source\UserSourceInterface;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;

/**
 * UserSourceService provides helper methods for working with UserSources
 * on behalf of a specific user.
 *
 * @since 1.19
 */
class UserSourceService
{
    /**
     * @event UserEvent triggered on UserSourceService after a user is created by a UserSource.
     * Listen via: Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_CREATE, $handler)
     * @since 1.19
     */
    public const EVENT_AFTER_CREATE = 'afterUserSourceCreate';

    /**
     * @event UserEvent triggered on UserSourceService after a user is updated by a UserSource.
     * Listen via: Event::on(UserSourceService::class, UserSourceService::EVENT_AFTER_UPDATE, $handler)
     * @since 1.19
     */
    public const EVENT_AFTER_UPDATE = 'afterUserSourceUpdate';

    public function __construct(public readonly User $user)
    {
    }

    /**
     * Returns the UserSource instance responsible for this user.
     *
     * Falls back to LocalUserSource when the configured source is unavailable
     * (e.g. module disabled or removed from config). This means the user can
     * still log in, no attribute sync is performed, and all auth clients are
     * permitted — a safe degraded state rather than a hard failure.
     */
    public function getUserSource(): UserSourceInterface
    {
        /** @var UserSourceCollection $collection */
        $collection = Yii::$app->userSourceCollection;

        $sourceId = $this->user->user_source ?? 'local';

        try {
            return $collection->getUserSource($sourceId);
        } catch (InvalidArgumentException) {
            Yii::warning("UserSource '{$sourceId}' not found for user {$this->user->id}, falling back to 'local'.", 'user');
            return $collection->getLocalUserSource();
        }
    }

    /**
     * Updates the user via its UserSource and fires EVENT_AFTER_UPDATE.
     */
    public function updateUser(array $attributes): bool
    {
        $result = $this->getUserSource()->updateUser($this->user, $attributes);
        Event::trigger(self::class, self::EVENT_AFTER_UPDATE, new UserEvent(['user' => $this->user]));
        return $result;
    }

    /**
     * Fires EVENT_AFTER_CREATE on UserSourceService.
     * Called by AuthClientService after a UserSource has created the user.
     */
    public static function triggerAfterCreate(User $user): void
    {
        Event::trigger(self::class, self::EVENT_AFTER_CREATE, new UserEvent(['user' => $user]));
    }

    /**
     * Returns all attribute names that are managed (read-only) for this user.
     * Used by profile forms and edit views to mark fields as non-editable.
     */
    public function getManagedAttributes(): array
    {
        return $this->getUserSource()->getManagedAttributes();
    }

    /**
     * Returns whether a given attribute is managed (read-only) for this user.
     */
    public function isManagedAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->getManagedAttributes(), true);
    }

    public function canChangeUsername(): bool
    {
        return !$this->isManagedAttribute('username');
    }

    public function canChangeEmail(): bool
    {
        return !$this->isManagedAttribute('email');
    }

    public function canChangePassword(): bool
    {
        $allowed = $this->getUserSource()->getAllowedAuthClientIds();
        return empty($allowed) || in_array('local', $allowed, true);
    }

    public function canDeleteAccount(): bool
    {
        return $this->getUserSource()->canDeleteAccount();
    }

    /**
     * Returns whether a given auth client is permitted for this user's source.
     * An empty allowed list means all clients are permitted.
     */
    public function isAuthClientAllowed(string $clientId): bool
    {
        $allowed = $this->getUserSource()->getAllowedAuthClientIds();
        return empty($allowed) || in_array($clientId, $allowed, true);
    }

    /**
     * Returns the UserSourceCollection application component.
     */
    public static function getCollection(): UserSourceCollection
    {
        return Yii::$app->userSourceCollection;
    }

    /**
     * Returns a UserSourceService for the given user, or the currently logged-in user if omitted.
     */
    public static function getForUser(?User $user = null): self
    {
        return new self($user ?? Yii::$app->user->getIdentity());
    }
}
