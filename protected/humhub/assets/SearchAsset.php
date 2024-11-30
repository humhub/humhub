<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * @since 1.16
 */
class SearchAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_END];

    public $basePath = '@webroot-static';
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.search.js'];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        $view->registerJsConfig('ui.search', [
            'url' => Url::to(['/meta-search']),
        ]);

        return parent::register($view);
    }
}
