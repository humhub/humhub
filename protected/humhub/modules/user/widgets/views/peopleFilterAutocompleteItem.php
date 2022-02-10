<?php

use humhub\modules\user\widgets\ProfileHeaderControls;
use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\widgets\ProfileEditButton;
use humhub\modules\user\widgets\ProfileHeaderCounterSet;
use humhub\modules\user\widgets\UserFollowButton;

/** @var $fullname string */
/** @var $username string */
/** @var $image string */

?>

<a class="autocomplete-item dropdown-item" href="<?= \humhub\modules\wiki\helpers\Url::to(['/u/' . $username]) ?>">
    <div class="autocomplete-item-image" style="background-image: url('<?= $image ?>')"></div>
    <?= $fullname ?>
</a>

