<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * tabbed form asset
 *
 * @author buddha
 * @deprecated since 1.4 the ui.form namespace is now part of core api
 */
class UIFormAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_END];

    public $basePath = '@webroot-static';
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.form.js'];

}
