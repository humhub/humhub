<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

/**
 * Fontawesome
 *
 * @author luke
 */
class FontAwesomeAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/font-awesome';

    /**
     * @inheritdoc
     */
    public $css = ['css/font-awesome.min.css'];

}
