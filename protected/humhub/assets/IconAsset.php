<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Icon stylesheets maintained by HumHub on top of [[TablerIconsAsset]]:
 *
 * - `css/icon-filled.css` — the Tabler filled variants as `ti-<name>-filled` classes with their own
 *   font family, so outline and filled icons can be used on the same page.
 * - `css/icon-legacy.css` — the Font Awesome 4 compatibility layer rendering markup with `fa-<name>`
 *   classes with Tabler glyphs. Removed in 1.22.
 *
 * Both files are derived from the package and checked by `IconCssSyncTest`.
 *
 * @since 1.20
 */
class IconAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/icon-filled.css',
        'css/icon-legacy.css',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        TablerIconsAsset::class,
    ];
}
