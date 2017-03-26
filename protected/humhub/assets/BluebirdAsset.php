<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-knob
 *
 * @author luke
 */
class BluebirdAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/bluebird';

    /**
     * @inheritdoc
     */
    public $js = ['js/browser/bluebird.min.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

}
