<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

/**
 * Exempts a web controller from the installation check in {@see Application::beforeAction()}: no redirect to
 * the installer, no 503 on an unreachable database. The controller must handle a missing or unreachable
 * database itself.
 *
 * The web counterpart of the console attribute {@see \humhub\components\console\WithoutModuleAutoload}.
 *
 * ```php
 * #[WithoutInstallationCheck]
 * class HealthController extends \yii\web\Controller { ... }
 * ```
 *
 * @since 1.20
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class WithoutInstallationCheck
{
    public static function isPresentOn(object $controller): bool
    {
        return (new \ReflectionClass($controller))->getAttributes(self::class) !== [];
    }
}
