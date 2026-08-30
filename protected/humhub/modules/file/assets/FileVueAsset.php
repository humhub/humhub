<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\assets;

use humhub\assets\CoreApiAsset;
use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the file module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=file`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class FileVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@file/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.file.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
    ];
}
