<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

/**
 * Enforces the scope an action requires on token requests. Requests authenticated otherwise
 * (session, other modules' methods) pass — those carry their own authorization.
 *
 * Fail-closed: an action without a declared scope is reachable with `full` only, or not at
 * all with [[requireDeclaredScope]].
 *
 * @since 1.20
 */
class ScopeFilter extends ActionFilter
{
    /**
     * @var array<string, string|string[]> action id (or `*`) → required scope (any of a list suffices)
     */
    public array $scopes = [];

    /**
     * @var bool whether `full` satisfies every action — true exactly where the platform's own
     *      frontend may go (`$allowSessionAuth`)
     */
    public bool $allowFull = false;

    /**
     * @var bool whether an action without a declared scope is refused even with `full` — for
     *      APIs opened to tokens endpoint by endpoint (the rest module's `/api/v1`)
     */
    public bool $requireDeclaredScope = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $grant = Module::instance()->activeGrant;
        if ($grant === null) {
            return true;
        }

        $required = (array)(self::resolveScope($this->scopes, $action->id) ?? []);
        if ($required === [] && $this->requireDeclaredScope) {
            // No scope can reach the action, so none is named.
            return $this->deny(null);
        }

        $effective = (new GrantService())->getEffectiveScopes($grant);

        if ($this->allowFull && in_array(ScopeRegistry::FULL, $effective, true)) {
            return true;
        }

        if ($required !== [] && array_intersect($required, $effective) !== []) {
            return true;
        }

        return $this->deny(implode(' ', $required !== [] ? $required : [ScopeRegistry::FULL]));
    }

    /**
     * The scope declared for an action: its own entry, else the `*` entry.
     *
     * @param array<string, string|string[]> $scopes action id (or `*`) → scope
     * @return string|string[]|null as declared, a list meaning any of its scopes
     */
    public static function resolveScope(array $scopes, string $actionId): string|array|null
    {
        return $scopes[$actionId] ?? $scopes['*'] ?? null;
    }

    /**
     * Answers `403 insufficient_scope`, naming the scope that would pass if there is one.
     */
    private function deny(?string $scope): bool
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->setStatusCode(403);
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer error="insufficient_scope"' . ($scope !== null ? ', scope="' . $scope . '"' : ''));
        $response->data = ['error' => 'insufficient_scope'] + ($scope !== null ? ['scope' => $scope] : []);

        return false;
    }
}
