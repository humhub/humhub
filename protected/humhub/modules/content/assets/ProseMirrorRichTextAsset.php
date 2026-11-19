<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\assets;

use humhub\assets\ProsemirrorEditorAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\content\events\RichTextPluginAssetsEvent;
use WeakMap;
use yii\base\Event;
use yii\web\View;

/**
 * Asset for core content resources.
 *
 * @since 1.3
 * @author buddha
 */
class ProseMirrorRichTextAsset extends AssetBundle
{
    /**
     * @event RichTextPluginAssetsEvent triggered once per view when this bundle is registered, modules add the asset
     * bundles of their rich text plugins to `RichTextPluginAssetsEvent::$assets`.
     * @since 1.20
     */
    public const EVENT_REGISTER_PLUGIN_ASSETS = 'registerPluginAssets';

    private static ?WeakMap $pluginAssetsRegistered = null;

    /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ui.richtext.prosemirror.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        ProsemirrorEditorAsset::class,
    ];

    /**
     * Registers the asset bundles modules added for their rich text plugins, see {@see EVENT_REGISTER_PLUGIN_ASSETS}.
     * The bundles are registered once per view, whenever this bundle is registered.
     *
     * @since 1.20
     */
    public static function registerPluginAssets(View $view): void
    {
        self::$pluginAssetsRegistered ??= new WeakMap();

        if (isset(self::$pluginAssetsRegistered[$view])) {
            return;
        }
        self::$pluginAssetsRegistered[$view] = true;

        $event = new RichTextPluginAssetsEvent();
        Event::trigger(self::class, self::EVENT_REGISTER_PLUGIN_ASSETS, $event);

        foreach (array_unique($event->assets) as $asset) {
            $view->registerAssetBundle($asset);
        }
    }
}
