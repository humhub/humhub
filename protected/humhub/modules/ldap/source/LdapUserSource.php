<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\source;

use Exception;
use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\source\BaseUserSource;
use humhub\modules\user\source\UserSourceInterface;
use Yii;
use yii\helpers\VarDumper;

/**
 * LdapUserSource manages user provisioning for LDAP-sourced users.
 *
 * Responsible for creating, updating and deleting LDAP users in HumHub.
 * Stores LDAP user associations in the user_auth table.
 *
 * @since 1.19
 */
class LdapUserSource extends BaseUserSource
{
    public function __construct(public readonly LdapAuth $authClient, array $config = [])
    {
        parent::__construct($config);
    }

    public function getId(): string
    {
        return $this->authClient->getId();
    }

    public function getTitle(): string
    {
        return 'LDAP (' . $this->authClient->getId() . ')';
    }

    /**
     * Returns attributes managed by LDAP (profile fields with ldap_attribute set
     * plus user-table attributes configured in syncUserTableAttributes).
     */
    public function getManagedAttributes(): array
    {
        $attributes = $this->authClient->syncUserTableAttributes;

        foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
            $attributes[] = $profileField->internal_name;
        }

        return $attributes;
    }

    public function requiresApproval(): bool
    {
        return false;
    }

    public function getAllowedAuthClientIds(): array
    {
        return $this->authClient->allowedAuthClientIds;
    }

    public function canDeleteAccount(): bool
    {
        return false;
    }

    public function getUsernameStrategy(): string
    {
        return UserSourceInterface::USERNAME_AUTO_GENERATE;
    }

    /**
     * Creates a new HumHub user from LDAP attributes.
     *
     * Requires an 'id' attribute (mapped from idAttribute). Returns null if the
     * ID is missing or validation fails — the caller logs and skips.
     */
    public function createUser(array $attributes): ?User
    {
        if (!isset($attributes['id'])) {
            Yii::warning('Cannot create LDAP user: missing ID attribute (idAttribute not configured?).', 'ldap');
            return null;
        }

        $registration = $this->buildRegistration($attributes);
        if ($registration === null) {
            return null;
        }

        if (!$registration->register()) {
            Yii::warning(
                'Could not create LDAP user. Errors: ' . VarDumper::dumpAsString($registration->getErrors()),
                'ldap',
            );
            return null;
        }

        $user = $registration->getUser();
        $user->updateAttributes(['user_source' => $this->getId()]);

        $auth = new Auth([
            'user_id' => $user->id,
            'source' => $this->getId(),
            'source_id' => (string)$attributes['id'],
        ]);
        if (!$auth->save()) {
            Yii::warning(
                'Could not create user_auth entry for LDAP user ' . $user->id . ': '
                . VarDumper::dumpAsString($auth->getErrors()),
                'ldap',
            );
        }

        return $user;
    }

    private function buildRegistration(array $attributes): ?Registration
    {
        $registration = new Registration(enableEmailField: true, enablePasswordForm: false);
        $registration->enableUserApproval = false;

        unset(
            $attributes['id'],
            $attributes['guid'],
            $attributes['contentcontainer_id'],
            $attributes['user_source'],
            $attributes['status'],
        );

        if (empty($attributes['username'])) {
            $resolved = $this->getUsernameResolver()->resolve($attributes, $this->getUsernameStrategy());
            if ($resolved === null) {
                return null;
            }
            $attributes['username'] = $resolved;
        }

        $registration->getUser()->setAttributes($attributes, false);
        $registration->getProfile()->setAttributes($attributes, false);
        $registration->getGroupUser()->setAttributes($attributes, false);
        $registration->setModels();

        return $registration;
    }

    /**
     * Syncs LDAP attributes to HumHub user and profile fields.
     */
    public function updateUser(User $user, array $attributes): bool
    {
        foreach ($this->getManagedAttributes() as $attributeName) {
            if (isset($attributes[$attributeName])) {
                if ($user->hasAttribute($attributeName)) {
                    $user->setAttribute($attributeName, $attributes[$attributeName]);
                } elseif ($user->profile->hasAttribute($attributeName)) {
                    $user->profile->setAttribute($attributeName, $attributes[$attributeName]);
                }
            } else {
                if ($user->profile->hasAttribute($attributeName)) {
                    $user->profile->setAttribute($attributeName, '');
                }
            }
        }

        if (count($user->getDirtyAttributes()) !== 0 && !$user->save()) {
            Yii::warning(
                'Could not update LDAP user (' . $user->id . '). Error: '
                . VarDumper::dumpAsString($user->getErrors()),
                'ldap',
            );
            return false;
        }

        if (count($user->profile->getDirtyAttributes()) !== 0 && !$user->profile->save()) {
            Yii::warning(
                'Could not update LDAP user profile (' . $user->id . '). Error: '
                . VarDumper::dumpAsString($user->profile->getErrors()),
                'ldap',
            );
            return false;
        }

        return true;
    }

    /**
     * Full LDAP user sync — creates/updates/disables users based on the LDAP directory.
     * Called by LdapSyncJob.
     */
    public function syncUsers(): void
    {
        if ($this->authClient->autoRefreshUsers !== true) {
            return;
        }

        try {
            $ids = [];

            foreach ($this->authClient->getLdapService()->getAuthClients() as $dn => $authClient) {
                $authClientService = new AuthClientService($authClient);
                $user = $authClientService->getUser();

                if ($user === null) {
                    $attributes = $authClient->getUserAttributes();
                    $user = $this->createUser($attributes);
                    if ($user === null) {
                        Yii::warning('Could not automatically create LDAP user - DN: ' . $dn, 'ldap');
                        continue;
                    }
                } else {
                    $authClientService->updateUser($user);
                }

                $attributes = $authClient->getUserAttributes();
                if (isset($attributes['id'])) {
                    $ids[] = $attributes['id'];
                }
            }

            if ($this->authClient->idAttribute !== null) {
                foreach ((new AuthClientService($this->authClient))->getUsersQuery()->each() as $user) {
                    $ldapId = Auth::find()
                        ->select('source_id')
                        ->where(['user_id' => $user->id, 'source' => $this->getId()])
                        ->scalar();

                    $foundInLdap = $ldapId !== null && in_array($ldapId, $ids);

                    if ($foundInLdap && $user->status === User::STATUS_DISABLED) {
                        $this->enableUser($user);
                        Yii::info(
                            'Enabled user: ' . $user->username . ' (' . $user->id . ') - Found in LDAP!',
                            'ldap',
                        );
                    } elseif (!$foundInLdap && $user->status == User::STATUS_ENABLED) {
                        $this->deleteUser($user);
                        Yii::warning(
                            'Disabled user: ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!',
                            'ldap',
                        );
                    }
                }
            }
        } catch (Exception $ex) {
            Yii::error('An error occurred while LDAP user sync: ' . $ex->getMessage(), 'ldap');
        }
    }
}
