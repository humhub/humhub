<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Tabler Icons outline webfont, published as shipped by the `@tabler/icons-webfont` package.
 *
 * The filled variants and the Font Awesome 4 compatibility layer live in [[IconAsset]].
 *
 * @since 1.20
 */
class TablerIconsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/tabler--icons-webfont/dist';

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'tabler-icons.min.css',
            'fonts/tabler-icons.woff2',
            'fonts/tabler-icons.woff',
            'fonts/tabler-icons.ttf',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $css = ['tabler-icons.min.css'];
}
