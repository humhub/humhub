<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

/**
 * OpenSans Font
 *
 * @since 1.3
 * @author luke
 */
class OpenSansAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/fontsource--open-sans';

    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $preload = [
        'latin.css',
        'latin-italic.css',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'latin.css',
        'latin-italic.css',
    ];
}
