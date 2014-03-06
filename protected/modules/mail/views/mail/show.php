<div id="message_details">
    <div class="panel panel-default">

        <?php if ($message == null) { ?>

            <div class="panel-body">
                <?php echo Yii::t('MailModule.base', 'There are no messages yet.'); ?>
            </div>
        <?php } else { ?>


        <!--    <a href="--><?php //echo $this->createUrl('//mail/mail/create') ?><!--" class="btn btn-primary"><i-->
        <!--            class="icon-plus icon-white"></i> -->
        <?php //echo Yii::t('MailModule.base', "Write new message"); ?><!--</a>-->

        <div class="panel-heading">
            <?php echo $message->title; ?>

            <div class="pull-right">
                <?php if (count($message->users) != 0) : ?>
                    <?php foreach ($message->users as $user) : ?>
                        <?php $uniqID = uniqid(); ?>
                        <a href="<?php echo $user->getProfileUrl(); ?>">
                            <img src="<?php echo $user->getProfileImage()->getUrl(); ?>"
                                 class="img-rounded tt img_margin" height="29" width="29" data-toggle="tooltip"
                                 data-placement="top" title=""
                                 data-original-title="<h1><?php echo $user->displayName; ?></h1><?php echo $user->title; ?>">
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>


        <div class="panel-body">

            <hr style="margin-top: 0;">

            <ul class="media-list">
                <!-- BEGIN: Results -->
                <?php foreach ($message->entries as $entry) : ?>



                    <div class="media" style="margin-top: 0;">
                        <a class="pull-left" href="#">
                            <img class="media-object img-rounded"
                                 src="<?php echo $entry->user->getProfileImage()->getUrl(); ?>"
                                 data-src="holder.js/50x50" alt="50x50" style="width: 50px; height: 50px;">
                        </a>

                        <div class="media-body">
                            <h4 class="media-heading" style="font-size: 14px;"><?php echo $entry->user->displayName; ?>
                                <small><span class="time"><?php echo $entry->created_at; ?></span></small>
                            </h4>
                            <br>
                            <span class="content"><?php echo $entry->content; ?></span>
                        </div>
                    </div>

                    <hr>

                <?php endforeach; ?>

            </ul>
            <!-- END: Results -->


            <div class="row-fluid">

                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'reply-message-form',
                    'enableAjaxValidation' => false,
                ));
                ?>


                <?php //echo $form->errorSummary($replyForm); ?>
                <div class="form-group">
                    <?php echo $form->textArea($replyForm, 'message', array('class' => 'form-control', 'rows' => '4', 'placeholder' => 'Write an answer...')); ?>
                </div>
                <hr>

                <?php echo CHtml::submitButton(Yii::t('MailModule.base', 'Send'), array('class' => 'btn btn-primary')); ?>


                <div class="pull-right">

                    <!-- Button to trigger modal to add user to conversation -->
                    <?php
                    echo CHtml::link('<i class="icon-plus"></i> ' . Yii::t('MailModule.base', 'Add user'), $this->createUrl('//mail/mail/adduser', array('id' => $message->id, 'ajax' => 1)), array('class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
                    ?>

                    <?php if (count($message->users) > 2 && $message->originator->id != Yii::app()->user->id): ?>
                        <a class="btn btn-danger"
                           href="<?php echo $this->createUrl('leave', array('id' => $message->id)); ?>"><i
                                class="icon-signout"></i> <?php echo Yii::t('MailModule.base', "Leave discussion"); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>
            <?php } ?>

        </div>
    </div>


    <script type="text/javascript">

        // show Tooltips on elements inside the views, which have the class 'tt'
        //$('.tt').tooltip();

    </script>