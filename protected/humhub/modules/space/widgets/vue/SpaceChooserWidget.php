<?php

namespace humhub\modules\space\widgets\vue;

use humhub\components\rendering\templating\VueWidget;

class SpaceChooserWidget extends VueWidget
{
    public string $renderer = 'renderSpaceChooser';
    public string $rootTag = 'li';
    public array $options = [
        'class' => 'nav-item dropdown',
    ];
    public string $assetBundle = SpaceChooserAsset::class;
}
