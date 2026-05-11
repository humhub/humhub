<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\source;

use Exception;
use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\source\BaseUserSource;
use humhub\modules\user\source\UserSourceInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

/**
 * LdapUserSource manages provisioning for users from a single LDAP connection.
 *
 * One instance per connection ID — the source is created/registered by the
 * LDAP module's bootstrap based on the {@see LdapConnectionRegistry}.
 *
 * @since 1.19
 */
class LdapUserSource extends BaseUserSource
{
    /**
     * @var string|null The connection (and source) ID this source provisions for.
     * Required — must match an entry in {@see LdapConnectionRegistry}.
     */
    public ?string $connectionId = null;

    public function init()
    {
        parent::init();

        if ($this->connectionId === null || $this->connectionId === '') {
            throw new InvalidConfigException(self::class . ' requires a non-empty $connectionId.');
        }
        if ($this->id === '') {
            $this->id = $this->connectionId;
        }
        if ($this->allowedAuthClientIds === []) {
            $this->allowedAuthClientIds = [$this->connectionId];
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title !== '' ? $this->title : $this->getConfig()->title;
    }

    public function getConfig(): LdapConnectionConfig
    {
        return $this->getModule()->getConnectionRegistry()->getConfig($this->connectionId);
    }

    public function getLdapService(): LdapService
    {
        return $this->getModule()->getConnectionRegistry()->getService($this->connectionId);
    }

    private function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        return $module;
    }

    /**
     * Resolves the AuthClient that maps LDAP attributes for this source.
     * The AuthClient handles the normalisation logic (id/username/email/profile
     * fields), even when sync runs independently of an actual login.
     */
    private function getAuthClient(): LdapAuth
    {
        /** @var LdapAuth $client */
        $client = Yii::$app->authClientCollection->getClient($this->connectionId);
        return $client;
    }

    public function getManagedAttributes(): array
    {
        $attributes = $this->getConfig()->syncUserTableAttributes;

        foreach (ProfileField::find()->andWhere(['!=', 'ldap_attribute', ''])->all() as $profileField) {
            $attributes[] = $profileField->internal_name;
        }

        return $attributes;
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

        // Set user_source before save so EVENT_AFTER_CREATE listeners see the
        // correct source rather than the default 'local'.
        $registration->getUser()->user_source = $this->getId();

        if (!$registration->register()) {
            Yii::warning(
                'Could not create LDAP user. Errors: ' . VarDumper::dumpAsString($registration->getErrors()),
                'ldap',
            );
            return null;
        }

        $user = $registration->getUser();

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
        $registration->enableUserApproval = $this->requiresApproval($this->connectionId);

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
            } elseif ($user->profile->hasAttribute($attributeName)) {
                $user->profile->setAttribute($attributeName, '');
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
        $config = $this->getConfig();
        if (!$config->autoRefreshUsers) {
            return;
        }

        try {
            $service = $this->getLdapService();
            $authClient = $this->getAuthClient();

            $ids = [];

            foreach ($service->getAllUserEntries() as $entry) {
                $dn = $entry['dn'] ?? '?';

                // Each entry gets its own client clone so attribute state doesn't leak.
                $clientForUser = clone $authClient;
                $clientForUser->init();
                $clientForUser->setUserAttributes($entry);

                $authClientService = new AuthClientService($clientForUser);
                $user = $authClientService->getUser();
                $attributes = $clientForUser->getUserAttributes();

                if ($user === null) {
                    $user = $this->createUser($attributes);
                    if ($user === null) {
                        Yii::warning('Could not automatically create LDAP user - DN: ' . $dn, 'ldap');
                        continue;
                    }
                } else {
                    $authClientService->updateUser($user);
                }

                if (isset($attributes['id'])) {
                    $ids[] = $attributes['id'];
                }
            }

            $this->syncUserStatuses($ids);
        } catch (Exception $ex) {
            Yii::error('An error occurred while LDAP user sync: ' . $ex->getMessage(), 'ldap');
        }
    }

    /**
     * Re-enables users present in LDAP and disables users no longer found.
     * No-op when the connection has no idAttribute (we'd risk re-enabling users
     * that haven't been mapped yet).
     */
    private function syncUserStatuses(array $ldapIds): void
    {
        if ($this->getConfig()->idAttribute === null) {
            return;
        }

        $query = User::find()->andWhere(['user_source' => $this->getId()]);

        foreach ($query->each() as $user) {
            $ldapId = Auth::find()
                ->select('source_id')
                ->where(['user_id' => $user->id, 'source' => $this->getId()])
                ->scalar();

            $foundInLdap = $ldapId !== null && in_array($ldapId, $ldapIds, true);

            if ($foundInLdap && $user->status === User::STATUS_DISABLED) {
                $this->enableUser($user);
                Yii::info(
                    'Enabled user: ' . $user->username . ' (' . $user->id . ') - Found in LDAP!',
                    'ldap',
                );
            } elseif (!$foundInLdap && $user->status === User::STATUS_ENABLED) {
                $this->deleteUser($user);
                Yii::warning(
                    'Disabled user: ' . $user->username . ' (' . $user->id . ') - Not found in LDAP!',
                    'ldap',
                );
            }
        }
    }
}
