<?php


namespace humhub\modules\admin\grid;


use humhub\libs\Html;
use humhub\modules\user\models\ProfileField;
use yii\grid\DataColumn;

class CheckboxColumn extends DataColumn
{

    public $attribute;

    public $disabled = true;

    public $format = 'raw';

    public $options = ['style' => 'width:100px;'];

    public $contentOptions = ['style' => 'text-align:center'];

    public $headerOptions = ['style' => 'text-align:center'];

    public function init()
    {
        $this->value = function ($model) {
            /* @var $model ProfileField */
            $attr = $this->attribute;
            return Html::checkbox($attr, $model->$attr, ['disabled' => $this->disabled]);
        };
        parent::init();

    }
}
