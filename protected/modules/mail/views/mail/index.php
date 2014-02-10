<?php //echo Yii::t('MailModule.base', 'Messagebox'); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('MailModule.base', 'Inputbox') ?>

        <!-- Button to trigger modal write a new message -->
        <?php
        echo CHtml::link(Yii::t('MailModule.base', 'New message'), $this->createUrl('//mail/mail/create', array('ajax' => 1)), array('class' => 'btn btn-info pull-right', 'data-toggle' => 'modal', 'data-target' => '#globalModal'));
        ?>

    </div>

<hr>

    <ul class="media-list">

        <?php $count = 0; ?>
        <?php $firstMessageId = 0; ?>
        <?php foreach ($userMessages as $userMessage) : ?>

            <?php
            $message = $userMessage->message;
            $users = $message->users;

            if ($count == 0) {
                // save id from the first message
                $firstMessageId = $message->id;
            }
            ?>

            <?php if ($message->getLastEntry() != null) : ?>

                <li id="message_<?php echo $message->id; ?>"
                    class="entry <?php if ($message->updated_at > $userMessage->last_viewed) {
                        echo 'new';
                    } ?>">

                    <a href="javascript:showMessageDetails('<?php echo $this->createUrl("show", array("id" => $message->id, "ajax" => 1)); ?>', '<?php echo $message->id; ?>');">
                        <div class="media">
                            <!-- Show user image -->
                            <img class="media-object img-rounded pull-left" data-src="holder.js/32x32" alt="32x32"
                                 style="width: 32px; height: 32px;"
                                 src="<?php echo $message->getLastEntry()->user->getProfileImage()->getUrl(); ?>">


                            <div class="media-body">

                                <h4 class="media-heading"><?php echo $message->getLastEntry()->user->displayName; ?> <small><span class="time"><?php echo $message->updated_at; ?></span></small></h4>
                                <!--<i class="icon-paper-clip"></i> <i class="icon-star"></i>-->

                                <h5><?php print Helpers::truncateText($message->title, 75); ?></h5>

                                <?php $text = strip_tags($message->getLastEntry()->content);  ?>
                                <?php echo Helpers::truncateText($text, 200); ?>
                                <!--<span class="badge pull-right"><?php /*echo count($message->entries); */?></span>-->
                                <?php
                                // show the new badge, if this message is still unread
                                if ($message->updated_at > $userMessage->last_viewed) {
                                    echo '<span class="label label-danger">' . Yii::t('MailModule.base', 'New') . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </a>
                </li>

                <?php $count++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php
        if ($count == 0) {
            echo '<br><div class="placeholder_text"><br>' . Yii::t('MailModule.base', 'There are no messages yet.') . '</div>';
        }
        ?>

    </ul>

</div>

<div class="pagination-container">
    <?php
    $this->widget('CLinkPager', array(
        'currentPage' => $pages->getCurrentPage(),
        'itemCount' => $mailCount,
        'pageSize' => $pageSize,
        'maxButtonCount' => 5,
        'nextPageLabel' => '<i class="icon-step-forward"></i>',
        'prevPageLabel' => '<i class="icon-step-backward"></i>',
        'firstPageLabel' => '<i class="icon-fast-backward"></i>',
        'lastPageLabel' => '<i class="icon-fast-forward"></i>',
        'header' => '',
        'htmlOptions' => array('class' => 'pagination'),
    ));
    ?>
</div>

<script type="text/javascript">

    <?php if (isset($_GET['id'])) { ?>
    // load message details by the committed id
    showMessageDetails('<?php echo $this->createUrl('show', array('id' => $_GET['id'], 'ajax' => 1)); ?>');
    selectEntry(<?php echo $_GET['id']; ?>);
    <?php } else { ?>
    // load the latest message details
    showMessageDetails('<?php echo $this->createUrl('show', array('id' => $firstMessageId, 'ajax' => 1)); ?>');
    selectEntry(<?php echo $firstMessageId; ?>);
    <?php } ?>

    function showMessageDetails(url, id) {

        // load message details
        $.ajax({
            'type': 'GET',
            'url': url,
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'beforeSend': function () {
                jQuery("#message_details").replaceWith('<div id="message_details"><div class="loader"></div></div>');
                $('.entry').removeClass('active');
            },
            'success': function (html) {
                jQuery("#message_details").replaceWith(html)
                selectEntry(id);
            }});


    }

    function selectEntry(id) {
        $('.messages').find('#message_' + id).addClass('active');
    }

</script>

