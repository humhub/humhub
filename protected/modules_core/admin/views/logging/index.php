<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.logging', '<strong>Error</strong> logging'); ?></div>
    <div class="panel-body">


        <?php echo Yii::t('AdminModule.logging', 'Total {count} entries found.', array("{count}" => $itemCount)); ?>
        <?php echo Yii::t('AdminModule.logging', 'Displaying {count} entries per page.', array("{count}" => $pageSize)); ?>

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


                        <h4 class="media-heading"><span
                                class="pull-left label <?php echo $labelClass; ?>"><?php echo $entry->level; ?></span>&nbsp;
                            (<?php echo $entry->id; ?>)&nbsp;
                            <?php echo date('r', $entry->logtime); ?>&nbsp;
                            <strong><?php echo $entry->category; ?></strong>
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
                'nextPageLabel' => '<i class="icon-step-forward"></i>',
                'prevPageLabel' => '<i class="icon-step-backward"></i>',
                'firstPageLabel' => '<i class="icon-fast-backward"></i>',
                'lastPageLabel' => '<i class="icon-fast-forward"></i>',
                'htmlOptions' => array('class' => 'pagination'),
            ));
            ?>
        </center>

    </div>
</div>