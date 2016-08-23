<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-pjax
 * 
 * @author buddha
 */
class JqueryPjaxAsset extends AssetBundle
{
    
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-pjax';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.pjax.js'];
    
    /**
     * @inheritdoc
     */
    public $css = [];
    
}
