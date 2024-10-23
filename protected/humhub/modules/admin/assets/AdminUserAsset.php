<?php

namespace humhub\modules\admin\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AdminUserAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'position' => View::POS_END,
    ];
    public $sourcePath = '@admin/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.admin.user.js',
    ];
}
