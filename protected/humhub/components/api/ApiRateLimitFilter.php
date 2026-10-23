<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use humhub\components\rateLimit\RateLimiter;
use humhub\modules\oauth\components\ScopeFilter;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module as OAuthModule;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

/**
 * Limits API requests by the policy of their channel (`token` or `session`) and the scope of the
 * action. Attached after authentication and [[ScopeFilter]], so a refused request is not counted.
 *
 * See `docs/develop/concept-rate-limiting.md`.
 *
 * @since 1.20
 */
class ApiRateLimitFilter extends ActionFilter
{
    public const CHANNEL_TOKEN = 'token';
    public const CHANNEL_SESSION = 'session';

    /**
     * @var array<string, string|string[]> action id (or `*`) → scope, as in [[ScopeFilter::$scopes]]
     */
    public array $scopes = [];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /** @var RateLimiter $limiter */
        $limiter = Yii::$app->rateLimiter;

        [$channel, $subject] = $this->resolveSubject();
        $policy = self::resolvePolicy($limiter, $channel, self::scopeOf($this->scopes, $action->id));
        if ($policy === null) {
            return true;
        }

        $result = $limiter->hit($policy, $subject, "route '{$action->getUniqueId()}'");

        $response = Yii::$app->response;
        foreach ($result->toHeaders() as $name => $value) {
            $response->getHeaders()->set($name, $value);
        }

        if ($result->allowed) {
            return true;
        }

        $response->format = Response::FORMAT_JSON;
        $response->setStatusCode(429);
        $response->data = [
            'error' => 'rate_limited',
            'message' => Yii::t('base', 'Too many requests, try again in {seconds} seconds.', ['seconds' => $result->retryAfter]),
            'retryAfter' => $result->retryAfter,
        ];

        return false;
    }

    /**
     * The scope an action counts under: the first one declared, `full` without a declaration.
     */
    public static function scopeOf(array $scopes, string $actionId): string
    {
        $scope = ScopeFilter::resolveScope($scopes, $actionId);
        if (is_array($scope)) {
            $scope = reset($scope);
        }

        return is_string($scope) && $scope !== '' ? $scope : ScopeRegistry::FULL;
    }

    /**
     * The first configured policy of `<channel>.<scope>`, `<channel>.*:<suffix>` and `<channel>.full`.
     *
     * A policy set to `false` ends the lookup, so disabling it leaves its requests unlimited.
     */
    public static function resolvePolicy(RateLimiter $limiter, string $channel, string $scope): ?string
    {
        $names = ["$channel.$scope"];
        $colon = strrpos($scope, ':');
        if ($colon !== false) {
            $names[] = "$channel.*" . substr($scope, $colon);
        }
        $names[] = "$channel." . ScopeRegistry::FULL;

        foreach ($names as $name) {
            if (array_key_exists($name, $limiter->policies)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @return array{string, string} channel and subject of the current request
     */
    protected function resolveSubject(): array
    {
        $grant = OAuthModule::instance()->activeGrant;
        if ($grant !== null) {
            return [self::CHANNEL_TOKEN, 'token:' . $grant->id];
        }

        $userId = Yii::$app->user->getId();
        if ($userId !== null) {
            return [self::CHANNEL_SESSION, 'session:' . $userId];
        }

        return [self::CHANNEL_SESSION, 'guest:' . (Yii::$app->request->getUserIP() ?? 'unknown')];
    }
}
