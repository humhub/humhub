<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-knob
 * 
 * @author luke
 */
class Select2ExtensionAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    /**
     * @inheritdoc
     */
    public $js = ['js/select2-extension.js'];
    
    public $depends = [
        'humhub\assets\Select2Asset'
    ];
}
