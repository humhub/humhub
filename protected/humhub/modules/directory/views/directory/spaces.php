<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="panel panel-default">

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.views_directory_spaces', '<strong>Space</strong> directory'); ?>
    </div>

    <div class="panel-body">

        <?php echo Html::beginForm(Url::to(['/directory/directory/spaces']), 'get', array('class' => 'form-search')); ?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group form-group-search">
                    <?php echo Html::textInput("keyword", $keyword, array("class" => "form-control form-search", "placeholder" => Yii::t('DirectoryModule.views_directory_spaces', 'search for spaces'))); ?>
                    <?php echo Html::submitButton(Yii::t('DirectoryModule.views_directory_spaces', 'Search'), array('class' => 'btn btn-default btn-sm form-button-search')); ?>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <?php echo Html::endForm(); ?>


        <?php if (count($spaces) == 0): ?>
            <p><?php echo Yii::t('DirectoryModule.views_directory_spaces', 'No spaces found!'); ?></p>
        <?php endif; ?>

    </div>

    <hr>
    <ul class="media-list">

        <!-- BEGIN: Results -->
        <?php foreach ($spaces as $space) : ?>
            <li>
                <div class="media">

                    <!-- Follow Handling -->
                    <div class="pull-right">
                        <?php
                            humhub\modules\space\widgets\FollowButton::widget([
                                'space' => $space,
                                'followOptions' => ['class' => 'btn btn-primary btn-sm'],
                                'unfollowOptions' => ['class' => 'btn btn-info btn-sm']
                            ]);
                        ?>
                    </div>

                    <?php echo \humhub\modules\space\widgets\Image::widget([
                        'space' => $space,
                        'width' => 50,
                        'htmlOptions' => [
                            'class' => 'media-object',
                        ],
                        'link' => 'true',
                        'linkOptions' => [
                            'class' => 'pull-left',
                        ],
                    ]); ?>

                    <?php if ($space->isMember()) { ?>
                        <i class="fa fa-user space-member-sign tt" data-toggle="tooltip" data-placement="top"
                           title=""
                           data-original-title="<?php echo Yii::t('DirectoryModule.views_directory_spaces', 'You are a member of this space'); ?>"></i>
                    <?php } ?>

                    <div class="media-body">
                        <h4 class="media-heading"><a
                                href="<?php echo $space->getUrl(); ?>"><?php echo Html::encode($space->name); ?></a>
                        </h4>
                        <h5><?php echo Html::encode(humhub\libs\Helpers::truncateText($space->description, 100)); ?></h5>

                        <?php $tag_count = 0; ?>
                        <?php if ($space->hasTags()) : ?>
                            <?php foreach ($space->getTags() as $tag): ?>
                                <?php if ($tag_count <= 5) { ?>
                                    <?php echo Html::a(Html::encode($tag), ['/directory/directory/spaces', 'keyword' => $tag], array('class' => 'label label-default')); ?>
                                    <?php
                                    $tag_count++;
                                }
                                ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </li>
        <?php endforeach; ?>

        <!-- END: Results -->
    </ul>
</div>

<div class="pagination-container">
    <?php echo \humhub\widgets\LinkPager::widget(array('pagination' => $pagination)); ?>
</div>
