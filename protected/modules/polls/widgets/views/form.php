<?php
/**
 * This view shows the form to create new polls.
 * Used by PollFormWidget.
 *
 * @property Space $space the current space
 *
 * @package humhub.modules.polls.widgets.views
 * @since 0.5
 */
?>
<div class="panel panel-default">

    <div class="panel-body">
        <?php

        echo CHtml::form(Yii::app()->createUrl('polls/poll/create', array('guid' => $space->guid, 'ajax' => 1)), 'post', array(
            'id' => 'poll_addform'
        ));

        ?>
        <?php echo CHtml::textArea("question", "", array('id' => "pollFrom_messageField", 'class' => 'form-control autosize', 'rows' => '1', "tabindex" => "1", "placeholder" => Yii::t('PollsModule.base', "Ask something..."))); ?>


        <div id="pollForm_more">
            <hr>
            <?php echo CHtml::textArea("answers", "", array('id' => "pollFrom_answersField", 'rows' => '5', "class" => "form-control", "tabindex" => "2", "placeholder" => Yii::t('PollsModule.base', "Possible answers (one per line)"))); ?>

            <?php if ($space->canShare()): ?>
            <div class="checkbox">
                <label>
                    <?php echo CHtml::checkbox("public", "", array('class' => 'checkbox tick', "tabindex" => "3")); ?> <?php echo Yii::t('PollsModule.base', 'This is a public poll (also non-members)'); ?>
                </label>
            </div>
            <?php endif; ?>

            <div class="checkbox">
                <label>
                    <?php echo CHtml::checkbox("allowMultiple", "", array('class' => 'checkbox tick', "tabindex" => "4")); ?> <?php echo Yii::t('PollsModule.base', 'Allow multiple answers per user?'); ?>
                </label>
            </div>

            <hr>

            <?php
            echo CHtml::ajaxButton('Ask', array('/polls/poll/create', 'guid' => $space->guid, 'ajax' => 1), array(
                'type' => 'POST',
                'success' => 'function(response){
			json = jQuery.parseJSON(response);
			currentStream.prependEntry(json.wallEntryId);

			// Clear Form
            $("#pollFrom_messageField").val("");
            $("#pollFrom_messageField").css("height", "30px");
            $("#pollFrom_answersField").val("");
            $("#public").attr("checked", false);
            $("#allowMultiple").attr("checked", false);
            jQuery("#pollForm_more").hide();
		}',
            ), array('class' => 'btn btn-info', "tabindex" => "5"));

            ?>
        </div>

        <?php echo CHtml::endForm(); ?>
    </div>
</div>


<div class="clearFloats"></div>

<script type="text/javascript">

    jQuery('#pollForm_more').hide();

    // active autorizing for the comment textfield
    //jQuery('#questionFrom_messageField').autosize();

    // Remove info text from the textinput
    jQuery('#pollFrom_messageField').click(function () {

        jQuery('#pollForm_more').show();
        resetTextfield(jQuery(this));

    });
    jQuery('#pollFrom_answersField').click(function () {
        resetTextfield(jQuery(this));
    });

    jQuery('#pollFrom_answersField').focus(function () {
        resetTextfield(jQuery(this));
    });


    function resetTextfield($this) {
        // Change textinput content just at the first click
        if (jQuery($this).attr('alt') != "ready") {

            // Change textfield color
            jQuery($this).css('color', '#3e3e3e');

            // Save, that the first click is done in the attribute
            jQuery($this).attr('alt', 'ready');

            // remove the placeholder text
            jQuery($this).val('');

        }
    }

    // add autosize function to input
    $('.autosize').autosize();

</script>