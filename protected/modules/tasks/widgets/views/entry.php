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


<div class="panel panel-default">

    <div class="panel-body">
        <?php $this->beginContent('application.modules_core.wall.views.wallLayout', array('object' => $task)); ?>


        <?php
        $assignedUsers = $task->getAssignedUsers();
        $currentUserAssigned = false;

        // Check if current user is assigned to this task
        foreach ($assignedUsers as $au) {
            if ($au->id == Yii::app()->user->id) {
                $currentUserAssigned = true;
                break;
            }
        }
        ?>
        <div class="task">
            <h5>
                <?php if ($task->status == Task::STATUS_OPEN) : ?>
                    <?php if ($currentUserAssigned || (count($assignedUsers) < $task->max_users)) { ?>
                        <?php

                        echo HHtml::ajaxLink(
                            '<span class="tasks-check tt" data-toggle="tooltip" data-placement="top" data-original-title="' . Yii::t("TasksModule.base", "Click, to finish this task") . '"><i class="fa fa-check-empty"> </i></span>', CHtml::normalizeUrl(array('/tasks/task/changeStatus', 'guid' => $space->guid, 'taskId' => $task->id, 'status' => Task::STATUS_FINISHED)), array(
                                'dataType' => "json",
                                'success' => "function(json) {  $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output)); }",
                            ), array('id' => "TaskFinishLink_" . $task->id)
                        );
                        ?>
                    <?php } else { ?>
                        <span class="tasks-check disabled tt" data-toggle="tooltip" data-placement="top"
                              data-original-title="<?php echo Yii::t("TasksModule.base", "You're not assigned to this task"); ?>"><i
                                class="fa fa-square-o"> </i></span>
                    <?php } ?>
                <?php elseif ($task->status == Task::STATUS_FINISHED) : ?>
                    <?php if ($currentUserAssigned || (count($assignedUsers) < $task->max_users)) { ?>
                        <?php
                        echo HHtml::ajaxLink(
                            '<span class="tasks-check tt"  data-toggle="tooltip" data-placement="top" data-original-title="' . Yii::t("TasksModule.base", "This task is already done. Click to reopen.") . '"><i class="fa fa-check-square-o"> </i></span>', CHtml::normalizeUrl(array('/tasks/task/changeStatus', 'guid' => $space->guid, 'taskId' => $task->id, 'status' => Task::STATUS_OPEN)), array(
                                'dataType' => "json",
                                'success' => "function(json) {  $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output));}",
                            ), array('id' => "TaskOpenLink_" . $task->id)
                        );
                        ?>
                    <?php } else { ?>
                        <span class="tasks-check disabled tt" data-toggle="tooltip" data-placement="top"
                              data-original-title="<?php echo Yii::t("TasksModule.base", "This task is already done"); ?>"><i
                                class="fa fa-check-square-o"> </i></span>
                    <?php } ?>

                <?php endif; ?>

                <?php echo $task->title; ?>
                <small>
                    <!-- Show deadline -->

                    <?php if ($task->deathline != "") : ?>
                        <?php
                        $timestamp = strtotime($task->deathline);
                        $class = "label";

                        if (date("d.m.yy", $timestamp) <= date("d.m.yy", time())) {
                            $class = "label label-danger";
                        }
                        ?>
                        <span class="<?php echo $class; ?>"><i
                                class="fa fa-clock-o"> </i> <?php echo date("d. M", $timestamp); ?></span>
                    <?php endif; ?>

                </small>

                <div class="user pull-right" style="display: inline;">
                    <!-- Show assigned user -->
                    <?php if (count($assignedUsers) != 0) : ?>
                        <?php foreach ($assignedUsers as $user): ?>
                            <a href="<?php echo $user->getProfileUrl(); ?>" id="user_<?php echo $task->id; ?>">
                                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt"
                                     height="24" width="24" alt="24x24" data-src="holder.js/24x24"
                                     style="width: 24px; height: 24px;" data-toggle="tooltip" data-placement="top"
                                     title=""
                                     data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->title; ?>">
                            </a>

                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
                <div class="clearfix"></div>
            </h5>
        </div>

        <?php $this->endContent(); ?>
    </div>
</div>

<!-- Show progress of the task -->
<!--    <?php /*if ($currentUserAssigned) { */?>
        <?php
/*        echo HHtml::dropDownList('percent_ddl_' . $task->id, $task->percent, array(
                '0' => '0%', '10' => '10%', '20' => '20%', '30' => '30%', '40' => '40%', '50' => '50%', '60' => '60%', '70' => '70%', '80' => '80%', '90' => '90%', '100' => '100%'), array('class' => 'percent_dropdown', 'id' => 'percent_ddl_' . $task->id)
        );
        */?>

    <?php /*} else { */?>
        <span class="label label-success"><?php /*echo $task->percent; */?>%</span>
    --><?php //} ?>



