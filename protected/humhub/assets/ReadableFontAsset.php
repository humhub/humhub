<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Lexend, the font of the accessibility option "Readable font"
 *
 * Registered by [[\humhub\components\Appearance]] only while the option is on.
 *
 * @since 1.20
 */
class ReadableFontAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/fontsource-variable--lexend';

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'index.css',
            'files/*.woff2',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'index.css',
    ];
}
