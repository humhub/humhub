<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * select2
 *
 * @author buddha
 */
class PjaxAsset extends AssetBundle
{

    public $jsOptions = ['position' => View::POS_HEAD];

    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/jquery.pjax.modified.js'];

    public $depends = [
        //'yii\web\YiiAsset',
    ];

}
