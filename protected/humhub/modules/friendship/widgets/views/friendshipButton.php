<?php

use yii\helpers\Html;
use humhub\modules\friendship\models\Friendship;

/* @var $user \humhub\modules\user\models\User */
/* @var $friendshipState string */
/* @var $options array */
?>
<?php if ($friendshipState === Friendship::STATE_FRIENDS) : ?>
    <?= Html::a($options['friends']['title'], '#', $options['friends']['attrs']); ?>
<?php elseif ($friendshipState === Friendship::STATE_NONE) : ?>
    <?= Html::a($options['addFriend']['title'], '#', $options['addFriend']['attrs']); ?>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_RECEIVED) : ?>
    <div class="<?= $options['acceptFriendRequest']['groupClass'] ?>">
        <?= Html::a($options['acceptFriendRequest']['title'], '#', $options['acceptFriendRequest']['attrs']); ?>
        <button type="button" class="<?= $options['acceptFriendRequest']['togglerClass'] ?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= Html::a($options['denyFriendRequest']['title'], '#', $options['denyFriendRequest']['attrs']); ?>
            </li>
        </ul>
    </div>
<?php elseif ($friendshipState === Friendship::STATE_REQUEST_SENT) : ?>
    <?= Html::a($options['cancelFriendRequest']['title'], '#', $options['cancelFriendRequest']['attrs']); ?>
<?php endif; ?>
