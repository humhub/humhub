<div class="panel_right">
    <div class="panel_right_header">
        <div class="title"><?php echo Yii::t('TasksModule.base', 'My tasks'); ?></div>
    </div>
    <div id="taskOverview" class="content">
        <ul class="ws_list">

            <?php foreach ($tasks as $task): ?>

                <?php
                $assignedUsers = $task->getAssignedUsers();
                $workspace = $task->content->container;

                $color = 'orange';

                if (count($assignedUsers) == 0) {
                    $color = 'red';
                }

                if ($task->status == Task::STATUS_FINISHED) {
                    $color = 'green';
                }

                if (!$workspace instanceof Space) {
                    continue;
                }
                ?>
                <li>
                    <a href="<?php echo Yii::app()->createUrl('wall/perma/content', array('model'=>'Task', 'id'=>$task->id)); ?>">
                    <div>
                        <div class="task p10">
                            <!--<div class="tasks_check"></div>-->
                            <div class="tasks_title fl"><?php echo $task->title; ?></div>
                            <div class="fr">
                                <?php if ($task->deathline != "") : ?>
                                    <?php
                                    $timestamp = strtotime($task->deathline);
                                    if (date("d", $timestamp) <= date("d", time())) {
                                        echo '<div class="tasks_deadline_today">' . date("d. M", $timestamp) . '</div>';
                                    } else {
                                        echo '<div class="tasks_deadline">' . date("d. M", $timestamp) . '</div>';
                                    }
                                    ?>

                                <?php endif; ?>
                            </div>
                            <div class="clearFloats"></div>
                        </div>
                    </div>
                    </a>
                </li>

            <?php endforeach; ?>
        </ul>
    </div>
</div>

