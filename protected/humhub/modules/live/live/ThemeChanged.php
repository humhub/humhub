<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\live;

use humhub\modules\live\components\LiveEvent;

/**
 * Live event for theme changing
 *
 * @since 1.17.4
 */
class ThemeChanged extends LiveEvent
{
    public ?string $oldTheme = null;
    public ?string $newTheme = null;
}
