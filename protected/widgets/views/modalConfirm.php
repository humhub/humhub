<!-- Link to call the confirm modal -->
<button class="<?php echo $class; ?>" data-toggle="modal" data-target="#confirmModal_<?php echo $uniqueID; ?>" data-placement="top" data-original-title="<?php echo $linkTooltipText; ?>">
    <?php echo $linkContent; ?>
</button>

<!-- start: Confirm modal -->
<div class="modal" id="confirmModal_<?php echo $uniqueID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <?php echo $message; ?>
            </div>
            <div class="modal-footer">

                <?php echo HHtml::ajaxButton($buttonTrue, $linkHref, array(
                    'type' => 'POST',
                    'data' => array(Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
                    'success' => 'function(html){ $("#confirmModal_'. $uniqueID .'").html(html);}',
                ), array('class' => 'btn btn-danger'));
                ?>

                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $buttonFalse; ?></button>
            </div>
        </div>
    </div>
</div>
<!-- end: Confirm modal -->