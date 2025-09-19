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

<?= $uploadPreview ?>
<div class="img-uploader-field-buttons">
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
<?= $uploadProgress ?>
<?= $hiddenInput ?>
<?= $uploadInput ?>

