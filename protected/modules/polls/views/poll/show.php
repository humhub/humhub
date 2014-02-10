<?php
/**
 * This view is shown when a user clicks on the "Polls" Navigation Items in the
 * Space Navigation.
 *
 * Its shows an FormWidget to create a new poll and a stream widget which shows
 * all existing polls.
 *
 * @package humhub.modules.polls.views
 * @since 0.5
 */
?>
<?php $this->widget('application.modules.polls.widgets.PollFormWidget', array('space' => $this->getSpace())); ?>
<?php $this->widget('application.modules.polls.widgets.PollsStreamWidget', array('type' => Wall::TYPE_SPACE, 'guid' => $this->getSpace()->guid)); ?>
