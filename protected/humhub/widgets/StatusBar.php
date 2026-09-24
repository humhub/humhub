<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * StatusBar for user feedback (error/warning/info) — the `StatusBar` island of the core
 * component set, mounted once per page by {@see LayoutAddons}.
 *
 * The `id` stays on the mount element: theme CSS and the acceptance test helpers
 * (`AcceptanceTester::seeSuccess()` and friends) address the bar as `#status-bar`. Visibility
 * is component state — a `d-none` class on the mount element would keep the island invisible.
 *
 * @see LayoutAddons
 * @author buddha
 * @since 1.2
 */
class StatusBar extends VueWidget
{
    protected string $component = 'StatusBar';

    /**
     * @inheritdoc
     */
    protected function getOptions(): array
    {
        return ['id' => 'status-bar', 'class' => 'clearfix'];
    }
}
