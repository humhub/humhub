<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

/**
 * Content container asset for shared user/space js functionality.
 * 
 * @since 1.2
 * @author buddha
 */
class ContentContainerAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.content.container.js'
    ];

}
