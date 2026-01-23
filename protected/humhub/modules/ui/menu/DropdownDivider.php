<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\helpers\Html;
use humhub\modules\ui\menu\widgets\Menu;

/**
 * Class DropdownDivider
 *
 * Used for rendering divider within a DropdownMenu.
 *
 * Usage:
 *
 * ```php
 * $dropdown->addEntry(new DropdownDivider(['sortOrder' => 100]);
 * ```
 *
 * @since 1.4
 * @see Menu
 */
class DropdownDivider extends MenuEntry
{
    /**
     * @inheritdoc
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        Html::removeCssClass($extraHtmlOptions, 'dropdown-item');
        Html::addCssClass($extraHtmlOptions, 'dropdown-divider');
        return Html::tag(
            'li',
            Html::tag('hr', '', $this->getHtmlOptions($extraHtmlOptions)),
        );
    }
}
