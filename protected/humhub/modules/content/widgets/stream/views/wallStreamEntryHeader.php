<?php

use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use humhub\widgets\TimeAgo;
use humhub\modules\content\widgets\VisibilityIcon;
use yii\helpers\Url;

/* @var $this View */
/* @var $model ContentActiveRecord */
/* @var $controlsOptions array */
/* @var $showContainerInfo boolean */
/* @var $headImage string */
/* @var $isPinned boolean */

$controlsOptions = $controlsOptions ?: [];
$renderControls = $controlsOptions !== false;
$container = $model->content->container;
?>

<div class="stream-entry-icon-list">
    <?php if($isPinned) :?>
        <?= Icon::get('map-pin', ['htmlOptions' =>['class' => 'icon-pin tt', 'title' => Yii::t('ContentModule.base', 'Pinned')]])?>
    <?php endif; ?>
</div>

<!-- since v1.2 -->
<div class="stream-entry-loader"></div>

<!-- start: show wall entry options -->
<?php if ($renderControls) : ?>
    <?= WallEntryControls::widget(['object' => $model, 'wallEntryWidget' => $this->context]) ?>
<?php endif; ?>
<!-- end: show wall entry options -->


<div class="wall-entry-header-image">
    <?= $headImage ?>
</div>

<div class="wall-entry-header-info media-body">

    <div class="media-heading">
        <?= Html::containerLink($model->createdBy) ?>

        <?php  if ($showContainerInfo && $model->content->container): ?>
            <span class="viaLink">
                <?= Icon::get('caret-right') ?>
                <?= Html::containerLink($model->content->container) ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="media-subheading">

        <?php /*  if($showContainerInfo && $container instanceof Space) : ?>
        <?= Yii::t('SpaceModule.base', 'Space') ?>:
            <?= Html::containerLink($model->content->container, ['class' => 'wall-entry-container-link']) ?> |
        <?php endif; */  ?>

        <?php //TODO: RENDER ONLY IN NON POST STYLE ?>
        <?php if(!$container->is($model->content->createdBy)) : ?>
        <?php // Yii::t('SpaceModule.base', 'Author') ?>
        <?= Html::containerLink($model->content->createdBy, ['class' => 'wall-entry-container-link']) ?> |
        <?php endif ?>

        <a href="<?= Url::to(['/content/perma', 'id' => $model->content->id], true) ?>">
            <?= TimeAgo::widget(['timestamp' => $model->content->created_at]) ?>
        </a>

        <?php if($model->content->isEdited()) : ?>
            <?= Icon::get('clock-o', ['htmlOptions' => [
                'class' => 'tt',
                'title' => Yii::t('ContentModule.base', 'Last updated {time}', ['time' => Yii::$app->formatter->asDateTime($model->content->updated_at)])
            ]]) ?>
        <?php endif; ?>

       <?= VisibilityIcon::getByModel($model) ?>
    </div>
</div>

