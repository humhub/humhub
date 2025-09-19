<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Driver.js library
 * https://github.com/kamranahmedse/driver.js
 *
 * @author luke
 */
class DriverJsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/driver.js/dist';

    public $js = [
        'driver.js.iife.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'driver.css',
    ];
}
