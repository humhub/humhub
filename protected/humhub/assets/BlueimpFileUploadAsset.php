<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

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
    public $sourcePath = '@bower/blueimp-file-upload';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/jquery.fileupload.js',
        'js/jquery.iframe-transport.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\JqueryWidgetAsset',
    ];

}
