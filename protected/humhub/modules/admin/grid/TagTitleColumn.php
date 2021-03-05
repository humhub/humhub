<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\grid;

use Yii;
use yii\bootstrap\Html;

/**
 * TagTitleColumn
 */
class TagTitleColumn extends SpaceBaseColumn
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            $this->attribute = 'name';
        }

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.base', 'Name');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return '<div>' . Html::encode($model->name) . '</div>';
    }

}
