<?php

use yii\bootstrap5\Html;
use humhub\widgets\GridView;

?>
<div class="card-header">
    <?= Yii::t('FriendshipModule.base', '<strong>My</strong> friends'); ?>
</div>


<?php echo \humhub\modules\friendship\widgets\ManageMenu::widget(['user' => $user]); ?>

<div class="card-body">
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'username',
            'profile.firstname',
            'profile.lastname',
            [
                'header' => Yii::t('base', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'update' => function () {
                        return;
                    },
                    'view' => function () {
                        return;
                    },
                    'delete' => function($url, $model) {
                        return Html::a(Yii::t('FriendshipModule.base', 'Unfriend'), ['/friendship/request/delete', 'userId' => $model->id], ['class' => 'btn btn-danger btn-sm', 'data-method' => 'POST']);
                    },
                        ],
                    ]],
            ]);
            ?>

</div>



