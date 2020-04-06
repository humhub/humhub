<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use yii\web\View;

/**
 * HumHub Core Api Asset
 */
class CoreApiAsset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/humhub.core.js',
        'js/humhub/humhub.util.js',
        'js/humhub/humhub.log.js',
        'js/humhub/humhub.ui.additions.js',
        'js/humhub/humhub.ui.loader.js',
        'js/humhub/humhub.action.js',
        'js/humhub/humhub.ui.widget.js',
        'js/humhub/humhub.client.js',
        'js/humhub/humhub.ui.status.js',
        'js/humhub/humhub.ui.view.js',
        'js/humhub/humhub.ui.navigation.js', // Required here since we set the active navigation on each call
        'js/humhub/humhub.ui.modal.js', // Should be moved to CoreModuleScriptAssets later
        'js/humhub/humhub.ui.progress.js',
    ];

}
