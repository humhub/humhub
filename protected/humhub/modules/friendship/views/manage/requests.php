<?php

use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\friendship\widgets\ManageMenu;
use humhub\widgets\GridView;

?>
<div class="panel-heading">
    <?php echo Yii::t('FriendshipModule.base', '<strong>Pending</strong> friend requests'); ?>
</div>

<?php echo ManageMenu::widget(['user' => $user]); ?>

<div class="panel-body">
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
                // The same island as on the profile: its received-request state accepts, its
                // dropdown denies.
                'value' => fn($model) => FriendshipButton::widget([
                    'user' => $model,
                    'buttonClass' => 'btn btn-accent btn-sm',
                    'stateClass' => 'btn btn-accent active btn-sm',
                    'togglerClass' => 'btn btn-accent active btn-sm',
                ]),
            ]],
    ]);
    ?>

</div>
