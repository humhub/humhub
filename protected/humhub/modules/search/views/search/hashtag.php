<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-heading"><strong><?php echo Yii::t('base', 'Search'); ?></strong></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group form-group-search">
                                <?php echo Html::textInput('keyword', $keyword, array('placeholder' => Yii::t('SearchModule.views_search_index', 'Search for user, spaces and content'), 'class' => 'form-control form-search', 'id' => 'search-input-field')); ?>
                                <a id="search" class='btn btn-default btn-sm form-button-search' href="<?=Url::to(['/search/search/hashtag']) . "?q=" . substr($keyword, 1)?>">Поиск</a>
                            </div>
                            <br>
                        </div>
                        <div class="col-md-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($keyword != ""): ?>
        <div class="row">
            <div class="col-md-2">
                <div class="panel panel-default">
                    <div
                        class="panel-heading"><?php echo Yii::t('SearchModule.views_search_hashtag', '{n, plural, =1{Found {n, number} post} =other{Found {n, number} posts}}', ['n' => count($results)]); ?></div>
                </div>
            </div>
            <div class="col-md-10">
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
    $('#search').on('click', function () {
        var tag = $('#search-input-field').val().split('#');
        if (tag[1] != undefined) {
            $("#search").attr("href", "/search/hashtag?q=" + tag[1]);
        } else {
            $("#search").attr("href", "/search?keyword=" + tag[0]);
        }
    });
    $(".searchResults").highlight("<?php echo Html::encode($keyword); ?>", {wordsOnly: true});
</script>
