<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use yii\web\View;

class DirectoryAsset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/humhub.directory.js',
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_END];

}
