<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * IE9FixesAsset provides CSS/JS fixes for Internet Explorer 9 versions
 *
 * @see IEFixesAsset for older IE versions
 * @since 1.2
 * @author Luke
 */
class Html5shivAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/html5shiv';

    /**
     * @inheritdoc
     */
    public $js = [
        'dist/html5shiv.min.js'
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'condition' => 'lt IE 9'
    ];

}
