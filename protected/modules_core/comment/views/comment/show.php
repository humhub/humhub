
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel">
                <?php echo Yii::t('CommentModule.base', 'Comments'); ?>
            </h4>
        </div>


        <div id="userlist-content">
            <div class="well well-small" id="comment_<?php echo $id; ?>">
                <div class="comment" id="comments_area_<?php echo $id; ?>">
                    <?php echo $output; ?>
                </div>          
                <?php $this->widget('application.modules_core.comment.widgets.CommentFormWidget', array('object' => $object)); ?>
            </div>

        </div>

    </div>

    <script type="text/javascript">

        /*
         * Modal handling by close event
         */
        $('#globalModal').on('hidden.bs.modal', function(e) {

            // Reload whole page (to see changes on it)
            //window.location.reload();

            // just close modal and reset modal content to default (shows the loader)
            $('#globalModal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
        })
    </script>

    <script type="text/javascript">

        // scroll to top of list
        $(".modal-body").animate({scrollTop: 0}, 200);

    </script>


