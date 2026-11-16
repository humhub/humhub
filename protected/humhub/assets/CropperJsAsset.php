<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Cropper.js, the image cropper behind `humhub\widgets\CropImage`
 * https://github.com/fengyuanchen/cropperjs
 *
 * @since 1.20
 */
class CropperJsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/cropperjs/dist';

    /**
     * @inheritdoc
     */
    public $publishOptions = ['only' => ['cropper.min.js']];

    /**
     * @inheritdoc
     */
    public $js = [
        'cropper.min.js',
    ];
}
