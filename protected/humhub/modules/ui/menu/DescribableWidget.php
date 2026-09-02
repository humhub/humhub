<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

/**
 * Implemented by a widget that can describe itself as a menu entry descriptor instead of
 * only rendering itself to HTML.
 *
 * A {@see WidgetMenuEntry} wraps a widget class rather than data, so by default there is
 * nothing to serialize — only markup to render. That is fine for a server-rendered menu and
 * useless for a client-rendered one: a Vue menu needs `{id, label, icon, …}` so it can sort,
 * filter, override and remove the entry like any other.
 *
 * Implementing this interface makes a widget describable, which is what lets a module keep
 * contributing to a menu that has moved into a Vue island without changing anything about
 * HOW it contributes ({@see \humhub\modules\content\widgets\WallEntryControls::EVENT_INIT}
 * keeps working unchanged). {@see \humhub\modules\content\widgets\WallEntryControlLink}
 * implements it for the whole family of control links that extend it.
 *
 * A widget that cannot be reduced to a descriptor — one rendering its own view with markup
 * the descriptor shape has no room for — simply does not implement this, and is rendered
 * server-side and delivered as raw HTML instead (see
 * {@see \humhub\modules\content\controllers\api\ControlsController}).
 *
 * @since 1.20
 */
interface DescribableWidget
{
    /**
     * Describes this widget as a menu entry descriptor.
     *
     * Recognized keys: `id`, `label`, `icon` (an {@see \humhub\modules\ui\icon\widgets\Icon}
     * name without the `fa-` prefix), `url`, `htmlOptions`. `sortOrder` and a fallback `id`
     * are supplied by the wrapping {@see WidgetMenuEntry}, so an implementation normally
     * omits them.
     *
     * @return array|null the descriptor, or null when this instance should not appear at all
     */
    public function describeMenuEntry(): ?array;
}
