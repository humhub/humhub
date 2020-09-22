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
 * @deprecated since v1.5 not in use anymore
 */
class AtJsAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/at.js';

    /**
     * @inheritdoc
     */
    public $js = ['dist/js/jquery.atwho.min.js'];

    /**
     * @inheritdoc
     */
    public $depends = [
        CaretjsAsset::class,
        AtJsStyleAsset::class
    ];

}
