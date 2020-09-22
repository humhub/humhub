<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use Yii;
use humhub\libs\ActionColumn;
use humhub\modules\space\models\Space;
use humhub\modules\admin\controllers\UserController;

/**
 * SpaceActionColumn
 *
 * @author Luke
 */
class SpaceActionColumn extends ActionColumn
{

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $actions = [];
        $actions[Yii::t('base', 'Edit')] = ['open', 'section' => 'edit'];
        $actions[] = '---';
        $actions[Yii::t('AdminModule.space', 'Manage members')] = ['open', 'section' => 'members'];
        $actions[Yii::t('AdminModule.space', 'Change owner')] = ['open', 'section' => 'owner'];
        $actions[Yii::t('AdminModule.space', 'Manage modules')] = ['open', 'section' => 'modules'];
        $actions[Yii::t('base', 'Delete')] = ['open', 'section' => 'delete'];
        $actions[] = '---';
        $actions[Yii::t('AdminModule.space', 'Open space')] = ['open'];
        $this->actions = $actions;

        return parent::renderDataCellContent($model, $key, $index);
    }

}
