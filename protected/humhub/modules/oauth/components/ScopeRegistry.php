<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\oauth\events\ScopesEvent;
use humhub\modules\oauth\models\Scope;
use Yii;
use yii\base\Event;

/**
 * The scopes grants can hold: the core scopes plus the ones modules append in
 * [[EVENT_COLLECT_SCOPES]]:
 *
 * ```php
 * // a module's config.php
 * [ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, [Events::class, 'onCollectScopes']],
 * ```
 *
 * @since 1.20
 */
final class ScopeRegistry
{
    public const EVENT_COLLECT_SCOPES = 'collectScopes';

    /**
     * Everything the web interface can do, see `ScopeFilter::$allowFull`.
     */
    public const FULL = 'full';

    /**
     * Scopes contributed by modules are prefixed with the module id.
     */
    public const MODULE_SCOPE_PATTERN = '/^[a-z0-9-]+:[a-z0-9:_-]+$/';

    /**
     * @var array<string, array<string, Scope>> by language, labels are translated
     */
    private static array $scopes = [];

    /**
     * @return array<string, Scope> by id
     */
    public static function getScopes(): array
    {
        $language = (string)Yii::$app->language;
        if (isset(self::$scopes[$language])) {
            return self::$scopes[$language];
        }

        $scopes = [];
        foreach (self::getCoreScopes() as $scope) {
            $scopes[$scope->id] = $scope;
        }

        $event = new ScopesEvent();
        Event::trigger(self::class, self::EVENT_COLLECT_SCOPES, $event);
        foreach ($event->scopes as $scope) {
            if (!$scope instanceof Scope) {
                Yii::warning('Ignored an OAuth scope that is not a Scope object.', 'oauth');
                continue;
            }
            if (!preg_match(self::MODULE_SCOPE_PATTERN, $scope->id)) {
                Yii::warning('Ignored the OAuth scope "' . $scope->id . '": module scopes are named "<module-id>:<name>".', 'oauth');
                continue;
            }
            if (isset($scopes[$scope->id])) {
                Yii::warning('Ignored the duplicate OAuth scope "' . $scope->id . '".', 'oauth');
                continue;
            }
            $scopes[$scope->id] = $scope;
        }

        return self::$scopes[$language] = $scopes;
    }

    public static function has(string $id): bool
    {
        return isset(self::getScopes()[$id]);
    }

    public static function get(string $id): ?Scope
    {
        return self::getScopes()[$id] ?? null;
    }

    /**
     * @return string[] the default upper bound for user clients and personal access tokens
     */
    public static function getDefaultRestrictedScopeIds(): array
    {
        return array_values(array_filter(
            array_map(static fn(Scope $scope) => $scope->id, self::getCoreScopes()),
            static fn(string $id) => str_ends_with($id, ':read'),
        ));
    }

    /**
     * Forgets the collected scopes (tests, after a module changed its handlers).
     */
    public static function reset(): void
    {
        self::$scopes = [];
    }

    /**
     * @return Scope[]
     */
    private static function getCoreScopes(): array
    {
        return [
            new Scope(self::FULL, Yii::t('OauthModule.base', 'Full access to your account')),
            new Scope('account:read', Yii::t('OauthModule.base', 'Read your account and settings')),
            new Scope('account:write', Yii::t('OauthModule.base', 'Change your settings')),
            new Scope('notifications:read', Yii::t('OauthModule.base', 'Read your notifications')),
            new Scope('notifications:write', Yii::t('OauthModule.base', 'Mark notifications as seen')),
            new Scope('users:read', Yii::t('OauthModule.base', 'Read people and profiles')),
            new Scope('users:write', Yii::t('OauthModule.base', 'Follow people and manage friendships')),
            new Scope('spaces:read', Yii::t('OauthModule.base', 'Read spaces and memberships')),
            new Scope('spaces:write', Yii::t('OauthModule.base', 'Join, leave and follow spaces')),
            new Scope('content:read', Yii::t('OauthModule.base', 'Read content, comments and likes')),
            new Scope('content:write', Yii::t('OauthModule.base', 'Write comments, like, upload files')),
        ];
    }
}
