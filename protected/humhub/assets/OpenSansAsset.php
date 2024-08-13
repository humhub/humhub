<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use humhub\modules\ui\view\components\View;

/**
 * OpenSans Font
 *
 * @since 1.3
 * @author luke
 */
class OpenSansAsset extends WebStaticAssetBundle
{
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
        'css/open-sans.css'
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/open-sans.css',
    ];
}
