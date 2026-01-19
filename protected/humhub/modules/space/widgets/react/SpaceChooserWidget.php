<?php

namespace humhub\modules\space\widgets\react;


use humhub\components\rendering\templating\ReactWidget;

class SpaceChooserWidget extends ReactWidget
{
    public string $renderer = 'renderSpaceChooser';
    public string $rootTag = 'li';
    public array $options = [
        'class' => 'nav-item dropdown',
    ];
    public string $assetBundle = SpaceChooserAsset::class;

    public function translations(): array
    {
        return [];
    }
}
