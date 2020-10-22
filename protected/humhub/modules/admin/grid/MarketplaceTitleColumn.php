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
 * MarketplaceTitleColumn
 */
class MarketplaceTitleColumn extends SpaceBaseColumn
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            $this->attribute = 'title';
        }

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.base', 'Title');
        }

        $this->options['style'] = 'width:500px';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return '<div>' . Html::encode($model->title) . '</div>';
    }

}
