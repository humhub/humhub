<?php

use humhub\libs\ActionColumn;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\admin\widgets\GroupMenu;
use humhub\modules\user\models\Group;
use humhub\widgets\Label;
use humhub\widgets\Link;
use yii\helpers\Url;
use humhub\widgets\GridView;

/* @var $searchModel GroupSearch */
?>
<div class="panel-body">
    <div class="pull-right">
        <?= Link::success(Yii::t('AdminModule.user', 'Create new group'))->href(Url::to(['edit']))->sm()->icon('add') ?>
    </div>

    <h4><?= Yii::t('AdminModule.user', 'Manage groups'); ?></h4>

    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'Users can be assigned to different groups (e.g. teams, departments etc.) with specific standard spaces, group managers and permissions.'); ?>
    </div>
</div>

<?= GroupMenu::widget() ?>

<div class="panel-body">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-hover'],
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function (Group $group) {
                    // Yii::t is available for default texts
                    return Yii::t('AdminModule.base', $group->name) .
                        ($group->is_default_group ? ' ' . Label::defaultType(Yii::t('AdminModule.user', 'Default')) : '') .
                        ($group->is_protected ? ' ' . Label::defaultType(Yii::t('AdminModule.user', 'Protected')) : '');
                }
            ],
            [
                'attribute' => 'description',
                'value' => function (Group $group) {
                    // Yii::t is available for default texts
                    return Yii::t('AdminModule.base', $group->description);
                }
            ],
            [
                'attribute' => 'members',
                'label' => Yii::t('AdminModule.user', 'Members'),
                'format' => 'raw',
                'options' => ['style' => 'text-align:center;'],
                'value' => function (Group $data) {
                    return $data->getGroupUsers()->count();
                }
            ],
            [
                'class' => ActionColumn::class,
                'actions' => function ($group, $key, $index) {
                    /* @var $group Group */
                    if ($group->is_admin_group && !Yii::$app->user->isAdmin()) {
                        return [];
                    }

                    return [
                        Yii::t('AdminModule.user', 'Settings') => ['edit'],
                        '---',
                        Yii::t('AdminModule.user', 'Permissions') => ['manage-permissions'],
                        Yii::t('AdminModule.user', 'Members') => ['manage-group-users'],
                    ];
                }
            ],
        ],
    ]) ?>
</div>
