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
class BlueimpFileUploadAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/blueimp-file-upload/js';

    /**
     * @inheritdoc
     */
    public $js = [
        'jquery.fileupload.js',
        'jquery.iframe-transport.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JqueryWidgetAsset::class
    ];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'jquery.fileupload.js',
            'jquery.iframe-transport.js',
        ]
    ];

}
