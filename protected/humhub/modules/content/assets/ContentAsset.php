<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

class ContentAsset extends AssetBundle
{

    public $sourcePath = '@content/assets';
    public $css = [];
    public $js = [
        'js/humhub.content.js'
    ];
    
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
