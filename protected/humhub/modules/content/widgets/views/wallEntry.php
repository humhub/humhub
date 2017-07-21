<?php

use humhub\libs\Html;
use humhub\widgets\TimeAgo;
use humhub\modules\space\models\Space;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryLabels;
?>

<div class="panel panel-default wall_<?= $object->getUniqueId(); ?>">
    <div class="panel-body">

        <div class="media">
            <!-- since v1.2 -->
            <div class="stream-entry-loader"></div>

            <!-- start: show wall entry options -->
            <?php if($renderControls) : ?>
                <ul class="nav nav-pills preferences">
                    <li class="dropdown ">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-label="<?= Yii::t('base', 'Toggle stream entry menu'); ?>" aria-haspopup="true">
                            <i class="fa fa-angle-down"></i>
                        </a>


                            <ul class="dropdown-menu pull-right">
                                <?= WallEntryControls::widget(['object' => $object, 'wallEntryWidget' => $wallEntryWidget]); ?>
                            </ul>
                    </li>
                </ul>
            <?php endif; ?>
            <!-- end: show wall entry options -->

            <?=
            UserImage::widget([
                'user' => $user,
                'width' => 40,
                'htmlOptions' => ['class' => 'pull-left']
            ]);
            ?>

            <?php if ($showContentContainer && $container instanceof Space): ?>
                <?=
                SpaceImage::widget([
                    'space' => $container,
                    'width' => 20,
                    'htmlOptions' => ['class' => 'img-space'],
                    'link' => 'true',
                    'linkOptions' => ['class' => 'pull-left'],
                ]);
                ?>
            <?php endif; ?>

            <div class="media-body">
                <div class="media-heading">
                    <?= Html::containerLink($user); ?>
                    <?php if ($showContentContainer): ?>
                        <span class="viaLink">
                            <i class="fa fa-caret-right" aria-hidden="true"></i>
                            <?= Html::containerLink($container); ?>
                        </span>
                    <?php endif; ?>

                    <div class="pull-right <?= ($renderControls) ? 'labels' : ''?>">
                        <?= WallEntryLabels::widget(['object' => $object]); ?>
                    </div>
                </div>
                <div class="media-subheading">
                    <?= TimeAgo::widget(['timestamp' => $createdAt]); ?>
                    <?php if ($updatedAt !== null) : ?>
                        &middot;
                        <span class="tt" title="<?= Yii::$app->formatter->asDateTime($updatedAt); ?>"><?= Yii::t('ContentModule.base', 'Updated'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <hr/>

            <div class="content" id="wall_content_<?= $object->getUniqueId(); ?>">
                <?= $content; ?>
            </div>

            <!-- wall-entry-addons class required since 1.2 -->
            <?php if($renderAddons) : ?>
                <div class="stream-entry-addons clearfix">
                    <?= WallEntryAddons::widget($addonOptions); ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
