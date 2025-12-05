<?php

namespace humhub\modules\like\vue;

use humhub\helpers\Html;
use yii\base\Widget;
use yii\helpers\Json;

class LikeWidget extends Widget
{
    public array $props = [];

    public function run()
    {
        LikeAsset::register($this->view);

        $this->view->registerJs("renderLikeButton('$this->id', " . Json::htmlEncode($this->props) . ")");

        return Html::tag('span', '', [
            'id' => $this->id,
        ]);
    }
}
