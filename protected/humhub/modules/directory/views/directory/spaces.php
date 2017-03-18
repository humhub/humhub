<?php
/* @var $this \yii\web\View */
/* @var $keyword string */
/* @var $spaces humhub\modules\space\models\Space[] */
/* @var $pagination yii\data\Pagination */

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\libs\Helpers;
use humhub\widgets\LinkPager;
use humhub\modules\space\widgets\FollowButton;
use humhub\modules\space\widgets\Image;
use humhub\modules\directory\widgets\SpaceTagList;
?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.base', '<strong>Space</strong> directory'); ?>
    </div>

    <div class="panel-body">
        <?= Html::beginForm(Url::to(['/directory/directory/spaces']), 'get', ['class' => 'form-search']); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?= Html::textInput('keyword', $keyword, ['class' => 'form-control form-search', 'placeholder' => Yii::t('DirectoryModule.base', 'search for spaces')]); ?>
                    <?= Html::submitButton(Yii::t('DirectoryModule.base', 'Search'), ['class' => 'btn btn-default btn-sm form-button-search']); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?= Html::endForm(); ?>

        <?php if (count($spaces) == 0): ?>
            <p><?= Yii::t('DirectoryModule.base', 'No spaces found!'); ?></p>
        <?php endif; ?>
    </div>

    <hr>
    <ul class="media-list">
        <?php foreach ($spaces as $space) : ?>
            <li>
                <div class="media">
                    <div class="pull-right">
                        <?=
                        FollowButton::widget([
                            'space' => $space,
                            'followOptions' => ['class' => 'btn btn-primary btn-sm'],
                            'unfollowOptions' => ['class' => 'btn btn-info btn-sm']
                        ]);
                        ?>
                    </div>

                    <?= Image::widget(['space' => $space, 'width' => 50, 'htmlOptions' => ['class' => 'media-object'], 'link' => true, 'linkOptions' => ['class' => 'pull-left']]); ?>

                    <?php if ($space->isMember()): ?>
                        <i class="fa fa-user space-member-sign tt" data-toggle="tooltip" data-placement="top" title=""
                           data-original-title="<?= Yii::t('DirectoryModule.base', 'You are a member of this space'); ?>"></i>
                       <?php endif; ?>

                    <div class="media-body">
                        <h4 class="media-heading"><a href="<?= $space->getUrl(); ?>"><?= Html::encode($space->name); ?></a>
                            <?php if ($space->isArchived()) : ?>
                                <span class="label label-warning"><?= Yii::t('ContentModule.widgets_views_label', 'Archived'); ?></span>
                            <?php endif; ?>
                        </h4>

                        <h5><?= Html::encode(Helpers::truncateText($space->description, 100)); ?></h5>
                        <?= SpaceTagList::widget(['space' => $space]); ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="pagination-container">
    <?= LinkPager::widget(['pagination' => $pagination]); ?>
</div>
