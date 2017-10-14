<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * masonry asset class
 *
 * @author buddha
 */
class ImagesLoadedAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/imagesloaded';

    /**
     * @inheritdoc
     */
    public $js = ['imagesloaded.pkgd.min.js'];

    /**
     * @inheritdoc
     */
    public $css = [];
}
