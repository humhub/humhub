<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreVueAsset;
use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the content module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=content`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class ContentVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.content.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
        // ContentControls.vue renders <DropdownMenu>, which lives in the core component set
        // (protected/humhub/vue/) - CoreVueAsset must register it before this bundle's own
        // script runs.
        CoreVueAsset::class,
    ];
}
