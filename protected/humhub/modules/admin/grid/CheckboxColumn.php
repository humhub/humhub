<?php

namespace humhub\modules\admin\grid;

use humhub\helpers\Html;
use humhub\modules\user\models\ProfileField;
use humhub\widgets\Icon;
use Yii;
use yii\grid\DataColumn;

class CheckboxColumn extends DataColumn
{
    public $attribute;

    /**
     * @var bool whether the value is only shown (a check mark and "Yes" / "No" for screen readers)
     * instead of an editable checkbox
     */
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

            if (!$this->disabled) {
                return Html::checkbox($attr, $model->$attr, ['aria-label' => $this->label ?? $this->header]);
            }

            // A disabled checkbox has no name and reads as a control (WCAG 1.3.1, 4.1.2)
            return $model->$attr
                ? Icon::get('check') . Html::tag('span', Yii::t('base', 'Yes'), ['class' => 'visually-hidden'])
                : Html::tag('span', Yii::t('base', 'No'), ['class' => 'visually-hidden']);
        };
        parent::init();
    }
}
