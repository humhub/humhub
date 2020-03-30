<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

/**
 * Asset for core content resources.
 *
 * @since 1.3
 * @author buddha
 */
class LegacyRichTextAsset extends AssetBundle
{
     /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';

     /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ui.richtext.legacy.js'
    ];
}
