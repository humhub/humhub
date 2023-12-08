<?php

namespace humhub\widgets;

use yii\helpers\ArrayHelper;

class Pjax extends \yii\widgets\Pjax
{
    public $timeout = 30000;

    public function init()
    {
        $this->options = ArrayHelper::merge($this->options, [
            'class' => (array) ArrayHelper::getValue($this->options, 'class', [])
        ], [
            'class' => ['exclude-from-pjax-client'],
        ]);

        parent::init();
    }
}
