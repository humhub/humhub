<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\widgets\UserFollowButton;
use humhub\libs\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $user \humhub\modules\user\models\User */
?>

<?= UserFollowButton::widget([
    'user' => $user,
    'followOptions' => ['class' => 'btn btn-primary btn-sm'],
    'unfollowOptions' => ['class' => 'btn btn-primary btn-sm active'],
    'unfollowLabel' => '<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('FriendshipModule.base', 'Following'),
]); ?>

<?php
if (!Yii::$app->user->isGuest && !$user->isCurrentUser() && Yii::$app->getModule('friendship')->getIsEnabled()) {
    $friendShipState = Friendship::getStateForUser(Yii::$app->user->getIdentity(), $user);
    if ($friendShipState === Friendship::STATE_NONE) {
        echo Html::a('<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;' . Yii::t("FriendshipModule.base", "Add Friend"), Url::to(['/friendship/request/add', 'userId' => $user->id]), ['class' => 'btn btn-info btn-sm','data-ui-loader' => '', 'data-method' => 'POST']);
    } elseif ($friendShipState === Friendship::STATE_FRIENDS) {
        echo Html::a('<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t("FriendshipModule.base", "Friends"), $user->getUrl(), ['class' => 'btn btn-info btn-sm active', 'data-ui-loader' => '']);
    }
}