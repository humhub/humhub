<?php

namespace  app\humhub\modules\prompt\assets;

use yii\web\AssetBundle;

/**
* AssetsBundles are used to include assets as javascript or css files
*/
class Assets extends AssetBundle
{
    /**
     * @var string defines the path of your module assets
     */
    public $sourcePath = '@bower/quick-prompt/dist';

    /**
     * @var array defines where the js files are included into the page, note your custom js files should be included after the core files (which are included in head)
     */
    public $jsOptions = ['position' => \yii\web\View::POS_END];

    /**
    * @var array change forceCopy to true when testing your js in order to rebuild this assets on every request (otherwise they will be cached)
    */
    public $publishOptions = [
        'forceCopy' => false
    ];

    public $js = [
        'assets/index-31427fa9.js'
    ];

    /**
     * Configurer Yii2 pour utiliser les assets de l'application vite-app en créant un nouver asset bundle dans Yii2
     */

    public $css = [
        'assets/index-f1ce6610.css'
    ];

    public $html = [
        'index.html'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

}
