<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use yii\web\View;

/**
 * Vue 3 runtime (runtime-only build). The template compiler is deliberately not
 * shipped — it requires runtime code generation, which the CSP forbids. Templates
 * are precompiled at build time, see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class VueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/vue/dist';

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'vue.runtime.global.js',
            'vue.runtime.global.prod.js',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'vue.runtime.global.js',
    ];

    /**
     * @inheritdoc
     */
    public $jsProd = [
        'vue.runtime.global.prod.js',
    ];
}
