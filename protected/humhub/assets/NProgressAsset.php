<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * NProgress assets
 *
 * @since 1.2
 * @author luke
 */
class NProgressAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/nprogress';

    /**
     * @inheritdoc
     */
    public $css = [
        'nprogress.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'nprogress.js'
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            '/nprogress.css',
            '/support/extras.css',
            '/nprogress.js',
        ],
    ];

}
