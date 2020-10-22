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

/**
 * ChallengeStatusColumn
 */
class ChallengeStatusColumn extends SpaceBaseColumn
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            $this->attribute = 'status';
        }

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.base', 'Status');
        }

        $this->options['style'] = 'width:300px';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return $model->status ?
            '<div> <i class="fa fa-check-circle-o fa-2x" aria-hidden="true" style="color: green"></i> </div>':
            '<div> <i class="fa fa-times-circle-o fa-2x" aria-hidden="true" style="color: red"></i> </div>';
    }

}
