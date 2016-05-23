<?php

use yii\bootstrap\Html;
use humhub\widgets\GridView;
?>
<div class="panel-heading">
    <?php echo Yii::t('FriendshipModule.base', '<strong>My</strong> friends'); ?>
</div>


<?php echo \humhub\modules\friendship\widgets\ManageMenu::widget(['user' => $user]); ?>

<div class="panel-body">
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'username',
            'profile.firstname',
            'profile.lastname',
            [
                'header' => 'Actions',
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'update' => function () {
                        return;
                    },
                    'view' => function () {
                        return;
                    },
                    'delete' => function($url, $model) {
                        return Html::a('Unfriend', ['/friendship/request/delete', 'userId' => $model->id], ['class' => 'btn btn-danger btn-sm', 'data-method' => 'POST']);
                    },
                        ],
                    ]],
            ]);
            ?>

</div>



