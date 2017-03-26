<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-At.js
 *
 * @author buddha
 */
class JplayerAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jplayer/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        'jplayer/jquery.jplayer.js',
        'add-on/jplayer.playlist.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = ['skin/blue.monday/css/jplayer.blue.monday.min.css'];

}
