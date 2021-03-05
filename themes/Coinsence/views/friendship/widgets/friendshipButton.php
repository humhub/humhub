<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\friendship\models\Friendship;

/* @var $user \humhub\modules\user\models\User */
/* @var $friendshipState string */
?>
<?php if ($friendshipState === Friendship::STATE_FRIENDS) : ?>
    <div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle styleState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="display: flex;
align-items: center;">
    <span class="glyphicon glyphicon-ok" style="color: #456ca8">
        
    </span> 
    <span class="stateFriend">
    <?= Yii::t("FriendshipModule.base", "Friends"); ?>
    </span>
    &nbsp;<span class="caret"></span>

            </button>
            <ul class="dropdown-menu">
            <li><?= Html::a(Yii::t("FriendshipModule.base", "Unfriend"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), ['data-method' => 'POST', 'data-ui-loader' => '']); ?></li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_NONE) : ?>
<?= Html::a('<i class="fa fa-user-plus"></i>&nbsp;&nbsp;' . Yii::t("FriendshipModule.base", "Add Friend"), Url::to(['/friendship/request/add', 'userId' => $user->id]), ['class' => 'btn btn-default connect', 'data-method' => 'POST', 'data-ui-loader' => '']); ?>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_RECEIVED) : ?>
    <div class="btn-group">
        <?= Html::a(Yii::t("FriendshipModule.base", "Accept Friend Request"), Url::to(['/friendship/request/add', 'userId' => $user->id]), ['class' => 'btn btn-success', 'data-method' => 'POST', 'data-ui-loader' => '']); ?>
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= Html::a(Yii::t("FriendshipModule.base", "Deny friend request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), ['data-method' => 'POST', 'data-ui-loader' => '']); ?>
            </li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_SENT) : ?>

    <?= Html::a(Yii::t("FriendshipModule.base", "Cancel friend request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), ['class' => 'btn btn-default disconnect', 'data-method' => 'POST', 'data-ui-loader' => '']); ?>
<?php endif; ?>
