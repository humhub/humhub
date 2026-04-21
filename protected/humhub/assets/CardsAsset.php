<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use yii\web\View;

class CardsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/humhub.cards.js',
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_END];

}
