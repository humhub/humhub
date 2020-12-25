<?php

namespace humhub\modules\space\assets;

use humhub\components\assets\AssetBundle;

class SpaceSettingAsset extends AssetBundle
{
    public $sourcePath = '@space/resources';

    public $js = [
        'js/humhub.space.setting.js'
    ];

    public $depends = [
        SpaceAsset::class
    ];
}
