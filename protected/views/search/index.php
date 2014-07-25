<?php
/**
 * This View shows the quick search results
 *
 * @property Boolean $moreResults if there are more results
 * @property String $keyword is the query search query
 * @property Array $results of search result content (html)
 * @property String $spaceGuid if we search also inside of a space, this is the space guid
 * @property Int $pageSize shows max. records per page
 * @property CPagination $pages is the pagination instance
 *
 * @package humhub.controllers
 * @since 0.5
 */
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('base', 'Search'); ?></div>


    <div class="panel-body">
        <?php echo CHtml::beginForm(null, 'GET'); ?>
        <?php //echo Yii::t('base', 'Keyword:') ?>
        <?php echo CHtml::textField('keyword', $keyword, array('placeholder' => 'Keyword', 'class' => 'form-control')); ?>
        <?php echo CHtml::hiddenField('sguid', $spaceGuid); ?><br>
        <?php echo CHtml::submitButton(Yii::t('base', 'Search'), array('class' => 'btn btn-primary')); ?>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>


<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('base', 'Results'); ?></div>

    <div class="panel-body">

        <?php if (count($results) > 0): ?>
            <ul class="media-list">
                <?php foreach ($results as $result): ?>
                    <?php echo $result; ?>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>

            <p><?php echo Yii::t('base', 'Sorry, nothing found!'); ?></p>

        <?php endif; ?>
    </div>

</div>
<div class="pagination-container">
    <?php
    $this->widget('CLinkPager', array(
        'currentPage' => $pages->getCurrentPage(),
        'itemCount' => $hitCount,
        'pageSize' => $pageSize,
        'maxButtonCount' => 5,
        'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
        'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
        'firstPageLabel' => '<i class="fa fa-fast-backward"></i>',
        'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
        'header' => '',
        'htmlOptions' => array('class' => 'pagination'),
    ));
    ?>
</div>
