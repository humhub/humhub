<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\assets;

use yii\web\AssetBundle;

/**
 * Fle related assets.
 * 
 * @since 1.2
 * @author buddha
 */
class FileAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@file/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.file.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
