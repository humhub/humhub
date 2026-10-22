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
 * Fail-closed: an action without a declared scope is reachable with `full` only.
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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $grant = Module::instance()->activeGrant;
        if ($grant === null) {
            return true;
        }

        $effective = (new GrantService())->getEffectiveScopes($grant);

        if ($this->allowFull && in_array(ScopeRegistry::FULL, $effective, true)) {
            return true;
        }

        $required = (array)($this->scopes[$action->id] ?? $this->scopes['*'] ?? []);
        if ($required !== [] && array_intersect($required, $effective) !== []) {
            return true;
        }

        $scope = implode(' ', $required !== [] ? $required : [ScopeRegistry::FULL]);
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->setStatusCode(403);
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer error="insufficient_scope", scope="' . $scope . '"');
        $response->data = ['error' => 'insufficient_scope', 'scope' => $scope];

        return false;
    }
}
