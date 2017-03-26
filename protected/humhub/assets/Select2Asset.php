<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * select2
 *
 * @author buddha
 */
class Select2Asset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/select2';

    /**
     * @inheritdoc
     */
    public $js = ['dist/js/select2.full.js'];

    /**
     * @inheritdoc
     */
    public $css = ['dist/css/select2.min.css'];

    public $depends = [
        'yii\web\JqueryAsset',

        'yii\bootstrap\BootstrapAsset'
    ];

}
