<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

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
    public $sourcePath = '@npm/imagesloaded';

    /**
     * @inheritdoc
     */
    public $js = ['imagesloaded.pkgd.min.js'];
}
