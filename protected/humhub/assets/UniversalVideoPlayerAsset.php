<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://source.ind.ie/project/video-player/blob/master/LICENSE.md
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Embedded universial responsive video player
 *
 * @author BastusIII
 */
class UniversalVideoPlayerAsset extends AssetBundle
{

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
    public $js = [
        'js/modernizr.js',
        'js/px-video.js'
    ];
    
    public $css = [
        'css/px-video.css'
    ];
}
