<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * select2 bootstrap asset
 * 
 * @author buddha
 */
class Select2BootstrapAsset extends AssetBundle
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
    public $css = ['resources/css/select2Theme/select2-humhub.css'];
    
    /**
     *
     * @var type 
     */
    public $depends = [
        'humhub\assets\Select2Asset'
    ];

}
