<?php

use yii\helpers\Html;
?>
<?php if (count($spaces) > 0) { ?>
    <div id="user-spaces-panel" class="card card-default members" style="position: relative;">
        <!-- Display panel menu widget -->
        <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'user-spaces-panel']); ?>

        <div class="card-header">
            <?php echo Yii::t('UserModule.base', '<strong>Member</strong> of these Spaces'); ?>
        </div>

        <div class="card-body">
            <?php foreach ($spaces as $space): ?>
                <?php
                echo \humhub\modules\space\widgets\Image::widget([
                    'space' => $space,
                    'width' => 24,
                    'htmlOptions' => [
                        'class' => 'current-space-image',
                    ],
                    'link' => 'true',
                    'linkOptions' => [
                        'class' => 'tt',
                        'data-bs-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => $space->name,
                    ]
                ]);
                ?>
            <?php endforeach; ?>

            <?php if ($showMoreLink): ?>
                <br>
                <br>
                <?= Html::a('Show all', $user->createUrl('/user/profile/space-membership-list'), ['class' => 'float-end btn btn-sm btn-outline-secondary', 'data-target' => '#globalModal']); ?>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>
