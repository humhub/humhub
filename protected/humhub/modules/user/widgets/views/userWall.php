<?php

use humhub\libs\Html;
use humhub\modules\user\widgets\Image;
use humhub\modules\directory\widgets\UserTagList;
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="media">
            <span class="label label-default pull-right"><?php echo Yii::t('UserModule.base', 'User'); ?></span>
            <?= Image::widget(['user' => $user, 'width' => 40, 'htmlOptions' => ['class' => 'pull-left']]); ?>
            <div class="media-body">
                <h4 class="media-heading"><?= Html::containerLink($user); ?></h4>
                <h5><?= Html::encode($user->displayNameSub); ?></h5>
                <?= UserTagList::widget(['user' => $user]); ?>
            </div>
        </div>
    </div>
</div>