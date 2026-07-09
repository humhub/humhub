<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\console\WithoutModuleAutoload;

/**
 * Manages application caches.
 *
 * @since 1.19
 */
#[WithoutModuleAutoload]
class CacheController extends \yii\console\controllers\CacheController
{
}
