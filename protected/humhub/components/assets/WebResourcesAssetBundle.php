<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\assets;

/**
 * Base asset bundle class for @webroot-resources assets residing in `protected/humhub/resources` directory.
 *
 * @package humhub\components\assets
 */
class WebResourcesAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@webroot-resources';

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'except' => [
            'scss/',
            '.gitignore',
        ],
    ];
}
