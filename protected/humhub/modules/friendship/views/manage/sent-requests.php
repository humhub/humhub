<?php

use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\widgets\GridView;

?>
<?php $this->beginContent('@friendship/views/manage/_layout.php', ['user' => $user]) ?>
<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'username',
        'profile.firstname',
        'profile.lastname',
        [
            'header' => Yii::t('base', 'Actions'),
            'format' => 'raw',
            // The same island as on the profile: its "Pending" state withdraws the request.
            'value' => fn($model) => FriendshipButton::widget([
                'user' => $model,
                'buttonClass' => 'btn btn-accent btn-sm',
                'stateClass' => 'btn btn-accent active btn-sm',
                'togglerClass' => 'btn btn-accent active btn-sm',
            ]),
        ]],
]);
?>
<?php $this->endContent() ?>
