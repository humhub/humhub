<?php

namespace humhub\components\rendering\templating;

use humhub\helpers\Html;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;

abstract class TemplateWidget extends Widget
{
    public string $renderer;
    public string $assetBundle;
    public string $rootTag = 'div';
    public array $props = [];

    public function init()
    {
        parent::init();

        $this->props['language'] = Yii::$app->language;
        $this->props['translations'] = [Yii::$app->language => ['translation' => $this->translations()]];
    }

    public function run()
    {
        $this->view->registerAssetBundle($this->assetBundle, $this->view::POS_END);
        $this->view->registerJs("$this->renderer('$this->id', " . Json::htmlEncode($this->props) . ")");

        return Html::tag($this->rootTag, '', [
            'id' => $this->id,
        ]);
    }

    public function translations(): array
    {
        return [];
    }
}
