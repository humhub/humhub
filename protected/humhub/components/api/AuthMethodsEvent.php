<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use yii\base\Event;

/**
 * Collects the authentication methods available to API requests.
 *
 * Core itself contributes nothing here — it ships {@see SessionAuth}, which every API
 * controller appends last on its own. A module providing machine authentication (access
 * tokens, JWT, HTTP Basic …) handles {@see BaseController::EVENT_COLLECT_AUTH_METHODS} and
 * appends its method configurations:
 *
 * ```php
 * // a module's config.php
 * 'events' => [
 *     [
 *         'class' => BaseController::class,
 *         'event' => BaseController::EVENT_COLLECT_AUTH_METHODS,
 *         'callback' => [Events::class, 'onCollectApiAuthMethods'],
 *     ],
 * ],
 * ```
 *
 * ```php
 * public static function onCollectApiAuthMethods(AuthMethodsEvent $event): void
 * {
 *     $event->authMethods[] = ['class' => BearerAuth::class];
 * }
 * ```
 *
 * Contributed methods are tried in the order they were appended and always BEFORE session
 * authentication, so a request carrying a valid token authenticates as the token user even
 * when a session cookie is present.
 *
 * Note the fall-through semantics of `yii\filters\auth\CompositeAuth`: a method returning
 * `null` lets the next one try, a method that throws ends the request. Contributed methods
 * should throw only for a *malformed* credential, never for a missing one.
 *
 * @since 1.20
 */
class AuthMethodsEvent extends Event
{
    /**
     * @var array method configurations for {@see \yii\filters\auth\CompositeAuth::$authMethods}
     */
    public array $authMethods = [];
}
