<?php
/**
 * This view represents a wall entry of a polls.
 * Used by PollWallEntryWidget to show Poll inside a wall.
 *
 * @property User $user the user which created this poll
 * @property Poll $poll the current poll
 * @property Space $space the current space
 *
 * @package humhub.modules.polls.widgets.views
 * @since 0.5
 */
?>
<div class="panel panel-default">
    <div class="panel-body">

        <?php $this->beginContent('application.modules_core.wall.views.wallLayout', array('object' => $poll)); ?>


        <?php echo CHtml::beginForm(); ?>

        <?php print nl2br($poll->question); ?><br><br>

        <!-- Loop and Show Answers -->
        <?php foreach ($poll->answers as $answer): ?>

            <div class="row">
                <?php if (!$poll->hasUserVoted()) : ?>
                    <div class="col-md-1">
                        <?php if ($poll->allow_multiple) : ?>
                            <?php echo CHtml::checkBox('answers[' . $answer->id . ']'); ?>
                        <?php else: ?>
                            <?php echo CHtml::radioButton('answers', false, array('value' => $answer->id)); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                $percent = round($answer->getPercent());
                $color = "progress-bar-info";
                ?>

                <div class="col-md-6">
                    <b><?php echo $answer->answer; ?></b><br>

                    <div class="progress">
                        <div id="progress_<?php echo $answer->id; ?>" class="progress-bar <?php echo $color; ?>" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                    </div>
                    <script type="text/javascript">
                        $('#progress_<?php echo $answer->id; ?>').css('width', '<?php echo $percent; ?>%');
                    </script>
                </div>

                <div class="col-md-4">

                    <?php
                    $userlist = ""; // variable for users output
                    $maxUser = 10; // limit for rendered users inside the tooltip


                    for ($i = 0; $i < count($answer->votes); $i++) {

                        // if only one user likes
                        // check if exists more user as limited
                        if ($i == $maxUser) {
                            // output with the number of not rendered users
                            $userlist .= Yii::t('PollsModule.base', 'and {count} more vote for this.', array('{count}' => (intval(count($answer->votes) - $maxUser))));

                            // stop the loop
                            break;
                        } else {
                            $userlist .= "<strong>" . $answer->votes[$i]->user->displayName . "</strong><br>";
                        }
                    }
                    ?>
                    <p style="margin-top: 14px;">
                        <?php if (count($answer->votes) > 0) { ?>
                            <a href="<?php echo $this->createUrl('//polls/poll/userListResults', array('pollId' => $poll->id, 'answerId' => $answer->id)); ?>"
                               class="tt" data-toggle="modal"
                               data-placement="top" title="" data-target="#globalModal"
                               data-original-title="<?php echo $userlist; ?>"><?php echo count($answer->votes) . " " . Yii::t('PollsModule.base', 'votes'); ?></a>


                        <?php } else { ?>
                            0 <?php echo Yii::t('PollsModule.base', 'votes'); ?>
                        <?php } ?>
                    </p>

                </div>


            </div>
            <div class="clearFloats"></div>
        <?php endforeach; ?>


        <?php if (!$poll->hasUserVoted()) : ?>
            <br>
            <?php
            $voteUrl = CHtml::normalizeUrl(array('/polls/poll/answer', 'sguid' => $space->guid, 'pollId' => $poll->id));
            echo HHtml::ajaxSubmitButton(Yii::t('PollsModule.base', 'Vote'), $voteUrl, array(
                    'dataType' => 'json',
                    'success' => "function(json) {  $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output)); }",
                ), array('id' => "PollAnswerButton_" . $poll->id, 'class' => 'btn btn-primary')
            );
            ?>
            <br>
        <?php endif; ?>

        <div class="clearFloats"></div>

        <?php echo CHtml::endForm(); ?>

        <?php if ($poll->hasUserVoted()) : ?>
            <br>
            <?php
            $voteUrl = CHtml::normalizeUrl(array('/polls/poll/answerReset', 'sguid' => $space->guid, 'pollId' => $poll->id));
            echo HHtml::ajaxLink(Yii::t('PollsModule.base', 'Reset my vote'), $voteUrl, array(
                    'dataType' => 'json',
                    'success' => "function(json) { $('#wallEntry_'+json.wallEntryId).html(parseHtml(json.output)); }",
                ), array('id' => "PollAnswerResetButton_" . $poll->id, 'class' => 'btn btn-danger')
            );
            ?>
            <br>
        <?php endif; ?>


        <?php $this->endContent(); ?>

    </div>

</div>

<script type="text/javascript">

    // show Tooltips on elements inside the views, which have the class 'tt'
    $('.tt').tooltip({html: true});

</script>




