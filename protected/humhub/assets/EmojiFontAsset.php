<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Noto Color Emoji as the font family 'HumHub Emoji', in chunks per emoji group (built by emoji.build.mjs)
 *
 * Registered by [[\humhub\components\Appearance]] unless the emoji style is "System".
 *
 * @see \humhub\helpers\EmojiHelper
 * @since 1.20
 */
class EmojiFontAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $css = [
        'fonts/emoji/emoji.css',
    ];
}
