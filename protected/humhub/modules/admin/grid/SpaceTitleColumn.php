<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use Yii;
use yii\bootstrap\Html;
use humhub\modules\space\models\Space;
use humhub\libs\Helpers;
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
            $this->label = Yii::t('SpaceModule.base', 'Name');
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $space = $this->getSpace($model);

        $badge = '';
        if ($space->status == Space::STATUS_ARCHIVED) {
            $badge = '&nbsp;<span class="badge">'.Yii::t('SpaceModule.base', 'Archived').'</span>';
        }
        
        return '<div>' . Html::encode($space->name) . $badge . '<br> ' .
                '<small>' . Html::encode(Helpers::trimText($space->description, 100)) . '</small></div>';
    }

}
