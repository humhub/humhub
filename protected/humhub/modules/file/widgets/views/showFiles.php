<?php

use yii\helpers\Html;
use humhub\modules\file\libs\FileHelper;
use humhub\libs\Helpers;

$object = $this->context->object;
?>

<?php if (count($files) > 0) : ?>
<!-- hideOnEdit mandatory since 1.2 -->
<div class="hideOnEdit">
    <!-- Show Images as Thumbnails -->
    <div class="post-files" id="post-files-<?php echo $object->getUniqueId(); ?>">
        <?php foreach ($files as $file): ?>
            <?php if ($previewImage->applyFile($file)): ?>
                <a data-ui-gallery="<?= "gallery-" . $object->getUniqueId(); ?>"  href="<?= $file->getUrl(); ?>#.jpeg">
                    <?= $previewImage->render(); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($files as $file): ?>
            <?php $fileExtension = FileHelper::getExtension($file->file_name); ?>
            <?php if ($fileExtension == "mp3") : ?>
                <!-- Integrate jPlayer -->
                <?= xj\jplayer\AudioWidget::widget(array(
                    'id' => $file->id,
                    'mediaOptions' => [
                        'mp3' => $file->getUrl(),
                        'title' =>  Html::encode(Helpers::trimText($file->file_name, 40))
                    ],
                    'jsOptions' => [
                        'smoothPlayBar' => true
                    ]
                ))?>
            <?php endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- Show List of all files -->
    <hr>
    <?= \humhub\modules\file\widgets\FilePreview::widget([
        'hideImageFileInfo' => $hideImageFileInfo,
        'model' => $object,
    ]);?>
    
</div>
<?php endif; ?>

