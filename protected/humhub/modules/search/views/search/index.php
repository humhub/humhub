<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use humhub\modules\search\models\forms\SearchForm;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

humhub\modules\stream\assets\StreamAsset::register($this);

?>
<div class="container" data-action-component="stream.SimpleStream">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-heading"><strong><?php echo Yii::t('base', 'Search'); ?></strong></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <?php $form = ActiveForm::begin(['action' => Url::to(['index']), 'method' => 'GET']); ?>
                                <div class="form-group form-group-search">
                                    <?= $form->field($model, 'keyword')->textInput(['placeholder' => Yii::t('SearchModule.views_search_index', 'Search for user, spaces and content'), 
                                        'title' => Yii::t('SearchModule.views_search_index', 'Search for user, spaces and content'), 'class' => 'form-control form-search', 'id' => 'search-input-field'])->label(false); ?>
                                    <?php echo Html::submitButton(Yii::t('base', 'Search'), ['class' => 'btn btn-default btn-sm form-button-search', 'data-ui-loader' => '']); ?>
                                </div>

                                <div class="text-center">
                                    <a data-toggle="collapse" id="search-settings-link" href="#collapse-search-settings"
                                       style="font-size: 11px;"><i
                                            class="fa fa-caret-right"></i> <?php echo Yii::t('SearchModule.views_search_index', 'Advanced search settings'); ?>
                                    </a>
                                </div>

                                <div id="collapse-search-settings" class="panel-collapse collapse">
                                    <br>
                                    <?=  Yii::t('SearchModule.views_search_index', 'Search only in certain spaces:'); ?>
                                    <?= \humhub\modules\space\widgets\SpacePickerField::widget([
                                        'id' => 'space_filter',
                                        'model' => $model,
                                        'attribute' => 'limitSpaceGuids',
                                        'selection' => $limitSpaces,
                                        'placeholder' => Yii::t('SearchModule.views_search_index', 'Specify space')
                                    ]) ?>
                                </div>
                                <br>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div class="col-md-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($model->keyword != ""): ?>
        <div class="row">
            <div class="col-md-2">
                <div class="panel panel-default">
                    <div
                        class="panel-heading"><?php echo Yii::t('SearchModule.views_search_index', '<strong>Search </strong> results'); ?></div>
                    <div class="list-group">
                        <a data-pjax-prevent href='<?php echo Url::to(['/search/search/index', 'SearchForm[keyword]' => $model->keyword, 'SearchForm[limitSpaceGuids]' => $model->limitSpaceGuids, 'SearchForm[scope]' => SearchForm::SCOPE_ALL]); ?>'
                           class="list-group-item <?php if ($model->scope == SearchForm::SCOPE_ALL): ?>active<?php endif; ?>">
                            <div>
                                <div class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'All'); ?>
                                    (<?php echo $totals[SearchForm::SCOPE_ALL]; ?>)
                                </div>
                            </div>
                        </a>
                        <br/>
                        <a data-pjax-prevent href='<?php echo Url::to(['/search/search/index', 'SearchForm[keyword]' => $model->keyword, 'SearchForm[limitSpaceGuids]' => $model->limitSpaceGuids, 'SearchForm[scope]' => SearchForm::SCOPE_CONTENT]); ?>'
                           class="list-group-item <?php if ($model->scope == SearchForm::SCOPE_CONTENT): ?>active<?php endif; ?>">
                            <div>
                                <div
                                    class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Content'); ?>
                                    (<?php echo $totals[SearchForm::SCOPE_CONTENT]; ?>)
                                </div>
                            </div>
                        </a>
                        <a data-pjax-prevent href='<?php echo Url::to(['/search/search/index', 'SearchForm[keyword]' => $model->keyword, 'SearchForm[limitSpaceGuids]' => $model->limitSpaceGuids, 'SearchForm[scope]' => SearchForm::SCOPE_USER]); ?>'
                           class="list-group-item <?php if ($model->scope == SearchForm::SCOPE_USER): ?>active<?php endif; ?>">
                            <div>
                                <div
                                    class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Users'); ?>
                                    (<?php echo $totals[SearchForm::SCOPE_USER]; ?>)
                                </div>
                            </div>
                        </a>
                        <a data-pjax-prevent href='<?php echo Url::to(['/search/search/index', 'SearchForm[keyword]' => $model->keyword, 'SearchForm[limitSpaceGuids]' => $model->limitSpaceGuids, 'SearchForm[scope]' => SearchForm::SCOPE_SPACE]); ?>'
                           class="list-group-item <?php if ($model->scope == SearchForm::SCOPE_SPACE): ?>active<?php endif; ?>">
                            <div>
                                <div
                                    class="edit_group "><?php echo Yii::t('SearchModule.views_search_index', 'Spaces'); ?>
                                    (<?php echo $totals[SearchForm::SCOPE_SPACE]; ?>)
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-10">

                <div class="searchResults">

                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $result): ?>
                            <?php if ($result instanceof ContentActiveRecord) : ?>
                                <?= humhub\modules\stream\actions\Stream::renderEntry($result) ?>
                            <?php elseif ($result instanceof ContentContainerActiveRecord) : ?>
                                 <?= $result->getWallOut(); ?>
                            <?php else: ?>
                                No Output for Class <?php echo get_class($result); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>


                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p><strong><?php echo Yii::t('SearchModule.views_search_index', 'Your search returned no matches.'); ?></strong></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div
                    class="pagination-container"><?php echo humhub\widgets\LinkPager::widget(['pagination' => $pagination]); ?></div>
                <br><br>
            </div>

        </div>
    <?php endif; ?>

</div>

<script type="text/javascript">

    $(document).ready(function () {
        // set focus to input for seach field
        $('#search-input-field').focus();
    });


    $('#collapse-search-settings').on('show.bs.collapse', function () {
        // change link arrow
        $('#search-settings-link i').removeClass('fa-caret-right');
        $('#search-settings-link i').addClass('fa-caret-down');
    });

    $('#collapse-search-settings').on('shown.bs.collapse', function () {
        $('#space_input_field').focus();
    })

    $('#collapse-search-settings').on('hide.bs.collapse', function () {
        // change link arrow
        $('#search-settings-link i').removeClass('fa-caret-down');
        $('#search-settings-link i').addClass('fa-caret-right');
    });


<?php foreach (explode(" ", $model->keyword) as $k) : ?>
        $(".searchResults").highlight("<?php echo Html::encode($k); ?>");
        $(document).ajaxComplete(function (event, xhr, settings) {
            $(".searchResults").highlight("<?php echo Html::encode($k); ?>");
        });
<?php endforeach; ?>
</script>

