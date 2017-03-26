<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-color
 *
 * @author buddha
 */
class JqueryColorAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-color';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.color.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

}
