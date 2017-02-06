<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\assets;

use yii\web\AssetBundle;

class PostAsset extends AssetBundle
{

    public $sourcePath = '@post/resources';
    public $css = [];
    public $js = [
        'js/humhub.post.js'
    ];
    
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
