<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\assets;

use humhub\components\assets\AssetBundle;
use humhub\modules\topic\assets\TopicAsset;

class FilterAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@ui/filter/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ui.filter.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        TopicAsset::class
    ];
}
