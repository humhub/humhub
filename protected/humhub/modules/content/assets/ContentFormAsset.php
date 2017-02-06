<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

/**
 * Asset for stream content create form resources.
 * 
 * @since 1.2
 * @author buddha
 */
class ContentFormAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => \yii\web\View::POS_END];
    
    /**
     * @inheritdoc
     */
    public $sourcePath = '@content/resources';
    
    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.content.form.js'
    ];
    
    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];

}
