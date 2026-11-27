<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\grid;

use humhub\helpers\Html;
use humhub\modules\user\widgets\Image as UserImage;
use Yii;

/**
 * ImageColumn
 *
 * @since 1.3
 * @author Luke
 */
class ImageColumn extends BaseColumn
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->options['style'] = 'width:38px';

        // A header for screen readers only: an empty header cell names nothing (WCAG 1.3.1)
        if ($this->header === null && $this->label === null && $this->attribute === null) {
            $this->header = Html::tag('span', Yii::t('base', 'Image'), ['class' => 'visually-hidden']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return UserImage::widget(['user' => $this->getUser($model), 'width' => 34]);
    }

}
