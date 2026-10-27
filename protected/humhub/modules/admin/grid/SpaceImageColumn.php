<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\space\models\Space;

/**
 * SpaceColumn
 *
 * @since 1.3
 * @author Luke
 * @deprecated since 1.20 the administration's space list is the `DataTable` island of
 * {@see \humhub\modules\admin\widgets\SpaceTable}, on {@see \humhub\modules\admin\components\AdminSpaceList}
 */
class SpaceImageColumn extends SpaceBaseColumn
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->options['style'] = 'width:38px';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return SpaceImage::widget(['space' => $this->getSpace($model), 'width' => 34, 'link' => true]);
    }

}
