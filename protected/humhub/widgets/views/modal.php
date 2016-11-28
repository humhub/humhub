<div id="<?= $id ?>" <?= $modalData ?> class="modal" tabindex="-1" role="dialog" aria-hidden="true">
    <?= \humhub\widgets\ModalDialog::widget([
            'header' => $header,
            'body' => $body,
            'footer' => $footer,
            'initialLoader' => $initialLoader
    ]); ?>   
</div>