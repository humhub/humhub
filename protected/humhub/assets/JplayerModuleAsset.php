<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

/**
 * jquery-At.js
 *
 * @author buddha
 */
class JplayerModuleAsset extends WebResourcesAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/humhub.media.Jplayer.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JplayerAsset::class,
    ];
}
