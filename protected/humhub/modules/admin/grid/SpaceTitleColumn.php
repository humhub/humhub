<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use humhub\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\space\models\Space;
use humhub\widgets\bootstrap\Badge;
use Yii;

/**
 * TitleColumn
 *
 * @since 1.3
 * @author Luke
 */
class SpaceTitleColumn extends SpaceBaseColumn
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
            $this->label = Space::find()->where(['not', ['sort_order' => 100]])->count() ?
                Yii::t('SpaceModule.base', 'Name / Sort order') :
                Yii::t('SpaceModule.base', 'Name');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $space = $this->getSpace($model);

        $badge = '';
        if ($space->status === Space::STATUS_ARCHIVED) {
            $badge = '&nbsp;' . Badge::none(Yii::t('SpaceModule.base', 'Archived'));
        }

        return Html::tag(
            'div',
            Html::encode($space->name) . $badge .
            ($space->sort_order === 100 ? '' : ' ' . Badge::light($space->sort_order)) .
            '<br> ' . '<small>' . Html::encode(Helpers::trimText($space->description, 100)) . '</small>',
        );
    }

}
