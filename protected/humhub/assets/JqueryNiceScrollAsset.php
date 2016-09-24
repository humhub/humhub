<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jQuery-nicescroll
 * 
 * @author luke
 */
class JqueryNiceScrollAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-nicescroll';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.nicescroll.min.js'];

}
