<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use humhub\components\View;

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
     *
     * Only the latin/latin-ext subsets registered below are published;
     * `files/open-sans-latin-*` covers both subsets' font files.
     */
    public $publishOptions = [
        'only' => [
            'latin.css',
            'latin-ext.css',
            'latin-italic.css',
            'latin-ext-italic.css',
            'files/open-sans-latin-*',
        ],
    ];

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
