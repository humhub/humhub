<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\services;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\User;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

/**
 * Creates, checks and revokes grants.
 *
 * Usability and effective scopes are evaluated on every request, so an administrator's
 * change of settings or client scopes applies to existing grants immediately.
 *
 * @since 1.20
 */
class GrantService
{
    /**
     * How often `last_used_at` is written at most.
     */
    public const TOUCH_INTERVAL = 300;

    private readonly Module $module;

    public function __construct(?Module $module = null)
    {
        $this->module = $module ?? Module::instance();
    }

    /**
     * @param string[] $scopeIds the scopes the user approved
     */
    public function createOAuthGrant(User $user, Client $client, array $scopeIds): Grant
    {
        $grant = new Grant([
            'user_id' => $user->id,
            'type' => Grant::TYPE_OAUTH,
            'client_id' => $client->id,
            'name' => $client->name,
        ]);
        $grant->setScopeIds($scopeIds);

        $maxDays = $this->module->getGrantMaxLifetimeDays();
        if ($maxDays > 0) {
            $grant->expires_at = time() + $maxDays * 86400;
        }

        if (!$grant->save()) {
            throw new InvalidArgumentException('Could not save the grant: ' . implode(' ', $grant->getErrorSummary(true)));
        }

        return $grant;
    }

    public function isClientTypeAllowed(Client $client): bool
    {
        return match ($client->type) {
            Client::TYPE_SYSTEM => $this->module->allowsApp(),
            Client::TYPE_ADMIN => $this->module->allowsClients(),
            default => false,
        };
    }

    /**
     * Whether the grant may authenticate anything right now.
     */
    public function isUsable(Grant $grant): bool
    {
        if ($grant->isExpired()) {
            return false;
        }

        $user = $grant->user;
        if ($user === null || (int)$user->status !== User::STATUS_ENABLED) {
            return false;
        }

        if ($grant->type === Grant::TYPE_PAT) {
            return $this->module->allowsPersonalAccessTokens();
        }

        if ($grant->type !== Grant::TYPE_OAUTH) {
            return false;
        }

        $client = $grant->client;
        return $client !== null && $client->is_active && $this->isClientTypeAllowed($client);
    }

    /**
     * The scopes allowed for clients of this kind, before a user approved anything.
     *
     * @return string[]
     */
    public function getAllowedScopes(Client $client): array
    {
        $scopes = array_values(array_filter($client->getAllowedScopes(), [ScopeRegistry::class, 'has']));

        return match ($client->type) {
            Client::TYPE_SYSTEM => [ScopeRegistry::FULL],
            Client::TYPE_ADMIN => $scopes,
            default => [],
        };
    }

    /**
     * The scopes the grant holds right now. Does not check usability, callers check [[isUsable()]].
     *
     * @return string[]
     */
    public function getEffectiveScopes(Grant $grant): array
    {
        $scopes = array_values(array_filter($grant->getScopeIds(), [ScopeRegistry::class, 'has']));

        if ($grant->type === Grant::TYPE_PAT) {
            return array_values(array_intersect($scopes, $this->module->getPatScopes()));
        }

        if ($grant->type !== Grant::TYPE_OAUTH) {
            return [];
        }

        $client = $grant->client;
        if ($client === null) {
            return [];
        }

        if ($client->type === Client::TYPE_SYSTEM) {
            return [ScopeRegistry::FULL];
        }

        return array_values(array_intersect($scopes, $this->getAllowedScopes($client)));
    }

    public function revoke(Grant $grant): void
    {
        if ($grant->delete() === false) {
            throw new Exception('Could not revoke grant ' . $grant->guid);
        }
    }

    /**
     * Records the use, at most every [[TOUCH_INTERVAL]] seconds.
     */
    public function touch(Grant $grant): void
    {
        if ($grant->last_used_at === null || $grant->last_used_at < time() - self::TOUCH_INTERVAL) {
            $grant->updateAttributes(['last_used_at' => time()]);
        }
    }
}
