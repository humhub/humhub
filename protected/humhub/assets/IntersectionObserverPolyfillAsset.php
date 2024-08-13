<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use yii\web\View;

/**
 * animate.css
 *
 * @author buddha
 */
class IntersectionObserverPolyfillAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/intersection-observer';

    /**
     * @inheritdoc
     */
    public $js = ['intersection-observer.js'];

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => [
            'intersection-observer.js'
        ]
    ];

}
