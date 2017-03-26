<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * animate.css
 *
 * @author buddha
 */
class AnimateCssAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/animate.css';

    /**
     * @inheritdoc
     */
    public $js = [];

    /**
     * @inheritdoc
     */
    public $css = ['animate.min.css'];

}
