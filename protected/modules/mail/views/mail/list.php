<?php $count = 0; ?>
<?php foreach ($userMessages as $userMessage) : ?>

    <?php
    $message = $userMessage->message;
    $users = $message->users;
    ?>

    <?php if ($message->getLastEntry() != null) : ?>

        <li class="entry <?php if ($message->updated_at > $userMessage->last_viewed) : ?>new<?php endif; ?>">
            <a href="<?php echo $this->createUrl('index', array('id' => $message->id)); ?>">
                <div class="media">

                    <!-- show user image -->
                    <img class="media-object img-rounded pull-left"
                         data-src="holder.js/32x32" alt="32x32"
                         style="width: 32px; height: 32px;"
                         src="<?php echo $message->getLastEntry()->user->getProfileImage()->getUrl(); ?>">

                    <!-- show content -->
                    <div class="media-body">
                        <strong><?php echo $message->getLastEntry()->user->displayName; ?>
                            <small>(<?php echo count($users) . ' recipients'; ?>)</small>
                        </strong>
                        <br>
                        <h5><?php print Helpers::truncateText($message->title, 35); ?></h5>
                        <?php $text = strip_tags($message->getLastEntry()->content); ?>
                        <?php echo Helpers::truncateText($text, 200); ?>
                        <br><span class="time"
                                  title="<?php echo $message->updated_at; ?>"><?php echo $message->updated_at; ?></span>
                        <?php if ($message->updated_at > $userMessage->last_viewed) : ?> <span
                            class="label label-danger">New</span><?php endif; ?>
                    </div>

                </div>
            </a>
        </li>

        <?php $count++; ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php
if ($count == 0) {
    echo '<li class="placeholder">' . Yii::t('MailModule.base', 'There are no messages yet.') . '</li>';
}
?>

<script type="text/javascript">
    jQuery("span.time").timeago();
    jQuery("#filter_message_input").focus();
</script>