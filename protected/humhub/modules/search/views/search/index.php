<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\search\controllers\SearchController;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-heading"><?php echo Yii::t('base', 'Search'); ?></div>
                <div class="panel-body">

                    <?php echo Html::beginForm(Url::to(['index']), 'GET'); ?>

                    <div class="input-group">
                        <?php echo Html::textInput('keyword', $keyword, array('placeholder' => 'Keyword', 'class' => 'form-control')); ?>

                        <span class="input-group-btn">
                            <?php echo Html::submitButton(Yii::t('base', 'Search'), array('class' => 'btn btn-primary pull-right')); ?>
                        </span>
                    </div>
                    <br />
                    Search only in certain spaces:
                    <?php echo Html::textInput('limitSpaceGuids', $limitSpaceGuids, array('placeholder' => 'Specify space', 'style' => 'width:200px', 'id' => 'space_filter')); ?>
                    <?php
                    echo humhub\modules\space\widgets\Picker::widget([
                        'inputId' => 'space_filter',
                        'value' => $limitSpaceGuids
                    ]);
                    ?>
                    <?php echo Html::endForm(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($keyword != ""): ?>
        <div class="row">
            <div class="col-md-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo Yii::t('SearchModule.views_search_index', '<strong>Search </strong> results'); ?></div>
                    <div class="list-group">
                        <a href='<?php echo Url::to(['/search/search/index', 'keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_ALL]); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_ALL): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'All'); ?> (<?php echo $totals[SearchController::SCOPE_ALL]; ?>)</div></div>
                        </a>
                        <br />
                        <a href='<?php echo Url::to(['/search/search/index', 'keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_CONTENT]); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_CONTENT): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Content'); ?> (<?php echo $totals[SearchController::SCOPE_CONTENT]; ?>)</div></div>
                        </a>
                        <a href='<?php echo Url::to(['/search/search/index', 'keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_USER]); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_USER): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Users'); ?> (<?php echo $totals[SearchController::SCOPE_USER]; ?>)</div></div>
                        </a>
                        <a href='<?php echo Url::to(['/search/search/index', 'keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_SPACE]); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_SPACE): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Spaces'); ?> (<?php echo $totals[SearchController::SCOPE_SPACE]; ?>)</div></div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-10">
                <ul class="media-list">
                    <div class="searchResults">

                        <?php if (count($results) > 0): ?>
                            <?php foreach ($results as $result): ?>

                                <?php if ($result instanceof ContentActiveRecord || $result instanceof ContentContainerActiveRecord) : ?>
                                    <?php echo $result->getWallOut(); ?>
                                <?php else: ?>
                                    No Output for Class <?php echo get_class($result); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Sorry nothing found :(</p>
                        <?php endif; ?>
                    </div>
                </ul>

                <div class="pagination-container"><?php echo humhub\widgets\LinkPager::widget(['pagination' => $pagination]); ?></div>
            </div>

        </div>
    <?php endif; ?>

</div>

<script>
<?php foreach (explode(" ", $keyword) as $k) : ?>
        $(".searchResults").highlight("<?php echo Html::encode($k); ?>");
        $(document).ajaxComplete(function (event, xhr, settings) {
            $(".searchResults").highlight("<?php echo Html::encode($k); ?>");
        });
<?php endforeach; ?>
</script>

