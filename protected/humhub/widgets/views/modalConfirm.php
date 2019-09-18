<?php

use humhub\widgets\AjaxButton;

// add Tooltip to link
$tooltip = '';

if ($linkTooltipText != '') {
    $tooltip = 'data-placement="top" data-toggle="tooltip" data-original-title="' . $linkTooltipText . '"';
}

// replace by default the modal content by the new loaded content
$confirm = 'function(html){ $("#confirmModal_' . $uniqueID . '").html(html);}';

if ($confirmJS != '') {
    // ... or execute own JavaScript code, if exists
    $confirm = $confirmJS;
}

// Link to call the confirm modal
if ($linkOutput == 'button') {
?>

    <!-- create button element -->
    <button class="<?= $class; ?> <?= ($ariaLabel) ? ' aria-label="'.$ariaLabel.'"' : '' ?> <?php if ($tooltip != '') : ?>tt<?php endif; ?>" style="<?= $style; ?>"
            data-toggle="modal" data-target="#confirmModal_<?= $uniqueID; ?>" <?= $tooltip; ?>>
                <?= $linkContent; ?>
    </button>

<?php } elseif ($linkOutput == 'a') { ?>

    <!-- create normal link element -->
    <a id="deleteLinkPost_<?= $uniqueID; ?>" <?= ($ariaLabel) ? ' aria-label="'.$ariaLabel.'"' : '' ?> class="<?= $class; ?> <?php if ($tooltip != '') : ?>tt<?php endif; ?>" style="<?= $style; ?>" href="#"
       data-toggle="modal" data-target="#confirmModal_<?= $uniqueID; ?>" <?= $tooltip; ?>>
           <?= $linkContent; ?>
    </a>

<?php } ?>

<!-- start: Confirm modal -->
<div class="modal" id="confirmModal_<?= $uniqueID; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-extra-small animated pulse">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?= $title; ?></h4>
            </div>
            <div class="modal-body text-center">
                <?= $message; ?>
            </div>
            <div class="modal-footer">
                <?php if ($buttonTrue != '') { ?>
                    <?= AjaxButton::widget([
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
                <?php if ($buttonFalse != '') { ?>
                    <button type="button" class="btn btn-primary"
                            data-dismiss="modal"><?= $buttonFalse; ?></button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script <?= \humhub\libs\Html::nonce() ?>>

    $(function() {
        // Move modal to body
        $('#confirmModal_<?= $uniqueID; ?>').appendTo(document.body);
    });

    $('#confirmModal_<?= $uniqueID; ?>').on('shown.bs.modal', function (e) {

        // Execute optional JavaScript code, when modal is showing
        <?php if ($modalShownJS != '') {
            echo $modalShownJS;
        } ?>

        // Remove standard modal with
        $('#confirmModal_<?= $uniqueID; ?> .modal-dialog').attr('style', '');
    });

</script>
<!-- end: Confirm modal -->
