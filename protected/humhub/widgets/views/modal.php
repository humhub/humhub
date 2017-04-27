<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
?>

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