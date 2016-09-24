<?php

use yii\helpers\Html;
?>
<?php if (count($spaces) > 0) { ?>
    <div id="user-spaces-panel" class="panel panel-default members" style="position: relative;">

        <!-- Display panel menu widget -->
        <?php echo \humhub\widgets\PanelMenu::widget(['id' => 'user-spaces-panel']); ?>

        <div class="panel-heading">
            <?php echo Yii::t('UserModule.widgets_views_userSpaces', '<strong>Member</strong> in these spaces'); ?>
        </div>

        <div class="panel-body">
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
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => Html::encode($space->name),
                    ]
                ]);
                ?>
            <?php endforeach; ?>

            <?php if ($showMoreLink): ?>
                <br>
                <br>
                <?= Html::a('Show all', $user->createUrl('/user/profile/space-membership-list'), ['class' => 'pull-right btn btn-sm btn-default', 'data-target' => '#globalModal']); ?>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>