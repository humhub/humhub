<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu\widgets;

/**
 * @deprecated since 1.20, use \humhub\widgets\menu\PillTabs instead. Will be removed in 1.21.
 */
abstract class SubTabMenu extends \humhub\widgets\menu\PillTabs
{
    /**
     * @inheritdoc
     */
    public string $variant = self::VARIANT_SECONDARY;
}
