<?php

use humhub\modules\user\widgets\ProfileHeaderControls;
use humhub\modules\friendship\widgets\FriendshipButton;
use humhub\modules\user\widgets\ProfileEditButton;
use humhub\modules\user\widgets\ProfileHeaderCounterSet;
use humhub\modules\user\widgets\UserFollowButton;

/* @var $container \humhub\modules\content\components\ContentContainerActiveRecord */
?>
<div class="panel-body">
    <div class="panel-profile-controls">
        <div class="row">
            <div class="col-md-12">
                <?= ProfileHeaderCounterSet::widget(['user' => $container]); ?>

                <div class="controls controls-header pull-right">
                    <?= ProfileHeaderControls::widget([
                        'user' => $container,
                        'widgets' => [
                            [ProfileEditButton::class, ['user' => $container], []],
                            [UserFollowButton::class, ['user' => $container], []],
                            [FriendshipButton::class, ['user' => $container], []],
                        ]
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


