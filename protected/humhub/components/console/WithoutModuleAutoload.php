<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\console;

/**
 * Marks a console controller as module-independent.
 *
 * When applied to a console controller class, the module autoloader will skip
 * loading all external modules before running it. The controller must not rely
 * on any module-provided services or classes.
 *
 * Use this for lightweight utility commands that should run cleanly at any
 * point in the application lifecycle — including during upgrades when external
 * modules may reference removed core classes.
 *
 * Example:
 * ```php
 * #[WithoutModuleAutoload]
 * class SettingsController extends Controller { ... }
 * ```
 *
 * @since 1.19
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class WithoutModuleAutoload
{
}
