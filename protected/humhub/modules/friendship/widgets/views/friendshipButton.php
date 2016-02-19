<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\friendship\models\Friendship;

if ($friendshipState === Friendship::STATE_FRIENDS) {
    print Html::a(Yii::t("FriendshipModule.base", "Unfriend"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('class' => 'btn btn-primary', 'data-method' => 'POST'));
} elseif ($friendshipState === Friendship::STATE_NONE) {
    print Html::a(Yii::t("FriendshipModule.base", "Add Friend"), Url::to(['/friendship/request/add', 'userId' => $user->id]), array('class' => 'btn btn-info', 'data-method' => 'POST'));
} elseif ($friendshipState === Friendship::STATE_REQUEST_RECEIVED) {
    print Html::a(Yii::t("FriendshipModule.base", "Accept Friend Request"), Url::to(['/friendship/request/add', 'userId' => $user->id]), array('class' => 'btn btn-info', 'data-method' => 'POST'));
    print Html::a(Yii::t("FriendshipModule.base", "Decline Friend Request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('class' => 'btn btn-info', 'data-method' => 'POST'));
} elseif ($friendshipState === Friendship::STATE_REQUEST_SENT) {
    print Html::a(Yii::t("FriendshipModule.base", "Cancel Pending Friend Request"), Url::to(['/friendship/request/delete', 'userId' => $user->id]), array('class' => 'btn btn-info', 'data-method' => 'POST'));
}
