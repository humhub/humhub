<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\assets;

use yii\web\AssetBundle;

class TopicAsset extends AssetBundle
{
    public $sourcePath = '@topic/resources';
    public $css = [];
    public $js = [
        'js/humhub.topic.js'
    ];
    
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
