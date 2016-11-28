<div id="<?= $id ?>" <?= $modalData ?> class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <?= \humhub\widgets\ModalDialog::widget([
            'header' => $header,
            'animation' => $animation,
            'size' => $size,
            'centerText' => $centerText,
            'body' => $body,
            'footer' => $footer,
            'initialLoader' => $initialLoader
    ]); ?>   
</div>