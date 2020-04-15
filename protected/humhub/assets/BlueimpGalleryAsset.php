<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * jQery Blueimp File Upload
 *
 * @author luke
 */
class BlueimpGalleryAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/blueimp-gallery/js';

    /**
     * @inheritdoc
     */
    public $js = [
        'blueimp-gallery.min.js',
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'blueimp-gallery.min.js',
            'blueimp-gallery.min.js.map'
        ]
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JqueryWidgetAsset::class,
        BlueimpGalleryStyleAsset::class
    ];
}
