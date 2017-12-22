<!-- add Tooltip to link -->
<?php
$tooltip = "";

if ($linkTooltipText != "") {
    $tooltip = 'data-placement="top" data-toggle="tooltip" data-original-title="' . $linkTooltipText . '"';
}
?>

<?php
// replace by default the modal content by the new loaded content
$confirm = 'function(html){ $("#confirmModal_' . $uniqueID . '").html(html);}';

if ($confirmJS != "") {

    // ... or execute own JavaScript code, if exists
    $confirm = $confirmJS;
}
?>

<!-- Link to call the confirm modal -->
<?php if ($linkOutput == 'button') { ?>

    <!-- create button element -->
    <button class="<?php echo $class; ?> <?= ($ariaLabel) ? ' aria-label="'.$ariaLabel.'"' : '' ?> <?php if ($tooltip != "") : ?>tt<?php endif;?>" style="<?php echo $style; ?>"
            data-toggle="modal" data-target="#confirmModal_<?php echo $uniqueID; ?>" <?php echo $tooltip; ?>>
                <?php echo $linkContent; ?>
    </button>

<?php } else if ($linkOutput == 'a') { ?>

    <!-- create normal link element -->
    <a id="deleteLinkPost_<?php echo $uniqueID; ?>" <?= ($ariaLabel) ? ' aria-label="'.$ariaLabel.'"' : '' ?> class="<?php echo $class; ?> <?php if ($tooltip != "") : ?>tt<?php endif;?>" style="<?php echo $style; ?>" href="#"
       data-toggle="modal" data-target="#confirmModal_<?php echo $uniqueID; ?>" <?php echo $tooltip; ?>>
           <?php echo $linkContent; ?>
    </a>

<?php } ?>

<!-- start: Confirm modal -->
<div class="modal" id="confirmModal_<?php echo $uniqueID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-extra-small animated pulse">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?></h4>
            </div>
            <div class="modal-body text-center">
                <?php echo $message; ?>
            </div>
            <div class="modal-footer">
                <?php if ($buttonTrue != "") { ?>

                    <?php
                    echo \humhub\widgets\AjaxButton::widget([
                        'label' => $buttonTrue,
                        'ajaxOptions' => [
                            'type' => 'POST',
                            'success' => $confirm,
                            'url' => $linkHref,
                        ],
                        'htmlOptions' => [
                            'return' => 'true',
                            'class' => 'btn btn-primary modalConfirm',
                            'data-dismiss' => 'modal'
                        ]
                    ]);
                    ?>
                <?php } ?>
                <?php if ($buttonFalse != "") { ?>
                    <button type="button" class="btn btn-primary"
                            data-dismiss="modal"><?php echo $buttonFalse; ?></button>
                        <?php } ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        // move modal to body
        $('#confirmModal_<?php echo $uniqueID; ?>').appendTo(document.body);



    });


    $('#confirmModal_<?php echo $uniqueID; ?>').on('shown.bs.modal', function (e) {

        // execute optional JavaScript code, when modal is showing
<?php
if ($modalShownJS != "") {
    echo $modalShownJS;
}
?>

        // remove standard modal with
        $('#confirmModal_<?php echo $uniqueID; ?> .modal-dialog').attr('style', '');
    })


</script>
<!-- end: Confirm modal -->