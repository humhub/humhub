<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use yii\helpers\Url;
use yii\web\View;

/**
 * @since 1.16
 */
class SearchAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_END];

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
