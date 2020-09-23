<?php

use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\ArchivedIcon;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\UpdatedIcon;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use humhub\widgets\TimeAgo;
use humhub\modules\content\widgets\VisibilityIcon;

/* @var $this View */
/* @var $model ContentActiveRecord */
/* @var $headImage string */
/* @var $permaLink string */
/* @var $title string */
/* @var $renderOptions WallStreamEntryOptions */

$container = $model->content->container;
?>

<div class="stream-entry-icon-list">
    <?php if ($renderOptions->isPinned($model)) : ?>
        <?= Icon::get('map-pin', ['htmlOptions' => ['class' => 'icon-pin tt', 'title' => Yii::t('ContentModule.base', 'Pinned')]]) ?>
    <?php endif; ?>
</div>

<!-- since v1.2 -->
<div class="stream-entry-loader"></div>

<!-- start: show wall entry options -->
<?php if (!$renderOptions->isContextMenuDisabled()) : ?>
    <?= WallEntryControls::widget(['renderOptions' => $renderOptions, 'object' => $model, 'wallEntryWidget' => $this->context]) ?>
<?php endif; ?>
<!-- end: show wall entry options -->


<div class="wall-entry-header-image">
    <?= $headImage ?>
</div>

<div class="wall-entry-header-info media-body">

    <div class="media-heading">
        <?= $title ?>

        <?php if ($renderOptions->isShowContainerInformation($model)): ?>
            <span class="viaLink">
                <?= Icon::get('caret-right') ?>
                <?= Html::containerLink($model->content->container) ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="media-subheading">

        <?php if ($renderOptions->isShowTargetSpaceImage($model)) : ?>
            <?= SpaceImage::widget([
                'space' => $container,
                'width' => 20,
                'htmlOptions' => ['class' => 'img-space'],
                'link' => 'true',
                'linkOptions' => ['class' => 'pull-left'],
            ]) ?>
        <?php endif; ?>

        <?php if ($renderOptions->isShowAuthorLinkInSubHeadLine($model)) : ?>
            <?= Html::containerLink($model->content->createdBy, ['class' => 'wall-entry-container-link']) ?> |
        <?php endif ?>

        <a href="<?= $permaLink ?>">
            <?= TimeAgo::widget(['timestamp' => $model->content->created_at]) ?>
        </a> &middot;

        <?php if ($model->content->isUpdated()) : ?>
            <?= UpdatedIcon::getByDated($model->content->updated_at) ?>
        <?php endif; ?>

        <?= VisibilityIcon::getByModel($model) ?>

        <?= ArchivedIcon::getByModel($model) ?>
    </div>
</div>

