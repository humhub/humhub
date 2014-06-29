<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.base', '<strong>Error</strong> logging'); ?></div>
    <div class="panel-body">

        <div>
            <?php echo Yii::t('AdminModule.logging', 'Total {count} entries found.', array("{count}" => $itemCount)); ?>
            <span
                class="pull-right"><?php echo Yii::t('AdminModule.logging', 'Displaying {count} entries per page.', array("{count}" => $pageSize)); ?></span>
        </div>

        <hr>

        <ul class="media-list">
            <?php foreach ($entries as $entry) : ?>

                <li class="media">
                    <div class="media-body">

                        <?php
                        $labelClass = "label-primary";
                        if ($entry->level == 'error') {
                            $labelClass = "label-danger";
                        } elseif ($entry->level == 'error') {
                            $labelClass = "label-warning";
                        } elseif ($entry->level == 'info') {
                            $labelClass = "label-info";
                        }
                        ?>

                        <h4 class="media-heading">
                            <span class="label <?php echo $labelClass; ?>"><?php echo $entry->level; ?></span>&nbsp;
                            <?php echo date('r', $entry->logtime); ?>&nbsp;
                            <span class="pull-right"><?php echo $entry->category; ?></span>
                        </h4>
                        <?php echo $entry->message; ?>
                    </div>
                </li>

            <?php endforeach; ?>
        </ul>


        <center>
            <?php
            $this->widget('CLinkPager', array(
                'currentPage' => $pagination->getCurrentPage(),
                'itemCount' => $itemCount,
                'pageSize' => $pageSize,
                'maxButtonCount' => 5,
                'header' => '',
                'nextPageLabel' => '<i class="fa fa-step-forward"></i>',
                'prevPageLabel' => '<i class="fa fa-step-backward"></i>',
                'firstPageLabel' => '<i class="fa fa-fast-backward"></i>',
                'lastPageLabel' => '<i class="fa fa-fast-forward"></i>',
                'htmlOptions' => array('class' => 'pagination'),
            ));
            ?>
        </center>
    </div>
</div>