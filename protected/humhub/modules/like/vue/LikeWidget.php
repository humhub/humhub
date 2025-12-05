<?php

namespace humhub\modules\like\vue;

use humhub\helpers\Html;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;

class LikeWidget extends Widget
{
    public array $props = [];

    public function init()
    {
        parent::init();

        $this->props['language'] = \Yii::$app->language;
        $this->props['translations'] = [\Yii::$app->language => [
            'translation' => [
                'like' => Yii::t('LikeModule.base', 'Like'),
                'unlike' => Yii::t('LikeModule.base', 'Unlike'),
            ]
        ]];

    }

    public function run()
    {
        LikeAsset::register($this->view);

        $this->view->registerJs("renderLikeButton('$this->id', " . Json::htmlEncode($this->props) . ")");

        return Html::tag('span', '', [
            'id' => $this->id,
        ]);
    }
}
