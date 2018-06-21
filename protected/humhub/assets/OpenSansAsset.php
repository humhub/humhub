<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * OpenSans Font
 *
 * @since 1.3
 * @author luke
 */
class OpenSansAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/open-sans-fontface';

    /**
     * @inheritdoc
     */
    public $css = ['open-sans.css'];
}
