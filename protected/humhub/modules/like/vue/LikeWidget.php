<?php

namespace humhub\modules\like\vue;

use humhub\helpers\Html;
use yii\base\Widget;

class LikeWidget extends Widget
{
    public function run()
    {
        LikeAsset::register($this->view);

        $this->view->registerJs("renderLikeButton('$this->id')");

        return Html::tag('div', '', [
            'id' => $this->id,
        ]);
    }
}
