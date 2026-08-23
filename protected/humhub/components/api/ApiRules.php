<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

/**
 * Builds URL rules for the platform's HTTP API.
 *
 * A module declares its API routes in its own `config.php`, next to the module they belong
 * to, through the existing `urlManagerRules` key — {@see \humhub\components\ModuleManager}
 * registers those rules PREPENDED, so they win over Yii's generic fallback routing:
 *
 * ```php
 * // humhub/modules/comment/config.php
 * 'urlManagerRules' => ApiRules::v2([
 *     ['pattern' => 'comment/<id:\d+>', 'route' => 'comment/api/comment/view', 'verb' => ['GET', 'HEAD']],
 * ]),
 * ```
 *
 * The helper only prefixes the patterns with the version prefix, so `api/v2/` is written
 * once instead of in every rule. Routes point at the module's own API controllers
 * (`controllers/api/`); Yii resolves controller subdirectories from the route on its own
 * ({@see \yii\base\Module::createController()}), so no `controllerMap` entry is needed and
 * the internal route shape stays invisible to clients.
 *
 * @since 1.19
 */
class ApiRules
{
    /**
     * URL prefix of the current API version. Everything below it is served by
     * {@see BaseController} subclasses; nothing above it may reach them
     * (see {@see BaseController::beforeAction()}).
     */
    public const PREFIX_V2 = 'api/v2/';

    /**
     * Prefixes the given rules with {@see self::PREFIX_V2}.
     *
     * @param array $rules rules in `['pattern' => ..., 'route' => ..., 'verb' => ...]` form
     * @return array
     */
    public static function v2(array $rules): array
    {
        return static::prefix($rules, static::PREFIX_V2);
    }

    /**
     * @param array $rules
     * @param string $prefix
     * @return array
     */
    protected static function prefix(array $rules, string $prefix): array
    {
        foreach ($rules as $i => $rule) {
            if (isset($rule['pattern'])) {
                $rules[$i]['pattern'] = $prefix . ltrim((string)$rule['pattern'], '/');
            }
        }

        return $rules;
    }
}
