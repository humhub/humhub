<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * select2
 * 
 * @author buddha
 */
class PjaxAsset extends AssetBundle
{   
    /**
     * @inheritdoc
     */
    public $basePath = '@webroot';
    
    /**
     * @inheritdoc
     */
    public $baseUrl = '@web';

    /**
     * @inheritdoc
     */
    public $js = ['js/jquery.pjax.modified.js'];
    
    public $depends = [
        'yii\web\YiiAsset',
    ];
  
}
