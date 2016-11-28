<?= \humhub\modules\space\widgets\InviteModal::widget([
    'model' => $model,
    'submitText' => Yii::t('SpaceModule.views_space_invite', 'Done'),
    'submitAction' => \yii\helpers\Url::to(['/space/create/invite', 'spaceId' => $space->id])
]); ?>