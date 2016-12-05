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
class JuiBootstrapBridgeAsset extends AssetBundle
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
    public $js = ['js/jui.bootstrap.bridge.js'];
    
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}
