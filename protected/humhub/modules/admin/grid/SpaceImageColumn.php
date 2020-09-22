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
