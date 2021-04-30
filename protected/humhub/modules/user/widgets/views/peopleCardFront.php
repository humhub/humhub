<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PeopleActionButtons;
use humhub\modules\user\widgets\Image;
use humhub\modules\user\widgets\PeopleIcons;
use humhub\modules\user\widgets\PeopleTagList;
use yii\web\View;

/* @var $this View */
/* @var $user User */
?>

<div class="card-front">
    <div class="card-image<?= $user->getProfileBannerImage()->hasImage() ? '' : ' card-no-image'; ?>" style="background-image: url('<?= $user->getProfileBannerImage()->getUrl() ?>')"></div>
    <div class="card-header">
        <?= Image::widget([
            'user' => $user,
            'linkOptions' => ['data-contentcontainer-id' => $user->contentcontainer_id],
            'htmlOptions' => ['class' => 'card-user-image'],
            'width' => 100,
        ]); ?>
        <div class="card-buttons">
            <?= PeopleActionButtons::widget(['user' => $user]); ?>
        </div>
    </div>
    <div class="card-body">
        <h4><?= Html::containerLink($user); ?></h4>
        <h5><?= Html::encode($user->profile->title); ?></h5>
        <?= PeopleTagList::widget(['user' => $user]); ?>
    </div>
    <div class="card-footer">
        <?= PeopleIcons::widget(['user' => $user]); ?>
    </div>
</div>