<?php
/**
 * This view represents a wall entry of a task.
 *
 * @property User $user the user which created this post
 * @property Task $task the current task
 * @property Space $space the current space
 *
 * @package humhub.modules.tasks
 * @since 0.5
 */
?>


<div class="panel media-list">

    <?php $this->beginContent('application.modules_core.wall.views.wallLayout', array('object' => $task)); ?>

    <?php
    $assignedUsers = $task->getAssignedUsers();
    $currentUserAssigned = false;
    ?>

    <?php if ($task->status == Task::STATUS_OPEN) : ?>
        <?php if ($currentUserAssigned) : ?>
            <?php
            echo HHtml::ajaxLink(
                '<div class="check">jo</div>', CHtml::normalizeUrl(array('/tasks/task/changeStatus', 'guid' => $space->guid, 'taskId' => $task->id, 'status' => Task::STATUS_FINISHED)), array(
                    'dataType' => "json",
                    'success' => "function(json) {  $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output)); }",
                ), array('id' => "TaskFinishLink_" . $task->id)
            );
            ?>
        <?php endif; ?>
    <?php elseif ($task->status == Task::STATUS_FINISHED) : ?>
        <?php if ($currentUserAssigned) : ?>
            <?php
            echo HHtml::ajaxLink(
                'Reopen', CHtml::normalizeUrl(array('/tasks/task/changeStatus', 'guid' => $space->guid, 'taskId' => $task->id, 'status' => Task::STATUS_OPEN)), array(
                    'dataType' => "json",
                    'success' => "function(json) {  $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output));}",
                ), array('id' => "TaskOpenLink_" . $task->id, 'class' => 'btn btn-primary')
            );
            ?>
        <?php endif; ?>
    <?php endif; ?>


    <?php if ($task->deathline != "") : ?>

    <?php
    $timestamp = strtotime($task->deathline);
    if (date("d.m.yy", $timestamp) <= date("d.m.yy", time())) :
    ?>
    <span class="label pull-left" id="deadline_<?php echo $task->id; ?>">
            <?php else: ?>
        <span class="label label-important pull-left" id="deadline_<?php echo $task->id; ?>">
                <?php endif; ?>
            <?php
            echo date("d. M", $timestamp);
            ?>
            </span>
        <?php endif; ?>

        <div class="" id="title_<?php echo $task->id; ?>"> <h5> <?php echo $task->title; ?></h5>
            <?php if ($currentUserAssigned) : ?>
                <?php if ($task->status == Task::STATUS_OPEN) : ?>

                <?php endif; ?>
            <?php else :
                ?>
                <!-- Not Assigned to this User -->

                <?php if (count($assignedUsers) < $task->max_users) : ?>

                <div><span
                        class="note hide"><?php echo Yii::t('TasksModule.base', 'Do you want to handle this task?'); ?> </span>
                    <?php
                    echo HHtml::ajaxLink(
                        Yii::t('TasksModule.base', 'I do it!'), CHtml::normalizeUrl(array('/tasks/task/assign', 'guid' => $space->guid, 'taskId' => $task->id)), array(
                            'dataType' => 'json',
                            'success' => "function(json) { $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output)); }",
                        ), array('id' => "TaskAssignLink_" . $task->id, 'class' => 'button pt2')
                    );
                    ?>
                </div>


            <?php endif; ?>

            <?php endif; ?>

        </div>

        <?php if ($currentUserAssigned) { ?>
            <?php
            echo HHtml::dropDownList('percent_ddl_' . $task->id, $task->percent, array(
                    '0' => '0%', '10' => '10%', '20' => '20%', '30' => '30%', '40' => '40%', '50' => '50%', '60' => '60%', '70' => '70%', '80' => '80%', '90' => '90%', '100' => '100%'), array('class' => 'percent_dropdown', 'id' => 'percent_ddl_' . $task->id)
            );
            ?>

        <?php } else { ?>
            <div class="task_progress"><?php echo $task->percent; ?><span> %</span></div>
        <?php } ?>

        <div class="user" id="user_<?php echo $task->id; ?>">
            <?php if (count($assignedUsers) != 0) : ?>
                <?php foreach ($assignedUsers as $user): ?>
                    <a href="<?php echo $user->getProfileUrl(); ?>" >
                        <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin" height="24" width="24" data-toggle="tooltip" data-placement="top" title="" data-original-title="<h1><?php echo $user->displayName; ?></h1><?php echo $user->title; ?>">
                    </a>

                <?php endforeach; ?>
            <?php endif; ?>
        </div>
            <div class="clearFloats"></div>



        <?php $this->endContent(); ?>

</div>



