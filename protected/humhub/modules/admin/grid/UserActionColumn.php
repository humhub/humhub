<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use Yii;
use humhub\libs\ActionColumn;
use humhub\modules\user\models\User;

/**
 * UserActionColumn
 *
 * @author Luke
 */
class UserActionColumn extends ActionColumn
{

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        /** @var User $model */

        $actions = [];
        if ($model->status == User::STATUS_SOFT_DELETED) {
            $actions[Yii::t('AdminModule.user', 'Permanently delete')] = ['delete'];
        } else {
            $actions[Yii::t('base', 'Edit')] = ['edit'];

            if(Yii::$app->user->isAdmin() || !$model->isSystemAdmin()) {
                $actions[] = '---';
                if ($model->status == User::STATUS_DISABLED) {
                    $actions[Yii::t('AdminModule.user', 'Enable')] = ['enable', 'linkOptions' => ['data-method' => 'post', 'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure that you want to enable this user?')]];
                } elseif ($model->status == User::STATUS_ENABLED) {
                    $actions[Yii::t('AdminModule.user', 'Disable')] = ['disable', 'linkOptions' => ['data-method' => 'post', 'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure that you want to disable this user?')]];
                }
                if (!$model->isCurrentUser()) {
                    $actions[Yii::t('base', 'Delete')] = ['delete'];
                }
            }


            if ($model->status == User::STATUS_ENABLED) {
                $actions[] = '---';
                if (Yii::$app->user->canImpersonate($model)) {
                    $actions[Yii::t('AdminModule.user', 'Impersonate')] = ['impersonate', 'linkOptions' => ['data-method' => 'post', 'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure that you want to impersonate this user?')]];
                }
                $actions[Yii::t('AdminModule.user', 'View profile')] = ['view-profile'];
            }
        }
        $this->actions = $actions;

        return parent::renderDataCellContent($model, $key, $index);
    }

}
