<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components shared platform-wide (e.g. `RichTextOutput`,
 * `LegacyFormWrapper`) — core infrastructure, not tied to any single module.
 *
 * Source: `protected/humhub/vue/`, built via `grunt build-vue --module=core`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * Listed in `CoreBundleAsset::STATIC_DEPENDS` so these components are always
 * available wherever any other module's island might nest them.
 *
 * @since 1.19
 */
class CoreVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.core.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
    ];
}
