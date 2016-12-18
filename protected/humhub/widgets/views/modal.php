<?= yii\helpers\Html::beginTag('div', $options) ?>

    <?= \humhub\widgets\ModalDialog::widget([
            'header' => $header,
            'animation' => $animation,
            'size' => $size,
            'centerText' => $centerText,
            'body' => $body,
            'footer' => $footer,
            'initialLoader' => $initialLoader
    ]); ?>   

<?= yii\helpers\Html::endTag('div') ?>