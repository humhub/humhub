<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */


namespace humhub\modules\admin\grid;

use humhub\modules\xcoin\widgets\CategoryImage;
use Yii;

/**
 * CategoryImageColumn
 */
class CategoryImageColumn extends SpaceBaseColumn
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->label === null) {
            $this->label = Yii::t('AdminModule.category', 'Cover Image');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return CategoryImage::widget(['category' => $this->getSpace($model), 'width' => 100]);
    }

}
