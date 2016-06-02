<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\friendship\models\Friendship;
?>


<?php if ($friendshipState === Friendship::STATE_FRIENDS) : ?>
    <div class="btn-group">
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="glyphicon glyphicon-ok"></span> <?= Yii::t("FriendshipModule.base", "Friends"); ?>&nbsp;<span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a(Yii::t("FriendshipModule.base", "Unfriend"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('data-method' => 'POST')); ?></li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_NONE) : ?>
    <?= Html::a('<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . Yii::t("FriendshipModule.base", "Add Friend"), Url::to(['/friendship/request/add', 'userId' => $user->id]), array('class' => 'btn btn-info', 'data-method' => 'POST')); ?>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_RECEIVED) : ?>
    <div class="btn-group">
        <?= Html::a(Yii::t("FriendshipModule.base", "Accept Friend Request"), Url::to(['/friendship/request/add', 'userId' => $user->id]), array('class' => 'btn btn-success', 'data-method' => 'POST')); ?>
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= Html::a(Yii::t("FriendshipModule.base", "Deny friend request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('data-method' => 'POST')); ?>
            </li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_SENT) : ?>
    <?= Html::a(Yii::t("FriendshipModule.base", "Cancel friend request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('class' => 'btn btn-danger', 'data-method' => 'POST')); ?>
<?php endif; ?>
