<?php

/**
 * @var \humhub\components\View $this
 * @var string $uploadProgress
 * @var string $uploadPreview
 * @var string $hiddenInput
 * @var string $uploadInput
 */

use humhub\widgets\bootstrap\Button;

?>

<div class="d-flex justify-content-between">
    <div class="align-self-start">
        <?= $uploadPreview ?>
    </div>
    <div class="align-self-end">
        <?= Button::danger()
            ->sm()
            ->icon('times')
            ->cssClass(['img-uploader-remove', 'd-none'])
            ->action('delete')
            ->loader(false) ?>
        <?= Button::accent()
            ->sm()
            ->icon('cloud-upload ')
            ->cssClass(['img-uploader-upload'])
            ->action('upload')
            ->loader(false) ?>
    </div>
</div>
<?= $uploadProgress ?>
<?= $hiddenInput ?>
<?= $uploadInput ?>
