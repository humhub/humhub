<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * The stylesheet of PhotoSwipe, not in a cascade layer: the core's rules for it are in `_vendor.scss`
 *
 * @see PhotoSwipeAsset
 * @since 1.20
 */
class PhotoSwipeStyleAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/photoswipe/dist';

    /**
     * @inheritdoc
     */
    public $css = [
        'photoswipe.css',
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'photoswipe.css',
        ],
    ];
}
