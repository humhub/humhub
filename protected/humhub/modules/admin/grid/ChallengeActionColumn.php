<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\grid;

use Yii;
use humhub\libs\ActionColumn;

/**
 * ChallengeActionColumn
 */
class ChallengeActionColumn extends ActionColumn
{

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $actions[Yii::t('AdminModule.category', 'Edit')] = ['edit', 'linkOptions' => ['data-target' => '#globalModal']];

        $this->actions = $actions;

        return parent::renderDataCellContent($model, $key, $index);
    }

}
