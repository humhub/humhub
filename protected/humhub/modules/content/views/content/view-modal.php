<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use humhub\modules\content\widgets\PinLink;
use humhub\modules\stream\assets\StreamAsset;
use humhub\modules\stream\actions\Stream;

/* @var $this \humhub\components\View */
/* @var $entry humhub\modules\content\components\ContentActiveRecord */
?>
<?php StreamAsset::register($this); ?>

<?php ModalDialog::begin(['size' => 'large', 'closable' => true]); ?>

<div class="modal-body" style="padding-bottom:0px">

    <div data-action-component="stream.SimpleStream">
        <?=
        Stream::renderEntry($entry, [
            'stream' => false,
            'controlsOptions' => [
                'prevent' => [PinLink::class]
            ]
        ])
        ?>
    </div>
</div>
<div class="modal-footer">
    <?= ModalButton::cancel(Yii::t('base', 'Close')) ?>
</div>
<?php ModalDialog::end(); ?>
