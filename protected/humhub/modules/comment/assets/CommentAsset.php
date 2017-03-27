<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\assets;

use yii\web\AssetBundle;

class CommentAsset extends AssetBundle
{

    public $sourcePath = '@comment/resources';
    public $css = [];
    public $js = [
        'js/humhub.comment.js'
    ];

    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
