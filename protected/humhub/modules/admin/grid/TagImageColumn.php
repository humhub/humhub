<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */


namespace humhub\modules\admin\grid;

use humhub\modules\xcoin\widgets\TagImage;
use Yii;

/**
 * TagImageColumn
 */
class TagImageColumn extends SpaceBaseColumn
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.tag', 'Cover Image');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return TagImage::widget(['tag' => $model, 'width' => 100]);
    }

}
