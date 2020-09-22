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
    public $sourcePath = '@npm/select2/dist/js';

    /**
     * @inheritdoc
     */
    public $js = ['select2.full.min.js'];

    /**
     * @inheritdoc
     */
    public $depends = [
        Select2StyleAsset::class
    ];
}
