<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * PhotoSwipe, the engine of the media viewer (`humhub.ui.gallery`), as the global `PhotoSwipe`. Internal: modules
 * open the viewer through `data-ui-gallery` or `humhub.modules.ui.gallery.open()`, see docs/develop/ui-js-gallery.md
 *
 * @since 1.20
 */
class PhotoSwipeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/photoswipe/dist/umd';

    /**
     * @inheritdoc
     */
    public $js = [
        'photoswipe.umd.min.js',
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'photoswipe.umd.min.js',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        PhotoSwipeStyleAsset::class,
    ];
}
