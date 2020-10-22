<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\grid;

use humhub\modules\space\widgets\Image as SpaceImage;
use Yii;

/**
 * ChallengeSpaceColumn
 */
class ChallengeSpaceColumn extends SpaceBaseColumn
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->attribute === null) {
            $this->attribute = 'space_id';
        }

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.base', 'Space');
        }

        $this->options['style'] = 'width:300px';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return SpaceImage::widget(['space' => $model->getSpace()->one(), 'width' => 34, 'link' => true]);
    }

}
