<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\assets;

use humhub\components\assets\AssetBundle;

class ModuleAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@admin/resources';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/modules.css'
    ];

    public $forceCopy = true;
}
