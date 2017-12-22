<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-At.js
 *
 * @author buddha
 */
class AtJsAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/At.js';

    /**
     * @inheritdoc
     */
    public $js = ['dist/js/jquery.atwho.min.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\CaretJsAsset',
        'humhub\assets\AtJsStyleAsset'
    ];

}
