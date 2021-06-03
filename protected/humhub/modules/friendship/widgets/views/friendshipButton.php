<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\friendship\models\Friendship;

/* @var $user \humhub\modules\user\models\User */
/* @var $friendshipState string */
/* @var $options array */
?>
<?php if ($friendshipState === Friendship::STATE_FRIENDS) : ?>
    <?= Html::a($options['friends']['title'], Url::to(['/friendship/request/delete', 'userId' => $user->id]), $options['friends']['attrs']); ?>
<?php elseif ($friendshipState === Friendship::STATE_NONE) : ?>
    <?= Html::a($options['addFriend']['title'], Url::to(['/friendship/request/add', 'userId' => $user->id]), $options['addFriend']['attrs']); ?>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_RECEIVED) : ?>
    <div class="<?= $options['acceptFriendRequest']['groupClass'] ?>">
        <?= Html::a($options['acceptFriendRequest']['title'], Url::to(['/friendship/request/add', 'userId' => $user->id]), $options['acceptFriendRequest']['attrs']); ?>
        <button type="button" class="<?= $options['acceptFriendRequest']['togglerClass'] ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= Html::a($options['denyFriendRequest']['title'], Url::to(['/friendship/request/delete', 'userId' => $user->id]), $options['denyFriendRequest']['attrs']); ?>
            </li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_SENT) : ?>
    <?= Html::a($options['cancelFriendRequest']['title'], Url::to(['/friendship/request/delete', 'userId' => $user->id]), $options['cancelFriendRequest']['attrs']); ?>
<?php endif; ?>
