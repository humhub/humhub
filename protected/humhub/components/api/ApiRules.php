<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use yii\helpers\Url;

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
 * @since 1.20
 */
class ApiRules
{
    /**
     * URL prefix of the whole API URL space, every generation included. Everything below it
     * answers JSON, errors included - see {@see self::isApiPath()}.
     */
    public const PREFIX = 'api/';

    /**
     * URL prefix of the current API version. Everything below it is served by
     * {@see BaseController} subclasses; nothing above it may reach them
     * (see {@see BaseController::beforeAction()}).
     */
    public const PREFIX_V2 = 'api/v2/';

    /**
     * Whether a request path lies in the API URL space.
     *
     * {@see \humhub\components\Application::handleRequest()} fixes the response format to JSON
     * for such a path BEFORE routing, so that an unknown route, a request with a verb no rule
     * was registered for, and an exception thrown before an API controller's own
     * `beforeAction()` ran all render as Yii's JSON error body instead of the HTML error page.
     * A module's `/api/v1` lies in the same space and gets the same treatment.
     */
    public static function isApiPath(string $pathInfo): bool
    {
        return str_starts_with($pathInfo, self::PREFIX);
    }

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
     * The URL of an API endpoint, for markup the server renders (a `data-action-url`, a link):
     * the PHP counterpart of `apiUrl()` in `humhub.vue.js`, built the same way.
     *
     * The path is the endpoint's path below the version prefix, `space/3/membership` -
     * NOT built through `Url::to()` with the controller route: a URL manager without pretty
     * URLs would render `index.php?r=space/api/membership/remove`, which never passes
     * {@see BaseController::beforeAction()}'s prefix guard. The result is relative to the host
     * the page is served from (`Url::base()`), not the configured base URL, for the reason
     * `CoreJsConfig` gives.
     *
     * @param string $path endpoint path below {@see self::PREFIX_V2}, empty for the prefix itself
     * @return string
     */
    public static function url(string $path = ''): string
    {
        return rtrim(Url::base(), '/') . '/' . static::PREFIX_V2 . ltrim($path, '/');
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
