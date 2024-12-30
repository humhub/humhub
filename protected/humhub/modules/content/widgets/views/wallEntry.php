<?php

use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\WallEntryLabels;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\TimeAgo;
use yii\helpers\Url;

/* @var $object Content */
/* @var $container ContentContainerActiveRecord */
/* @var $renderControls bool */
/* @var $wallEntryWidget string */
/* @var $user \humhub\modules\user\models\User */
/* @var $showContentContainer \humhub\modules\user\models\User */
?>


<div class="panel panel-default wall_<?= $object->getUniqueId(); ?>">
    <div class="panel-body">

        <div class="d-flex">
            <!-- since v1.2 -->
            <div class="stream-entry-loader"></div>

            <!-- start: show wall entry options -->
            <?php if ($renderControls) : ?>
                <?= WallEntryControls::widget(['object' => $object, 'wallEntryWidget' => $wallEntryWidget]) ?>
            <?php endif; ?>
            <!-- end: show wall entry options -->

            <div class="flex-shrink-0 me-2">
                <?= UserImage::widget([
                    'user' => $user,
                    'width' => 40,
                    'htmlOptions' => ['data-contentcontainer-id' => $user->contentcontainer_id],
                ]) ?>
            </div>

            <?php if ($showContentContainer && $container instanceof Space): ?>
                <div class="flex-shrink-0 me-2">
                    <?= SpaceImage::widget([
                        'space' => $container,
                        'width' => 20,
                        'htmlOptions' => ['class' => 'img-space'],
                        'link' => 'true',
                        'linkOptions' => ['data-contentcontainer-id' => $container->contentcontainer_id],
                    ]) ?>
                </div>
            <?php endif; ?>

            <div class="flex-grow-1">
                <h4 class="mt-0">
                    <?= Html::containerLink($user); ?>
                    <?php if ($container && $showContentContainer): ?>
                        <span class="viaLink">
                            <i class="fa fa-caret-right" aria-hidden="true"></i>
                            <?= Html::containerLink($container); ?>
                        </span>
                    <?php endif; ?>

                    <div class="float-end <?= ($renderControls) ? 'labels' : '' ?>">
                        <?= WallEntryLabels::widget(['object' => $object]); ?>
                    </div>
                </h4>

                <h5>
                    <a href="<?= Url::to(['/content/perma', 'id' => $object->content->id], true) ?>">
                        <?= TimeAgo::widget(['timestamp' => $createdAt]); ?>
                    </a>
                    <?php if ($updatedAt !== null) : ?>
                        &middot;
                        <span class="tt"
                              title="<?= Yii::$app->formatter->asDateTime($updatedAt); ?>"><?= Yii::t('ContentModule.base', 'Updated'); ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <hr/>

            <div class="content" id="wall_content_<?= $object->getUniqueId(); ?>">
                <?= $content; ?>
            </div>

            <!-- wall-entry-addons class required since 1.2 -->
            <?php if ($renderAddons) : ?>
                <div class="stream-entry-addons clearfix">
                    <?= WallEntryAddons::widget($addonOptions); ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
