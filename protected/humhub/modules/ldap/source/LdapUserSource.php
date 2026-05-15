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
use humhub\modules\user\services\AuthClientUserService;
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
        // Default to allowing LDAP login when no explicit list was configured
        // (e.g. direct LdapUserSource instantiation without an admin-UI save).
        // The admin UI controls 'ldap' as a regular entry in the list, so it
        // must NOT be re-added unconditionally — an empty list passed in is
        // a deliberate "all clients permitted" config, distinguished here only
        // from "absolutely nothing configured at all".
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
     *
     * Match is done via {@see $connectionId} — that's the stable invariant
     * between source and auth client. Their public IDs may differ when an
     * admin overrides them through config.
     */
    private function getAuthClient(): LdapAuth
    {
        foreach (Yii::$app->authClientCollection->getClients() as $client) {
            if ($client instanceof LdapAuth && $client->connectionId === $this->connectionId) {
                return $client;
            }
        }
        throw new InvalidConfigException(
            "No LdapAuth registered for LDAP connection '{$this->connectionId}'.",
        );
    }

    /**
     * Claims user creation only when the directory actually contains the user.
     *
     * For the source's own LDAP auth client the user is by definition an LDAP
     * user (the client's `id` attribute IS the directory ID). For foreign
     * clients also listed in `allowedAuthClientIds` (e.g. OpenID/SAML allowed
     * for LDAP users), we verify the email exists in the directory before
     * claiming — otherwise an unrelated external user would get
     * `user_source = 'ldap'` and collide with the real LDAP user on the
     * next direct LDAP login.
     */
    public function claimsUserCreation(string $authClientId, array $attributes): bool
    {
        if (!parent::claimsUserCreation($authClientId, $attributes)) {
            return false;
        }
        if ($authClientId === $this->connectionId) {
            return true;
        }

        $email = $attributes['email'] ?? null;
        if (!is_string($email) || $email === '') {
            return false;
        }

        try {
            return $this->getLdapService()->getUserDn($email) !== null;
        } catch (Exception $ex) {
            Yii::warning(
                'LdapUserSource (' . $this->getId() . '): directory lookup failed during claim check: '
                . $ex->getMessage(),
                'ldap',
            );
            return false;
        }
    }

    /**
     * Resolves the HumHub user that corresponds to the given LDAP attribute
     * set, with self-healing semantics:
     *
     *  1. Primary lookup via the user_auth table (source + source_id).
     *  2. If that misses, falls back to matching by user_source + email,
     *     objectguid or username — typical when the LDAP entry has been
     *     deleted and re-created (objectGuid changes, the user's email is
     *     preserved) or for users imported pre-1.19 that don't yet have a
     *     user_auth row.
     *  3. When the fallback hits and a new LDAP unique ID is available,
     *     the user_auth row is transparently rewritten to the new ID and
     *     a warning is logged. This prevents stale rows from leaving a
     *     "ghost" HumHub user that blocks re-provisioning via email
     *     uniqueness.
     *
     * When the LDAP unique ID is stable and only the email changed, the
     * primary lookup hits as before — no reassign happens, the email
     * update flows through the regular attribute sync.
     *
     * Returns null only when no matching user exists at all; the caller
     * (login flow, sync job) may then proceed to create a new user.
     */
    public function findUser(array $attributes): ?User
    {
        $sourceId = isset($attributes['id']) ? (string) $attributes['id'] : null;

        if ($sourceId !== null) {
            $auth = Auth::find()
                ->where(['source' => $this->getId(), 'source_id' => $sourceId])
                ->one();
            if ($auth !== null) {
                return $auth->user;
            }
        }

        // Match on the *normalised* keys ('email', 'username', 'id') — those
        // reflect the connection's configured emailAttribute / usernameAttribute /
        // idAttribute mappings, not any specific LDAP-server flavour
        // (Active Directory's objectGuid, OpenLDAP's uid, etc.).
        $conditions = ['OR'];
        if (!empty($attributes['email'])) {
            $conditions[] = ['email' => $attributes['email']];
        }
        if (!empty($attributes['username'])) {
            $conditions[] = ['username' => $attributes['username']];
        }
        if ($sourceId !== null) {
            // Legacy fallback: pre-1.19 LDAP integrations stored the LDAP
            // unique id in user.guid before the user_auth table existed.
            $conditions[] = ['guid' => $sourceId];
        }
        if (count($conditions) <= 1) {
            return null;
        }

        $user = User::find()
            ->where(['user_source' => $this->getId()])
            ->andWhere($conditions)
            ->one();
        if ($user === null) {
            return null;
        }

        if ($sourceId !== null) {
            $this->reassignSourceId($user, $sourceId);
        }
        return $user;
    }

    /**
     * Updates (or creates) the user_auth row so its source_id points at the
     * current LDAP unique ID. Logs a warning when a previous, different ID
     * was on file — the diagnostic trail for admins investigating a
     * deleted-and-re-created LDAP entry.
     */
    private function reassignSourceId(User $user, string $newSourceId): void
    {
        $auth = Auth::find()
            ->where(['user_id' => $user->id, 'source' => $this->getId()])
            ->one();

        if ($auth === null) {
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => $this->getId(),
                'source_id' => $newSourceId,
            ]);
            if (!$auth->save()) {
                Yii::warning(
                    'LdapUserSource (' . $this->getId() . '): could not record LDAP id for user '
                    . $user->username . ' (' . $user->id . '); errors: '
                    . VarDumper::dumpAsString($auth->getErrors()),
                    'ldap',
                );
            }
            return;
        }

        if ($auth->source_id === $newSourceId) {
            return;
        }

        $oldId = $auth->source_id;
        $auth->source_id = $newSourceId;
        if (!$auth->save()) {
            Yii::warning(
                'LdapUserSource (' . $this->getId() . '): could not update LDAP id for user '
                . $user->username . ' (' . $user->id . '); old=' . $oldId . ' new=' . $newSourceId
                . '; errors: ' . VarDumper::dumpAsString($auth->getErrors()),
                'ldap',
            );
            return;
        }

        Yii::warning(
            'LdapUserSource (' . $this->getId() . '): rewrote LDAP id for user '
            . $user->username . ' (' . $user->id . ') — old=' . $oldId . ' new=' . $newSourceId
            . '. Matched by email/guid/username; LDAP entry was likely deleted and re-created.',
            'ldap',
        );
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
     * Creates a new HumHub user with user_source = this source.
     *
     * Does NOT record a user_auth row. The caller knows which auth client was
     * used and must record the row through {@see AuthClientUserService::add()}
     * — which writes the right `source_id` for the actual auth client. This
     * matters when the source is dispatched by a foreign auth client (e.g.
     * OpenID matched to the LDAP source by email): writing the LDAP objectGuid
     * row here from OpenID attributes would store the OpenID subject as the
     * LDAP source_id and break the next direct LDAP login.
     */
    public function createUser(array $attributes): ?User
    {
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

        return $registration->getUser();
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
            $authClientService = new AuthClientService($authClient);

            $ids = [];

            foreach ($service->getAllUserEntries() as $entry) {
                $dn = $entry['dn'] ?? '?';

                // Single AuthClient instance, attributes overwritten per
                // iteration. setUserAttributes() re-normalises and replaces
                // the cached value, so nothing leaks between users.
                // (Cloning + re-init() on every entry would re-fire init-time
                // event subscriptions in subclasses, which is what subscribed
                // module code relies on NOT happening.)
                $authClient->setUserAttributes($entry);
                $attributes = $authClient->getUserAttributes();
                // Self-healing lookup: primary user_auth hit, fallback per
                // email/guid/username, transparent reassign on LDAP-id
                // change. Avoids the "ghost user blocking createUser via
                // email uniqueness" support case.
                $user = $this->findUser($attributes);

                if ($user === null) {
                    $user = $this->createUser($attributes);
                    if ($user === null) {
                        Yii::warning('Could not automatically create LDAP user - DN: ' . $dn, 'ldap');
                        continue;
                    }
                    // Record the LDAP objectGuid -> user mapping. createUser()
                    // no longer writes user_auth itself (so it stays usable
                    // when dispatched from a foreign auth client).
                    (new AuthClientUserService($user))->add($authClient);
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
