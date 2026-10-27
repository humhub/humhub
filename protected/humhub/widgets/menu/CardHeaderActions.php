<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * The actions of a content card's header ({@see \humhub\widgets\ContentCard}), with ghost tools.
 *
 * A concrete menu is named after its card plus `CardActions` (`AdminUserCardActions`).
 * Vue pages render the same actions from {@see Menu::getEntriesData()} with `ContentCard.vue`.
 *
 * @since 1.20
 */
abstract class CardHeaderActions extends HeaderActions
{
    /**
     * @inheritdoc
     */
    public string $toolVariant = 'ghost';
}
