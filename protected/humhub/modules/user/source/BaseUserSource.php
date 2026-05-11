<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;
use Yii;
use yii\base\Component;
use yii\helpers\VarDumper;

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

    /**
     * @var array Attribute names owned by this source. Locked in the profile UI
     * and used by the default updateUser() to know which attributes to apply
     * from incoming sync data.
     */
    public array $managedAttributes = [];

    /**
     * @var array AuthClient IDs that this source is responsible for.
     *
     * Used in three places:
     *  - Login authorisation (existing user logging in via this client)
     *  - Sync gate (only listed clients may push attributes via updateUser())
     *  - createUser dispatch (a new user authenticated via a listed client
     *    is created in this source)
     *
     * Empty array means "all clients allowed" for login and sync — but does
     * NOT claim ownership for createUser dispatch (LocalUserSource is the
     * implicit fallback there).
     */
    public array $allowedAuthClientIds = [];

    /**
     * @var bool Whether new users from this source require admin approval.
     */
    public bool $approval = false;

    /**
     * @var array AuthClient IDs whose login flow bypasses the approval requirement
     * even when $approval is true. Use this to let trusted auth clients (e.g. SAML,
     * LDAP) skip approval while keeping it enabled for form-based self-registration.
     */
    public array $trustedAuthClientIds = [];

    /**
     * @var bool Whether users from this source may delete their own account.
     */
    public bool $deleteAccount = true;

    /**
     * @var bool Whether existing users may be linked to this source by email
     * on first login via a matching auth client.
     */
    public bool $emailAutoLink = true;

    /**
     * @var string Username strategy — see UserSourceInterface constants.
     */
    public string $usernameStrategy = UserSourceInterface::USERNAME_AUTO_GENERATE;

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
        return $this->managedAttributes;
    }

    public function requiresApproval(?string $authClientId = null): bool
    {
        if ($authClientId !== null && in_array($authClientId, $this->trustedAuthClientIds, true)) {
            return false;
        }
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

    /**
     * Default delete: soft-disable the user.
     */
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

    /**
     * Default sync: write each managed attribute that is present in $attributes
     * onto either the user or its profile and save changed records.
     *
     * Sources with non-trivial mapping (e.g. LDAP group/space sync) override this.
     */
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
                'UserSource (' . $this->getId() . '): could not update user (' . $user->id . '). Errors: '
                . VarDumper::dumpAsString($user->getErrors()),
                'user',
            );
            return false;
        }

        if ($user->profile->getDirtyAttributes() && !$user->profile->save()) {
            Yii::warning(
                'UserSource (' . $this->getId() . '): could not update profile (' . $user->id . '). Errors: '
                . VarDumper::dumpAsString($user->profile->getErrors()),
                'user',
            );
            return false;
        }

        return true;
    }

    protected function getUsernameResolver(): UsernameResolver
    {
        return new UsernameResolver();
    }
}
