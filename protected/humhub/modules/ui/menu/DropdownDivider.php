<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\menu\widgets\Menu;
use yii\bootstrap\Html;

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
        Html::addCssClass($extraHtmlOptions, 'divider');
        return Html::tag('li', '', $this->getHtmlOptions($extraHtmlOptions));
    }
}
