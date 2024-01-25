<?php

use humhub\libs\Html;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\modules\user\widgets\PeopleTagList;

/* @var User $user */
?>
<div class="card card-default">
    <div class="card-body">
        <div class="media">
            <span class="label label-default float-end"><?php echo Yii::t('UserModule.base', 'User'); ?></span>
            <?= Image::widget(['user' => $user, 'width' => 40, 'htmlOptions' => ['class' => 'float-start']]); ?>
            <div class="media-body">
                <h4 class="media-heading"><?= Html::containerLink($user); ?></h4>
                <h5><?= Html::encode($user->displayNameSub); ?></h5>
                <?= PeopleTagList::widget(['user' => $user]); ?>
            </div>
        </div>
    </div>
</div>
