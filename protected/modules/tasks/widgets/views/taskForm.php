<?php
// load js for datepicker component
Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_BEGIN
);
?>

<?php echo CHtml::textArea("title", "", array('id' => 'contentForm_title', 'class' => 'form-control autosize contentForm', 'rows' => '1', "tabindex" => "1", "placeholder" => Yii::t('PollsModule.base', "Ask something..."))); ?>

<div class="contentForm_options">

    <?php echo CHtml::textField('preassignedUsers', ''); ?>
    <?php
    $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
        'inputId' => 'preassignedUsers',
        'userSearchUrl' => $this->createUrl('//space/space/searchMemberJson', array('sguid' => $contentContainer->guid, 'keyword' => '-keywordPlaceholder-')),
        'maxUsers' => 10,
    ));
    ?>

    <?php echo CHtml::hiddenField('deathline', '', array('class' => '', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => Yii::t('TaskModule.base', 'Deadline?'))); ?>
    <div class="form-group">
        <input class="form-control contentForm" id="dateinput" size="16" type="text" value="" readonly=""
               placeholder="Deadline">
    </div>    

    <?php echo CHtml::hiddenField('max_user', 1); ?>

</div>


<script type="text/javascript">
    $(function() {
        $('#dateinput').datepicker({
            format: 'dd.mm.yyyy',
            weekStart: 1
        }).on('changeDate', function(event) {

            // hide calendar
            $('.datepicker').hide();

            // formating date
            var _date = event.date.getFullYear() + "-" + (event.date.getMonth() + 1) + "-" + event.date.getDate();

            // set date to form input
            $('#deathline').val(_date);
        });
    })
</script>