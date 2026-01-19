<?php

namespace humhub\modules\like\vue;

use humhub\components\rendering\templating\VueWidget;
use Yii;

class LikeWidget extends VueWidget
{
    public string $renderer = 'renderLikeButton';
    public string $rootTag = 'span';
    public string $assetBundle = LikeAsset::class;
}
