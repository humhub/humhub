<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * The `ui.cropImage` module of `humhub\widgets\CropImage`
 *
 * @since 1.20
 */
class CropImageAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/humhub.ui.cropImage.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CropperJsAsset::class,
    ];
}
