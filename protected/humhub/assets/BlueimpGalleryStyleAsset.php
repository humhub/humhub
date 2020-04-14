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
class BlueimpGalleryStyleAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/blueimp-gallery/css';

    /**
     * @inheritdoc
     */
    public $css = [
        'blueimp-gallery.min.css',
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'blueimp-gallery.min.css',
            'blueimp-gallery.min.css.map'
        ]
    ];
}
