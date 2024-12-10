<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use humhub\modules\ui\view\components\View;

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
    public $sourcePath = '@npm/fontsource--open-sans';

    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    /**
     * @inheritdoc
     */
    public $preload = [
        'latin.css',
        'latin-ext.css',
        'latin-ext-italic.css',
        'latin-italic.css',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'latin.css',
        'latin-ext.css',
        'latin-ext-italic.css',
        'latin-italic.css',
    ];
}
