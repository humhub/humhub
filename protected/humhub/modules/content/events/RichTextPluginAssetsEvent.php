<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\events;

use yii\base\Event;

/**
 * RichTextPluginAssetsEvent is triggered once per request, as soon as the rich text editor bundle is
 * registered in a view, letting modules add the asset bundle of their rich text plugins
 * (`prosemirror.plugin.registerPlugin()`), so the plugins are available wherever a rich text can be rendered
 * or edited: full pages, Ajax responses (modals, Pjax) and Vue components.
 *
 * ```php
 * Event::on(
 *     ProseMirrorRichTextAsset::class,
 *     ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS,
 *     static fn(RichTextPluginAssetsEvent $event) => $event->assets[] = MyPluginAsset::class,
 * );
 * ```
 *
 * The asset bundle has to depend on {@see \humhub\modules\content\assets\ProseMirrorRichTextAsset}.
 *
 * @see \humhub\modules\content\assets\ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS
 * @since 1.20
 */
class RichTextPluginAssetsEvent extends Event
{
    /**
     * @var string[] class names of the asset bundles to register
     */
    public array $assets = [];
}
