<?php
// load js for datepicker component
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_BEGIN
);
?>

<div class="panel panel-default">
    <div class="panel-body">
        <?php echo CHtml::form(Yii::app()->createUrl('tasks/task/create', array('guid' => $workspace->guid, 'ajax' => 1)), 'post', array('id' => 'task_addform')); ?>
        <?php echo CHtml::textArea("todo", "", array('id' => "taskFrom_todoField", 'class' => 'form-control autosize', 'rows' => '1', 'placeholder' => 'What is to do?', 'tabindex' => '1')); ?>
        <?php echo CHtml::hiddenField("fileList", '', array('id' => "taskFrom_files")); ?>

        <div id="taskForm_more">
            <br/>
                <?php echo CHtml::textField('preAssignedUsers', ''); ?>

                <?php
                $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                    'inputId' => 'preAssignedUsers',
                    'userSearchUrl' => $this->createUrl('//space/space/searchMemberJson', array('sguid' => $workspace->guid)),
                    'maxUsers' => 10,
                ));
                ?>

            <!--            <div class="input-append date" id="dateinput" data-date="" data-date-format="dd.mm.yyyy">
                            <input class="form-control" size="16" type="text" value="" readonly="" placeholder="Deadline">
                            <span class="add-on"><i class="icon-calendar"></i></span>
                        </div>-->
            <div class="form-group">
                <input class="form-control" id="dateinput" size="16" type="text" value="" readonly=""
                       placeholder="Deadline">
            </div>


            <?php //echo CHtml::textField('deathline', '', array('class' => 'span3', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => Yii::t('TaskModule.base', 'Deadline?')));  ?>
            <?php echo CHtml::hiddenField('deathline', '', array('class' => '', 'data-date-format' => 'dd.mm.yyyy', 'placeholder' => Yii::t('TaskModule.base', 'Deadline?'))); ?>

            <?php echo CHtml::hiddenField('max_user', 1); ?>

            <?php if ($workspace->canShare()): ?>
                <div class="checkbox">
                    <label>
                        <?php echo CHtml::checkbox("public", "", array('class' => 'checkbox')); ?> <?php echo Yii::t('TaskModule.base', 'This is a public task (also non-members)'); ?>
                    </label>
                </div>
            <?php endif; ?>


            <div class="clearfix"></div>
            <hr>

            <?php
            echo CHtml::ajaxButton('Create', array('/tasks/task/create', 'guid' => $workspace->guid, 'ajax' => 1), array(
                'type' => 'POST',
                'success' => 'function(response){
			json = jQuery.parseJSON(response);
			if (json.success) {
				currentStream.prependEntry(json.wallEntryId);

                // Clear Form
                $("#taskFrom_todoField").val("");
                $("#taskFrom_todoField").css("height", "30px");
                $("#dateinput input").val("");
                $("#invite_tags .userInput").remove();
                $("#public").attr("checked", false);
                $("#taskForm_more").hide();
			} else {
				alert(json.error);
                }
		}',
            ), array('class' => 'btn btn-info', 'id' => 'task_send_button', 'tabindex' => '4'));
            ?>

            <?php
            // Creates Uploading Button
            $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
                'uploaderId' => 'taskFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'taskFrom_files', // Hidden field to store uploaded files
            ));
            ?>
            <br/>
        </div>

        <?php echo CHtml::endForm(); ?>

        <?php
        // Creates a list of already uploaded Files
        $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
            'uploaderId' => 'taskFormFiles', // Unique ID of Uploader Instance
            'bindToFormFieldId' => 'taskFrom_files', // Hidden field to store uploaded files
        ));
        ?>

    </div>
</div>


<script type="text/javascript">

    jQuery('#taskForm_more').hide();

    jQuery('#taskFrom_todoField').click(function () {
        jQuery('#taskForm_more').show();

    });

    $('#taskFrom_todoField').keydown(function (event) {
        if (event.keyCode == 9) {
            event.preventDefault();
            $('#tag_input_field').focus();
        }
    })


    /*    var dateinput = $('#dateinput').datepicker({
     format: 'dd.mm.yyyy',
     onRender: function (date) {
     }
     }).on('changeDate',function (event) {

     //alert(event.date);

     // hide calendar
     dateinput.hide();

     // formating date
     var _date = event.date.getFullYear() + "-" + (event.date.getMonth() + 1) + "-" + event.date.getDate();

     // set date to form input
     $('#deathline').val(_date);

     }).data('datepicker')


     $('#dateinput').datepicker('setStartDate', '01.11.2013');*/


    $(function () {
        $('#dateinput').datepicker({
            format: 'dd.mm.yyyy',
            weekStart: 1
        }).on('changeDate', function (event) {


                /*            var date = new Date(event.date.valueOf());

                 var _day = date.getDate();
                 var _month = date.getMonth() + 1;

                 if (_month < 10) {
                 _month = "0" + _month;
                 }

                 var _year = date.getFullYear();

                 //alert(_year + _month + _day);
                 //alert(_url + "/" + event.date.valueOf());
                 window.location.href = _url + "/" + _year + _month + _day;*/

                // hide calendar
                $('.datepicker').hide();

                // formating date
                var _date = event.date.getFullYear() + "-" + (event.date.getMonth() + 1) + "-" + event.date.getDate();

                // set date to form input
                $('#deathline').val(_date);
            });


    })


    // add autosize function to input
    $('.autosize').autosize();

</script>