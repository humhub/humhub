<?php

namespace humhub\widgets;

/**
 * Interface Reloadable
 *
 * This interface is used for JsWidgets which can be reloaded.
 *
 * @package humhub\widgets
 * @since 1.4
 */
interface Reloadable
{
    /**
     * @return string|array url used to reload this widget
     */
    public function getReloadUrl();
}
