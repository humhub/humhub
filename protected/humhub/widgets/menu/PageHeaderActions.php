<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * The actions of a page header ({@see \humhub\widgets\PageHeader}), with secondary tools.
 *
 * A concrete menu is named after its page plus `HeaderActions` (`PeopleHeaderActions`).
 * Vue pages render the same actions from {@see Menu::getEntriesData()} with `PageHeader.vue`.
 *
 * @since 1.20
 */
abstract class PageHeaderActions extends HeaderActions
{
}
