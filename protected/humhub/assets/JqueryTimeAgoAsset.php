<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use yii\web\View;

/**
 * TimeAgo Asset Bundle
 *
 * @author luke
 */
class JqueryTimeAgoAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

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
     *
     * Must stay identical to `JqueryTimeAgoLocaleAsset::$publishOptions` as both
     * bundles publish the same source path.
     */
    public $publishOptions = [
        'only' => [
            'jquery.timeago.js',
            'locales/*',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/timeago';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.timeago.js'];

}
