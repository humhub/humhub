
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-heading"><?php echo Yii::t('base', 'Search'); ?></div>
                <div class="panel-body">

                    <?php echo CHtml::beginForm($this->createUrl('index'), 'GET'); ?>

                    <div class="input-group">
                        <?php echo CHtml::textField('keyword', $keyword, array('placeholder' => 'Keyword', 'class' => 'form-control')); ?>

                        <span class="input-group-btn">
                            <?php echo CHtml::submitButton(Yii::t('base', 'Search'), array('class' => 'btn btn-primary pull-right')); ?>                            
                        </span>
                    </div>
                    <br />
                    Search only in certain spaces:
                    <?php echo CHtml::textField('limitSpaceGuids', $limitSpaceGuids, array('placeholder' => 'Specify space', 'style' => 'width:200px', 'id' => 'space_filter')); ?>
                    <?php
                    $this->widget('application.modules_core.space.widgets.SpacePickerWidget', array(
                        'inputId' => 'space_filter',
                        'value' => $limitSpaceGuids,
                    ));
                    ?>
                    <?php echo CHtml::endForm(); ?>
                </div>                
            </div>
        </div>
    </div>
    <?php if ($keyword != ""): ?>
        <div class="row">
            <div class="col-md-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo Yii::t('DirectoryModule.views_directory_layout', '<strong>Search </strong> results'); ?></div>
                    <div class="list-group">
                        <a href='<?php echo $this->createUrl('//search/search/index', array('keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_ALL)); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_ALL): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('DirectoryModule.views_directory_layout', 'All'); ?> (<?php echo $totals[SearchController::SCOPE_ALL]; ?>)</div></div>
                        </a>
                        <br />
                        <a href='<?php echo $this->createUrl('//search/search/index', array('keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_CONTENT)); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_CONTENT): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('DirectoryModule.views_directory_layout', 'Content'); ?> (<?php echo $totals[SearchController::SCOPE_CONTENT]; ?>)</div></div>
                        </a>
                        <a href='<?php echo $this->createUrl('//search/search/index', array('keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_USER)); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_USER): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('DirectoryModule.views_directory_layout', 'Users'); ?> (<?php echo $totals[SearchController::SCOPE_USER]; ?>)</div></div>
                        </a>
                        <a href='<?php echo $this->createUrl('//search/search/index', array('keyword' => $keyword, 'limitSpaceGuids' => $limitSpaceGuids, 'scope' => SearchController::SCOPE_SPACE)); ?>' class="list-group-item <?php if ($scope == SearchController::SCOPE_SPACE): ?>active<?php endif; ?>">
                            <div><div class="edit_group "><?php echo Yii::t('DirectoryModule.views_directory_layout', 'Spaces'); ?> (<?php echo $totals[SearchController::SCOPE_SPACE]; ?>)</div></div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-10">
                <ul class="media-list">
                    <div class="searchResults">

                        <?php if (count($results) > 0): ?>
                            <?php foreach ($results as $result): ?>

                                <?php if ($result instanceof HActiveRecordContent || $result instanceof HActiveRecordContentContainer) : ?>
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


                <div class="pagination-container"><?php $this->widget('HLinkPager', array('pages' => $pagination)); ?></div>

            </div>

        </div>
    <?php endif; ?>

</div>

<script>
<?php foreach (explode(" ", $keyword) as $k) : ?>
        $(".searchResults").highlight("<?php echo CHtml::encode($k); ?>");
        $(document).ajaxComplete(function (event, xhr, settings) {
            $(".searchResults").highlight("<?php echo CHtml::encode($k); ?>");
        });
<?php endforeach; ?>
</script>

