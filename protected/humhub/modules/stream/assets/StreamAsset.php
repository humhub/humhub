<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\assets;

use yii\web\AssetBundle;

class StreamAsset extends AssetBundle
{

    public $sourcePath = '@humhub/modules/stream/assets';
    public $css = [];
    public $js = [
        'js/humhub.stream.js'
    ];
    
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
