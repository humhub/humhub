<?php

/**
 * This View shows a list of all user spaces in sidebar.
 *
 * @property Array $spaces contains all spaces where the user is member. (Max. 30)
 *
 * @package humhub.modules_core.user
 * @since 0.5
 */
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
                <?php echo \humhub\modules\space\widgets\Image::widget([
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
                ]); ?>
            <?php endforeach; ?>

        </div>
    </div>
<?php } ?>