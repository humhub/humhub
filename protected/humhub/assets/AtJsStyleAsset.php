<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-At.js
 * 
 * @author buddha
 */
class AtJsStyleAsset extends AssetBundle
{
   
    /**
     * @inheritdoc
     */
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jquery.atwho.modified.css',
    ];
    
}
